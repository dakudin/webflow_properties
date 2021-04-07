<?php

namespace app\components;

class GMBServiceAccount extends GMyBusinessClient
{
    public function __construct($credentials, $locationDomain, $isOldVersion = true)
    {
        $this->setGoogleClient();
        $this->client->setAuthConfig($credentials);
        $this->client->useApplicationDefaultCredentials();

        $this->prepareClient($locationDomain);
    }
}
