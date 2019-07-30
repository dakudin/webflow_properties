<?php
/**
 * Created by Kudin Dmitry
 * Date: 30.07.2019
 * Time: 15:32
 */

namespace app\components;

use Yii;
use yii\base\Component;

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

    public function _constructor($clientId, $clientSecret, $clientEmail, $refreshToken)
    {
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
    protected function refreshReviews()
    {

        $this->myBusinessService = new \Google_Service_MyBusiness($this->client);
        $accounts = $this->myBusinessService->accounts;
        $accountsList = $accounts->listAccounts()->getAccounts();
        $params = ['pageSize' => 100];

        foreach ($accountsList as $accKey => $account) {
//            var_dump('$account->name', $account->name);

            $locations = $this->myBusinessService->accounts_locations;
            $locationsList = $locations->listAccountsLocations($account->name)->getLocations();
//            var_dump('$locationsList', $locationsList);


            // Final Goal of my Code
            if (empty($locationsList) === false) {
                foreach ($locationsList as $locKey => $location) {

                    $reviews = $this->myBusinessService->accounts_locations_reviews;

                    do {
                        if(isset($nextPageToken)){
                            $params['pageToken'] = $nextPageToken;
                        }
                        $listReviewsResponse = $reviews->listAccountsLocationsReviews($location->name, $params);

                        $reviewsList = $listReviewsResponse->getReviews();
                        foreach ($reviewsList as $index => $review) {
                            //Accesing $review Object
                            $review->
                            echo                $review->createTime;
                            echo                $review->updateTime;
                            echo                $review->starRating;
                            echo                $review->reviewer->displayName . "\r\n";
                            echo                $review->reviewReply->comment;
                            //                $review->getReviewReply()->getComment();
                            //                $review->getReviewReply()->getUpdateTime();
                        }

                        $nextPageToken = $listReviewsResponse->nextPageToken;

                    } while ($listReviewsResponse->nextPageToken);
                }
            }
        }

    }

}