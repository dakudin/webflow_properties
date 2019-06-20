<?php

/**
 * Created by Kudin Dmitry
 * Date: 20.06.2019
 * Time: 8:57
 */

namespace app\components\whitehouse\clinic;

use app\components\WFReviewWorkerBase;
use app\models\GoogleReview;
use yii\helpers\StringHelper;

class WFReviewWorker extends WFReviewWorkerBase
{
    protected static $reviewDesktopLength = 170;

    protected static $reviewMobileLength = 140;

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
        $item = [
            '_archived' => false,
            '_draft' => false,
            'reviewid' => $googleReviewId,
            'stars' => static::getWFStarByGoogleStar($review->starRating),
            'review-full-text' => $review->comment,
            'review-text-desktop' => StringHelper::truncate($review->comment, $this->reviewDesktopLength, '...'),
            'review-text-mobile' => StringHelper::truncate($review->comment, $this->reviewMobileLength, '...'),
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