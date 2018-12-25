<?php
/**
 * Created by Kudin Dmitry
 * Date: 14.12.2018
 * Time: 22:21
 */

namespace app\commands;

use app\components\DezrezFeedParser;
use Yii;
use app\components\DezrezClient;
use app\components\WebFlowClient;
use yii\console\Controller;
use app\models\Property;
use yii\console\ExitCode;
use yii\helpers\Json;

/**
 * This command parse remote feed and store data to webflow web server by its api.
 *
 * @author Kudin Dmitry <dakudin@gmail.com>
 */
class ParserController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param integer $page the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex($page = 1)
    {

        $client = new DezrezClient();
        $data = $client->getProperties(
            Yii::$app->params['dezrez_test_api_key'],
            [
                'PageSize' => 2,
                'PageNumber' => $page
            ]
        );

        $parser = new DezrezFeedParser();
        $parser->parse($data);

//        var_dump($parser->getProperties());


        $webFlow = new WebFlowClient();
/*
        $data = $client->getCollectionItems(
            Yii::$app->params['webflow_api_key'],
            Yii::$app->params['webflow_collection_id']
        );
*/
        $properties = $parser->getProperties();
        foreach($properties as $property){
//            var_dump($property);
            if($property instanceof Property) {
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
//                    'floorplan' => $property->florPlanImageUrl,
                ];
/*
                $i = 0;
                foreach($property->images as $image) {
                    $i++;
                    if ($i > 8) break;

                    $item['image-'.$i] = $image;
                }
*/
                $result = $webFlow->addCollectionItem(
                    Yii::$app->params['webflow_api_key'],
                    Yii::$app->params['webflow_collection_id'],
                    $item
                );

//                echo Json::encode( $result) . "\n";
            }
        }
//        echo Json::encode( $data) . "\n";

        return ExitCode::OK;
    }
}
