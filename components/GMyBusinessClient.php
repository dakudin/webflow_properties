<?php
/**
 * Created by Kudin Dmitry
 * Date: 30.07.2019
 * Time: 15:32
 */

namespace app\components;

use app\models\GoogleLocation;
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

    protected $locations;

    protected $locationDomain;

    protected $averageRating;

    protected $totalReviewCount;

    public function __construct($clientId, $clientSecret, $clientEmail, $refreshToken, $locationDomain)
    {
        parent::__construct();

        $this->client = new \Google_Client();
        $this->client->setClientId($clientId);
        $this->client->setClientSecret($clientSecret);
        $this->client->addScope($this->scope);
        $this->client->setSubject($clientEmail);
        $this->client->refreshToken($refreshToken);
        $this->locationDomain = $locationDomain;
        $this->averageRating = 5;
        $this->totalReviewCount = 0;
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

    public function getAverageRating(){
        return $this->averageRating;
    }

    public function getTotalReviewCount(){
        return $this->totalReviewCount;
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
        $this->locations = [];
        $this->reviews = [];
        $locations = $this->myBusinessService->accounts_locations;
        $locationsList = $locations->listAccountsLocations($account->name)->getLocations();
//            var_dump('$locationsList', $locationsList);

        if (empty($locationsList) === false) {
            foreach ($locationsList as $locKey => $location) {
                //if location from specified domain get reviews
                if(strpos($location->websiteUrl, $this->locationDomain) !== FALSE) {
                    $this->getGMBReviews($location);
                }
            }
        }

        $this->countReviewStats();
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
                $this->addReview($location, $review);
            }

            $this->addReviewStats($location, $listReviewsResponse->averageRating, $listReviewsResponse->totalReviewCount);

            $nextPageToken = $listReviewsResponse->nextPageToken;

            echo "Location: " . $location->locationName . " / Average rating: " . $listReviewsResponse->averageRating . "/ Review count: " . $listReviewsResponse->totalReviewCount . "\r\n";

        } while ($listReviewsResponse->nextPageToken);
    }

    protected function addReviewStats($location, $averageRating, $totalReviewCount)
    {
        if(key_exists($location->name, $this->locations)) return;

        if(!$averageRating || !$totalReviewCount) return;

        $reviewStat = new GoogleLocation();
        $reviewStat->name = $location->name;
        $reviewStat->locationName = $location->locationName;
        $reviewStat->reviewAverageRating = $averageRating;
        $reviewStat->totalReviewCount = $totalReviewCount;
        if(!$reviewStat->validate()){
            echo "Error on validation location: " . $location->locationName . " review stats\r\n";
            print_r($reviewStat->getErrors());
            throw new \Exception( "Error on validation location: " . $location->locationName . " review stats" );
        }

        $this->locations[$location->name] = $reviewStat;
    }

    /*
     * Count average rating and total reviews count
     */
    private function countReviewStats()
    {
        $this->averageRating = 5;
        $this->totalReviewCount = 0;
        $totalReviewCount = 0;
        $ratedReviewCount = 0;
        $averageRating = 0;

        foreach ($this->reviews as $review){
            if(!$review instanceof GoogleReview) continue;

            $totalReviewCount++;

            if($review->starRating == GoogleReview::STAR_RATING_UNSPECIFIED) continue;

            $ratedReviewCount++;
            $averageRating += $review->getStarRatingAsNumber();
        }

        $this->totalReviewCount = $totalReviewCount;

        if($ratedReviewCount>0){
            echo "Rating data - sumRates:$averageRating, cnt:$ratedReviewCount, avr:" . ($averageRating / $ratedReviewCount);
            $this->totalReviewCount = $totalReviewCount;
            $this->averageRating = round($averageRating / $ratedReviewCount, 1, PHP_ROUND_HALF_UP);
        }
    }

    protected function addReview($location, $review)
    {
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

    protected function getLocationAddress($location)
    {
        $address = implode(', ', $location->address->addressLines);

        $address .= ', ' . $location->address->locality . ' '
            . $location->address->postalCode;

        return $address;
    }

}