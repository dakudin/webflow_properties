<?php
/**
 * Created by Kudin Dmitry
 * Date: 20.12.2018
 * Time: 10:02
 */

namespace app\models;

use Yii;
use yii\base\Model;

class Property extends Model
{
    const ROLE_TYPE_SALE = 'Selling';
    const ROLE_TYPE_LET = 'Letting';
    const ROLE_TYPE_AUCTION = 'Auction';

    const STATUS_LET_AGREED = 'Let Agreed';
    const STATUS_TO_LET = 'To Let';
    const STATUS_SOLD = 'Sold';
    const STATUS_SSTC = 'SSTC';
    const STATUS_FOR_SALE = 'For Sale';

    public $id;
    public $name;
    public $roleType;
    public $marketStatus;
    public $price;
    public $priceText;
    public $numberOfRooms;
    public $numberOfBath;
    public $fullDescription;
    public $shortDescription;
    public $images;
    public $floorPlanImageUrl;
    public $propertyType;
    public $address;
    public $epc;
    public $brochure;
    public $featured;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // id, roleType, marketStatus and body are required
            [['id', 'roleType', 'marketStatus'], 'required'],
            ['price', 'number'],
            [['numberOfRooms', 'numberOfBath'], 'integer'],

            // roleType needs to be 'Selling' or 'Letting' or 'Auction'
            ['roleType', 'in', 'range' => [static::ROLE_TYPE_LET, static::ROLE_TYPE_SALE, static::ROLE_TYPE_AUCTION]],
            ['marketStatus', 'in', 'range' => [static::STATUS_LET_AGREED, static::STATUS_TO_LET,
                static::STATUS_SOLD, static::STATUS_SSTC, static::STATUS_FOR_SALE]],
        ];
    }
}