<?php
/**
 * Created by PhpStorm.
 * User: Monk
 * Date: 20.06.2019
 * Time: 18:05
 */

namespace app\commands;

use app\components\GMyBusinessClient;
use Yii;
use yii\console\Controller;
use app\models\GoogleReview;
use yii\console\ExitCode;
use app\components\whitehouseclinic\WFReviewWorker;

class WHClinicController extends Controller
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
     * This command parse properties from Google My Business and store to WebFlow site via API
     * @return int Exit code
     */
    public function actionIndex()
    {
        $this->WFReviewWorker = new WFReviewWorker(
            Yii::$app->params['white_house_clinic']['webflow_api_key'],
            Yii::$app->params['white_house_clinic']['webflow_review_collection'],
            Yii::$app->params['white_house_clinic']['webflow_published_to_live']
        );

        //load all old reviews
        $this->WFReviewWorker->loadAllReviews();

        // update reviews and insert new ones
        $this->refreshReviews();

        //delete not exists reviews from WebFlow collection
        $this->WFReviewWorker->deleteOldReviews();

        echo "WebFlow: Inserted - " . $this->WFReviewWorker->getInsertedCount() . "; Updated - " . $this->WFReviewWorker->getUpdatedCount() . "\r\n";

        return ExitCode::OK;
    }

    /*
     *  get reviews by via web client authentication
     */
    protected function refreshReviews()
    {
        $gmb = Yii::$app->params['white_house_clinic']['GMB_API']['web_client'];
        $gmbClient = new GMyBusinessClient(
            $gmb['client_id'],
            $gmb['client_secret'],
            $gmb['account_email'],
            $gmb['refresh_token']
        );

        $gmbClient->refreshAllReviews();

        $this->storeReviewsIntoWebFlow($gmbClient->getReviews());
    }

    protected function refreshReviews2()
    {
        $gmb = Yii::$app->params['white_house_clinic']['GMB_API'];

        /*$accounts previously populate*/
        /*(GMB - v4)*/
        $credentials_f = $gmb['credential'];
        $client = new \Google_Client();
        $client->setApplicationName($gmb['application_name']);
        $client->setDeveloperKey($gmb['developer_key']);
        $client->setAuthConfig($credentials_f);
        $client->setScopes("https://www.googleapis.com/auth/plus.business.manage");
        $client->setSubject($gmb['web_client']['account_email']);
        $token = $client->refreshToken($gmb['web_client']['refresh_token']);
        $client->authorize();

        $mybusinessService = new \Google_Service_Mybusiness($client);

        $locationName = "accounts/#######/locations/########";
        $accounts = $mybusinessService->accounts;
        $accountsList = $accounts->listAccounts()->getAccounts();
        $params = ['pageSize' => 100];

        foreach ($accountsList as $accKey => $account) {
//            var_dump('$account->name', $account->name);

            $locations = $mybusinessService->accounts_locations;
            $locationsList = $locations->listAccountsLocations($account->name)->getLocations();
//            var_dump('$locationsList', $locationsList);


            // Final Goal of my Code
            if (empty($locationsList) === false) {
                foreach ($locationsList as $locKey => $location) {

                    $reviews = $mybusinessService->accounts_locations_reviews;

                    do {
                        if(isset($nextPageToken)){
                            $params['pageToken'] = $nextPageToken;
                        }
                        $listReviewsResponse = $reviews->listAccountsLocationsReviews($location->name, $params);

                        $reviewsList = $listReviewsResponse->getReviews();
                        foreach ($reviewsList as $index => $review) {
                            //Accesing $review Object
                            //                $review->createTime;
                            //                $review->updateTime;
                            //                $review->starRating;
                            echo                $review->reviewer->displayName . "\r\n";
                            //                $review->reviewReply->comment;
                            //                $review->getReviewReply()->getComment();
                            //                $review->getReviewReply()->getUpdateTime();
                        }

                        $nextPageToken = $listReviewsResponse->nextPageToken;

                    } while ($listReviewsResponse->nextPageToken);
                }
            }
        }
    }

    /**
     * Get all properties from Google My Business and store their to WebFlow
     */
    protected function refreshReviewsSA()
    {
        $pageNumber = 0;
        $gmb = Yii::$app->params['white_house_clinic']['GMB_API'];

        $client = new \Google_Client();
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $gmb['credential']);
        $client->setApplicationName($gmb['application_name']);
        $client->setDeveloperKey($gmb['developer_key']);
        $client->useApplicationDefaultCredentials();
        $client->addScope('https://www.googleapis.com/auth/plus.business.manage');
        $gmb = new \Google_Service_MyBusiness( $client );
        $accounts = $gmb->accounts->listAccounts()->getAccounts();
        var_dump($accounts);
        $location = $gmb->accounts_locations->listAccountsLocations( $accounts[0]['name'] );
        var_dump( $location->getLocations() );
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
                    echo "Error GMB: Cannot store review \r\n";
                    var_dump($review);
                }
            }
        }
    }

}