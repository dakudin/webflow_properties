<?php

/**
 * Created by Kudin Dmitry
 * Date: 20.06.2019
 * Time: 8:57
 */

namespace app\components\whitehouseclinic;

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
     * @param $publishToLiveSite
     * @throws \Exception
     */
    public function __construct($apiKey, $reviewCollectionName, $publishToLiveSite)
    {
        parent::__construct($apiKey, $reviewCollectionName, $publishToLiveSite);
    }

    /**
     * @param GoogleReview $review
     * @param $googleReviewId
     * @return array
     */
    protected function fillReview(GoogleReview $review, $googleReviewId)
    {
        $commentInOneLine = str_replace(["\r","\n"], ' ', $review->comment);
        $item = [
            '_archived' => false,
            '_draft' => false,
            'review-id' => $googleReviewId,
            'stars' => static::getWFStarByGoogleStar($review->starRating),
            'review-full-text' => $review->comment,
            'review-text-desktop' => StringHelper::truncate($commentInOneLine, static::$reviewDesktopLength, '...'),
            'review-text-mobile' => StringHelper::truncate($commentInOneLine, static::$reviewMobileLength, '...'),
            'creation-date' => $review->createTime, //\DateTime::createFromFormat('Y-m-d\TH:i:s.uP', $review->createTime)->format('m/d/Y'),
            'clinic-location' => $review->locationName,
            'location-address' => $review->locationAddress,
            'location-primaryphone' => $review->locationPrimaryPhone,
            'name' => $review->reviewerName,
            'slug' => $googleReviewId,
        ];

        return $item;
    }

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
}