<?php
/**
 * Created by Kudin Dmitry
 * Date: 17.12.2018
 * Time: 14:15
 * See https://github.com/yiisoft/yii2-httpclient
 * https://github.com/yiisoft/yii2-httpclient/blob/master/docs/guide/basic-usage.md
 */

namespace app\components;

use Yii;
use yii\base\Component;
use yii\httpclient\Client;

/**
 * Client provide interface for WebFlow API.
 *
 * @author Kudin Dmitry <dakudin@gmail.com>
 */
class WebFlowClient extends Component
{

    const BASE_URL = 'https://api.webflow.com';

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
        $response = $request->send();
        if (!$response->getIsOk()) {
            throw new InvalidResponseException($response, 'Request failed with code: ' . $response->getStatusCode() . ', message: ' . $response->getContent());
        }

        //need some waiting for API (rate limit 60 requests per minute)
        sleep(2);

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
     * @param string $apiKey api key.
     * @param string $collectionId
     * @param integer $limit maximum number of items to be returned.
     * @param integer $offset used for pagination if collection has more than $limit items.
     * @param array $params additional request params.
     * @return array $response list of properties.
     */
    public function getCollectionItems($apiKey, $collectionId, $limit = 100, $offset = 0, $params = [])
    {
        $defaultParams = [
            'limit' => 100,
//            'offset' => 0
        ];

        $url = self::BASE_URL . '/collections/' . $collectionId . '/items?limit=' . $limit;

        if($offset > 0)
            $url .= '&offset=' . $offset;


        $request = $this->createRequest($apiKey)
            ->setMethod('GET')
            ->setUrl($url)
            ->setData(array_merge($defaultParams, $params));

        $response = $this->sendRequest($request);

        return $response;
    }

    /**
     * Create new collection item
     * @see https://developers.webflow.com/#create-new-collection-item
     * @param string $apiKey api key.
     * @param string $collectionId
     * @param boolean $needToPublish
     * @param array $params array with item fields.
     * @return array $response inserted item.
     */
    public function addCollectionItem($apiKey, $collectionId, $needToPublish = false, $params = [])
    {
        $defaultParams = [
            'fields' => []
        ];

        $url = self::BASE_URL . '/collections/' . $collectionId . '/items';

        if($needToPublish)
            $url .= '?live=true';

        $request = $this->createRequest($apiKey)
            ->setMethod('POST')
            ->setUrl($url)
            ->setData(array_merge($defaultParams, ['fields' => $params]));

        $response = $this->sendRequest($request);

        return $response;
    }

    /**
     * Update existing collection item
     * @see https://developers.webflow.com/#update-live-collection-item
     * @param string $apiKey api key.
     * @param string $collectionId
     * @param string $itemId
     * @param boolean $needToPublish
     * @param array $params array with item fields.
     * @return array $response updated item.
     */
    public function updateCollectionItem($apiKey, $collectionId, $itemId, $needToPublish = false, $params = [])
    {
        $defaultParams = [
            'fields' => []
        ];

        $url = self::BASE_URL . '/collections/' . $collectionId . '/items/' . $itemId;

        if($needToPublish)
            $url .= '?live=true';

        $request = $this->createRequest($apiKey)
            ->setMethod('PUT')
            ->setUrl($url)
            ->setData(array_merge($defaultParams, ['fields' => $params]));

        $response = $this->sendRequest($request);

        return $response;
    }

    /**
     * Patch existing collection item
     * @see https://developers.webflow.com/?shell#patch-live-collection-item
     * @param string $apiKey api key.
     * @param string $collectionId
     * @param string $itemId
     * @param boolean $needToPublish
     * @param array $params array with item fields.
     * @return array $response patched item.
     */
    public function patchCollectionItem($apiKey, $collectionId, $itemId, $needToPublish = false, $params = [])
    {
        $defaultParams = [
            'fields' => []
        ];

        $url = self::BASE_URL . '/collections/' . $collectionId . '/items/' . $itemId;

        if($needToPublish)
            $url .= '?live=true';

        $request = $this->createRequest($apiKey)
            ->setMethod('PATCH')
            ->setUrl($url)
            ->setData(array_merge($defaultParams, ['fields' => $params]));

        $response = $this->sendRequest($request);

        return $response;
    }

    /**
     * Delete collection item
     * @see https://developers.webflow.com/?shell#remove-collection-item
     * @param string $apiKey api key.
     * @param string $collectionId
     * @param string $itemId
     * @return boolean If item was deleted or not.
     */
    public function deleteCollectionItem($apiKey, $collectionId, $itemId)
    {
        $request = $this->createRequest($apiKey)
            ->setMethod('DELETE')
            ->setUrl(self::BASE_URL . '/collections/' . $collectionId . '/items/' . $itemId);

        $response = $this->sendRequest($request);

        if(!empty($response['deleted']) && $response['deleted']==1)
            return true;

        return false;
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