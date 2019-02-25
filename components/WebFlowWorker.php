<?php
/**
 * Created by Kudin Dmitry
 * Date: 25.12.2018
 * Time: 23:10
 */

namespace app\components;

use Yii;
use yii\base\Component;
use app\models\Property;
use app\components\WebFlowClient;


/**
 * Class provide interface for inserting and updating properties via WebFlow API.
 *
 * @author Kudin Dmitry <dakudin@gmail.com>
 */
class WebFlowWorker extends Component
{

    /**
     * slug for field 'In feed'
     * if filed is true that property exists in feed, false otherwise
     */
    const FIELD_IN_FEED_SLUG = 'in-feed-3';

    /**
     * WebFlow field id
     */
    const FIELD_ID = '_id';

    /**
     * @var \app\components\WebFlowClient client for work with WebFlow via API
     */
    protected $_webFlowClient;

    /**
     * @var string API key
     */
    protected $_apiKey;

    /**
     * @var string WebFlow collection id in which need inset/update/delete properties
     */
    protected $_collectionId;

    /**
     * @var array of WebFlow properties ids
     */
    protected $_wfItems;

    /**
     * @var int Number of items of the collection which we get per request
     */
    protected $_itemsPerPage = 20;

    /**
     *
     * @var bool Flag that show for which kind of items need to work: Live or not
     * set to true for publishing to live site
     */
    protected $_publishToLiveSite = false;

    /**
     * @var int Number of attempts for storing properties
     * If first attempt was wrong it detect images which didn't store, resize them and try to store again
     */
    protected $_attemptCount = 2;

    /**
     * @var int Number of items which were inserted to collection
     */
    protected $_insertedCount = 0;

    /**
     * @var int Number of items which were updated in collection
     */
    protected $_updatedCount = 0;

    /**
     * @param string $apiKey
     * @param string $collectionId
     */
    public function __construct($apiKey, $collectionId)
    {
        parent::__construct();

        $this->_apiKey = $apiKey;

        $this->_collectionId = $collectionId;

//        $this->_publishToLiveSite = true;

        $this->_webFlowClient = new WebFlowClient();
    }

    /**
     * @return int Number of items which were inserted to collection
     */
    public function getInsertedCount()
    {
        return $this->_insertedCount;
    }

    /**
     * @return int Number of items which were inserted to collection
     */
    public function getUpdatedCount()
    {
        return $this->_updatedCount;
    }

    /**
     * Get all old items from WebFlow collection. Store properties id for detecting which ones need to delete
     * after inserting/updating
     * @return bool
     */
    public function loadAllItems()
    {
        $this->_wfItems = [];
        $offset = 0;

        do {
            $response = $this->_webFlowClient->getCollectionItems(
                $this->_apiKey, $this->_collectionId, $this->_itemsPerPage, $offset
            );

            if(!isset($response['items']) || !isset($response['count']))
                return false;

            foreach($response['items'] as $item){
                $this->_wfItems[$item['propertyid-2']] = [
                    'id' => $item['_id'],
                    'flagUpdated' => false,
                ];
            }

            $offset += $this->_itemsPerPage;
        } while ($response['total']>0 && $response['total'] > $response['offset'] + $response['count']);

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
        $item = [
            self::FIELD_IN_FEED_SLUG => true, // slug for field 'In feed'
            '_archived' => false,
            '_draft'=> false,
            'name' => $property->name,
            'slug' => $dezrezPropertyId,
            'propertyid-2' => $dezrezPropertyId,
            'property-status' => $property->getWebflowMarketStatus(),
            'rent-or-sale-price' => $property->price,
            'number-of-rooms' => $property->numberOfRooms,
            'number-of-baths' => $property->numberOfBath,
            'property-description' => $property->fullDescription,
            'short-description' => $property->shortDescription,
            'property-type-2' => $property->propertyType,
            'property-address' => $property->address,
            'role-type' => $property->getWebflowRoleType(),
        ];

        if(!empty($property->floorPlanImageUrl))
            $item['floorplan'] = $property->floorPlanImageUrl;

        if(!empty($property->epc))
            $item['epc-rating'] = $property->epc;

        if(!empty($property->brochure))
            $item['pdf-brochure'] = $property->brochure;

        $i = 0;
        foreach($property->images as $image) {
            $i++;
            if ($i > 8) break;
            $item['image-'.$i] = $image;
        }
        $imagesToUpload = $i>0 ? $i-1 : $i;

        $success = false;

        $isInserted = false;

        //try to update/insert WebFlow item
        //if fault then try again
        for($i=1; $i<=$this->_attemptCount; $i++) {
            echo "----------store property-------------".$dezrezPropertyId." (attempt $i)\r\n";
            // need to update item or insert a new one
            if (array_key_exists($dezrezPropertyId, $this->_wfItems)) {
                $wfItem = $this->updateProperty($dezrezPropertyId, $this->_wfItems[$dezrezPropertyId]['id'], $item);
            } else {
                $wfItem = $this->insertProperty($dezrezPropertyId, $item);
                $isInserted = true;
            }

            // if WebFlow cannot store property
            if(array_key_exists(self::FIELD_ID, $wfItem) === FALSE){
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
            echo "Error: property `" . $dezrezPropertyId . "` wasn't saved properly \r\n";

        return $success;
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
                $result['item']['image-'.$i] = $item['image-'.$i] . '?width=1000';
                $result['allSaved'] = false;
            }
        }

        return $result;
    }

    /**
     * Insert new item to WebFlow collection
     * @param string $dezrezPropertyId ID of item for inserting
     * @param array $item Item of WebFlow collection
     * @return array of inserted WebFlow item
     */
    protected function insertProperty($dezrezPropertyId, $item)
    {
        echo "----------insert property-------------".$dezrezPropertyId."\r\n";

        $result = $this->_webFlowClient->addCollectionItem(
            $this->_apiKey,
            $this->_collectionId,
            $this->_publishToLiveSite,
            $item
        );

        if(array_key_exists(self::FIELD_ID, $result) !== FALSE){
                $this->_wfItems[$dezrezPropertyId] = [
                'id' => $result['_id'],
                'flagUpdated' => true,
            ];
        }

        return $result;
    }

    /**
     * Update item of WebFlow collection
     * @param string $dezrezPropertyId ID of item for updating
     * @param string $itemId ID of item for updating
     * @param array $item Item of WebFlow collection
     * @return array of updated WebFlow item
     */
    protected function updateProperty($dezrezPropertyId, $itemId, $item)
    {
        echo "----------update property-------------".$dezrezPropertyId."\r\n";

        $result = $this->_webFlowClient->updateCollectionItem(
            $this->_apiKey,
            $this->_collectionId,
            $itemId,
            $this->_publishToLiveSite, // set to true for publishing to live site
            $item
        );

        if(array_key_exists(self::FIELD_ID, $result) !== FALSE){
            $this->_wfItems[$dezrezPropertyId]['flagUpdated'] = true;
        }

        return $result;
    }

    /**
     * Patch item of WebFlow collection
     * @param string $dezrezPropertyId ID of item for patching
     * @param string $itemId ID of item for patching
     * @param array $item Item of WebFlow collection
     * @return array of patched WebFlow item
     */
    protected function patchProperty($dezrezPropertyId, $itemId, $item)
    {
//        echo "----------patch property-------------".$dezrezPropertyId."\r\n";

        $result = $this->_webFlowClient->patchCollectionItem(
            $this->_apiKey,
            $this->_collectionId,
            $itemId,
            $this->_publishToLiveSite, // set to true for publishing to live site
            $item
        );

        return $result;
    }

    /**
     * Detect which properties don't exists in Dezred feed and delete their in WebFlow collection
     */
    public function deleteOldProperties(){
        $deleted = 0;

        foreach($this->_wfItems as $wfItemId=>$wfItemData) {
            if (!$wfItemData['flagUpdated']){
                if (!$this->deleteProperty($wfItemData['id'])) {
                    echo 'WebFlow: Couldn\'t delete item ID-' . $wfItemData['id'] . ' with name `' . $wfItemId . '`' . "\r\n";
                }else {
                    $deleted++;
                }
            }
        }

        echo 'WebFlow: Deleted - ' . $deleted . "\r\n";
    }

    /**
     * Detect which properties don't exists in Dezred feed and set their as not 'In feed'
     */
    public function setOldPropertiesAsNotInFeed(){
        $hidden = 0;

        foreach($this->_wfItems as $wfItemId=>$wfItemData) {
            if (!$wfItemData['flagUpdated']){
                $item = $this->patchProperty(
                    $wfItemId,
                    $wfItemData['id'],
                    [
                        self::FIELD_IN_FEED_SLUG => false,
                    ]);

                if(array_key_exists(self::FIELD_IN_FEED_SLUG, $item) && $item[self::FIELD_IN_FEED_SLUG]===false){
                    $hidden++;
                }else{
                    echo 'Error WebFlow: Couldn\'t set as not `In feed` item ID-' . $wfItemData['id'] . ' with name `' . $wfItemId . '`' . "\r\n";
                }
            }
        }

        echo 'WebFlow: Set as not `In feed` - ' . $hidden . "\r\n";
    }

    /**
     * Delete item of WebFlow collection
     * @param $itemId
     * @return bool
     */
    protected function deleteProperty($itemId)
    {
        return $this->_webFlowClient->deleteCollectionItem(
            $this->_apiKey,
            $this->_collectionId,
            $itemId
        );
    }

}