<?php
namespace Eniture\FedExSmallPackageQuotes\Cron;

/**
 * Class PlanUpgrade
 * @package Eniture\FedExSmallPackageQuotes\Cron
 */
class PlanUpgrade
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    public $curl;
    /**
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    public $resourceConfig;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    public $logger;

    /**
     * @var string
     */
    private $curlUrl = 'https://ws066.eniture.com/web-hooks/subscription-plans/create-plugin-webhook.php';

    /**
     * PlanUpgrade constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\App\Config\ConfigResource\ConfigInterface $resourceConfig
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $resourceConfig,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->storeManager = $storeManager;
        $this->curl = $curl;
        $this->resourceConfig = $resourceConfig;
        $this->logger = $logger;
    }

    /**
     *
     */
    public function execute()
    {
        $domain = $this->storeManager->getStore()->getUrl();
        $webhookUrl = $domain.'fedexsmallpackagequotes';
        $postData = http_build_query([
            'platform' => 'magento2',
            'carrier' => '66',
            'store_url' => $domain,
            'webhook_url' => $webhookUrl,
        ]);

        $this->curl->post($this->curlUrl, $postData);
        $output = $this->curl->getBody();
        if(!empty($output) && is_string($output)){
            $result = json_decode($output, true);
        }else{
            $result = [];
        }

        $plan = isset($result['pakg_group']) ? $result['pakg_group'] : '';
        $expireDay = isset($result['pakg_duration']) ? $result['pakg_duration'] : '';
        $expiryDate = isset($result['expiry_date']) ? $result['expiry_date'] : '';
        $planType = isset($result['plan_type']) ? $result['plan_type'] : '';
        $pakgPrice = isset($result['pakg_price']) ? $result['pakg_price'] : 0;
        if ($pakgPrice == 0) {
            $plan = 0;
        }

        $today =  date('F d, Y');
        if (strtotime($today) > strtotime($expiryDate)) {
            $plan ='-1';
        }
        $this->saveConfigurations('eniture/ENFedExSmpkg/plan', "$plan");
        $this->saveConfigurations('eniture/ENFedExSmpkg/expireday', "$expireDay");
        $this->saveConfigurations('eniture/ENFedExSmpkg/expiredate', "$expiryDate");
        $this->saveConfigurations('eniture/ENFedExSmpkg/storetype', "$planType");
        $this->saveConfigurations('eniture/ENFedExSmpkg/pakgprice', "$pakgPrice");
        $this->saveConfigurations('eniture/ENFedExSmpkg/label', "ENITURE SMALL PACKAGE QUOTES - FOR FEDEX");
        $this->logger->info($output);
    }


    /**
     * @param $path
     * @param $value
     */
    function saveConfigurations($path, $value)
    {
        $this->resourceConfig->saveConfig(
            $path,
            $value,
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        );
    }
}
