<?php
/**
 * Created by Kudin Dmitry
 * Date: 13.06.2019
 * Time: 17:30
 */

namespace app\components;

use app\models\GoogleReview;

/**
 * Class provide interface for inserting and updating review via WebFlow API.
 *
 * @author Kudin Dmitry <dakudin@gmail.com>
 */
class WFReviewWorkerBase extends WFWorkerBase
{
    /**
     * @var string WebFlow collection name of reviews
     */
    protected $reviewCollectionName;

    /**
     * @var WebFlowCollection WebFlow collection of reviews
     */
    protected $reviewCollection;

    /**
     * @param array $apiKey
     * @param $reviewCollectionName
     * @param $publishToLiveSite
     * @throws \Exception
     */
    public function __construct($apiKey, $reviewCollectionName, $publishToLiveSite)
    {
        parent::__construct($apiKey, $publishToLiveSite);

        $this->reviewCollectionName = $reviewCollectionName;

        if (!$this->prepareWFClient()) {
            throw new \Exception('Error - cannot prepare WebFlow client');
        }
    }

    /**
     * Detect which reviews don't exists in external source and delete their in WebFlow collection
     */
    public function deleteOldReviews(){
        $this->deleteOldItems($this->reviewCollection->getId());
    }

        /**
     * Prepare WebFlow client for first use. Load collections with old reviews IDs
     * @return bool
     */
    protected function prepareWFClient()
    {
        if (!$this->getSiteId()) return false;
        if (!$this->loadCollections()) return false;

        return true;
    }

    /**
     * Load Web Flow collections with reviews
     * @return bool
     */
    protected function loadCollections()
    {
        $collections = $this->_webFlowClient->getSiteCollections($this->_apiKey, $this->_siteId);

        if (!is_array($collections)) return false;

        foreach ($collections as $collection) {
            if ($collection['name'] == $this->reviewCollectionName) {
                $this->reviewCollection = new WebFlowCollection($collection['_id'], $collection['name'], $collection['slug'], $this->_webFlowClient);
                if (!$this->reviewCollection->loadFields($this->_apiKey))
                    return false;

                break;
            }
        }

        return true;
    }

    /**
     * Get all old items from WebFlow collection. Store reviews id for detecting which ones need to delete
     * after inserting/updating
     * @return bool
     */
    public function loadAllReviews()
    {
        $this->_wfItems = [];
        $offset = 0;

        do {
            if (!$this->reviewCollection->loadItems($this->_apiKey, $this->_itemsPerPage, $offset))
                return false;

            foreach ($this->reviewCollection->getItems() as $item) {
                $this->_wfItems[$item['review-id']] = [
                    'id' => $item['_id'],
                    'flagUpdated' => false,
                ];
            }

            $offset += $this->_itemsPerPage;
        } while ($this->reviewCollection->getItemsTotal() > 0
            && $this->reviewCollection->getItemsTotal() > $this->reviewCollection->getItemsOffset() + $this->reviewCollection->getItemsCount());

        echo "WebFlow reviews count before update: " . count($this->_wfItems) . "\r\n";

        return true;
    }

    /**
     * Store item as review in WebFlow collection (detect if it need to insert or update)
     * @param GoogleReview $review
     * @return bool
     */
    public function storeReview(GoogleReview $review)
    {
        $googleReviewId = (string)$review->reviewId;
        $item = $this->fillReview($review, $googleReviewId);

        $success = false;
        $isInserted = false;
        //try to update/insert WebFlow item
        echo "----------store review-------------" . $googleReviewId . "\r\n";
        // need to update item or insert a new one
        if (array_key_exists($googleReviewId, $this->_wfItems)) {
            $wfItem = $this->updateWFItem($this->reviewCollection->getId(), $googleReviewId, $this->_wfItems[$googleReviewId]['id'], $item);
        } else {
            $wfItem = $this->insertWFItem($this->reviewCollection->getId(), $googleReviewId, $item);
            $isInserted = true;
        }

        // if WebFlow cannot store review
        if (array_key_exists($this->fieldId, $wfItem) === FALSE) {
            $success = false;
        }

        if ($success) {
            echo "WebFlow: review `" . $googleReviewId . "` was saved successfully \r\n";

            if ($isInserted) $this->_insertedCount++;
            else $this->_updatedCount++;
        } else
            echo "Warning: review `" . $googleReviewId . "` wasn't saved properly \r\n";

        return $success;
    }

    /**
     * Need to realize this method in child class for filling webflow item with data
     * @param GoogleReview $review
     * @param $googleReviewId
     * @return array
     */
    protected function fillReview(GoogleReview $review, $googleReviewId)
    {
        return [];
    }
}