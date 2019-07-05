<?php
/**
 * Created by PhpStorm.
 * User: Monk
 * Date: 20.06.2019
 * Time: 18:05
 */

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\models\GoogleReview;
use yii\console\ExitCode;
use app\components\whitehouse\clinic\WFReviewWorker;

class WHClinicController extends Controller
{
    /**
     * @var int Number of reviews for getting from Google API
     */
    private $reviewsPerPage = 10;

    /**
     * @var WFReviewWorker worker for manipulate WebFlow API.
     */
    protected $WFReviewWorker;

    /**
     * This command parse properties from Dezred feed and store to WebFlow site via API
     * @return int Exit code
     */
    public function actionIndex()
    {
/*
        $this->WFReviewWorker = new WFReviewWorker(
            Yii::$app->params['white_house_clinic']['webflow_api_key'],
            Yii::$app->params['white_house_clinic']['webflow_review_collection'],
            Yii::$app->params['white_house_clinic']['webflow_published_to_live']
        );

        //load all old reviews
        $this->WFReviewWorker->loadAllReviews();*/

        // update reviews and insert new ones
        $this->refreshReviews();

        //delete not exists reviews from WebFlow collection
//        $this->WFReviewWorker->deleteOldReviews();

        return ExitCode::OK;
    }

    protected function refreshReviews2()
    {
        /*$accounts previusly populate*/
        /*(GMB - v4)*/
/*        $credentials_f = Yii::$app->params['white_house_clinic']['google_mybusiness_api_credential'];
        $client = new \Google_Client();
        $client->setApplicationName(Yii::$app->params['white_house_clinic']['google_mybusiness_api_application_name']);
        $client->setDeveloperKey(Yii::$app->params['white_house_clinic']['google_mybusiness_api_developer_key']);
        $client->setAuthConfig($credentials_f);
        $client->setScopes("https://www.googleapis.com/auth/plus.business.manage");
        $client->setSubject($accounts->email );
        $token = $client->refreshToken($accounts->refresh_token);
        $client->authorize();

        $locationName = "accounts/#######/locations/########";

        $mybusinessService = new Google_Service_Mybusiness($client);

        $reviews = $mybusinessService->accounts_locations_reviews;

        do{
            $listReviewsResponse = $reviews->listAccountsLocationsReviews($locationName, array('pageSize' => 100,
                'pageToken' => $listReviewsResponse->nextPageToken));

            $reviewsList = $listReviewsResponse->getReviews();
            foreach ($reviewsList as $index => $review) {
                //Accesing $review Object

                $review->createTime;
                $review->updateTime;
                $review->starRating;
                $review->reviewer->displayName;
                $review->reviewReply->comment;
                $review->getReviewReply()->getComment();
                $review->getReviewReply()->getUpdateTime();


            }

        }while($listReviewsResponse->nextPageToken);*/
    }

    /**
     * Get all properties from Google My Business and store their to WebFlow
     */
    protected function refreshReviews()
    {
        $pageNumber = 0;

        $client = new \Google_Client();
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . Yii::$app->params['white_house_clinic']['google_mybusiness_api_credential']);
        $client->useApplicationDefaultCredentials();
        $client->addScope('https://www.googleapis.com/auth/plus.business.manage');
        $gmb = new \Google_Service_MyBusiness( $client );
        $accounts = $gmb->accounts->listAccounts()->getAccounts();
        var_dump($accounts);
        $location = $gmb->accounts_locations->listAccountsLocations( $accounts[0]['name'] );
        var_dump( $location->getLocations() );
/*        $parser = new DezrezFeedParser();

        do {
            $pageNumber++;

            $data = $client->getProperties(
                Yii::$app->params['one_agency']['dezrez_live_api_key'],
                [
                    'PageSize' => $this->reviewsPerPage,
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

        echo "WebFlow: Inserted - " . $this->WFPropertyWorker->getInsertedCount() . "; Updated - " . $this->WFPropertyWorker->getUpdatedCount() . "\r\n";*/
    }

    /**
     * Get pack of parsed reviews and store their into WebFlow
     * @param array $reviews
     */
    private function storeReviewsIntoWebFlow(array $reviews)
    {
        foreach($reviews as $review){
            if($review instanceof GoogleReview) {
                if(!$this->WFPropertyWorker->storeProperty($property)){
                    echo "Error Dezrez: Cannot store property \r\n";
                    var_dump($property);
                }
            }
        }
    }

}