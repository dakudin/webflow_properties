<?php
/**
 * Created by Kudin Dmitry
 * Date: 25.12.2018
 * Time: 23:10
 */

namespace app\components\oneagency;

use Yii;
use yii\helpers\StringHelper;
use app\models\Property;
use app\components\WFWorkerBase;
use app\components\WebFlowCollection;

/**
 * Class provide interface for inserting and updating properties via WebFlow API.
 *
 * @author Kudin Dmitry <dakudin@gmail.com>
 */
class WFPropertyWorker extends WFWorkerBase
{
    const SHORT_DESCRIPTION_LENGTH = 230;

    const SHORT_DESCRIPTION_MOBILE_LENGTH = 100;

    const MAX_IMAGE_WIDTH = 1200;

    /**
     * slug for field 'In feed'
     * if filed is true that property exists in feed, false otherwise
     */
    const FIELD_IN_FEED_SLUG = 'in-feed-3';

    /**
     * @var string WebFlow collection name of Role types
     */
    protected $_roleTypeCollectionName;

    /**
     * @var string WebFlow collection name of properties
     */
    protected $_propertyCollectionName;

    /**
     * @var string WebFlow collection name of property statuses
     */
    protected $_propertyStatusCollectionName;

    /**
     * @var WebFlowCollection WebFlow collection of Role types
     */
    protected $_roleTypeCollection;

    /**
     * @var WebFlowCollection WebFlow collection of properties
     */
    protected $_propertyCollection;

    /**
     * @var WebFlowCollection WebFlow collection of property statuses
     */
    protected $_propertyStatusCollection;

    /**
     * @var WebFlowStatuses WebFlow ids of statuses and role types
     */
    protected $webFlowStatuses;

    /**
     * @var int Number of attempts for storing properties
     * If first attempt was wrong it detect images which didn't store, resize them and try to store again
     */
    protected $_attemptCount = 2;

    /**
     * @param array $apiKey
     * @param $roleTypeCollectionName
     * @param $propertyCollectionName
     * @param $propertyStatusCollectionName
     * @param $publishToLiveSite
     * @throws \Exception
     */
    public function __construct($apiKey, $roleTypeCollectionName, $propertyCollectionName,
                                $propertyStatusCollectionName, $publishToLiveSite)
    {
        parent::__construct($apiKey, $publishToLiveSite);

        $this->_roleTypeCollectionName = $roleTypeCollectionName;
        $this->_propertyCollectionName = $propertyCollectionName;
        $this->_propertyStatusCollectionName = $propertyStatusCollectionName;

        if(!$this->prepareWFClient()){
            throw new \Exception('Error - cannot prepare WebFlow client');
        }
    }

    /**
     * Get all old items from WebFlow collection. Store properties id for detecting which ones need to delete
     * after inserting/updating
     * @return bool
     */
    public function loadAllProperties()
    {
        $this->_wfItems = [];
        $offset = 0;

        do {
            if(!$this->_propertyCollection->loadItems($this->_apiKey, $this->_itemsPerPage, $offset))
                return false;

            foreach($this->_propertyCollection->getItems() as $item){
                $this->_wfItems[$item['propertyid-2']] = [
                    'id' => $item['_id'],
                    'flagUpdated' => false,
                ];
            }

            $offset += $this->_itemsPerPage;
        } while ($this->_propertyCollection->getItemsTotal()>0
            && $this->_propertyCollection->getItemsTotal() > $this->_propertyCollection->getItemsOffset() + $this->_propertyCollection->getItemsCount());

        echo "WebFlow properties count before update: " . count($this->_wfItems) . "\r\n";

        return true;
    }

    /**
     * Store item as property in WebFlow collection (detect if it need to insert or update)
     * @param Property $property
     * @return bool
     */
    public function storeProperty(Property $property)
    {
        $dezrezPropertyId = (string)$property->id;
        $item = $this->fillProperty($property, $dezrezPropertyId);
        $imagesToUpload = $this->countImagesForUpload($property, $item);

        $success = false;
        $isInserted = false;
        //try to update/insert WebFlow item
        //if fault then try again
        for($i=1; $i<=$this->_attemptCount; $i++) {
            echo "----------store property-------------".$dezrezPropertyId." (attempt $i)\r\n";
            // need to update item or insert a new one
            if (array_key_exists($dezrezPropertyId, $this->_wfItems)) {
                $wfItem = $this->updateWFItem($this->_propertyCollection->getId(), $dezrezPropertyId, $this->_wfItems[$dezrezPropertyId]['id'], $item);
            } else {
                $wfItem = $this->insertWFItem($this->_propertyCollection->getId(), $dezrezPropertyId, $item);
                $isInserted = true;
            }

            // if WebFlow cannot store property
            if(array_key_exists($this->fieldId, $wfItem) === FALSE){
                $success = false;
                break;
            }

            // if all images were saved then continue
            $checkedItem = $this->checkForAllImagesExists($wfItem, $item, $imagesToUpload);
            if($success = $checkedItem['allSaved'])
                break;

            $item = $checkedItem['item'];
        }

        if($success) {
            echo "WebFlow: property `" . $dezrezPropertyId . "` was saved successfully \r\n";

            if($isInserted) $this->_insertedCount++;
            else $this->_updatedCount++;
        } else
            echo "Warning: property `" . $dezrezPropertyId . "` wasn't saved properly \r\n";

        return $success;
    }

    /**
     * Detect which properties don't exists in Dezred feed and set their as not 'In feed'
     */
    public function setOldPropertiesAsNotInFeed(){
        $hidden = 0;

        foreach($this->_wfItems as $wfItemId=>$wfItemData) {
            if (!$wfItemData['flagUpdated']){
                $item = $this->patchWFItem(
                    $this->_propertyCollection->getId(),
                    $wfItemId,
                    $wfItemData['id'],
                    [
                        static::FIELD_IN_FEED_SLUG => false,
                    ]);

                if(array_key_exists(static::FIELD_IN_FEED_SLUG, $item) && $item[static::FIELD_IN_FEED_SLUG]===false){
                    $hidden++;
                }else{
                    echo 'Error WebFlow: Couldn\'t set as not `In feed` item ID-' . $wfItemData['id'] . ' with name `' . $wfItemId . '`' . "\r\n";
                }
            }
        }

        echo 'WebFlow: Set as not `In feed` - ' . $hidden . "\r\n";
    }

    /**
     * Prepare WebFlow client for first use. Load collections with role types, property types and old properties IDs
     * @return bool
     */
    protected function prepareWFClient()
    {
        if(!$this->getSiteId()) return false;
        if(!$this->loadCollections()) return false;

        return true;
    }

    /**
     * Load Web Flow collections with statuses, roles and properties
     * @return bool
     */
    protected function loadCollections()
    {
        $collections = $this->_webFlowClient->getSiteCollections($this->_apiKey, $this->_siteId);

        if(!is_array($collections)) return false;

        foreach($collections as $collection){
            if($collection['name']==$this->_roleTypeCollectionName){
                $this->_roleTypeCollection = new WebFlowCollection($collection['_id'], $collection['name'] ,$collection['slug'], $this->_webFlowClient);
                if(!$this->_roleTypeCollection->loadItems($this->_apiKey))
                    return false;
            }elseif($collection['name']==$this->_propertyCollectionName){
                $this->_propertyCollection = new WebFlowCollection($collection['_id'], $collection['name'] ,$collection['slug'], $this->_webFlowClient);
                if(!$this->_propertyCollection->loadFields($this->_apiKey))
                    return false;
            }elseif($collection['name']==$this->_propertyStatusCollectionName){
                $this->_propertyStatusCollection = new WebFlowCollection($collection['_id'], $collection['name'] ,$collection['slug'], $this->_webFlowClient);
                if(!$this->_propertyStatusCollection->loadItems($this->_apiKey))
                    return false;
            }
        }

        $this->webFlowStatuses = new WebFlowStatuses($this->_roleTypeCollection, $this->_propertyStatusCollection, $this->_propertyCollection);

        return true;
    }

    /**
     * @param Property $property
     * @param $dezrezPropertyId
     * @return array
     */
    protected function fillProperty(Property $property, $dezrezPropertyId)
    {
        $item = [
            static::FIELD_IN_FEED_SLUG => true, // slug for field 'In feed'
            '_archived' => false,
            '_draft' => false,
            'name' => $property->name,
            'slug' => $dezrezPropertyId,
            'propertyid-2' => $dezrezPropertyId,
            'property-status' => $this->webFlowStatuses->getWebFlowMarketStatus($property->marketStatus),
            'rent-or-sale-price' => $property->price,
            'asking-price-text' => $property->priceText,
            'number-of-rooms' => $property->numberOfRooms,
            'number-of-baths' => $property->numberOfBath,
            'property-description' => $property->fullDescription,
            'short-description' => StringHelper::truncate($property->shortDescription, static::SHORT_DESCRIPTION_LENGTH, '...'),
            'short-description-mobile' => StringHelper::truncate($property->shortDescription, static::SHORT_DESCRIPTION_MOBILE_LENGTH, '...'),
            'property-type-2' => $property->propertyType,
            'property-address' => $property->address,
            'filtering-category' => $this->webFlowStatuses->getWebFlowFilteredCategory($property->roleType),
            'role-type' => $this->webFlowStatuses->getWebFlowRoleType($property->roleType),
            'featured-property' => $property->featured,
            'shortcut-show-2' => $this->webFlowStatuses->getShowShortcutValue($property->marketStatus),
        ];

        if (!empty($property->floorPlanImageUrl))
            $item['floorplan'] = $property->floorPlanImageUrl;

        if (!empty($property->epc))
            $item['epc-rating'] = $property->epc;

        if (!empty($property->brochure))
            $item['pdf-brochure'] = $property->brochure;

        return $item;
    }

    /**
     * @param Property $property
     * @param $item
     * @return int
     */
    protected function countImagesForUpload(Property $property, &$item)
    {
        $i = 0;
        foreach ($property->images as $image) {
            $i++;
            if ($i > 8) break;
            $item['image-' . $i] = static::getResizedImage($image);
        }

        return $i > 0 ? $i - 1 : $i;
    }

    /**
     * @param array $wfItem
     * @param array $item
     * @param int $imagesCount count of images need to be stored
     * @return array Fixed item with resized images urls
     */
    protected function checkForAllImagesExists(array $wfItem, array $item, $imagesCount){
        $result = [
            'item' => $item,
            'allSaved' => true
        ];

        for($i=1; $i<=$imagesCount; $i++){
            if(!array_key_exists('image-'.$i, $wfItem)){
                echo 'WebFlow: image-'.$i.' (' . $item['image-'.$i] .') didn\'t stored for `' . $wfItem['name'] . '` property' . "\r\n";

                //resize image
                $result['item']['image-'.$i] = static::getResizedImage($item['image-'.$i]);

                $result['allSaved'] = false;
            }
        }

        return $result;
    }

    /**
     * @param string $image
     * @return string
     */
    protected static function getResizedImage($image)
    {
        if(strpos($image, '?') === FALSE) {
            return $image . '?width=' . static::MAX_IMAGE_WIDTH;
        }

        return $image . '&width=' . static::MAX_IMAGE_WIDTH;
    }
}