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

    /*
     * @var string WebFlow collection name of reviews stats
     */
    protected $reviewStatsCollectionName;

    /**
     * @var WebFlowCollection WebFlow collection of reviews stats
     */
    protected $reviewStatsCollection;

    /**
     * @var string WebFlow slug of total review field
     */
    protected $totalReviewsFieldSlug;

    /**
     * @var string WebFlow slug of overall rating field
     */
    protected $overallRatingFieldSlug;

    /**
     * @var string WebFlow slug of review stats item
     */
    protected $reviewStatsItemSlug;

    /**
     * @param array $apiKey
     * @param $reviewCollectionName
     * @param $reviewStatsCollectionName
     * @param $totalReviewsFieldSlug
     * @param $overallRatingFieldSlug
     * @param $reviewStatsItemSlug
     * @param $publishToLiveSite
     * @throws \Exception
     */
    public function __construct($apiKey, $reviewCollectionName, $reviewStatsCollectionName, $totalReviewsFieldSlug,
                                $overallRatingFieldSlug, $reviewStatsItemSlug, $publishToLiveSite)
    {
        parent::__construct($apiKey, $publishToLiveSite);

        $this->reviewCollectionName = $reviewCollectionName;
        $this->reviewStatsCollectionName = $reviewStatsCollectionName;
        $this->totalReviewsFieldSlug = $totalReviewsFieldSlug;
        $this->overallRatingFieldSlug = $overallRatingFieldSlug;
        $this->reviewStatsItemSlug = $reviewStatsItemSlug;

        $this->prepareWFClient();
    }

    /**
     * Detect which reviews don't exists in external source and delete their in WebFlow collection
     */
    public function deleteOldReviews(){
        $this->deleteOldItems($this->reviewCollection->getId());
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

        $success = true;
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
     * Update item as review stats in WebFlow collection
     * @param $totalReviews
     * @param $averageRating
     * @return bool
     */
    public function storeReviewStats($totalReviews, $averageRating)
    {
        $statsItems = $this->reviewStatsCollection->getItems();
//var_dump($statsItems);
        if(!(is_array($statsItems) && $statsItems[0]))
            return false;

        $item = $this->fillReviewStats($totalReviews, $averageRating);
var_dump($item);
        echo "----------update review stats-------------\r\n";

        $wfItem = $this->updateWFItem($this->reviewStatsCollection->getId(), '',  $statsItems[0][$this->fieldId], $item);
var_dump($wfItem);
        return array_key_exists($this->fieldId, $wfItem);
    }

    /**
     * Prepare WebFlow client for first use. Load collections with old reviews IDs
     * @throws \Exception
     */
    protected function prepareWFClient()
    {
        if (!$this->getSiteId())
            throw new \Exception("Cannot get Web Flow site id for getting collections");

        $this->loadCollections();
    }

    /**
     * Load Web Flow collections with reviews
     * @return bool
     * @throws \Exception
     */
    protected function loadCollections()
    {
        $collections = $this->_webFlowClient->getSiteCollections($this->_apiKey, $this->_siteId);

        if (!is_array($collections))
            throw new \Exception("Cannot get WF collection list");

        foreach ($collections as $collection) {
            if ($collection['name'] == $this->reviewCollectionName) {
                $this->reviewCollection = new WebFlowCollection($collection['_id'], $collection['name'] ,$collection['slug'], $this->_webFlowClient);
                if(!$this->reviewCollection->loadFields($this->_apiKey))
                    throw new \Exception("Cannot get WF collection $this->reviewCollectionName fields");
            }
            if ($collection['name'] == $this->reviewStatsCollectionName) {
                $this->reviewStatsCollection = new WebFlowCollection($collection['_id'], $collection['name'] ,$collection['slug'], $this->_webFlowClient);
                if(!$this->reviewStatsCollection->loadItems($this->_apiKey))
                    throw new \Exception("Cannot get WF collection fields");
            }
        }
//var_dump($this->reviewStatsCollection); die;
        return true;
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

    /**
     * Need to realize this method in child class for filling webflow item with data
     * @param $totalReviews
     * @param $averageRating
     * @return array
     */
    protected function fillReviewStats($totalReviews, $averageRating)
    {
        return [];
    }

    /*
     * Round total reviews down to 5 (177->175 / 175->175 / 171->170)
     */
    protected function getStatsTotalReviews($totalReviews)
    {
        $totalReviews = (round($totalReviews)%5 === 0) ? round($totalReviews) : round(($totalReviews+5/2)/5)*5-5;
        return $totalReviews > 0 ? $totalReviews : 1;
    }

    /*
     * Get WebFlow review rate by Google review rate
     * return empty string if rate isn't detected
     * @param $star string
     * @return string
     */
    protected static function getWFStarByGoogleStar($star)
    {
        switch($star)
        {
            case GoogleReview::STAR_FIVE :
                return '5 stars';
            case GoogleReview::STAR_FOUR :
                return '4 stars';
            case GoogleReview::STAR_THREE :
                return '3 stars';
            case GoogleReview::STAR_TWO :
                return '2 stars';
            case GoogleReview::STAR_ONE :
                return '1 star';
        }

        return '';
    }

    /*
     * Detect if review's rate equal to 4 or 5
     * @param $star string
     * @return boolean
     */
    protected static function isWFStarEqualTo4or5($star)
    {
        return $star==GoogleReview::STAR_FIVE || $star==GoogleReview::STAR_FOUR;
    }
}