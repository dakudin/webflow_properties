<?php

namespace app\components;

class GMBServiceAccount extends GMyBusinessClient
{
    public function __construct($credentials, $locationDomain)
    {
        $this->client = new \Google_Client([
            'api_format_v2' => true // To enable more detailed error messages in responses, such as absent required fields
        ]);
        $this->client->setAuthConfig($credentials);
        $this->client->useApplicationDefaultCredentials();

        $this->locationDomain = $locationDomain;
        $this->averageRating = 5;
        $this->totalReviewCount = 0;

        $this->myBusinessService = new \Google_Service_MyBusiness($this->client);
        $this->myBusinessAccount = new \Google_Service_MyBusinessAccountManagement($this->client);
    }
}
