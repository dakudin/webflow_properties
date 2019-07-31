<?php
/**
 * Created by Kudin Dmitry
 * Date: 18.06.2019
 * Time: 11:41
 */

namespace app\models;

use Yii;
use yii\base\Model;

class GoogleReview extends Model
{
    const STAR_RATING_UNSPECIFIED = 'STAR_RATING_UNSPECIFIED';
    const STAR_ONE = 'ONE';
    const STAR_TWO = 'TWO';
    const STAR_THREE = 'THREE';
    const STAR_FOUR = 'FOUR';
    const STAR_FIVE = 'FIVE';

    public $reviewId;

    public $reviewerName;

//    public $reviewerPhotoUrl;

    public $reviewerIsAnonimous;

    public $starRating;

    public $comment;

    public $createTime;

//    public $updateTime;

    //https://developers.google.com/my-business/reference/rest/v4/accounts.locations#Location
    //https://developers.google.com/my-business/reference/rest/v4/PostalAddress
    public $locationStoreCode;
    public $locationName;
    public $locationAddress;
    public $locationPrimaryPhone;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // id, roleType, marketStatus and body are required
            [['reviewId', 'reviewerName', 'reviewerIsAnonimous', 'starRating', 'comment', 'createTime', 'locationName'], 'required'],
            ['reviewerIsAnonimous', 'boolean'],

            ['starRating', 'in', 'range' => [static::STAR_RATING_UNSPECIFIED, static::STAR_ONE,
                static::STAR_TWO, static::STAR_THREE, static::STAR_FOUR, static::STAR_FIVE]],
        ];
    }
}