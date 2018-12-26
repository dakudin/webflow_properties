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

    public function __construct()
    {
        parent::__construct();

        $this->_webFlowClient = new WebFlowClient();
    }

    public function storeProperty(Property $property)
    {
        $item = [
            "_archived" => false,
            "_draft"=> false,
            'name' => (string)$property->id,
            'propertyid-2' => (string)$property->id,
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

        return $this->insertProperty($item);
    }

    protected function insertProperty($item)
    {
        return $this->_webFlowClient->addCollectionItem(
            Yii::$app->params['webflow_api_key'],
            Yii::$app->params['webflow_collection_id'],
            $item
        );
    }

    protected function uploadImage()
    {

    }

}