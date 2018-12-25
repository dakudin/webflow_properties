<?php
/**
 * Created by Kudin Dmitry
 * Date: 15.12.2018
 * Time: 0:28
 */

namespace app\components;

use Yii;
use yii\base\Component;
use yii\httpclient\Client;
//use yii\helpers\Json;

class DezrezClient extends Component
{
    const BASE_URL = 'https://api.dezrez.com/api';

    const SEARCH_RESULT_URL = 'simplepropertyrole/search';

    protected $fullDetailsUrl = 'https://api.dezrez.com/api/simplepropertyrole/ROLEID?APIKey=';

    /**
     * @var string api key.
     * This value mainly used as HTTP request parameter.
     */
    private $_apiKey;

    /**
     * @var array cURL request options. Option values from this field will overwrite corresponding
     * values from [[defaultRequestOptions()]].
     */
    private $_requestOptions = [];


    /**
     * @param string $key api key.
     */
    public function setApiKey($key)
    {
        $this->_apiKey = $key;
    }

    /**
     * @return string service id
     */
    public function getApiKey()
    {
        if (empty($this->_apiKey)) {
            $this->_apiKey = '';
        }

        return $this->_apiKey;
    }

    /**
     * @param array $options HTTP request options.
     */
    public function setRequestOptions(array $options)
    {
        $this->_requestOptions = $options;
    }

    /**
     * @return array HTTP request options.
     */
    public function getRequestOptions()
    {
        return $this->_requestOptions;
    }

    /**
     * Sends the given HTTP request, returning response data.
     * @param \yii\httpclient\Request $request HTTP request to be sent.
     * @return array response data.
     * @throws InvalidResponseException on invalid remote response.
     */
    protected function sendRequest($request)
    {
        $response = $request->send();

        if (!$response->getIsOk()) {
            throw new InvalidResponseException($response, 'Request failed with code: ' . $response->getStatusCode() . ', message: ' . $response->getContent());
        }

        return $response->getData();
    }


    /**
     * To access the feed you will be required to pass through some basic search parameters before any data will be returned.
     * @see https://developer.dezrez.com/rezi-webguide/making-requests
     * @param array $apiKey api key.
     * @param array $params additional request params.
     * @return Data list of properties.
     */
    public function getProperties($apiKey, $params = [])
    {
        $defaultParams = [
            'BranchIdList' => [],
            'MinimumPrice' => 0,
            'MaximumPrice' => 9999999,
            'MarketingFlags' => ["ApprovedForMarketingWebsite"],
            'PageSize' => 10,
            'PageNumber' => 1
        ];

        $request = $this->createRequest()
            ->setMethod('POST')
            ->setUrl(self::BASE_URL . '/' . self::SEARCH_RESULT_URL . '?APIKey=' . $apiKey)
            ->setData(array_merge($defaultParams, $params));

//        $this->applyClientCredentialsToRequest($request);

        $response = $this->sendRequest($request);

//        $token = $this->createToken(['params' => $response]);
//        $this->setAccessToken($token);

        return $response;
    }

    /**
     * Creates HTTP request instance.
     * @return \yii\httpclient\Request HTTP request instance.
     */
    public function createRequest()
    {
        $client = new Client([
//            'baseUrl' => self::BASE_URL,
            'requestConfig' => [
                'format' => Client::FORMAT_JSON
            ],
            'responseConfig' => [
                'format' => Client::FORMAT_JSON
            ],
        ]);

        return $client
            ->createRequest()
            ->setHeaders([
                'Rezi-Api-Version' => '1.0',
                'Content-Type' => 'application/json',
            ])
//            ->setFormat(Client::FORMAT_JSON)
            ->addOptions($this->defaultRequestOptions())
            ->addOptions($this->getRequestOptions());
    }

    /**
     * Returns default HTTP request options.
     * @return array HTTP request options.
     */
    protected function defaultRequestOptions()
    {
        return [
            'timeout' => 30,
            'sslVerifyPeer' => false,
        ];
    }

}

