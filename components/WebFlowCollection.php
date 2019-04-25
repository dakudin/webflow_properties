<?php
/**
 * Created by Kudin Dmitry
 * Date: 23.04.2019
 * Time: 20:21
 */

namespace app\components;

class WebFlowCollection
{

    /**
     * @var \app\components\WebFlowClient client for work with WebFlow via API
     */
    protected $webFlowClient;

    /**
     * @var string WebFlow collection id in which need inset/update/delete properties
     */
    protected $id;

    /**
     * @var string WebFlow collection id in which need inset/update/delete properties
     */
    protected $name;

    /**
     * @var string WebFlow collection id in which need inset/update/delete properties
     */
    protected $slug;

    /**
     * @var array WebFlow Items are based on the Collection that Items belongs to
     */
    protected $items;

    /**
     * @var int WebFlow total items count
     */
    protected $itemsTotal;

    /**
     * @var int WebFlow items count
     */
    protected $itemsCount;

    /**
     * @var int WebFlow items offset
     */
    protected $itemsOffset;

    /**
     * @var array WebFlow collection fields
     */
    protected $fields;

    /**
     * @param string $id
     * @param string $name
     * @param string $slug
     * @param WebFlowClient $webFlowClient
     */
    public function __construct($id, $name, $slug, WebFlowClient $webFlowClient)
    {
        $this->webFlowClient = $webFlowClient;
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
    }

    /**
     * @param string $apiKey
     * @param int $limit
     * @param int $offset
     * @return bool
     */
    public function loadItems($apiKey, $limit = 100, $offset = 0)
    {
        $response = $this->webFlowClient->getCollectionItems(
            $apiKey, $this->id, $limit, $offset
        );

        $this->items = $response['items'];
        $this->itemsCount = $response['count'];
        $this->itemsTotal = $response['total'];
        $this->itemsOffset = $response['offset'];

        return true;
    }

    /**
     * @param string $apiKey
     * @return bool
     */
    public function loadFields($apiKey)
    {
        $response = $this->webFlowClient->getCollectionSchema($apiKey, $this->id);
        $this->fields = $response['fields'];

        return true;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return int
     */
    public function getItemsTotal()
    {
        return $this->itemsTotal;
    }

    /**
     * @return int
     */
    public function getItemsCount()
    {
        return $this->itemsCount;
    }

    /**
     * @return int
     */
    public function getItemsOffset()
    {
        return $this->itemsOffset;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
}