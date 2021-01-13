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
 * https://github.com/googleapis/google-api-php-client A PHP client library for accessing Google APIs
 * https://developers.google.com/identity/protocols/OAuth2ServiceAccount Using OAuth 2.0 for Server to Server Applications
 * https://developers.google.com/my-business/content/location-data#list_all_locations Google My Business API Guide
 * https://developers.google.com/oauthplayground for getting new refresh token
 * https://stackoverflow.com/questions/54412869/how-to-get-business-locations-and-reviews-via-service-account-authentication topic about problem of getting locations
 * https://www.en.advertisercommunity.com/t5/Google-My-Business-API/GMB-API-Returning-empty-locations/m-p/1755477 another topic about problem
 */
class GMyBusinessClient extends Component
{
    protected $client;

    protected $myBusinessService;

    protected $scope = "https://www.googleapis.com/auth/plus.business.manage";

    protected $reviews;

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

    public function getReviews()
    {
        return $this->reviews;
    }

    protected function refreshAccounts()
    {
        $accounts = $this->myBusinessService->accounts;
        $accountsList = $accounts->listAccounts()->getAccounts();

        foreach ($accountsList as $accKey => $account) {
//            var_dump('$account->name', $account->name);
            $this->refreshLocations($account);
        }
    }

    protected function refreshLocations($account)
    {
        $this->reviews = [];
        $locations = $this->myBusinessService->accounts_locations;
        $locationsList = $locations->listAccountsLocations($account->name)->getLocations();
//            var_dump('$locationsList', $locationsList);

        if (empty($locationsList) === false) {
            foreach ($locationsList as $locKey => $location) {
                $this->getGMBReviews($location);
            }
        }
    }

    /*
     * https://developers.google.com/my-business/reference/rest/v4/accounts.locations
     */
    protected function getGMBReviews($location)
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
                $googleReview->locationAddress = $this->getLocationAddress($location);
                $googleReview->reviewId = $review->reviewId;
                $googleReview->reviewerName = $review->reviewer->displayName;
                $googleReview->reviewerIsAnonimous = $review->reviewer->isAnonymous;
                $googleReview->reviewerPhotoUrl = $review->reviewer->profilePhotoUrl;
                $googleReview->starRating = $review->starRating;
                $googleReview->comment = $review->comment;
                $googleReview->createTime = $review->createTime;
                // $review->getReviewReply()->getComment();
                $this->reviews[] = $googleReview;
            }

            $nextPageToken = $listReviewsResponse->nextPageToken;
            echo "Location: " . $location->locationName . " / Average rating: " . $listReviewsResponse->averageRating . "/ Review count: " . $listReviewsResponse->totalReviewCount . "\r\n";

        } while ($listReviewsResponse->nextPageToken);

    }

    protected function getLocationAddress($location)
    {
        $address = implode(', ', $location->address->addressLines);

        $address .= ', ' . $location->address->locality . ' '
            . $location->address->postalCode;

        return $address;
    }

}