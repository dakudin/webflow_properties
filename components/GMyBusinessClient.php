<?php
/**
 * Created by Kudin Dmitry
 * Date: 30.07.2019
 * Time: 15:32
 */

namespace app\components;

use app\models\GoogleReview;
use Yii;
use yii\base\Component;
//use app\models\GMBLocation;
//use app\models\GMBReview;

/**
 * Client provide interface for Google My Business API.
 *
 * @author Kudin Dmitry <dakudin@gmail.com>
 *
 * https://developers.google.com/oauthplayground for getting new refresh token
 */
class GMyBusinessClient extends Component
{
    protected $client;

    protected $myBusinessService;

    protected $scope = "https://www.googleapis.com/auth/plus.business.manage";

    protected $locations;

    public function __construct($clientId, $clientSecret, $clientEmail, $refreshToken)
    {
        parent::__construct();

        $this->client = new \Google_Client();
        $this->client->setClientId($clientId);
        $this->client->setClientSecret($clientSecret);
        $this->client->addScope($this->scope);
        $this->client->setSubject($clientEmail);
        $this->client->refreshToken($refreshToken);
    }

    /*
     *  get reviews by via web client authentication
     */
    public function refreshAllReviews()
    {
        $this->myBusinessService = new \Google_Service_MyBusiness($this->client);
        $this->refreshAccounts();
    }

    protected function refreshAccounts()
    {
        $accounts = $this->myBusinessService->accounts;
        $accountsList = $accounts->listAccounts()->getAccounts();

        foreach ($accountsList as $accKey => $account) {
//            var_dump('$account->name', $account->name);
            $this->refreshLocations($account);
        }
        var_dump($this->locations);
    }

    protected function refreshLocations($account)
    {
        $this->locations = [];
        $locations = $this->myBusinessService->accounts_locations;
        $locationsList = $locations->listAccountsLocations($account->name)->getLocations();
//            var_dump('$locationsList', $locationsList);

        if (empty($locationsList) === false) {
            foreach ($locationsList as $locKey => $location) {
                $this->getReviews($location);
            }
        }
    }

    /*
     * https://developers.google.com/my-business/reference/rest/v4/accounts.locations
     */
    protected function getReviews($location)
    {
        $params = ['pageSize' => 100];
        $reviews = $this->myBusinessService->accounts_locations_reviews;

        do {
            if(isset($nextPageToken)){
                $params['pageToken'] = $nextPageToken;
            }
            $listReviewsResponse = $reviews->listAccountsLocationsReviews($location->name, $params);

            $reviewsList = $listReviewsResponse->getReviews();
            foreach ($reviewsList as $index => $review) {
                $googleReview = new GoogleReview();
                $googleReview->locationStoreCode = $location->storeCode;
                $googleReview->locationName = $location->locationName;
                $googleReview->locationPrimaryPhone = $location->primaryPhone;
                $googleReview->locationAddress = implode(', ', $location->address->addressLines);
                $googleReview->reviewId = $review->reviewId;
                $googleReview->reviewerName = $review->reviewer->displayName;
                $googleReview->reviewerIsAnonimous = $review->reviewer->isAnonymous;
                $googleReview->starRating = $review->starRating;
                $googleReview->comment = $review->comment;
                $googleReview->createTime = $review->createTime;
                // $review->getReviewReply()->getComment();
                $this->locations[] = $googleReview;
            }

            $nextPageToken = $listReviewsResponse->nextPageToken;

        } while ($listReviewsResponse->nextPageToken);

    }

}