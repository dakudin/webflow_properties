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


class DezrezFeedParser extends Component
{
    const SALE_ROLE_STATUS = 'InstructionToSell';
    const LET_ROLE_STATUS = 'InstructionToLet';
    const OFFER_ACCEPTED_ROLE_STATUS = 'OfferAccepted';

    const ON_MARKET_STATUS = 'OnMarket';
    const FEATURED_MARKET_STATUS = 'Featured';
    const OFFER_ACCEPTED_MARKET_STATUS = 'OfferAccepted';
    const UNDER_OFFER_MARKET_STATUS = 'UnderOffer';

    const FULL_DESCRIPTION_FIELD_NAME = 'Main Marketing';

    const FLOOR_PLAN_FIELD_NAME = 'Floorplan';

    private $_allPropCount;

    private $_curPropCount;

    private $_pageNumber;

    private $_pageSize;

    private $_properties;

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

    protected function getProperty($dezrezProperty)
    {
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
        }

        return false;
    }

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

    protected function getImages(array $images)
    {
        $result = [];

        foreach($images as $image){
            $result[] = $image['Url'];
        }

        return $result;
    }

    protected function getFullDescription(array $descriptions)
    {
        foreach($descriptions as $description){
            if($description['Name'] == self::FULL_DESCRIPTION_FIELD_NAME)
                return $description['Text'];
        }

        return '';
    }

    protected function getMarketStatus(Property $property, $dezrezProperty){
        $roleStatus = $dezrezProperty['RoleStatus']['SystemName'];
        $roleFlags = [];
        foreach($dezrezProperty['Flags'] as $flag){
            if($flag['SystemName'] == self::UNDER_OFFER_MARKET_STATUS || $flag['SystemName'] == self::OFFER_ACCEPTED_MARKET_STATUS)
                $roleFlags[] = $flag['SystemName'];
        }

        if($property->roleType == Property::ROLE_TYPE_SALE && $roleStatus == self::SALE_ROLE_STATUS)
            return Property::STATUS_FOR_SALE;

        if($property->roleType == Property::ROLE_TYPE_SALE && in_array(self::UNDER_OFFER_MARKET_STATUS, $roleFlags))
            return Property::STATUS_SSTC;

        if($property->roleType == Property::ROLE_TYPE_SALE && $roleStatus == self::OFFER_ACCEPTED_ROLE_STATUS)
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