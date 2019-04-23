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

    const WF_STATUS_LET_AGREED = '5c9bba955bef787f6ea75e3b';
    const WF_STATUS_TO_LET = '5c9bba955bef7869c4a75e23';
    const WF_STATUS_SOLD = '5c9bba955bef78bc8da75e0f';
    const WF_STATUS_SSTC = '5c9bba955bef784312a75dfb';
    const WF_STATUS_FOR_SALE = '5c9bba955bef78a863a75de8';

    const WF_ROLE_TYPE_SALE = '5c9bba955bef7841c7a75eb1';
    const WF_ROLE_TYPE_LET = '5c9bba955bef780bffa75eb2';
    const WF_ROLE_TYPE_AUCTION = '5c9bba955bef78fa81a75eb3';

    const WF_FILTERED_CATEGORY_SALE = '65cc23e5c2a1c68df968ca6fb85777f5';
    const WF_FILTERED_CATEGORY_LET = '0cdda993be013d7085e688590f428a74';
    const WF_FILTERED_CATEGORY_AUCTION = '6234b37e65fac579c03fe4f13d487492';


    /*
    {"items":[{"_archived":false,"_draft":false,"name":
    "Let Agreed","slug":"let-agreed","_id":"5c08e296a482fe2de2b0dfdb"
    "To Let","slug":"to-let","updated-on":"2018-12-06T08:49:19.343Z","published-on":null,"published-by":null,"_id":"5c08e28f19c6a579cbbb689e"},
    "Sold","slug":"sold","updated-on":"2018-12-06T08:49:12.699Z","published-on":null,"published-by":null,"_cid":"5c08e2753ae945040ed9c693","_id":"5c08e288179c1c5a660f41d8"},
    "SSTC","slug":"sstc","updated-on":"2018-12-06T08:49:07.505Z","published-on":null,"published-by":null,"_cid":"5c08e2753ae945040ed9c693","_id":"5c08e2833ae945819ad9c699"},
    "For Sale","slug":"for-sale","updated-on":"2018-12-06T08:49:01.214Z","updated-by":"Person_5aba15c5ba193676c79b4eae","created-on":"2018-12-06T08:49:01.214Z","created-by":"Person_5aba15c5ba193676c79b4eae","published-on":null,"published-by":null,"_cid":"5c08e2753ae945040ed9c693","_id":"5c08e27da482fe1e50b0dfd2"}],"count":5,"limit":100,"offset":0,"total":5}
      */
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
            ['roleType', 'in', 'range' => [self::ROLE_TYPE_LET, self::ROLE_TYPE_SALE, self::ROLE_TYPE_AUCTION]],
            ['marketStatus', 'in', 'range' => [self::STATUS_LET_AGREED, self::STATUS_TO_LET,
                self::STATUS_SOLD, self::STATUS_SSTC, self::STATUS_FOR_SALE]],
        ];
    }

    public function getWebflowMarketStatus()
    {
        switch ($this->marketStatus) {
            case self::STATUS_LET_AGREED : return self::WF_STATUS_LET_AGREED;
            case self::STATUS_TO_LET : return self::WF_STATUS_TO_LET;
            case self::STATUS_SOLD : return self::WF_STATUS_SOLD;
            case self::STATUS_SSTC : return self::WF_STATUS_SSTC;
            case self::STATUS_FOR_SALE : return self::WF_STATUS_FOR_SALE;
        }

        return false;
    }

    public function getWebflowRoleType()
    {
        switch ($this->roleType) {
            case self::ROLE_TYPE_LET : return self::WF_ROLE_TYPE_LET;
            case self::ROLE_TYPE_SALE : return self::WF_ROLE_TYPE_SALE;
            case self::ROLE_TYPE_AUCTION : return self::WF_ROLE_TYPE_AUCTION;
        }

        return false;
    }

    public function getWebflowFilteredCategory()
    {
        switch ($this->roleType) {
            case self::ROLE_TYPE_LET : return self::WF_FILTERED_CATEGORY_LET;
            case self::ROLE_TYPE_SALE : return self::WF_FILTERED_CATEGORY_SALE;
            case self::ROLE_TYPE_AUCTION : return self::WF_FILTERED_CATEGORY_AUCTION;
        }

        return false;
    }

}