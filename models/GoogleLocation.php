<?php
/**
 * Created by Kudin Dmitry
 * Date: 15.03.2021
 * Time: 12:50
 */


namespace app\models;


use yii\base\Model;

class GoogleLocation extends Model
{
    // Google identifier for this location in the form: accounts/{accountId}/locations/{locationId}
    public $name;

    // Location name should reflect your business's real-world name
    public $locationName;

    public $reviewAverageRating;

    public $totalReviewCount;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['name', 'locationName', 'reviewAverageRating', 'totalReviewCount'], 'required'],
            ['reviewAverageRating', 'min' => 1, 'max' => 5], //, 'numberPattern' => '/[1-5]\.?[0-9]$/'
            ['totalReviewCount', 'integerOnly'],
        ];
    }
/*
    public function validateTotalReviewCount($attribute, $params, $validator)
    {
        if(!($attribute == intval($attribute) || $attribute - 0.5 == intval($attribute))) {
            $this->addError($attribute, 'Total review count The country must be either "USA" or "Indonesia".');
        }
    }
*/
}