<?php
/**
 * Created by Kudin Dmitry
 * Date: 14.12.2018
 * Time: 22:21
 */

namespace app\commands;

use app\components\DezrezFeedParser;
use app\components\oneagency\WFPropertyWorker;
use app\components\GMyBusinessClient;
use app\components\oneagency\WFReviewWorker;
use Yii;
use app\components\DezrezClient;
use yii\console\Controller;
use app\models\Property;
use app\models\GoogleReview;
use yii\console\ExitCode;

/**
 * This command parse remote feed and store data to webflow web server by its API.
 *
 * @author Kudin Dmitry <dakudin@gmail.com>
 */
class OneAgencyController extends Controller
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
     * @var int Number of reviews for getting from Google API
     */
    private $reviewsPerPage = 100;

    /**
     * @var WFReviewWorker worker for manipulate WebFlow API.
     */
    protected $WFReviewWorker;

    /**
     * This command parse properties from Dezred feed and store to WebFlow site via API
     * @return int Exit code
     * @throws \Exception
     */
    public function actionParseProperties()
    {
        $this->WFPropertyWorker = new WFPropertyWorker(
            Yii::$app->params['one_agency']['webflow_api_key'],
            Yii::$app->params['one_agency']['webflow_role_type_collection'],
            Yii::$app->params['one_agency']['webflow_sales_collection']['collection_name'],
            Yii::$app->params['one_agency']['webflow_sales_collection']['property_id_slug'],
            Yii::$app->params['one_agency']['webflow_lettings_collection']['collection_name'],
            Yii::$app->params['one_agency']['webflow_lettings_collection']['property_id_slug'],
            Yii::$app->params['one_agency']['webflow_property_status_collection'],
            Yii::$app->params['one_agency']['domain_name'],
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
     * This command parse reviews from Google My Business and store to WebFlow site via API
     * @return int Exit code
     * @throws \Exception
     */
    public function actionParseReviews()
    {
        $this->WFReviewWorker = new WFReviewWorker(
            Yii::$app->params['one_agency']['webflow_api_key'],
            Yii::$app->params['one_agency']['webflow_review_collection'],
            Yii::$app->params['one_agency']['webflow_review_stats_collection']['collection_name'],
            Yii::$app->params['one_agency']['webflow_review_stats_collection']['review_count_slug'],
            Yii::$app->params['one_agency']['webflow_review_stats_collection']['review_avg_rating_slug'],
            Yii::$app->params['one_agency']['webflow_review_stats_collection']['stat_item_slug'],
            Yii::$app->params['one_agency']['webflow_review_stats_collection']['stat_item_name'],
            Yii::$app->params['one_agency']['webflow_published_to_live']
        );

        //load all old reviews
        $this->WFReviewWorker->loadAllReviews();

        // update reviews and insert new ones
        $this->refreshReviews();

        //delete not exists reviews from WebFlow collection
        $this->WFReviewWorker->deleteOldReviews();

        echo "WebFlow OneAgency reviews: Inserted - " . $this->WFReviewWorker->getInsertedCount() . "; Updated - " . $this->WFReviewWorker->getUpdatedCount() . "\r\n";

        return ExitCode::OK;
    }

    /*
     *  get reviews by via web client authentication
     */
    private function refreshReviews()
    {
        $gmb = Yii::$app->params['one_agency']['GMB_API']['web_client'];
        $gmbClient = new GMyBusinessClient(
            $gmb['client_id'],
            $gmb['client_secret'],
            $gmb['account_email'],
            $gmb['refresh_token'],
            Yii::$app->params['one_agency']['domain_name']
        );

        $gmbClient->refreshAllReviews();

        $this->storeReviewStatsIntoWebFlow($gmbClient->getTotalReviewCount(), $gmbClient->getAverageRating());

        $this->storeReviewsIntoWebFlow($gmbClient->getReviews());
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

    /**
     * Get pack of parsed reviews and store their into WebFlow
     * @param array $reviews
     */
    private function storeReviewsIntoWebFlow(array $reviews)
    {
        foreach($reviews as $review){
            if($review instanceof GoogleReview) {
                if(!$this->WFReviewWorker->storeReview($review)){
                    echo "Error OneAgency GMB: Cannot store review \r\n";
                    var_dump($review);
                }
            }
        }
    }

    /**
     * Get pack of parsed reviews and store their into WebFlow
     * @param $totalReviews
     * @param $averageRating
     */
    private function storeReviewStatsIntoWebFlow($totalReviews, $averageRating)
    {
        if(!$this->WFReviewWorker->storeReviewStats($totalReviews, $averageRating)){
            echo "Error OneAgency GMB: Cannot store review stats\r\n";
        }
    }

}
