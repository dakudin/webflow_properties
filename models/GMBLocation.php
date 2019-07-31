<?php
/**
 * Created by PhpStorm.
 * User: Monk
 * Date: 30.07.2019
 * Time: 19:27
 */

namespace app\models;

use Yii;
use yii\base\Model;


class GMBLocation extends Model
{
    public $name;

    public $locationName;

    public $primaryPhone;

    public $reviews;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // id, roleType, marketStatus and body are required
            [['name', 'locationName'], 'required'],
            [['name', 'locationName', 'primaryPhone'], 'string'],
        ];
    }
}