<?php
/**
 * Created by Kudin Dmitry
 * Date: 25.12.2018
 * Time: 23:10
 */

namespace app\components\oneagency;

use Yii;
use yii\helpers\Inflector;
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
    const FIELD_IN_FEED_SLUG = 'in-feed';

    /**
     * @var string WebFlow collection name of Role types
     */
    protected $_roleTypeCollectionName;

    /**
     * @var string WebFlow collection name of sales properties
     */
    protected $_salesCollectionName;

    /**
     * @var string WebFlow property ID slug in sales collection
     */
    protected $_salesPropertyIdSlug;

    /**
     * @var string WebFlow collection name of lettings properties
     */
    protected $_lettingsCollectionName;

    /**
     * @var string WebFlow property ID slug in lettings collection
     */
    protected $_lettingsPropertyIdSlug;

    /**
     * @var string WebFlow collection name of property statuses
     */
    protected $_propertyStatusCollectionName;

    /**
     * @var WebFlowCollection WebFlow collection of Role types
     */
    protected $_roleTypeCollection;

    /**
     * @var WebFlowCollection WebFlow collection of sales properties
     */
    protected $_salesPropertyCollection;

    /**
     * @var WebFlowCollection WebFlow collection of lettings properties
     */
    protected $_lettingsPropertyCollection;

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
     * @param $salesCollectionName
     * @param $salesPropertyIdSlug
     * @param $lettingsCollectionName
     * @param $lettingsPropertyIdSlug
     * @param $propertyStatusCollectionName
     * @param $publishToLiveSite
     * @throws \Exception
     */
    public function __construct($apiKey, $roleTypeCollectionName, $salesCollectionName, $salesPropertyIdSlug,
                                $lettingsCollectionName, $lettingsPropertyIdSlug,
                                $propertyStatusCollectionName, $publishToLiveSite)
    {
        parent::__construct($apiKey, $publishToLiveSite);

        $this->_roleTypeCollectionName = $roleTypeCollectionName;
        $this->_salesCollectionName = $salesCollectionName;
        $this->_salesPropertyIdSlug = $salesPropertyIdSlug;
        $this->_lettingsCollectionName = $lettingsCollectionName;
        $this->_lettingsPropertyIdSlug = $lettingsPropertyIdSlug;
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

        if(!$this->loadPropertiesFromCollection($this->_salesPropertyCollection, $this->_salesPropertyIdSlug)){
            return false;
        }

        if(!$this->loadPropertiesFromCollection($this->_lettingsPropertyCollection, $this->_lettingsPropertyIdSlug)){
            return false;
        }

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
        $propertyCollectionId = $this->getPropertyCollectionIDByPropertyType($property);

        $success = false;
        $isInserted = false;
        //try to update/insert WebFlow item
        //if fault then try again
        for($i=1; $i<=$this->_attemptCount; $i++) {
            echo "----------store property-------------".$dezrezPropertyId." (attempt $i)\r\n";
            // need to update item or insert a new one
            if (array_key_exists($dezrezPropertyId, $this->_wfItems)) {
                $wfItem = $this->updateWFItem($propertyCollectionId, $dezrezPropertyId, $this->_wfItems[$dezrezPropertyId]['id'], $item);

/*                if($dezrezPropertyId==15869945){
                    $wfItem = $this->patchWFItem($propertyCollectionId, $dezrezPropertyId, $this->_wfItems[$dezrezPropertyId]['id'], ['pdf-brochure' => '']);
                    $wfItem = $this->patchWFItem($propertyCollectionId, $dezrezPropertyId, $this->_wfItems[$dezrezPropertyId]['id'], ['pdf-brochure' => $item['pdf-brochure']]);
                }*/
            } else {
                $wfItem = $this->insertWFItem($propertyCollectionId, $dezrezPropertyId, $item);
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
                    $wfItemData['collectionID'],
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
            }elseif($collection['name']==$this->_salesCollectionName){
                $this->_salesPropertyCollection = new WebFlowCollection($collection['_id'], $collection['name'] ,$collection['slug'], $this->_webFlowClient);
                if(!$this->_salesPropertyCollection->loadFields($this->_apiKey))
                    return false;
            }elseif($collection['name']==$this->_lettingsCollectionName){
                $this->_lettingsPropertyCollection = new WebFlowCollection($collection['_id'], $collection['name'] ,$collection['slug'], $this->_webFlowClient);
                if(!$this->_lettingsPropertyCollection->loadFields($this->_apiKey))
                    return false;
            }elseif($collection['name']==$this->_propertyStatusCollectionName){
                $this->_propertyStatusCollection = new WebFlowCollection($collection['_id'], $collection['name'] ,$collection['slug'], $this->_webFlowClient);
                if(!$this->_propertyStatusCollection->loadItems($this->_apiKey))
                    return false;
            }
        }

        $this->webFlowStatuses = new WebFlowStatuses($this->_roleTypeCollection, $this->_propertyStatusCollection,
            $this->_salesPropertyCollection, $this->_lettingsPropertyCollection);

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
            'slug' => $this->getPropertySlug($dezrezPropertyId, $property->name),
            'propertyid' => $dezrezPropertyId,
            'property-status' => $this->webFlowStatuses->getWebFlowMarketStatus($property->marketStatus),
            'asking-price' => $property->price,
            'asking-price-text' => $property->priceText,
            'number-of-rooms' => $property->numberOfRooms,
            'number-of-baths' => $property->numberOfBath,
            'property-description' => $property->fullDescription,
            'short-description' => StringHelper::truncate($property->shortDescription, static::SHORT_DESCRIPTION_LENGTH, '...'),
            'short-description-mobile' => StringHelper::truncate($property->shortDescription, static::SHORT_DESCRIPTION_MOBILE_LENGTH, '...'),
            'property-type' => $property->propertyType,
            'property-address' => $property->address,
            'filtering-category' => $this->webFlowStatuses->getWebFlowFilteredCategory($property->roleType),
            'role-type' => $this->webFlowStatuses->getWebFlowRoleType($property->roleType),
            'featured-property' => $property->featured,
            'shortcut-show' => $this->webFlowStatuses->getShowShortcutValue($property->marketStatus),
        ];

        if (!empty($property->floorPlanImageUrl))
            $item['floorplan'] = $property->floorPlanImageUrl;

        if (!empty($property->epc))
            $item['epc-rating'] = $property->epc;

        if (!empty($property->brochure))
            $item['pdf-brochure'] = $property->brochure;

        return $item;
    }

    protected function getPropertySlug($dezrezPropertyId, $propertyName)
    {
        return 'in-' . Inflector::slug($propertyName . '-' . $dezrezPropertyId);
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

    /**
     * Get all old items from WebFlow collection. Store properties id for detecting which ones need to delete
     * after inserting/updating
     * @param WebFlowCollection $propertyCollection
     * @param $propertyIdSlug
     * @return bool
     */
    private function loadPropertiesFromCollection(WebFlowCollection $propertyCollection, $propertyIdSlug)
    {
        $offset = 0;

        $collectionId = $propertyCollection->getId();
        do {
            if(!$propertyCollection->loadItems($this->_apiKey, $this->_itemsPerPage, $offset))
                return false;

            foreach($propertyCollection->getItems() as $item){
                $this->_wfItems[$item[$propertyIdSlug]] = [
                    'id' => $item['_id'],
                    'collectionID' => $collectionId,
                    'flagUpdated' => false,
                ];
            }

            $offset += $this->_itemsPerPage;
        } while ($propertyCollection->getItemsTotal()>0
        && $propertyCollection->getItemsTotal() > $propertyCollection->getItemsOffset() + $propertyCollection->getItemsCount());

        return true;
    }

    /**
     * @param Property $property
     * @return string
     */
    private function getPropertyCollectionIDByPropertyType(Property $property)
    {
        if($property->roleType == Property::ROLE_TYPE_LET){
            return $this->_lettingsPropertyCollection->getId();
        }

        return $this->_salesPropertyCollection->getId();
    }
}