<?php
/**
 * @category   Shipping
 * @package    Eniture_FedExSmallPackageQuotes
 * @author     Eniture Technology : <sales@eniture.com>
 * @website    http://eniture.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Eniture\FedExSmallPackageQuotes\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Data
 * @package Eniture\FedExSmallPackageQuotes\Helper
 */
class Data extends AbstractHelper
{

    /**
     * @var \Magento\Shipping\Model\Config
     */
    public $shippingConfig;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;
    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    public $currencyFactory;
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    public $priceCurrency;
    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    public $coreSession;
    /**
     * @var
     */
    public $originZip;
    /**
     * @var
     */
    public $residentialDelivery;
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    public $curl;
    /**
     * @var int
     */
    public $canAddWh = 1;
    /**
     * @var \Magento\Framework\App\Cache\Manager
     */
    public $cacheManager;
    /**
     * @var \Eniture\FedExSmallPackageQuotes\Model\WarehouseFactory
     */
    public $warehouseFactory;
    /**
     * @var \Eniture\FedExSmallPackageQuotes\Model\EnituremodulesFactory
     */
    private $enituremodulesFactory;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;
    /**
     * @var \Magento\Directory\Model\Currency
     */
    private $currenciesModel;
    /**
     * @var \Magento\Directory\Helper\Data
     */
    private $directoryHelper;
    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    private $context;

    public $objectManager;

    public $residentialDlvry;
    public $fedexRates;
    public $onlyGndService;
    public $gndHzrdousFee;
    public $airHzrdousFee;


    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Shipping\Model\Config $shippingConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Directory\Model\Currency $currencyModel
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param \Eniture\FedExSmallPackageQuotes\Model\WarehouseFactory $warehouseFactory
     * @param \Eniture\FedExSmallPackageQuotes\Model\EnituremodulesFactory $enituremodulesFactory
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\App\Cache\Manager $cacheManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Model\Currency $currencyModel,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Eniture\FedExSmallPackageQuotes\Model\WarehouseFactory $warehouseFactory,
        \Eniture\FedExSmallPackageQuotes\Model\EnituremodulesFactory $enituremodulesFactory,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\App\Cache\Manager $cacheManager,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
        $this->resource            = $resource;
        $this->shippingConfig      = $shippingConfig;
        $this->storeManager        = $storeManager;
        $this->currencyFactory     = $currencyFactory;
        $this->currenciesModel      = $currencyModel;
        $this->priceCurrency       = $priceCurrency;
        $this->directoryHelper      = $directoryHelper;
        $this->registry            = $registry;
        $this->coreSession         = $coreSession;
        $this->warehouseFactory    = $warehouseFactory;
        $this->enituremodulesFactory    = $enituremodulesFactory;
        $this->context       = $context;
        $this->curl = $curl;
        $this->cacheManager = $cacheManager;
        $this->objectManager = $objectmanager;
        parent::__construct($context);
    }

    /**
     * @param $index
     * @return mixed
     */
    public function wsHittingUrls($index)
    {
        $allWsUrl = [
            'testConnection' => 'https://ws066.eniture.com/s/fedex/fedex_shipment_rates_test.php',
            'getAddress' => 'https://ws066.eniture.com/addon/google-location.php',
            'multiDistance' => 'https://ws066.eniture.com/addon/google-location.php',
            'planUpgrade' => 'https://ws066.eniture.com/web-hooks/subscription-plans/create-plugin-webhook.php',
            'getQuotes' => 'https://ws066.eniture.com/v3.0/index.php'
        ];

        return $allWsUrl[$index];
    }

    /**
     * function to return the Store Base Currency
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseCurrencyCode()
    {
        return $this->storeManager->getStore()->getBaseCurrencyCode();
    }

    /**
     * @param $section
     * @param $whereClause
     * @return array
     */
    public function fetchWarehouseSecData($location)
    {
        $whCollection       = $this->warehouseFactory->create()
            ->getCollection()->addFilter('location', ['eq' => $location]);
        $warehouseSecData   = $this->purifyCollectionData($whCollection);

        return $warehouseSecData;
    }

    /**
     * @param $whCollection
     * @return array
     */
    public function purifyCollectionData($whCollection)
    {
        $warehouseSecData = [];
        foreach ($whCollection as $wh) {
            $warehouseSecData[] = $wh->getData();
        }
        return $warehouseSecData;
    }
    /**
     * @param $section
     * @param $whereClause
     * @return array
     */
    public function fetchDropshipWithID($warehouseId)
    {
        $whFactory = $this->warehouseFactory->create();
        $dsCollection  = $whFactory->getCollection()
            ->addFilter('location', ['eq' => 'dropship'])
            ->addFilter('warehouse_id', ['eq' => $warehouseId]);

        $dropshipSecData   = $this->purifyCollectionData($dsCollection);

        return $dropshipSecData;
    }

    /**
     * @param type $data
     * @param type $whereClause
     * @return type
     */
    public function updateWarehousData($data, $whereClause)
    {
        $defualtConn = \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION;
        $whTableName = $this->resource->getTableName('warehouse');
        return $this->resource->getConnection($defualtConn)->update("$whTableName", $data, "$whereClause");
    }

    /**
     * @param type $data
     * @param type $id
     * @return type
     */
    public function insertWarehouseData($data, $id)
    {
        $defualtConn    = \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION;
        $connection     =  $this->resource->getConnection($defualtConn);
        $whTableName    = $this->resource->getTableName('warehouse');
        $insertQry = $connection->insert("$whTableName", $data);
        if ($insertQry == 0) {
            $lastid = $id;
        } else {
            $lastid = $connection->lastInsertId();
        }
        return ['insertId' => $insertQry, 'lastId' => $lastid];
    }

    /**
     * @param type $data
     * @return type
     */
    public function deleteWarehouseSecData($data)
    {
        $defualtConn    = \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION;
        $whTableName    = $this->resource->getTableName('warehouse');
        return $this->resource->getConnection($defualtConn)->delete("$whTableName", $data);
    }

    /**
     * Data Array
     * @param $inputData
     * @return array
     */
    public function fedexSmpkgOriginArray($inputData)
    {
        $dataArr = [
            'city'      => $inputData['city'],
            'state'     => $inputData['state'],
            'zip'       => $inputData['zip'],
            'country'   => $inputData['country'],
            'location'  => $inputData['location'],
            'nickname'  => (isset($inputData['nickname'])) ? $inputData['nickname'] : '',
            'in_store'  => 'null',
            'local_delivery'  => 'null',
        ];
        $plan = $this->fedexSmallPlanName('ENFedExSmpkg');
        if ($plan['planNumber'] == 3) {
            $pickupDelvryArr = [
                'enable_store_pickup'           => ($inputData['instore_enable']=== 'true')?1:0,
                'miles_store_pickup'            => $inputData['is_within_miles'],
                'match_postal_store_pickup'     => $inputData['is_postcode_match'],
                'checkout_desc_store_pickup'    => $inputData['is_checkout_descp'],
                'suppress_other'                => ($inputData['ld_sup_rates'] === 'true')?1:0,
            ];
            $dataArr['in_store'] = json_encode($pickupDelvryArr);

            $localDelvryArr = [
                'enable_local_delivery'         => ($inputData['ld_enable']=== 'true')?1:0,
                'miles_local_delivery'          => $inputData['ld_within_miles'],
                'match_postal_local_delivery'   => $inputData['ld_postcode_match'],
                'checkout_desc_local_delivery'  => $inputData['ld_checkout_descp'],
                'fee_local_delivery'            => $inputData['ld_fee'],
                'suppress_other'                => ($inputData['ld_sup_rates'] === 'true')?1:0,
            ];
            $dataArr['local_delivery'] = json_encode($localDelvryArr);
        }

        return $dataArr;
    }

    /**
     * @param type $scopeConfig
     */
    public function quoteSettingsData($scopeConfig)
    {
        $this->residentialDlvry = $this->adminConfigData('residentialDlvry', $scopeConfig);
        $this->fedexRates = $this->adminConfigData('fedexRates', $scopeConfig);
        $this->onlyGndService = $this->adminConfigData('onlyGndService', $scopeConfig);
        $this->gndHzrdousFee = $this->adminConfigData('gndHzrdousFee', $scopeConfig);
        $this->airHzrdousFee = $this->adminConfigData('airHzrdousFee', $scopeConfig);
        
        // Get origin zipcode array for onerate settings
        $this->getOriginZipCodeArr();
    }

    /**
     * getOriginZipCodeArr
     */
    public function getOriginZipCodeArr()
    {
        if ($this->registry->registry('shipmentOrigin') !== null) {
            $originArr = $this->registry->registry('shipmentOrigin');
        }

        foreach ($originArr as $key => $origin) {
            $this->originZip[$key] = $origin['senderZip'];
        }
    }

    /**
     * validate Input Post
     * @param $sPostData
     * @return mixed
     */
    public function fedexSmpkgValidatedPostData($sPostData)
    {
        foreach ($sPostData as $key => $tag) {
            $preg = '/[#$%@^&_*!()+=\-\[\]\';,.\/{}|":<>?~\\\\]/';
            $check_characters = (!$key == 'nickname') ? preg_match($preg, $tag) : '';
            if ($check_characters != 1) {
                if ($key === 'city' ||
                    $key === 'nickname' ||
                    $key === 'in_store' ||
                    $key === 'local_delivery') {
                    $data[$key] = $tag;
                } else {
                    $data[$key] = preg_replace('/\s+/', '', $tag);
                }
            } else {
                $data[$key] = 'Error';
            }
        }

        return $data;
    }

    /**
     * @param type $getWarehouse
     * @param type $validateData
     * @return string
     */
    public function checkUpdateInstrorePickupDelivery($getWarehouse, array $validateData)
    {
        $update = 'no';

        if (empty($getWarehouse)) {
            return $update;
        }

        $newData = $oldData = [];
        $getWarehouse = reset($getWarehouse);
        unset($getWarehouse['warehouse_id']);
        unset($getWarehouse['nickname']);
        unset($validateData['nickname']);

        foreach ($getWarehouse as $key => $value) {
            if (empty($value) || $value === null) {
                $newData[$key] = 'empty';
            } else {
                $oldData[$key] = $value;
            }
        }

        $whData = array_merge($newData, $oldData);
        $diff1 = array_diff($whData, $validateData);
        $diff2 = array_diff($validateData, $whData);

        if ((is_array($diff1) && !empty($diff1)) || (is_array($diff2) && !empty($diff2))) {
            $update = 'yes';
        }

        return $update;
    }

    /**
     * This function send request and return responce
     * $isAssocArray Paramiter When TRUE, then returned objects will
     * be converted into associative arrays, otherwise its an object
     * @param $url
     * @param $postData
     * @param $isAssocArray
     * @return
     */
    public function fedexSmpkgSendCurlRequest($url, $postData, $isAssocArray = false)
    {
        $fieldString = http_build_query($postData);
        $this->curl->post($url, $fieldString);
        $output = $this->curl->getBody();
        if(!empty($output) && is_string($output)){
            $result = json_decode($output, $isAssocArray);
        }else{
            $result = ($isAssocArray) ? [] : '';
        }
        
        return $result;
    }

    /**
     * @param type $key
     * @return string|empty
     */
    public function getZipcode($key)
    {
        $key = empty($key) ? [] : explode("_", $key);
        return (isset($key[0])) ? $key[0] : "";
    }

    /**
     * @param type $quotes
     * @param type $isMultishipmentQuantity
     * @param type $scopeConfig
     * @return type
     */
    public function getQuotesResults($quotes, $getMinimum, $isMultishipmentQuantity, $scopeConfig)
    {
        $allConfigServices = $this->getAllConfigServicesArray($scopeConfig);
        $this->quoteSettingsData($scopeConfig);

        if ($isMultishipmentQuantity) {
            return $this->getOriginsMinimumQuotes($quotes, $allConfigServices, $scopeConfig);
        }

        $filteredQuotes = [];
        $multiShipment = (count($quotes) > 1 ? true : false);

        foreach ($quotes as $key => $quote) {
            if (isset($quote->severity) && $quote->severity == 'ERROR') {
                return [];
            }
            $isQuotes = false;
            //This is to check if Box exceeds 15 limit for a service
            foreach ($quote as $serviceName => $data) {
                if (isset($data->severity) && $data->severity == 'ERROR') {
                    unset($quote->$serviceName);
                }
//                elseif (isset($data->q)) {
//                    $isQuotes = true;
//                }
            }
            // comment this because, it doesn't show rates in case on only instore local del and suppress other rates (zip matched)
            //This is to check if this origin still has some quotes
//            if (!$isQuotes) {
//                return [];
//            }

            $quoteServices = [];
            $binPackaging = $this->setBinPackagingData($quote, $key);

            $binPackagingArr[] = $binPackaging;

            $shipment = $quote->shipment;
            $quoteServices[$shipment] = (isset($allConfigServices[$shipment])) ? $allConfigServices[$shipment] : [];

            $onerateSrvcs['fedexOneRate'] = $allConfigServices['onerateServices'];
            // log id: 0001 (2 changes)
            if(empty($filteredQuotes[$key])){
                $filteredQuotes[$key] = $this->parseFedexSmallOutput($quote, $quoteServices, $onerateSrvcs, $scopeConfig, $binPackaging[$key]);
            }
        }

        $this->coreSession->start();
        $this->coreSession->setFdxBinPackaging($binPackagingArr);

        if (!$multiShipment) {
            $this->setOrderDetailWidgetData([], $scopeConfig);
            return $getMinimum ? $filteredQuotes : reset($filteredQuotes);
        } else {
            $multiShipQuotes = $this->getMultishipmentQuotes($filteredQuotes);

            $this->setOrderDetailWidgetData($multiShipQuotes['orderWidgetQ'], $scopeConfig);
            return $multiShipQuotes['multiShipQ'];
        }
    }

    /**
     * @param type $filteredQuotes
     * @return array
     */
    public function getMultishipmentQuotes($filteredQuotes)
    {
        $totalRate = 0;
        foreach ($filteredQuotes as $key => $multiQuotes) {
            if (isset($multiQuotes[0])) {
                $totalRate += $multiQuotes[0]['rate'];
                $multiship[$key]['quotes'] = $multiQuotes[0];
            }
        }

        $response['multiShipQ']['fedexSmall'] = $this->getFinalQuoteArray(
            $totalRate,
            'FedExSPMS',
            'Shipping '.$this->residentialDelivery
        );
        $response['orderWidgetQ'] = $multiship;

        return $response;
    }

    /**
     * @param type $quote
     * @param type $key
     * @return array
     */
    public function setBinPackagingData($quote, $key)
    {
        $binPackaging = [];
        $binPackaging[$key]['fedexServices'] = [];
        $binPackaging[$key]['fedexOneRate'] = [];
        $binPackaging[$key]['fedexAirServices'] = [];
        $binPackaging[$key]['fedexSmartPost'] = [];
        if (isset($quote->fedexServices->binPackagingData)) {
            $binPackaging[$key]['fedexServices'] = $quote->fedexServices->binPackagingData;
            $binPackaging[$key]['fedexServices']->boxesFee = isset($quote->fedexServices->binPackagingData->response) ?
                $this->calculateBoxesFee($quote->fedexServices->binPackagingData->response)
                : 0;
        }

        if (isset($quote->fedexOneRate->binPackagingData)) {
            $binPackaging[$key]['fedexOneRate'] = $quote->fedexOneRate->binPackagingData;
            $binPackaging[$key]['fedexOneRate']->boxesFee = isset($quote->fedexOneRate->binPackagingData->response) ?
                $this->calculateBoxesFee($quote->fedexOneRate->binPackagingData->response)
                : 0;
        }

        if (isset($quote->fedexAirServices->binPackagingData)) {
            $binPackaging[$key]['fedexAirServices'] = $quote->fedexAirServices->binPackagingData;
            $binPackaging[$key]['fedexAirServices']->boxesFee = isset($quote->fedexAirServices->binPackagingData->response) ?
                $this->calculateBoxesFee($quote->fedexAirServices->binPackagingData->response)
                : 0;
        }
        if(isset($quote->smartPost->binPackagingData)) {
            $binPackaging[$key]['fedexSmartPost'] = $quote->smartPost->binPackagingData;
            $binPackaging[$key]['fedexSmartPost']->boxesFee = isset($quote->smartPost->binPackagingData->response) ?
                $this->calculateBoxesFee($quote->smartPost->binPackagingData->response)
                : 0;
        }

        return $binPackaging;
    }

    public function getBoxHelper($objectName)
    {
        if ($objectName == 'helper') {
            return $this->objectManager->get("Eniture\StandardBoxSizes\Helper\Data");
        }
        if ($objectName == 'boxFactory') {
            $boxHelper =  $this->objectManager->get("Eniture\StandardBoxSizes\Helper\Data");
            return $boxHelper->getBoxFactory();
        }
    }

    /**
     *
     * @param type $response
     * @return type
     */
    public function calculateBoxesFee($response)
    {

        $totalBoxesFee = 0;
        $boxesFee = $boxIDs = [];
        foreach ($response->bins_packed as $binDetails) {
            if (isset($binDetails->bin_data->type) && $binDetails->bin_data->type="item") { // If user boxes are not used
                $boxIDs = null;
            } else {
                $boxIDs[] = $binDetails->bin_data->id;
            }
        }
        if (!is_null($boxIDs) && count($boxIDs) > 0) {
            $boxFactory = $this->getBoxHelper('boxFactory');
            foreach ($boxIDs as $boxID) {
                if (!array_key_exists($boxID, $boxesFee)) {
                    $boxCollection = $boxFactory->getCollection()->addFilter('box_id', ['eq' => $boxID])->addFieldToSelect('boxfee');
                    foreach ($boxCollection as $box) {
                        $boxFee = $box->getData();
                    }
                    $boxesFee[$boxID]= !empty($boxFee['boxfee']) ? $boxFee['boxfee'] : 0;
                }

                $totalBoxesFee +=$boxesFee[$boxID];
            }
        }

        return $totalBoxesFee;
    }

    /**
     * Get Shipping Array For Single Shipment
     * @param $result
     * @param $serviceType
     * @return array
     */
    public function parseFedexSmallOutput($result, $idServices, $oneRateSrvcs, $scopeConfig, $binPackaging)
    {
        $quote = $allServicesArray = [];
        $transitTime = "";
        $isRad = $result->autoResidentialsStatus ?? '';
        $autoResTitle = $this->getAutoResidentialTitle($isRad);

        if (isset($result->fedexServices) && !(isset($result->fedexAirServices, $result->fedexOneRate))) {

            $quote['fedexServices'] = $this->quoteDetail($result->fedexServices);

            isset($result->smartPost) ? $quote['fedexSmartPost'] = $this->quoteDetail($result->smartPost) : "";

            $simpleQuotes = 1;
        } else {
            isset($result->fedexOneRate) ?
                $quote['fedexOneRate'] = $this->quoteDetail($result->fedexOneRate) : "";

            isset($result->fedexAirServices) ?
                $quote['fedexAirServices'] = $this->quoteDetail($result->fedexAirServices) : "";

            isset($result->fedexServices) ?
                $quote['fedexServices'] = $this->quoteDetail($result->fedexServices) : "";

            isset($result->smartPost) ?
                $quote['fedexSmartPost'] = $this->quoteDetail($result->smartPost) : "";
        }

        foreach ($quote as $serviceName => $servicesList) {
            $servicesList = $this->transitTimeRestriction($servicesList);

            if (isset($servicesList->SMART_POST) && $servicesList->SMART_POST->serviceType == "SMART_POST") {
                //this condtion is working for smart post feature
                $serviceType = $servicesList->SMART_POST->serviceType;
                $serviceTitle = "FedEx SmartPost";
                $totalCharge = $this->getQuoteAmount($servicesList->SMART_POST, $serviceName);
                $addedHandling = $this->calculateHandlingFee($totalCharge, $scopeConfig);
                $grandTotal = $this->calculateHazardousFee($serviceType, $addedHandling);

                $allServicesArray[$serviceType] = $this->getAllServicesArr(
                    $serviceType . "_" . $serviceName,
                    $this->getQuoteServiceCode($serviceType . "_" . $serviceName),
                    $grandTotal,
                    $transitTime,
                    $serviceTitle,
                    $serviceName
                );
            }
            if (isset($servicesList) && (!empty($servicesList))) {
                $services = ($serviceName == "fedexOneRate") ? $oneRateSrvcs : $idServices;
                $serviceKeyName = key($services);

                if ($serviceKeyName != "international" && (!isset($simpleQuotes))) {
                    //if ($serviceName == "fedexAirServices") {
                    if ($serviceName == "fedexServices") {
                        $homeGrdServices = [];
                        (isset($services[$serviceKeyName]['GROUND_HOME_DELIVERY'])) ?
                            $homeGrdServices[$serviceKeyName]['GROUND_HOME_DELIVERY'] = 'FedEx Home Delivery' : "";
                        (isset($services[$serviceKeyName]['FEDEX_GROUND'])) ?
                            $homeGrdServices[$serviceKeyName]['FEDEX_GROUND'] = 'FedEx Ground' : "";
                        $services = $homeGrdServices;
                        //} elseif ($serviceName == "fedexServices") {
                    } elseif ($serviceName == "fedexAirServices") {
                        if (isset($services[$serviceKeyName]['GROUND_HOME_DELIVERY'])) {
                            unset($services[$serviceKeyName]['GROUND_HOME_DELIVERY']);
                        }
                        if (isset($services[$serviceKeyName]['FEDEX_GROUND'])) {
                            unset($services[$serviceKeyName]['FEDEX_GROUND']);
                        }
                    }
                }

                foreach ($servicesList as $serviceKey => $service) {
                    if ($serviceTitle = (isset($services[$serviceKeyName][$service->serviceType])) ?
                        $services[$serviceKeyName][$service->serviceType] : "") {
                        // if (($serviceKeyName == "international" && $serviceName != "fedexAirServices") || $serviceKeyName != "international") {
                        // if ($serviceKeyName != "fedexOneRate" || ($serviceName != "fedexAirServices" || $serviceKeyName != "international")) {
//                        if (($serviceKeyName == "international" && $serviceName != "fedexServices") || $serviceKeyName != "international") {
                        // log id: 0001 (2 changes)
                        if (($serviceKeyName == "international" && ($serviceName == "fedexServices" || $serviceName == "fedexAirServices")) || $serviceKeyName != "international") {
                            $transitTime = (isset($service->transitTime)) ? $service->transitTime : '';

                            $serviceType = $service->serviceType;

                            $totalCharge = $this->getQuoteAmount($service, $serviceName, $binPackaging);

                            $addedHandling = $this->calculateHandlingFee($totalCharge, $scopeConfig);
                            $grandTotal = $this->calculateHazardousFee($serviceType, $addedHandling);
                            $allServicesArray[] = $this->getAllServicesArr(
                                $serviceType . "_" . $serviceName,
                                $this->getQuoteServiceCode($serviceType . "_" . $serviceName),
                                $grandTotal,
                                $transitTime,
                                $serviceTitle.' '.$autoResTitle,
                                $serviceName
                            );
                        }
                    }
                }
            }
        }

        $priceSortedKey = [];

        $allServicesArray = array_filter($allServicesArray);

        foreach ($allServicesArray as $key => $costCarrier) {
            $priceSortedKey[$key] = $costCarrier['rate'];
        }

        array_multisort($priceSortedKey, SORT_ASC, $allServicesArray);

        if (isset($result->fedexServices->InstorPickupLocalDelivery) && !empty($result->fedexServices->InstorPickupLocalDelivery)) {
            $allServicesArray = $this->instoreLocalDeliveryQuotes(
                $allServicesArray,
                $result->fedexServices->InstorPickupLocalDelivery
            );
        }
        return $allServicesArray;
    }

    /**
     * @param type $response
     */
    public function transitTimeRestriction($response)
    {
        $daysToRestrict = $this->getConfigData('fedexQuoteSetting/third/transitDaysNumber');
        $transitDayType = $this->getConfigData('fedexQuoteSetting/third/transitDaysRestrictionBy');
        $plan = $this->fedexSmallPlanName('ENFedExSmpkg');
        if ($plan['planNumber'] == 3 && !empty($daysToRestrict) && strlen($daysToRestrict) > 0 && !empty($transitDayType) && strlen($transitDayType) > 0) {
            foreach ($response as $row => $service) {
                if ($service->serviceType == "FEDEX_GROUND" &&
                    isset($service->$transitDayType) &&
                    ($service->$transitDayType >= $daysToRestrict)) {
                    unset($response->$row);
                    $res[] = $response;
                } else {
                    $res[] = $response;
                }
            }
            return reset($res);
        }
        return $response;
    }

    /**
     * @param type $service
     * @return string
     */
    public function getAutoResidentialTitle($service)
    {
        $append = '';
        $moduleManager = $this->context->getModuleManager();
        if ($moduleManager->isEnabled('Eniture_ResidentialAddressDetection')) {
            $isRadSuspend = $this->getConfigData("resaddressdetection/suspend/value");
            if ($this->residentialDlvry == "1") {
                $this->residentialDlvry = $isRadSuspend == "no" ? null : $isRadSuspend;
            } else {
                $this->residentialDlvry = $isRadSuspend == "no" ? null : $this->residentialDlvry;
            }

            if ($this->residentialDlvry == null || $this->residentialDlvry == '0') {
                if ($this->residentialDlvry == null
                    || $this->residentialDlvry == '0') {
                    if ($service == 'r') {
                        $append = ' with residential delivery';
                    }
                }
            }
            $this->residentialDelivery = $append;
        }
        return $append;
    }

    /**
     * @param type $result
     * @return array
     */
    public function quoteDetail($result)
    {
        return isset($result->RateReplyDetails) ? $result->RateReplyDetails : ((isset($result->q)) ? $result->q : []);
    }

    /**
     * @param type $serviceType
     * @param type $code
     * @param type $grandTotal
     * @param type $transitTime
     * @param type $serviceTitle
     * @param type $serviceName
     * @return array
     */
    public function getAllServicesArr($serviceType, $code, $grandTotal, $transitTime, $serviceTitle, $serviceName)
    {
        $allowed = [];
        if ($grandTotal > 0) {
            $allowed = [
                'serviceType'   => $serviceType,
                'code'          => $code,
                'rate'          => $grandTotal,
                'transitTime'   => $transitTime,
                'title'         => $serviceTitle,
                'serviceName'   => $serviceName,
            ];
        }
        return $allowed;
    }

    /**
     * @param type $serviceType
     * @return string
     */
    public function getQuoteServiceCode($serviceType)
    {
        $explode = empty($serviceType) ? [] : explode('_', $serviceType);
        $lastKey = end($explode);         // move the internal pointer to the end of the array
        $lastKeyCode = $this->getLastKeyCode($lastKey);

        array_pop($explode);
        $first  = (isset($explode[0])) ? substr($explode[0], 0, 1) : '';
        $second = (isset($explode[1])) ? substr($explode[1], 0, 1) : '';
        $third  = (isset($explode[2])) ? substr($explode[2], 0, 1) : '';
        $fourth = (isset($explode[3])) ? substr($explode[3], 0, 1) : '';

        $code = $first.$second.$third.$fourth.$lastKeyCode;
        return $code;
    }

    /**
     * @param type $lastKey
     * @return string
     */
    public function getLastKeyCode($lastKey)
    {
        $code = '';
        switch ($lastKey) {
            case 'fedexAirServices':
                $code = 'AIR';
                break;
            case 'fedexOneRate':
                $code = 'ORT';
                break;
            case 'fedexServices':
                $code = 'NML';
                break;

            case 'fedexSmartPost':
                $code = 'FSP';
                break;
            default:
                break;
        }
        return $code;
    }

    /**
     * @param type $serviceType
     * @param type $addedHandling
     * @return type
     */
    public function calculateHazardousFee($serviceType, $addedHandling)
    {
        $hazourdous = $this->checkHazardousShipment();
        if (!empty($hazourdous)) {
            $ground = ($serviceType == 'FEDEX_GROUND' || $serviceType == 'GROUND_HOME_DELIVERY') ? true : false;
            $addedHazardous = 0 ;
            if ($this->onlyGndService == '1') {
                if ($ground) {
                    $addedHazardous = $this->gndHzrdousFee + $addedHandling;
                } elseif (!$ground && $this->airHzrdousFee !== '') {
                    $addedHazardous = 0 ;
                }
            } else {
                if ($ground && $this->gndHzrdousFee !== '') {
                    $addedHazardous = $this->gndHzrdousFee + $addedHandling;
                } elseif (!$ground && $this->airHzrdousFee !== '') {
                    $addedHazardous = $this->airHzrdousFee + $addedHandling;
                } else {
                    $addedHazardous = $addedHandling;
                }
            }
        } else {
            $addedHazardous = $addedHandling;
        }
        return $addedHazardous;
    }

    /**
     * @return type
     */
    public function checkHazardousShipment()
    {
        $hazourdous = [];
        $checkHazordous = $this->registry->registry('hazardousShipment');
        if (isset($checkHazordous)) {
            foreach ($checkHazordous as $key => $data) {
                foreach ($data as $k => $d) {
                    if ($d['isHazordous'] == '1') {
                        $hazourdous[] =  $k;
                    }
                }
            }
        }
        return $hazourdous;
    }

    /**
     * Convert Quotes currency to store base currency
     * @param type $availableServ
     */
    public function getQuoteAmount($availableServ, $serviceName = '', $binPack = [])
    {
        $boxFee = 0;
        if (!empty($binPack) && isset($binPack[$serviceName])) {
            $boxFee = $binPack[$serviceName]->boxesFee ?? 0;
        }

        $fedexRateSource = $this->fedexRates;
        if ($fedexRateSource == 'negotiate') {
            if ($availableServ->NegotiatedRates->Amount) {
                $quoteCurrency = $availableServ->NegotiatedRates->Currency;
                $quoteAmmount = $availableServ->NegotiatedRates->Amount + $boxFee;
            } else {
                $quoteCurrency = $availableServ->totalNetCharge->Currency;
                $quoteAmmount = $availableServ->totalNetCharge->Amount + $boxFee;
            }
        } else {
            $quoteCurrency = $availableServ->totalNetCharge->Currency;
            $quoteAmmount = $availableServ->totalNetCharge->Amount + $boxFee;
        }

        return $quoteAmmount;
    }

    /**
     * @param type $serviceType
     * @param type $serviceTitle
     * @param type $minInQ
     * @return type
     */
    public function multishipSetOrderData($serviceType, $serviceTitle, $minInQ)
    {
        $servicesArr['quotes'] = $this->getFinalQuoteArray($minInQ, $serviceType, $serviceTitle);
        return $servicesArr;
    }

    /**
     * @param type $servicesArr
     * @param type $QCount
     */
    public function setOrderDetailWidgetData(array $servicesArr, $scopeConfig)
    {
        $orderDetail['residentialDelivery'] = ($this->residentialDelivery != '' || $this->residentialDlvry == '1' || $this->residentialDlvry == 'yes') ? 'Residential Delivery' : '';
        $setPkgForOrderDetailReg = null !== $this->registry->registry('setPackageDataForOrderDetail') ?
            $this->registry->registry('setPackageDataForOrderDetail') : [];
        $orderDetail['shipmentData'] = array_replace_recursive($setPkgForOrderDetailReg, $servicesArr);

        // set order detail widget data
        $this->coreSession->start();
        $this->coreSession->setFdxOrderDetailSession($orderDetail);
    }


    /**
     * @param array $quotes
     * @param array $allConfigServices
     * @param array $hazardousOriginArr
     * @return array
     */
    public function getOriginsMinimumQuotes($quotes, $allConfigServices, $scopeConfig)
    {

        $minIndexArr = $binPackagingArr = [];
        $resiArr = ['residential' => false, 'label' => ''];
        foreach ($quotes as $key => $quotesArray) {
            $isRad = $quotesArray->autoResidentialsStatus ?? '';
            $autoResTitle = $this->getAutoResidentialTitle($isRad);
            if ($this->residentialDlvry == "yes" || $autoResTitle != '') {
                $resiArr = ['residential' => true, 'label' => $autoResTitle];
            }
            $minInQ = $counter = 0;
            $binPackaging = $this->setBinPackagingData($quotesArray, $key);
            $binPackagingArr[] = $binPackaging;

            foreach ($quotesArray as $key2 => $quote) {
                $service = $key2 === 'fedexServices' ? 'domestic' :
                            (($key2 === 'fedexOneRate') ? 'onerateServices' :
                            (($key2 === 'fedexAirServices') ? 'international' : ''));
                if (isset($quote->q) && $service != '') {
                    foreach ($quote->q as $servkey => $availableServ) {
                        if (isset($availableServ->serviceType)
                            && array_key_exists($availableServ->serviceType, $allConfigServices[$service])) {
                            $serviceType = $availableServ->serviceType;
                            $totalCharge = $this->getQuoteAmount($availableServ, $key2, $binPackaging[$key]);
                            $addedHandling = $this->calculateHandlingFee($totalCharge, $scopeConfig);
                            $grandTotal = $this->calculateHazardousFee($serviceType, $addedHandling);
                            $code = $this->getQuoteServiceCode($serviceType . "_" . $key2);
                            $currentService = (string) $allConfigServices[$service][$availableServ->serviceType];
                            $currentArray = ['code'=> $code,
                                              'rate' => $grandTotal,
                                              'title' => $currentService.' '.$autoResTitle,
                                              'resi' => $resiArr];
                            if ($counter == 0) {
                                $minInQ = $currentArray;
                            } else {
                                $minInQ = ($currentArray['rate'] < $minInQ['rate'] ? $currentArray : $minInQ);
                            }
                            $counter++;
                        }
                    }
                }
            }
            if ($minInQ['rate'] > 0) {
                $minIndexArr[$key] = $minInQ;
            }
        }
        $this->coreSession->start();
        $this->coreSession->setSemiBinPackaging($binPackagingArr);
        return $minIndexArr;
    }


    /**
     * This Function returns all active services array from configurations
     * @return array
     */
    public function getAllConfigServicesArray($scopeConfig)
    {
        $grpSec = 'fedexQuoteSetting/third';
        $domSrvcs     = $scopeConfig->getValue(
            $grpSec.'/FedExDomesticServices',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $domSrvcs     = empty($domSrvcs) ? [] : explode(',', $domSrvcs);

        $intSrvcs     = $scopeConfig->getValue(
            $grpSec.'/FedExInternationalServices',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $intSrvcs     = empty($intSrvcs) ? [] : explode(',', $intSrvcs);

        $onerateSrvcs     = $scopeConfig->getValue(
            $grpSec.'/FedExOneRateServices',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $onerateSrvcs     = empty($onerateSrvcs) ? [] : explode(',', $onerateSrvcs);

        foreach ($domSrvcs as $key => $service) {
            $domestic[$service] = $this->getServiceTitle($service, 'domestic');
        }
        foreach ($intSrvcs as $key => $service) {
            $international[$service] = $this->getServiceTitle($service, 'international');
        }
        foreach ($onerateSrvcs as $key => $service) {
            $onerate[$service] = $this->getServiceTitle($service, 'onerate');
        }

        $allConfigServices = [
            'domestic' => $domestic ?? [],
            'international' => $international ?? [],
            'onerateServices' => $onerate ?? []
        ];

        return $allConfigServices;
    }

    /**
     * Final quotes array
     * @param $grandTotal
     * @param $code
     * @param $title
     * @return array
     */
    public function getFinalQuoteArray($grandTotal, $code, $title)
    {
        $allowed = [];
        if ($grandTotal > 0) {
            $allowed = [
                'code'  => $code,// or carrier name
                'title' => $title,
                'rate'  => $grandTotal
            ];
        }

        return $allowed;
    }

    /**
     * Calculate Handling Fee
     * @param $cost
     * @return int
     */
    public function calculateHandlingFee($totalPrice, $scopeConfig)
    {
        $grpSec = 'fedexQuoteSetting/third';
        $hndlngFeeMarkup = $scopeConfig->getValue(
            $grpSec.'/hndlngFee',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $symbolicHndlngFee = $scopeConfig->getValue(
            $grpSec.'/symbolicHndlngFee',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($hndlngFeeMarkup !== '') {
            if ($symbolicHndlngFee == '%') {
                $prcntVal = $hndlngFeeMarkup / 100 * $totalPrice;
                $grandTotal = $prcntVal + $totalPrice;
            } else {
                $grandTotal = $hndlngFeeMarkup + $totalPrice;
            }
        } else {
            $grandTotal = $totalPrice;
        }
        return $grandTotal;
    }

    /**
     * @param type $fieldId
     * @param type $scopeConfig
     * @return type
     */
    public function adminConfigData($fieldId, $scopeConfig)
    {
        return $scopeConfig->getValue(
            "fedexQuoteSetting/third/$fieldId",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return type
     */
    public function getActiveCarriersForENCount()
    {
        return $this->shippingConfig->getActiveCarriers();
    }

    /**
     * This Function returns service title
     * @param $serviceId
     * @return array
     */
    public function getServiceTitle($serviceId, $getServiceOf)
    {
        if ($getServiceOf == 'onerate') {
            $haystack = $this->fedexOnerateCarriersWithTitle();
        } elseif ($getServiceOf == 'international') {
            $haystack = $this->fedexInternationalCarriersWithTitle();
        } else {
            $haystack = $this->fedexCarriersWithTitle();
        }

        if (isset($haystack[$serviceId])) {
            return $haystack[$serviceId];
        }
    }

    /**
     * fedex carrier codes with title
     * @return array
     */
    public function fedexCarriersWithTitle()
    {
        return [
            'GROUND_HOME_DELIVERY'  => __('FedEx Home Delivery'),
            'FEDEX_GROUND'          => __('FedEx Ground'),
            'FEDEX_EXPRESS_SAVER'   => __('FedEx Express Saver'),
            'FEDEX_2_DAY'           => __('FedEx 2Day'),
            'FEDEX_2_DAY_AM'        => __('FedEx 2Day AM'),
            'STANDARD_OVERNIGHT'    => __('FedEx Standard Overnight'),
            'PRIORITY_OVERNIGHT'    => __('FedEx Priority Overnight'),
            'FIRST_OVERNIGHT'       => __('FedEx First Overnight'),
        ];
    }

    /**
     * @return array
     */
    public function fedexInternationalCarriersWithTitle()
    {
        return [
            //international services
            'FEDEX_GROUND'                          => __('FedEx International Ground'),
            'INTERNATIONAL_ECONOMY'                 => __('FedEx International Economy'),
            'INTERNATIONAL_ECONOMY_DISTRIBUTION'    => __('FedEx International Economy Distribution'),
            'INTERNATIONAL_ECONOMY_FREIGHT'         => __('FedEx International Economy Freight'),
            'INTERNATIONAL_FIRST'                   => __('FedEx International First'),
            'INTERNATIONAL_PRIORITY'                => __('FedEx International Priority'),
            'INTERNATIONAL_PRIORITY_DISTRIBUTION'   => __('FedEx International Priority Distribution'),
            'INTERNATIONAL_PRIORITY_FREIGHT'        => __('FedEx International Priority Freight'),
            'INTERNATIONAL_DISTRIBUTION_FREIGHT'    => __('FedEx International Distribution Freight'),
        ];
    }

    /**
     * @return array
     */
    public function fedexOnerateCarriersWithTitle()
    {
        return [
            //onerate services
            'FEDEX_EXPRESS_SAVER'   => __('FedEx One Rate Express Saver'),
            'FEDEX_2_DAY'           => __('FedEx One Rate 2Day'),
            'FEDEX_2_DAY_AM'        => __('FedEx One Rate 2Day AM'),
            'STANDARD_OVERNIGHT'    => __('FedEx One Rate Standard Overnight'),
            'PRIORITY_OVERNIGHT'    => __('FedEx One Rate Priority Overnight'),
            'FIRST_OVERNIGHT'       => __('FedEx One Rate First Overnight'),
        ];
    }

    /**
     * @return array
     */
    public function quoteSettingFieldsToRestrict()
    {
        $restriction = [];
        $currentPlanArr = $this->fedexSmallPlanName('ENFedExSmpkg');
        $transitFields = [
            'transitDaysNumber','transitDaysRestrictionByTransitTimeInDays','transitDaysRestrictionByCalenderDaysInTransit'
        ];
        $hazmatFields = [
            'onlyGndService','gndHzrdousFee','airHzrdousFee'
        ];
        switch ($currentPlanArr['planNumber']) {
            case 2:
                $restriction = [
                    'advance' => $transitFields
                ];
                break;
            case 3:
                break;
            default:
                $restriction = [
                    'advance' => $transitFields,
                    'standard' => $hazmatFields
                ];
                break;
        }
        return $restriction;
    }

    /**
     * @return string
     */
    public function fedexSmallSetPlanNotice($planRefreshUrl = '')
    {
        $planMsg = '';
        $planPackage = $this->fedexSmallPlanName('ENFedExSmpkg');
        if (is_null($planPackage['storeType'])) {
            $planPackage = [];
        }
        $planMsg = $this->diplayPlanMessages($planPackage, $planRefreshUrl);
        return $planMsg;
    }

    /**
     * @param type $planPackage
     * @return type
     */
    public function diplayPlanMessages($planPackage, $planRefreshUrl = '')
    {

        $planRefreshLink = '';
        if (!empty($planRefreshUrl)) {
            $planRefreshLink = ' <a href="javascript:void(0)" id="plan-refresh-link" planRefAjaxUrl = '.$planRefreshUrl.' onclick="fedexSmpkgPlanRefresh(this)" >Click here</a> to refresh the plan (please sign-in again after this action).';
            $planMsg = __('The subscription to the Fedex Small Freight Quotes module is inactive. If you believe the subscription should be active and you recently changed plans (e.g. upgraded your plan), your firewall may be blocking confirmation from our licensing system. To resolve the situation, <a href="javascript:void(0)" id="plan-refresh-link" planRefAjaxUrl = '.$planRefreshUrl.' onclick="fedexSmpkgPlanRefresh(this)" >click this link</a> and then sign in again. If this does not resolve the issue, log in to eniture.com and verify the license status.');
        }else{
            $planMsg = __('The subscription to the Fedex Small Freight Quotes module is inactive. Please log into eniture.com and update your license.');
        }

        if (isset($planPackage) && !empty($planPackage)) {
            if ($planPackage['planNumber'] !== null && $planPackage['planNumber'] != '-1') {
                $planMsg = __('The Fedex Small Freight Quotes from Eniture Technology is currently on the '.$planPackage['planName'].' and will renew on '.$planPackage['expiryDate'].'. If this does not reflect changes made to the subscription plan'.$planRefreshLink.'.');
            }
        }

        return $planMsg;
    }
    /**
     * Get FedEx Small Plan
     * @return string
     */
    public function fedexSmallPlanName($carrierId)
    {
        $plan = $this->getConfigData("eniture/$carrierId/plan");
        $storeType = $this->getConfigData("eniture/$carrierId/storetype");
        $expireDays = $this->getConfigData("eniture/$carrierId/expireday");
        $expiryDate = $this->getConfigData("eniture/$carrierId/expiredate");
        $planName = "";

        switch ($plan) {
            case 3:
                $planName = "Advanced Plan";
                break;
            case 2:
                $planName = "Standard Plan";
                break;
            case 1:
                $planName = "Basic Plan";
                break;
            case 0:
                $planName = "Trial Plan";
                break;
        }
        $packageArray = [
            'planNumber' => $plan,
            'planName' => $planName,
            'expireDays' => $expireDays,
            'expiryDate' => $expiryDate,
            'storeType' => $storeType
        ];
        return $packageArray;
    }

    /**
     * @param $confPath
     * @return mixed
     */
    public function getConfigData($confPath)
    {
        $scopeConfig = $this->context->getScopeConfig();
        return $scopeConfig->getValue($confPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return int
     */
    public function whPlanRestriction()
    {
        $planArr = $this->fedexSmallPlanName('ENFedExSmpkg');
        $warehouses = $this->fetchWarehouseSecData('warehouse');
        $planNumber = isset($planArr['planNumber']) ? $planArr['planNumber'] : '';

        if ($planNumber < 2 && count($warehouses) >= 1) {
            $this->canAddWh = 0;
        }
        return $this->canAddWh;
    }

    /**
     * @return int
     */
    public function checkAdvancePlan()
    {
        $advncPlan = 1;
        $planArr = $this->fedexSmallPlanName('ENFedExSmpkg');
        $planNumber = isset($planArr['planNumber']) ? $planArr['planNumber'] : '';

        if ($planNumber != 3) {
            $advncPlan = 0;
        }
        return $advncPlan;
    }

    /**
     * @param type $quotesarray
     * @param type $instoreLd
     * @return type
     */
    public function instoreLocalDeliveryQuotes($quotesarray, $instoreLd)
    {
        $data = $this->registry->registry('shipmentOrigin');
        if (count($data) > 1) {
            return $quotesarray;
        }

        foreach ($data as $array) {
            $warehouseData = $this->getWarehouseData($array);


            /* Quotes array only to be made empty if Suppress other rates is ON and Instore Pickup or Local Delivery also carries some quotes. Else if Instore Pickup or Local Delivery does not have any quotes i.e Postal code or within miles does not match then the Quotes Array should be returned as it is. */
            if ($warehouseData['suppress_other']) {
                if ((isset($instoreLd->inStorePickup->status) && $instoreLd->inStorePickup->status == 1)
                    || (isset($instoreLd->localDelivery->status) && $instoreLd->localDelivery->status == 1)) {
                    $quotesarray=[];
                }
            }
            if (isset($instoreLd->inStorePickup->status) && $instoreLd->inStorePickup->status == 1) {
                $quotesarray[] = [
                    'serviceType' => 'IN_STORE_PICKUP',
                    'code' => 'INSP',
                    'rate' => 0,
                    'transitTime' => '',
                    'title' => $warehouseData['inStoreTitle'],
                    'serviceName' => 'fedexServices'
                ];
            }

            if (isset($instoreLd->localDelivery->status) && $instoreLd->localDelivery->status == 1) {
                $quotesarray[] = [
                    'serviceType' => 'LOCAL_DELIVERY',
                    'code' => 'LOCDEL',
                    'rate' => $warehouseData['fee_local_delivery'],
                    'transitTime' => '',
                    'title' => $warehouseData['locDelTitle'],
                    'serviceName' => 'fedexServices'
                ];
            }
        }
        return $quotesarray;
    }

    /**
     *
     */
    public function clearCache()
    {
        $this->cacheManager->flush($this->cacheManager->getAvailableTypes());

        // or this
        $this->cacheManager->clean($this->cacheManager->getAvailableTypes());
    }

    /**
     * @param $data
     * @return array
     */
    public function getWarehouseData($data)
    {
        $return = [];
        $whCollection = $this->warehouseFactory->create()->getCollection()
            ->addFilter('location', ['eq' => $data['location']])
            ->addFilter('warehouse_id', ['eq' => $data['locationId']]);

        $whCollection = $this->purifyCollectionData($whCollection);

        if(!empty($whCollection[0]['in_store']) && is_string($whCollection[0]['in_store'])){
            $inStore = json_decode($whCollection[0]['in_store'], true);
        }else{
            $inStore = [];
        }

        if(!empty($whCollection[0]['local_delivery']) && is_string($whCollection[0]['local_delivery'])){
            $locDel = json_decode($whCollection[0]['local_delivery'], true);
        }else{
            $locDel = [];
        }

        if ($inStore) {
            $inStoreTitle = $inStore['checkout_desc_store_pickup'];
            if (empty($inStoreTitle)) {
                $inStoreTitle = "In-store pick up";
            }
            $return['inStoreTitle'] = $inStoreTitle;
            $return['suppress_other'] = $inStore['suppress_other']=='1' ? true : false;
        }

        if ($locDel) {
            $locDelTitle = $locDel['checkout_desc_local_delivery'];
            if (empty($locDelTitle)) {
                $locDelTitle = "Local delivery";
            }
            $return['locDelTitle'] = $locDelTitle;
            $return['fee_local_delivery'] = $locDel['fee_local_delivery'];
            $return['suppress_other'] = $locDel['suppress_other']=='1' ? true : false;
        }
        return $return;
    }
}
