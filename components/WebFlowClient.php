<?php
/**
 * Created by Kudin Dmitry
 * Date: 17.12.2018
 * Time: 14:15
 */

namespace app\components;

use Yii;
use yii\base\Component;
use yii\httpclient\Client;

class WebFlowClient extends Component
{

    const BASE_URL = 'https://api.webflow.com';

    const PROPERTY_COLLECTION_ID = '5c08e1d27fe16683b8309d92';

    /**
     * @var array cURL request options. Option values from this field will overwrite corresponding
     * values from [[defaultRequestOptions()]].
     */
    private $_requestOptions = [];


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
var_dump($request);
        $response = $request->send();
var_dump($response);
        if (!$response->getIsOk()) {
            throw new InvalidResponseException($response, 'Request failed with code: ' . $response->getStatusCode() . ', message: ' . $response->getContent());
        }

        return $response->getData();
    }

    /**
     * Get collection schema.
     * @see https://developers.webflow.com/#get-collection-with-full-schema
     * @param string $collectionId
     * @param string $apiKey api key.
     * @param array $params additional request params.
     * @return array $response list of properties.
     */
    public function getCollectionSchema($apiKey, $collectionId, $params = [])
    {
        $defaultParams = [
        ];

        $request = $this->createRequest($apiKey)
            ->setMethod('GET')
            ->setUrl(self::BASE_URL . '/collections/' . $collectionId)
            ->setData(array_merge($defaultParams, $params));

        $response = $this->sendRequest($request);

        return $response;
    }

    /**
     * Get all items for a collection
     * @see https://developers.webflow.com/?shell#get-all-items-for-a-collection
     * @param string $collectionId
     * @param string $apiKey api key.
     * @param array $params additional request params.
     * @return array $response list of properties.
     */
    public function getCollectionItems($apiKey, $collectionId, $params = [])
    {
        $defaultParams = [
            'limit' => 100,
//            'offset' => 0
        ];

        $request = $this->createRequest($apiKey)
            ->setMethod('GET')
            ->setUrl(self::BASE_URL . '/collections/' . $collectionId . '/items')
            ->setData(array_merge($defaultParams, $params));

        $response = $this->sendRequest($request);

        return $response;
    }

    /**
     * Create new collection item
     * @see https://developers.webflow.com/#create-new-collection-item
     * @param string $apiKey api key.
     * @param string $collectionId
     * @param array $params array with item fields.
     * @return array $response inserted item.
     */
    public function addCollectionItem($apiKey, $collectionId, $params = [])
    {
        $defaultParams = [
            'fields' => []
        ];

        $request = $this->createRequest($apiKey)
            ->setMethod('POST')
            ->setUrl(self::BASE_URL . '/collections/' . $collectionId . '/items')
            ->setData(array_merge($defaultParams, ['fields' => $params]));

        $response = $this->sendRequest($request);

        return $response;
    }

    /**
     * Upload new image
     * @see https://developers.webflow.com/?shell#upload-image
     * @param string $apiKey api key.
     * @param string $collectionId
     * @param string $itemId
     * @param array $params array with item fields.
     * @return array $response inserted item.
     */
    public function uploadImageToCollectionItem($apiKey, $collectionId, $itemId, $params = [])
    {
        $defaultParams = [
            'fields' => []
        ];

        $request = $this->createRequest($apiKey)
            ->setMethod('POST')
            ->setUrl(self::BASE_URL . '/collections/' . $collectionId . '/items/' . $itemId)
            ->setData(array_merge($defaultParams, ['fields' => $params]));

        $response = $this->sendRequest($request);

        return $response;
    }

    /**
     * Creates HTTP request instance.
     * @param string $apiKey api key.
     * @return \yii\httpclient\Request HTTP request instance.
     */
    public function createRequest($apiKey)
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
                'Accept-Version' => '1.0.0',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey,
            ])
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