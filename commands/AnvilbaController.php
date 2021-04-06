<?php
/**
 * Created by Kudin Dmitry
 * Date: 06.04.2021
 */

namespace app\commands;

use app\components\GMyBusinessClient;
use app\components\anvilba\WFReviewWorker;
use Yii;
use yii\console\Controller;
use app\models\GoogleReview;
use yii\console\ExitCode;

/**
 * This command parse remote feed and store data to webflow web server by its API.
 *
 * @author Kudin Dmitry <dakudin@gmail.com>
 */
class AnvilbaController extends Controller
{
    /**
     * @var int Number of reviews for getting from Google API
     */
    private $reviewsPerPage = 100;

    /**
     * @var WFReviewWorker worker for manipulate WebFlow API.
     */
    protected $WFReviewWorker;

    /**
     * This command parse reviews from Google My Business and store to WebFlow site via API
     * @return int Exit code
     * @throws \Exception
     */
    public function actionParseReviews()
    {
        $this->WFReviewWorker = new WFReviewWorker(
            Yii::$app->params['anvilba']['webflow_api_key'],
            Yii::$app->params['anvilba']['webflow_review_collection'],
            Yii::$app->params['anvilba']['webflow_review_stats_collection']['collection_name'],
            Yii::$app->params['anvilba']['webflow_review_stats_collection']['review_count_slug'],
            Yii::$app->params['anvilba']['webflow_review_stats_collection']['review_avg_rating_slug'],
            Yii::$app->params['anvilba']['webflow_review_stats_collection']['stat_item_slug'],
            Yii::$app->params['anvilba']['webflow_review_stats_collection']['stat_item_name'],
            Yii::$app->params['anvilba']['webflow_published_to_live']
        );

        //load all old reviews
//        $this->WFReviewWorker->loadAllReviews();

        // update reviews and insert new ones
        $this->refreshReviews();
die();
        //delete not exists reviews from WebFlow collection
        $this->WFReviewWorker->deleteOldReviews();

        echo "WebFlow Anvilba reviews: Inserted - " . $this->WFReviewWorker->getInsertedCount() . "; Updated - " . $this->WFReviewWorker->getUpdatedCount() . "\r\n";

        return ExitCode::OK;
    }

    /*
     *  get reviews by via web client authentication
     */
    private function refreshReviews()
    {
        $gmb = Yii::$app->params['anvilba']['GMB_API']['web_client'];
        $gmbClient = new GMyBusinessClient(
            $gmb['client_id'],
            $gmb['client_secret'],
            $gmb['account_email'],
            $gmb['refresh_token'],
            Yii::$app->params['anvilba']['domain_name']
        );

        $gmbClient->refreshAllReviews();
return ExitCode::OK;
die();
        $this->storeReviewStatsIntoWebFlow($gmbClient->getTotalReviewCount(), $gmbClient->getAverageRating());

        $this->storeReviewsIntoWebFlow($gmbClient->getReviews());
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
                    echo "Error Anvilba GMB: Cannot store review \r\n";
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
            echo "Error Anvilba GMB: Cannot store review stats\r\n";
        }
    }

}
