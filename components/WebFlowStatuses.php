<?php
/**
 * Created by Kudin Dmitry
 * Date: 24.04.2019
 * Time: 21:28
 */

namespace app\components;

use app\models\Property;

class WebFlowStatuses
{
    /**
     * Slugs of role types
     */
    const ROLE_TYPE_SALE = 'sales';
    const ROLE_TYPE_LET = 'lettings';
    const ROLE_TYPE_AUCTION = 'auctions';

    /**
     * Slugs of market statuses
     */
    const STATUS_LET_AGREED = 'let-agreed';
    const STATUS_TO_LET = 'to-let';
    const STATUS_SOLD = 'sold';
    const STATUS_SSTC = 'sstc';
    const STATUS_FOR_SALE = 'for-sale';

    /**
     * Slug from property collection for getting filtering role types
     */
    const FILTERING_CATEGORY = 'filtering-category';

    /**
     * @var string role type Sales ID
     */
    protected $roleTypeSaleId;

    /**
     * @var string role type Lettings ID
     */
    protected $roleTypeLetId;

    /**
     * @var string role type Auctions ID
     */
    protected $roleTypeAuctionId;

    /**
     * @var string role type Sales ID for filtering
     */
    protected $filteredTypeSaleId;

    /**
     * @var string role type Lettings ID for filtering
     */
    protected $filteredTypeLetId;

    /**
     * @var string role type Auctions ID for filtering
     */
    protected $filteredTypeAuctionId;

    /**
     * @var string market status Let Agreed ID
     */
    protected $statusLetAgreedId;

    /**
     * @var string market status To Let ID
     */
    protected $statusToLetId;

    /**
     * @var string market status Sold ID
     */
    protected $statusSoldId;

    /**
     * @var string market status SSTC ID
     */
    protected $statusSSTCId;

    /**
     * @var string market status For Sale ID
     */
    protected $statusForSaleId;

    public function __construct(WebFlowCollection $roleTypeCollection, WebFlowCollection $propertyStatusCollection,
                                WebFlowCollection $propertyCollection)
    {
        $this->fillFilteredRoleTypes($propertyCollection);
        $this->fillRoleTypes($roleTypeCollection);
        $this->fillStatuses($propertyStatusCollection);

/*        echo $this->roleTypeSaleId . "\r\n";
        echo $this->roleTypeLetId . "\r\n";
        echo $this->roleTypeAuctionId . "\r\n";

        echo $this->filteredTypeSaleId . "\r\n";
        echo $this->filteredTypeLetId . "\r\n";
        echo $this->filteredTypeAuctionId . "\r\n";

        echo $this->statusLetAgreedId . "\r\n";
        echo $this->statusToLetId . "\r\n";
        echo $this->statusSoldId . "\r\n";
        echo $this->statusSSTCId . "\r\n";
        echo $this->statusForSaleId . "\r\n";*/
    }

    public function getWebFlowMarketStatus($marketStatus)
    {
        switch ($marketStatus) {
            case Property::STATUS_LET_AGREED : return $this->statusLetAgreedId;
            case Property::STATUS_TO_LET : return $this->statusToLetId;
            case Property::STATUS_SOLD : return $this->statusSoldId;
            case Property::STATUS_SSTC : return $this->statusSSTCId;
            case Property::STATUS_FOR_SALE : return $this->statusForSaleId;
        }

        return false;
    }

    public function getWebFlowRoleType($roleType)
    {
        switch ($roleType) {
            case Property::ROLE_TYPE_LET : return $this->roleTypeLetId;
            case Property::ROLE_TYPE_SALE : return $this->roleTypeSaleId;
            case Property::ROLE_TYPE_AUCTION : return $this->roleTypeAuctionId;
        }

        return false;
    }

    public function getWebFlowFilteredCategory($roleType)
    {
        switch ($roleType) {
            case Property::ROLE_TYPE_LET : return $this->filteredTypeLetId;
            case Property::ROLE_TYPE_SALE : return $this->filteredTypeSaleId;
            case Property::ROLE_TYPE_AUCTION : return $this->filteredTypeAuctionId;
        }

        return false;
    }

    protected static function getIDbyParam(array $items, $paramName, $paramId, $slug)
    {
        foreach($items as $item) {
            if($item[$paramName]==$slug){
                return $item[$paramId];
            }
        }

        throw new \Exception('Error : didn`t find slug `' . $slug . '` in collection ' . var_dump($items));
    }

    protected function fillRoleTypes(WebFlowCollection $roleTypes)
    {
        $this->roleTypeAuctionId = static::getIDbyParam($roleTypes->getItems(), 'slug', '_id', static::ROLE_TYPE_AUCTION);
        $this->roleTypeLetId = static::getIDbyParam($roleTypes->getItems(), 'slug', '_id', static::ROLE_TYPE_LET);
        $this->roleTypeSaleId = static::getIDbyParam($roleTypes->getItems(), 'slug', '_id', static::ROLE_TYPE_SALE);
    }

    protected function fillFilteredRoleTypes(WebFlowCollection $roleTypes)
    {
        foreach($roleTypes->getFields() as $field){
            if($field['slug'] == static::FILTERING_CATEGORY){
                $this->setFilteredRoleTypes($field['validations']['options']);
                break;
            }
        }
    }

    protected function setFilteredRoleTypes(array $items)
    {
        $this->filteredTypeAuctionId = static::getIDbyParam($items, 'name', 'id', static::ROLE_TYPE_AUCTION);
        $this->filteredTypeLetId = static::getIDbyParam($items, 'name', 'id', static::ROLE_TYPE_LET);
        $this->filteredTypeSaleId = static::getIDbyParam($items, 'name', 'id', static::ROLE_TYPE_SALE);
    }

    protected function fillStatuses(WebFlowCollection $statuses)
    {
        $this->statusLetAgreedId = static::getIDbyParam($statuses->getItems(), 'slug', '_id', static::STATUS_LET_AGREED);
        $this->statusSoldId = static::getIDbyParam($statuses->getItems(), 'slug', '_id', static::STATUS_SOLD);
        $this->statusSSTCId = static::getIDbyParam($statuses->getItems(), 'slug', '_id', static::STATUS_SSTC);
        $this->statusToLetId = static::getIDbyParam($statuses->getItems(), 'slug', '_id', static::STATUS_TO_LET);
        $this->statusForSaleId = static::getIDbyParam($statuses->getItems(), 'slug', '_id', static::STATUS_FOR_SALE);
    }
}