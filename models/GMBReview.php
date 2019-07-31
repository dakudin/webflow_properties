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

class GMBReview extends Model
{
    $id;

    $authorName;

    $starRating;

    $comment;

    $createTime;

}