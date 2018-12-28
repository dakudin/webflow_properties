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


class WebFlowWorker extends Component
{

    protected $_webFlowClient;

    protected $_apiKey;

    protected $_collectionId;

    protected $_wfItems;

    protected $_itemsPerPage = 20;

    // set to true for publishing to live site
    protected $_publishToLiveSite = false;

    // count attempts for update/insert new property
    protected $_attemptCount = 3;

    public function __construct($apiKey, $collectionId)
    {
        parent::__construct();

        $this->_apiKey = $apiKey;

        $this->_collectionId = $collectionId;

        $this->_webFlowClient = new WebFlowClient();
    }

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
                $this->_wfItems[$item['name']] = [
                    'id' => $item['_id'],
                    'flagUpdated' => false,
                ];
            }

            $offset += $this->_itemsPerPage;
        } while ($response['total']>0 && $response['total'] > $response['offset'] + $response['count']);

        return true;
    }

    public function storeProperty(Property $property)
    {
        $dezrezPropertyId = (string)$property->id;
        $item = [
            "_archived" => false,
            "_draft"=> false,
            'name' => $dezrezPropertyId,
            'propertyid-2' => $dezrezPropertyId,
            'property-status' => $property->getWebflowMarketStatus(),
            'rent-or-sale-price' => $property->price,
            'number-of-rooms' => $property->numberOfRooms,
            'number-of-baths' => $property->numberOfBath,
            'property-description' => $property->fullDescription,
            'short-description' => $property->shortDescription,
        ];

        if(!empty($property->floorPlanImageUrl))
            $item['floorplan'] = $property->floorPlanImageUrl;

        $i = 0;
        foreach($property->images as $image) {
            $i++;
            if ($i > 8) break;
            $item['image-'.$i] = $image;
        }
        $imagesToUpload = $i>0 ? $i-1 : $i;

        $success = false;

        //try to update/insert WebFlow item
        //if fault then try again
        for($i=1; $i<=$this->_attemptCount; $i++) {
            // need to update item or insert a new one
            if (array_key_exists($dezrezPropertyId, $this->_wfItems)) {
                $wfItem = $this->updateProperty($this->_wfItems[$dezrezPropertyId], $item);
                $this->_wfItems[$dezrezPropertyId] = [
                    'flagUpdated' => true,
                ];
            } else {
                $wfItem = $this->insertProperty($item);
                $this->_wfItems[$dezrezPropertyId] = [
                    'id' => $item['_id'],
                    'flagUpdated' => true,
                ];
            }

            // if all images were saved then continue
            if($success = $this->ifAllImagesExists($wfItem, $imagesToUpload))
                break;
        }

        if($success)
            echo "WebFlow: property `" . $dezrezPropertyId . "` was saved successfully \r\n";
        else
            echo "WebFlow: property `" . $dezrezPropertyId . "` wasn't saved properly \r\n";

        return $success;
    }

    protected function ifAllImagesExists(array $wfItem, $imagesCount){
        for($i=1; $i<=$imagesCount; $i++){
            if(!array_key_exists('image-'.$i, $wfItem)){
                echo 'WebFlow: image-'.$i.' didn\'t stored for `' . $wfItem['name'] . '` property';
                return false;
            }
        }

        return true;
    }

    protected function insertProperty($item)
    {
        return $this->_webFlowClient->addCollectionItem(
            $this->_apiKey,
            $this->_collectionId,
            $this->_publishToLiveSite,
            $item
        );
    }

    protected function updateProperty($itemId, $item)
    {
        return $this->_webFlowClient->updateCollectionItem(
            $this->_apiKey,
            $this->_collectionId,
            $itemId,
            false, // set to true for publishing to live site
            $item
        );
    }

    public function deleteOldProperties(){
        foreach($this->_wfItems as $wfItemId=>$wfItemData){
            if(!$wfItemData['flagUpdated'])
                if(!$this->deleteProperty($wfItemData['id'])){
                    echo 'WebFlow: Couldn\'t delete item ID-' . $wfItemData['id'] . ' with name `' . $wfItemId . '`';
                }
        }
    }

    protected function deleteProperty($itemId)
    {
        return $this->_webFlowClient->deleteCollectionItem(
            $this->_apiKey,
            $this->_collectionId,
            $itemId
        );
    }

}