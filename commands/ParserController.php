<?php
/**
 * Created by Kudin Dmitry
 * Date: 14.12.2018
 * Time: 22:21
 */

namespace app\commands;

use app\components\DezrezFeedParser;
use app\components\WebFlowWorker;
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
                'PageSize' => 100,
                'PageNumber' => $page
            ]
        );

        $parser = new DezrezFeedParser();
        $parser->parse($data);

        $properties = $parser->getProperties();

        $webFlowWorker = new WebFlowWorker();

        foreach($properties as $property){
//            var_dump($property);
            if($property instanceof Property) {

                $webFlowProperty = $webFlowWorker->storeProperty($property);

//                echo Json::encode( $result) . "\n";
            }
        }
//        echo Json::encode( $data) . "\n";

        return ExitCode::OK;
    }
}
