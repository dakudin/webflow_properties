<?php
/**
 * Created by Kudin Dmitry
 * Date: 14.12.2018
 * Time: 22:21
 */

namespace app\commands;

use app\components\DezrezFeedParser;
use app\components\one\agency\WFPropertyWorker;
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
    private $propertiesPerPage = 10;

    /**
     * @var WFPropertyWorker worker for manipulate WebFlow API.
     */
    protected $WFPropertyWorker;

    /**
     * This command parse properties from Dezred feed and store to WebFlow site via API
     * @return int Exit code
     */
    public function actionIndex()
    {
        $this->WFPropertyWorker = new WFPropertyWorker(
            Yii::$app->params['one_agency']['webflow_api_key'],
            Yii::$app->params['one_agency']['webflow_role_type_collection'],
            Yii::$app->params['one_agency']['webflow_properties_collection'],
            Yii::$app->params['one_agency']['webflow_property_status_collection'],
            Yii::$app->params['one_agency']['webflow_published_to_live']
        );

        //load all old properties
        $this->WFPropertyWorker->loadAllProperties();

        // update properties and insert new ones
        $this->refreshFeed();

        //set as not `In feed` for properties which don't already exists in Dezrez feed
        $this->WFPropertyWorker->setOldPropertiesAsNotInFeed();

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
                Yii::$app->params['one_agency']['dezrez_live_api_key'],
                [
                    'PageSize' => $this->propertiesPerPage,
                    'PageNumber' => $pageNumber
                ]
            );

            //for getting logging response
            // \Yii::error($data); die;

            $properties = $parser->parse($data);

            echo "Dezrez: Page - " . $pageNumber . "; Total properties - " . $parser->getAllPropCount() . "; Properties on page - " . $parser->getCurPropCount() . "\r\n";

            $this->storePropsInWebFlow($properties);

            //break; //for testing
        } while($parser->getAllPropCount()>0 && $parser->getAllPropCount() >= $pageNumber * $this->propertiesPerPage);

        echo "WebFlow: Inserted - " . $this->WFPropertyWorker->getInsertedCount() . "; Updated - " . $this->WFPropertyWorker->getUpdatedCount() . "\r\n";
    }

    /**
     * Get pack of parsed properties and store their to WebFlow
     * @param array $properties
     */
    private function storePropsInWebFlow(array $properties)
    {
        foreach($properties as $property){
            if($property instanceof Property) {
               if(!$this->WFPropertyWorker->storeProperty($property)){
                   echo "Error Dezrez: Cannot store property \r\n";
                   var_dump($property);
               }
            }
        }
    }
}
