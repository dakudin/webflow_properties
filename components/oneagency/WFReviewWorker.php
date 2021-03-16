<?php

/**
 * Created by Kudin Dmitry
 * Date: 14.08.2019
 * Time: 14:57
 */

namespace app\components\oneagency;

use app\components\WFReviewWorkerBase;
use app\models\GoogleReview;
use yii\helpers\StringHelper;

class WFReviewWorker extends WFReviewWorkerBase
{
    protected static $reviewDesktopLength = 167;

    protected static $reviewMobileLength = 137;

    /**
     * @param array $apiKey
     * @param $reviewCollectionName
     * @param $reviewStatsCollectionName
     * @param $totalReviewsFieldSlug
     * @param $overallRatingFieldSlug
     * @param $reviewStatsItemSlug
     * @param $reviewStatsItemName
     * @param $publishToLiveSite
     * @throws \Exception
     */
    public function __construct($apiKey, $reviewCollectionName, $reviewStatsCollectionName, $totalReviewsFieldSlug,
                                $overallRatingFieldSlug, $reviewStatsItemSlug, $reviewStatsItemName, $publishToLiveSite)
    {
        parent::__construct($apiKey, $reviewCollectionName, $reviewStatsCollectionName, $totalReviewsFieldSlug,
            $overallRatingFieldSlug, $reviewStatsItemSlug, $reviewStatsItemName, $publishToLiveSite);
    }

    /**
     * @param GoogleReview $review
     * @param $googleReviewId
     * @return array
     */
    protected function fillReview(GoogleReview $review, $googleReviewId)
    {
        $commentInOneLine = trim(str_replace(["\r","\n"], ' ', $review->comment));
        $item = [
            '_archived' => false,
            '_draft' => false,
            'review-id' => $googleReviewId,
            'stars' => static::getWFStarByGoogleStar($review->starRating),
            '4-5-stars-only' => static::isWFStarEqualTo4or5($review->starRating),
            'review-full-text' => empty($commentInOneLine) ? '' : "“" . $commentInOneLine . "”",
            'review-text-desktop' => empty($commentInOneLine) ? '' : "“" . StringHelper::truncate($commentInOneLine, static::$reviewDesktopLength, '...') . "”",
            'review-text-mobile' => empty($commentInOneLine) ? '' : "“" . StringHelper::truncate($commentInOneLine, static::$reviewMobileLength, '...') . "”",
            'creation-date' => $review->createTime, //\DateTime::createFromFormat('Y-m-d\TH:i:s.uP', $review->createTime)->format('m/d/Y'),
            'location-name' => $review->locationName,
            'location-address' => $review->locationAddress,
            'location-primaryphone' => $review->locationPrimaryPhone,
            'name' => $review->reviewerName,
            'profile-image' => $review->reviewerIsAnonimous ? '' : $review->reviewerPhotoUrl,
            'slug' => $googleReviewId,
        ];

        return $item;
    }

    protected function fillReviewStats($totalReviews, $averageRating)
    {
        $item = [
            '_archived' => false,
            '_draft' => false,
            $this->totalReviewsFieldSlug => $this->getStatsTotalReviews($totalReviews),
            $this->overallRatingFieldSlug => $averageRating,
            'slug' => $this->reviewStatsItemSlug,
            'name' => $this->reviewStatsItemName,
//    ["_id"]=> "604f35624fc50cd870dca323"
        ];

        return $item;
    }
}