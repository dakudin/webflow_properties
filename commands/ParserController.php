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
    private $propertiesPerPage = 10;

    protected $webFlowWorker;

    /**
     * This command echoes what you have entered as the message.
     * @return int Exit code
     */
    public function actionIndex()
    {
        $this->webFlowWorker = new WebFlowWorker(
            Yii::$app->params['webflow_api_key'],
            Yii::$app->params['webflow_collection_id']
        );

        //load all old properties
        $this->webFlowWorker->loadAllItems();

        // update properties and insert new ones
        $this->refreshFeed();

        //delete properties which don't exists already in Dezrez feed
        $this->webFlowWorker->deleteOldProperties();

        return ExitCode::OK;
    }

    protected function refreshFeed()
    {

    }

    protected function getDezrezProperties()
    {
        $pageNumber = 1;

        $client = new DezrezClient();
        $parser = new DezrezFeedParser();

        do {
            $data = $client->getProperties(
                Yii::$app->params['dezrez_test_api_key'],
                [
                    'PageSize' => $this->propertiesPerPage,
                    'PageNumber' => $pageNumber
                ]
            );

            $parser->parse($data);

            $properties = $parser->getProperties();

            $this->storePropsInWebFlow($properties);

            $pageNumber++;

            echo "Dezrez: Page-" . $pageNumber . "; Properties on page - " . $parser->getCurPropCount() . "\r\n";

        } while($parser->getAllPropCount()>0 && $parser->getAllPropCount() > $parser->getPageNumber() * $this->propertiesPerPage);
    }

    private function storePropsInWebFlow(Property $properties)
    {

        foreach($properties as $property){
//            var_dump($property);
            if($property instanceof Property) {

                $webFlowProperty = $this->webFlowWorker->storeProperty($property);

//                echo Json::encode( $result) . "\n";
            }
        }
    }
}
