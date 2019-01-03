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
use yii\console\Controller;
use app\models\Property;
use yii\console\ExitCode;

/**
 * This command parse remote feed and store data to webflow web server by its api.
 *
 * @author Kudin Dmitry <dakudin@gmail.com>
 */
class ParserController extends Controller
{
    /**
     * @var int Number of properties for getting from Dezrez API
     */
    private $propertiesPerPage = 20;

    /**
     * @var WebFlowWorker worker for manipulate WebFlow API.
     */
    protected $webFlowWorker;

    /**
     * This command parse properties from Dezred feed and store to WebFlow site via API
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

    /**
     * Get all properties from Dezrez feed and store their to WebFlow
     */
    protected function refreshFeed()
    {
        $pageNumber = 0;

        $client = new DezrezClient();
        $parser = new DezrezFeedParser();

        do {
            $pageNumber++;

            $data = $client->getProperties(
                Yii::$app->params['dezrez_test_api_key'],
                [
                    'PageSize' => $this->propertiesPerPage,
                    'PageNumber' => $pageNumber
                ]
            );

            $properties = $parser->parse($data);

            echo "Dezrez: Page - " . $pageNumber . "; Total properties - " . $parser->getAllPropCount() . "; Properties on page - " . $parser->getCurPropCount() . "\r\n";

            $this->storePropsInWebFlow($properties);

        } while($parser->getAllPropCount()>0 && $parser->getAllPropCount() >= $pageNumber * $this->propertiesPerPage);
    }

    /**
     * Get pack of parsed properties and store their to WebFlow
     * @param array $properties
     */
    private function storePropsInWebFlow(array $properties)
    {
        foreach($properties as $property){
            if($property instanceof Property) {
               $this->webFlowWorker->storeProperty($property);
            }
        }
    }
}
