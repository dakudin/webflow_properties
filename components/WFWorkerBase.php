<?php
/**
 * Created by Kudin Dmitry
 * Date: 04.06.2019
 * Time: 17:04
 */

namespace app\components;

use yii\base\Component;


class WFWorkerBase extends Component
{

    /**
     * @var string API key
     */
    protected $_apiKey;

    /**
     * @var bool Flag that show for which kind of items need to work: Live or not
     * set to true for publishing to live site
     */
    protected $_publishToLiveSite = false;

    /**
     * @var \app\components\WebFlowClient client for work with WebFlow via API
     */
    protected $_webFlowClient;

    /**
     * @var string WebFlow site id with which items will insert/update/delete
     */
    protected $_siteId;


    /**
     * @param array $apiKey
     * @param $publishToLiveSite
     * @throws \Exception
     */
    public function __construct($apiKey, $publishToLiveSite)
    {
        parent::__construct();

        $this->_apiKey = $apiKey;
        $this->_publishToLiveSite = $publishToLiveSite===true;
        $this->_webFlowClient = new WebFlowClient();
    }


    /**
     * Get Web Flow site id for getting collections
     * @return bool
     */
    protected function getSiteId()
    {
        $info = $this->_webFlowClient->getInfo($this->_apiKey);

        if(!isset($info['sites'])) return false;

        // get only first site from list
        return (is_array($info['sites']) && $this->_siteId = $info['sites'][0]);
    }


}