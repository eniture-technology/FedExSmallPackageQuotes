<?php
namespace Eniture\FedExSmallPackages\Cron;

class PlanUpgrade
{
    protected $logger;

    private $curlUrl = 'https://eniture.com/ws/web-hooks/subscription-plans/create-plugin-webhook.php';

    private $helper;

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
        $webhookUrl = $domain.'/fedexsmallpackages';
        $postData = http_build_query([
            'platform' => 'magento2',
            'carrier' => '64',
            'store_url' => $domain,
            'webhook_url' => $webhookUrl,
        ]);

        $this->curl->post($this->curlUrl, $postData);
        $output = $this->curl->getBody();
        $result = json_decode($output, true);

        $plan = isset($result['pakg_group']) ? $result['pakg_group'] : '';
        $expireDay = isset($result['pakg_duration']) ? $result['pakg_duration'] : '';
        $expiryDate = isset($result['expiry_date']) ? $result['expiry_date'] : '';
        $planType = isset($result['plan_type']) ? $result['plan_type'] : '';
        $pakgPrice = isset($result['pakg_price']) ? $result['pakg_price'] : 0;
        if ($pakgPrice == 0) {
            $plan = 0;
        }
        $this->saveConfigurations('eniture/ENFedExSmpkg/plan', "$plan");
        $this->saveConfigurations('eniture/ENFedExSmpkg/expireday', "$expireDay");
        $this->saveConfigurations('eniture/ENFedExSmpkg/expiredate', "$expiryDate");
        $this->saveConfigurations('eniture/ENFedExSmpkg/storetype', "$planType");
        $this->saveConfigurations('eniture/ENFedExSmpkg/pakgprice', "$pakgPrice");
        $this->saveConfigurations('eniture/ENFedExSmpkg/label', "Eniture - FedEx Small Packages");
        $this->logger->info($output);
    }


    /**
     * @param type $path
     * @param type $value
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
