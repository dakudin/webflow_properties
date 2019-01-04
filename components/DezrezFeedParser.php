<?php
/**
 * Created by Kudin Dmitry
 * Date: 17.12.2018
 * Time: 13:45
 */

namespace app\components;

use Yii;
use yii\base\Component;
use app\models\Property;


/**
 * Class for parsing Dezrez feed.
 *
 * @author Kudin Dmitry <dakudin@gmail.com>
 */
class DezrezFeedParser extends Component
{
    /**
     * Role statuses
     */
    const SALE_ROLE_STATUS = 'InstructionToSell';
    const LET_ROLE_STATUS = 'InstructionToLet';
    const OFFER_ACCEPTED_ROLE_STATUS = 'OfferAccepted';

    /**
     * Property Flags
     */
    const ON_MARKET_STATUS = 'OnMarket';
    const FEATURED_MARKET_STATUS = 'Featured';
    const OFFER_ACCEPTED_MARKET_STATUS = 'OfferAccepted';
    const UNDER_OFFER_MARKET_STATUS = 'UnderOffer';

    /**
     * field name for detecting full description
     */
    const FULL_DESCRIPTION_FIELD_NAME = 'Main Marketing';

    /**
     * field name for detecting floor plan image url
     */
    const FLOOR_PLAN_FIELD_NAME = 'Floorplan';

    /**
     * @var int Total properties which we can get via API
     */
    private $_allPropCount;

    /**
     * @var int Number of properties which we receive in response
     */
    private $_curPropCount;

    /**
     * @var int Page number which Dezrez API return in response
     */
    private $_pageNumber;

    /**
     * @var int Number properties in response
     */
    private $_pageSize;

    /**
     * @var array of Property objects
     */
    private $_properties;

    /**
     * @var array of item collection
     */
    private $_collection;

    public function parse($data)
    {
        $this->_allPropCount = $data['TotalCount'];

        $this->_curPropCount = $data['CurrentCount'];

        $this->_pageNumber = $data['CurrentCount'];

        $this->_pageSize = $data['PageSize'];

        $this->_collection = $data['Collection'];

        $this->_properties = [];

        foreach($this->_collection as $dezrezProp){
            $property = $this->getProperty($dezrezProp);
            if($property){
                $this->_properties[] = $property;
            }
        }

        return $this->_properties;
    }

    public function getAllPropCount()
    {
        return $this->_allPropCount;
    }

    public function getCurPropCount()
    {
        return $this->_curPropCount;
    }

    public function getPageNumber()
    {
        return $this->_pageNumber;
    }

    public function getProperties()
    {
        return $this->_properties;
    }

    /**
     * Parse Dezrez property and return property as an object if model validation passed or false otherwise
     * @param $dezrezProperty
     * @return Property|bool
     */
    protected function getProperty($dezrezProperty)
    {

/*        if($dezrezProperty['RoleId'] == 9700076){
            var_dump($dezrezProperty);
        }*/

        $property = new Property();
        $property->id = $dezrezProperty['RoleId'];
        $property->name = $dezrezProperty['RoleId'];
        $property->roleType = $dezrezProperty['RoleType']['SystemName'];
        $property->marketStatus = $this->getMarketStatus($property, $dezrezProperty);
        $property->price = $dezrezProperty['Price']['PriceValue'];
        $property->numberOfRooms = $dezrezProperty['RoomCountsDescription']['Bedrooms'];
        $property->numberOfBath = $dezrezProperty['RoomCountsDescription']['Bathrooms'];
        $property->shortDescription = $dezrezProperty['SummaryTextDescription'];
        $property->fullDescription = $this->getFullDescription($dezrezProperty['Descriptions']);
        $property->images = $this->getImages($dezrezProperty['Images']);
        $property->floorPlanImageUrl = $this->getFloorPlanUrl($dezrezProperty['Documents']);


        if ($property->validate()){
            return $property;
        }else{
            echo "Error Dezrez validation model for property " . $dezrezProperty['RoleId'] . "\r\n";
            $errors = $property->errors;

            var_dump($errors);
        }

        return false;
    }

    /**
     * Parse and return floor plan url
     * @param array $documents
     * @return string
     */
    protected function getFloorPlanUrl(array $documents)
    {
        foreach($documents as $document){
            if($document['DocumentSubType']['SystemName'] == self::FLOOR_PLAN_FIELD_NAME
                && $document['DocumentType']['SystemName'] == 'Image'
            )
                return $document['Url'];
        }

        return '';
    }

    /**
     * Parse and return array with property images
     * @param array $images
     * @return array
     */
    protected function getImages(array $images)
    {
        $result = [];

        foreach($images as $image){
            $result[] = $image['Url'];
        }

        return $result;
    }

    /**
     * Get full description of property
     * @param array $descriptions
     * @return string
     */
    protected function getFullDescription(array $descriptions)
    {
        foreach($descriptions as $description){
            if($description['Name'] == self::FULL_DESCRIPTION_FIELD_NAME)
                return $description['Text'];
        }

        return '';
    }

    /**
     * Analize Dezrez property market status, role type and flags and return market status for WebFlow property
     * @param Property $property
     * @param array $dezrezProperty
     * @return bool|string
     */
    protected function getMarketStatus(Property $property, $dezrezProperty){
        $roleStatus = $dezrezProperty['RoleStatus']['SystemName'];
        $roleFlags = [];

        foreach($dezrezProperty['Flags'] as $flag){
            if($flag['SystemName'] == self::UNDER_OFFER_MARKET_STATUS || $flag['SystemName'] == self::OFFER_ACCEPTED_MARKET_STATUS)
                $roleFlags[] = $flag['SystemName'];
        }

        if(($property->roleType == Property::ROLE_TYPE_SALE || $property->roleType == Property::ROLE_TYPE_AUCTION)
            && $roleStatus == self::SALE_ROLE_STATUS)
            return Property::STATUS_FOR_SALE;

        if(($property->roleType == Property::ROLE_TYPE_SALE || $property->roleType == Property::ROLE_TYPE_AUCTION)
            && in_array(self::UNDER_OFFER_MARKET_STATUS, $roleFlags))
            return Property::STATUS_SSTC;

        if(($property->roleType == Property::ROLE_TYPE_SALE || $property->roleType == Property::ROLE_TYPE_AUCTION)
            && $roleStatus == self::OFFER_ACCEPTED_ROLE_STATUS)
            return Property::STATUS_SOLD;

        // if status is 'Instruction to Let' and property doesn't have flag 'Offer Accepted' we set status 'To Let’;
        if($property->roleType == Property::ROLE_TYPE_LET && $roleStatus == self::LET_ROLE_STATUS)
            return Property::STATUS_TO_LET;

        // if status is 'Instruction to Let' and property has flag 'Offer Accepted' we set status 'Let Agreed';
        if($property->roleType == Property::ROLE_TYPE_LET && $roleStatus == self::OFFER_ACCEPTED_ROLE_STATUS)
            return Property::STATUS_LET_AGREED;

        return false;
    }
}