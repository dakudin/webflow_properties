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

    const WF_STATUS_LET_AGREED = '5cb63ab8dc14306d193e89be';
    const WF_STATUS_TO_LET = '5cb63ab8dc143041d63e89ab';
    const WF_STATUS_SOLD = '5cb63ab8dc1430a1f43e8998';
    const WF_STATUS_SSTC = '5cb63ab8dc1430609e3e8985';
    const WF_STATUS_FOR_SALE = '5cb63ab8dc143020633e8972';

    const WF_ROLE_TYPE_SALE = '5cb63ab8dc1430a1783e8a3c';
    const WF_ROLE_TYPE_LET = '5cb63ab8dc143026233e8a3d';
    const WF_ROLE_TYPE_AUCTION = '5cb63ab8dc14305f273e8a3e';

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
            ['roleType', 'in', 'range' => [static::ROLE_TYPE_LET, static::ROLE_TYPE_SALE, static::ROLE_TYPE_AUCTION]],
            ['marketStatus', 'in', 'range' => [static::STATUS_LET_AGREED, static::STATUS_TO_LET,
                static::STATUS_SOLD, static::STATUS_SSTC, static::STATUS_FOR_SALE]],
        ];
    }
}