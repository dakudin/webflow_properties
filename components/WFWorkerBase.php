<?php
/**
 * Created by Kudin Dmitry
 * Date: 04.06.2019
 * Time: 17:04
 */

namespace app\components;

use yii\base\Component;


class WFWorkerBase extends Component
{

    /**
     * @var string API key
     */
    protected $_apiKey;

    /**
     * @var string WebFlow slug for field id
     */
    protected $fieldId = '_id';

    /**
     * @var bool Flag that show for which kind of items need to work: Live or not
     * set to true for publishing to live site
     */
    protected $_publishToLiveSite = false;

    /**
     * @var \app\components\WebFlowClient client for work with WebFlow via API
     */
    protected $_webFlowClient;

    /**
     * @var string WebFlow site id with which items will insert/update/delete
     */
    protected $_siteId;

    /**
     * @var array of WebFlow old items ids
     */
    protected $_wfItems;

    /**
     * @var int Number of items of the collection which we get per request
     */
    protected $_itemsPerPage = 20;

    /**
     * @var int Number of items which were inserted to collection
     */
    protected $_insertedCount = 0;

    /**
     * @var int Number of items which were updated in collection
     */
    protected $_updatedCount = 0;

    /**
     * @param array $apiKey
     * @param $publishToLiveSite
     * @throws \Exception
     */
    public function __construct($apiKey, $publishToLiveSite)
    {
        parent::__construct();

        $this->_apiKey = $apiKey;
        $this->_publishToLiveSite = $publishToLiveSite===true;
        $this->_webFlowClient = new WebFlowClient();
    }

    /**
     * @return int Number of items which were inserted to collection
     */
    public function getInsertedCount()
    {
        return $this->_insertedCount;
    }

    /**
     * @return int Number of items which were inserted to collection
     */
    public function getUpdatedCount()
    {
        return $this->_updatedCount;
    }

    /**
     * Get Web Flow site id for getting collections
     * @return bool
     */
    protected function getSiteId()
    {
        $info = $this->_webFlowClient->getInfo($this->_apiKey);

        if(!isset($info['sites'])) return false;

        // get only first site from list
        return (is_array($info['sites']) && $this->_siteId = $info['sites'][0]);
    }

    /**
     * Detect which items don't exists in external source and delete their in WebFlow collection
     * @param string $collectionId ID of collection of updating item
     */
    protected function deleteOldItems($collectionId){
        $deleted = 0;

        foreach($this->_wfItems as $wfItemId=>$wfItemData) {
            if (!$wfItemData['flagUpdated']){
                if (!$this->deleteWFItem($collectionId, $wfItemData['id'])) {
                    echo 'WebFlow: Couldn\'t delete item ID-' . $wfItemData['id'] . ' with name `' . $wfItemId . '`' . "\r\n";
                }else {
                    $deleted++;
                }
            }
        }

        echo 'WebFlow: Deleted - ' . $deleted . "\r\n";
    }

    /**
     * Insert new item to WebFlow collection
     * @param string $collectionId ID of collection of updating item
     * @param string $sourceItemId ID of item for inserting
     * @param array $item Item of WebFlow collection
     * @return array of inserted WebFlow item
     */
    protected function insertWFItem($collectionId, $sourceItemId, $item)
    {
        echo "----------insert item-------------".$sourceItemId."\r\n";

        $result = $this->_webFlowClient->addCollectionItem(
            $this->_apiKey,
            $collectionId,
            $this->_publishToLiveSite,
            $item
        );

        if(array_key_exists($this->fieldId, $result) !== FALSE){
            $this->_wfItems[$sourceItemId] = [
                'id' => $result['_id'],
                'flagUpdated' => true,
            ];
        }

        return $result;
    }

    /**
     * Update item of WebFlow collection
     * @param string $collectionId ID of collection of updating item
     * @param string $sourceItemId ID of item for updating
     * @param string $itemId ID of item for updating
     * @param array $item Item of WebFlow collection
     * @return array of updated WebFlow item
     */
    protected function updateWFItem($collectionId, $sourceItemId, $itemId, $item)
    {
        echo "----------update item-------------".$sourceItemId."\r\n";

        $result = $this->_webFlowClient->updateCollectionItem(
            $this->_apiKey,
            $collectionId,
            $itemId,
            $this->_publishToLiveSite, // set to true for publishing to live site
            $item
        );

/*        if($sourceItemId==15869945){
            var_dump($item);
            echo "\r\n----------------------------------------------\r\n";
            var_dump($result);
        }*/

        if(array_key_exists($this->fieldId, $result) !== FALSE){
            $this->_wfItems[$sourceItemId]['flagUpdated'] = true;
        }

        return $result;
    }

    /**
     * Patch item of WebFlow collection
     * @param string $collectionId ID of collection of updating item
     * @param string $sourceItemId ID of item for patching
     * @param string $itemId ID of item for patching
     * @param array $item Item of WebFlow collection
     * @return array of patched WebFlow item
     */
    protected function patchWFItem($collectionId, $sourceItemId, $itemId, $item)
    {
        echo "----------patch item-------------".$sourceItemId."\r\n";

        $result = $this->_webFlowClient->patchCollectionItem(
            $this->_apiKey,
            $collectionId,
            $itemId,
            $this->_publishToLiveSite, // set to true for publishing to live site
            $item
        );

/*        if($sourceItemId==15869945){
            var_dump($item);
            echo "\r\n----------------------------------------------\r\n";
            var_dump($result);
        }*/

        return $result;
    }

    /**
     * Delete item of WebFlow collection
     * @param string $collectionId ID of collection of updating item
     * @param $itemId
     * @return bool
     */
    protected function deleteWFItem($collectionId, $itemId)
    {
        return $this->_webFlowClient->deleteCollectionItem(
            $this->_apiKey,
            $collectionId,
            $itemId
        );
    }

}