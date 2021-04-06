<?php

namespace app\components;

class GMBServiceAccount extends GMyBusinessClient
{
    public function __construct($credentials, $locationDomain)
    {
        $client = new \Google\Client();
        $client->setAuthConfig($credentials);
        $client->useApplicationDefaultCredentials();

        $this->locationDomain = $locationDomain;
        $this->averageRating = 5;
        $this->totalReviewCount = 0;

        $this->myBusinessService = new \Google_Service_MyBusiness($this->client);
        $this->myBusinessAccount = new \Google_Service_MyBusinessAccountManagement($this->client);
    }
}
