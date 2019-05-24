<?php
/**
 * @category   Shipping
 * @package    Eniture_FedExSmallPackages
 * @author     Eniture Technology : <sales@eniture.com>
 * @website    http://eniture.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Eniture\FedExSmallPackages\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
 
class Data extends AbstractHelper
{
    public $connection;
    public $WHtableName;
    public $shippingConfig;
    public $storeManager;
    public $currencyFactory;
    public $priceCurrency;
    public $registry;
    public $coreSession;
    public $originZip;
    public $residentialDelivery;
    public $curl;
    public $canAddWh = 1;
    
    /**
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
     * @param \Eniture\FedExSmallPackages\Model\WarehouseFactory $warehouseFactory
     * @param \Magento\Framework\HTTP\Client\Curl $curl
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
        \Eniture\FedExSmallPackages\Model\WarehouseFactory $warehouseFactory,
        \Eniture\FedExSmallPackages\Model\EnituremodulesFactory $enituremodulesFactory,
        \Magento\Framework\HTTP\Client\Curl $curl
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
        parent::__construct($context);
    }
	
	public function wsHittingUrls($index){
		$allWsUrl = [
					'testConnection' => 'https://eniture.com/ws/s/fedex/fedex_shipment_rates_test.php',
					'getAddress' => 'https://eniture.com/ws/addon/google-location.php',
					'multiDistance' => 'https://eniture.com/ws/addon/google-location.php',
					'planUpgrade' => 'https://eniture.com/ws/web-hooks/subscription-plans/create-plugin-webhook.php',
					'getQuotes' => 'https://eniture.com/ws/v2.0/index.php'
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
            ];
         $plan = $this->fedexSmallPlanName('ENFedExSmpkg');
         if ($plan['planNumber'] == 3) {
             if ($inputData['instore_enable']) {
                 $pickupDelvryArr = [
                    'enable_store_pickup'           => ($inputData['instore_enable'] === 'true')?1:0,
                    'miles_store_pickup'            => $inputData['is_within_miles'],
                    'match_postal_store_pickup'     => $inputData['is_postcode_match'],
                    'checkout_desc_store_pickup'    => $inputData['is_checkout_descp'],
                 ];
                 $dataArr['in_store'] = json_encode($pickupDelvryArr);
                }
                if ($inputData['ld_enable']) {
                    $localDelvryArr = [
                    'enable_local_delivery'         => ($inputData['ld_enable'] === 'true')?1:0,
                    'miles_local_delivery'          => $inputData['ld_within_miles'],
                    'match_postal_local_delivery'   => $inputData['ld_postcode_match'],
                    'checkout_desc_local_delivery'  => $inputData['ld_checkout_descp'],
                    'fee_local_delivery'            => $inputData['ld_fee'],
                    'suppress_local_delivery'       => ($inputData['ld_sup_rates'] === 'true')?1:0,
                    ];
                    $dataArr['local_delivery'] = json_encode($localDelvryArr);
                }
         }

            return $dataArr;
    }
    
    /**
     * @param type $scopeConfig
     */
    public function quoteSettingsData($scopeConfig)
    {
        $fields = [
            'residentialDlvry'  => 'residentialDlvry',
            'fedexRates'        => 'fedexRates',
            'onlyGndService'    => 'onlyGndService',
            'gndHzrdousFee'     => 'gndHzrdousFee',
            'airHzrdousFee'     => 'airHzrdousFee',
        ];
        foreach ($fields as $key => $field) {
            $this->$key = $this->adminConfigData($field, $scopeConfig);
        }
        
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
                    $key === 'checkout_desc_store_pickup' ||
                    $key === 'checkout_desc_local_delivery') {
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
    public function checkUpdatePickupDelivery($getWarehouse, array $validateData)
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
        $result = json_decode($output, $isAssocArray);
        return $result;
    }
    
    /**
     * @param type $key
     * @return string|empty
     */
    public function getZipcode($key)
    {
        $key = explode("_", $key);
        return (isset($key[0])) ? $key[0] : "";
    }
    
    /**
     * @param type $quotes
     * @param type $isMultishipmentQuantity
     * @param type $scopeConfig
     * @return type
     */
    public function getQuotesResults($quotes, $isMultishipmentQuantity, $scopeConfig)
    {
        $allConfigServices = $this->getAllConfigServicesArray($scopeConfig);
        $this->quoteSettingsData($scopeConfig);
        
        if ($isMultishipmentQuantity) {
            return $this->getOriginsMinimumQuotes($quotes, $allConfigServices, $scopeConfig);
        }
        
        $servicesArr = $filteredQuotes = [];
        $multiShipment = (count($quotes) > 1 ? true : false);
        $totalMultiRate = 0;

        foreach ($quotes as $key => $quote) {
            if (isset($quote->severity) && $quote->severity == 'ERROR') {
                return [];
            }
            $binPackaging[] = $this->setBinPackagingData($quote, $key);

            $shipment = $quote->shipment;
            $quoteServices[$shipment] = (isset($allConfigServices[$shipment])) ? $allConfigServices[$shipment] : [];
            $onerateSrvcs['fedexOneRate'] = $allConfigServices['onerateServices'];
            
            $filteredQuotes[$key] = $this->parseFedexSmallOutput($quote, $quoteServices, $onerateSrvcs, $scopeConfig);
        }
        
        $this->coreSession->start();
        $this->coreSession->setOrderBinPackaging($binPackaging);
        
        if (!$multiShipment) {
            $this->setOrderDetailWidgetData([], $scopeConfig);
            return reset($filteredQuotes);
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
        isset($quote->fedexServices->binPackagingData) ?
            $binPackaging[$key]['fedexServices'] = $quote->fedexServices->binPackagingData : [];
        
        isset($quote->fedexOneRate->binPackagingData) ?
            $binPackaging[$key]['fedexOneRate'] = $quote->fedexOneRate->binPackagingData : [];
        
        isset($quote->fedexAirServices->binPackagingData) ?
            $binPackaging[$key]['fedexAirServices'] = $quote->fedexAirServices->binPackagingData : [];
        
        return $binPackaging;
    }
    
    /**
     * Get Shipping Array For Single Shipment
     * @param $result
     * @param $serviceType
     * @return array
     */
    public function parseFedexSmallOutput($result, $idServices, $oneRateSrvcs, $scopeConfig)
    {
        $quote = $accessorials = $allServicesArray = [];
        $transitTime = "";
        ($this->residentialDlvry == "1") ? $accessorials[] = "R" : "";
        
        if (isset($result->fedexServices) && !(isset($result->fedexAirServices, $result->fedexOneRate))) {
            $quote['fedexServices'] = $this->quoteDetail($result->fedexServices);
            $simpleQuotes = 1;
        } else {
            isset($result->fedexOneRate) ?
                $quote['fedexOneRate'] = $this->quoteDetail($result->fedexOneRate) : "";

            isset($result->fedexAirServices) ?
                $quote['fedexAirServices'] = $this->quoteDetail($result->fedexAirServices) : "";

            isset($result->fedexServices) ?
                $quote['fedexServices'] = $this->quoteDetail($result->fedexServices) : "";
        }
        
        foreach ($quote as $serviceName => $servicesList) {
            $servicesList = $this->transitTimeRestriction($servicesList);

            if (isset($servicesList->SMART_POST) && $servicesList->SMART_POST->serviceType == "SMART_POST") {
                //this condtion is working for smart post feature
                $serviceType = $servicesList->SMART_POST->serviceType;
                $serviceTitle = "FedEx SmartPost";
                $totalCharge = $this->getQuoteAmount($servicesList->SMART_POST);
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
            } elseif (isset($servicesList) && (!empty($servicesList))) {
                $services = ($serviceName == "fedexOneRate") ? $oneRateSrvcs : $idServices;
                $serviceKeyName = key($services);
                
                if ($serviceKeyName != "international" && (!isset($simpleQuotes))) {
                    if ($serviceName == "fedexAirServices") {
                        $homeGrdServices = [];
                        (isset($services[$serviceKeyName]['GROUND_HOME_DELIVERY'])) ?
                        $homeGrdServices[$serviceKeyName]['GROUND_HOME_DELIVERY'] = 'FedEx Home Delivery' : "";
                        (isset($services[$serviceKeyName]['FEDEX_GROUND'])) ?
                        $homeGrdServices[$serviceKeyName]['FEDEX_GROUND'] = 'FedEx Ground' : "";
                        $services = $homeGrdServices;
                    } elseif ($serviceName == "fedexServices") {
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
                        $autoResTitle = $this->getAutoResidentialTitle($service);
                    
                        if ($serviceKeyName == "international"
                                && $serviceName != "fedexAirServices"
                                || $serviceKeyName != "international") {
                            $transitTime = (isset($service->transitTime)) ? $service->transitTime : '';

                            $serviceType = $service->serviceType;

                            $totalCharge = $this->getQuoteAmount($service);

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
        if ($plan['planNumber'] == 3 && strlen($daysToRestrict) > 0 && strlen($transitDayType) > 0) {
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
        if ($moduleManager->isEnabled('Eniture_AutoDetectResidential')
            && $this->residentialDlvry == null
            || $this->residentialDlvry == '0') {
            if (isset($service->Surcharges->SurchargeType)
                && $service->Surcharges->SurchargeType == 'RESIDENTIAL_DELIVERY') {
                $append = ' with residential delivery';
            } else {
                if (isset($service->Surcharges)) {
                    foreach ($service->Surcharges as $key => $surcharge) {
                        if (isset($surcharge->SurchargeType)
                            && $surcharge->SurchargeType == 'RESIDENTIAL_DELIVERY') {
                            $append = ' with residential delivery';
                        }
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
        $explode = explode('_', $serviceType);
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
    public function getQuoteAmount($availableServ)
    {
        $fedexRateSource = $this->fedexRates;
        if ($fedexRateSource == 'negotiate') {
            if ($availableServ->NegotiatedRates->Amount) {
                $quoteCurrency = $availableServ->NegotiatedRates->Currency;
                $quoteAmmount = $availableServ->NegotiatedRates->Amount;
            } else {
                $quoteCurrency = $availableServ->totalNetCharge->Currency;
                $quoteAmmount = $availableServ->totalNetCharge->Amount;
            }
        } else {
            $quoteCurrency = $availableServ->totalNetCharge->Currency;
            $quoteAmmount = $availableServ->totalNetCharge->Amount;
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
        $orderDetail['residentialDelivery'] = ($this->residentialDelivery != '' || $this->residentialDlvry == '1') ?
            'Residential Delivery' : '';
        $setPkgForOrderDetailReg = null !== $this->registry->registry('setPackageDataForOrderDetail') ?
                $this->registry->registry('setPackageDataForOrderDetail') : [];
        $orderDetail['shipmentData'] = array_replace_recursive($setPkgForOrderDetailReg, $servicesArr);
        
        // set order detail widget data
        $this->coreSession->start();
        $this->coreSession->setOrderDetailSession($orderDetail);
    }

    
    /**
     * @param array $quotes
     * @param array $allConfigServices
     * @param array $hazardousOriginArr
     * @return array
     */
    public function getOriginsMinimumQuotes($quotes, $allConfigServices, $scopeConfig)
    {
        $minIndexArr = [];
        foreach ($quotes as $key => $quote) {
            $minInQ = $counter = 0;
            if (isset($quote->q)) {
                foreach ($quote->q as $servkey => $availableServ) {
                    if (isset($availableServ->serviceType)
                        && in_array($availableServ->serviceType, $allConfigServices)) {
                        $curruntAmount = $availableServ->totalNetCharge->Amount;
                        if ($counter == 0) {
                            $minInQ = $curruntAmount;
                        } else {
                            $minInQ = ($curruntAmount < $minInQ ? $curruntAmount : $minInQ);
                        }
                        $counter ++;
                    }
                }
                if ($minInQ > 0) {
                    $minInQ = $this->calculateHandlingFee($minInQ, $scopeConfig);
                    $minIndexArr[$key] = $minInQ;
                }
            }
        }
        return $minIndexArr;
    }
    
    /*
    * Average Rate ( LTL Freight Services )
    * @return Avg Rate
    */

    public function fedexSmpkgLtlGetAvgRate($allServices, $numberOption, $activCarriers)
    {
        $totalPrice = $price = 0;
        if (!empty($allServices)) {
            foreach ($allServices as $services) {
                $totalPrice += $services['rate'];
            }

            if ($numberOption < count($activCarriers) && $numberOption < count($allServices)) {
                $slicedArray = array_slice($allServices, 0, $numberOption);
                foreach ($slicedArray as $services) {
                    $price += $services['rate'];
                }
                $totalPrice = $price / $numberOption;
            } elseif (count($activCarriers) < $numberOption && count($activCarriers) < count($allServices)) {
                $totalPrice = $totalPrice / count($activCarriers);
            } else {
                $totalPrice = $totalPrice / count($allServices);
            }

            return $totalPrice;
        }
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
        $domSrvcs     = explode(',', $domSrvcs);
        
        $intSrvcs     = $scopeConfig->getValue(
            $grpSec.'/FedExInternationalServices',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $intSrvcs     = explode(',', $intSrvcs);
        
        $onerateSrvcs     = $scopeConfig->getValue(
            $grpSec.'/FedExOneRateServices',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $onerateSrvcs     = explode(',', $onerateSrvcs);
        
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
            'domestic' => $domestic,
            'international' => $international,
            'onerateServices' => $onerate
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
            case 0:
                $restriction = [
                    'advance' => $transitFields,
                    'standard' => $hazmatFields
                ];
                break;
            case 1:
                $restriction = [
                    'advance' => $transitFields,
                    'standard' => $hazmatFields
                ];
                break;
            case 2:
                $restriction = [
                    'advance' => $transitFields
                ];
                break;
            default:
                break;
        }
        return $restriction;
    }
    
    /**
     * @return string
     */
    public function fedexSmallSetPlanNotice()
    {
        $planMsg = '';
        $planPackage = $this->fedexSmallPlanName('ENFedExSmpkg');
        $plan = $planPackage['planNumber'];
        $storeType = $planPackage['storeType'];
        
        if ($storeType == "1" || $storeType == "0" &&
            ($plan == "0" || $plan == "1" || $plan == "2" || $plan == "3")) {
            $planMsg = $this->diplayPlanMessages($planPackage);
        }
        return $planMsg;
    }
    
    /**
     * @param type $planPackage
     * @return type
     */
    public function diplayPlanMessages($planPackage)
    {
        $planMsg = '';
        if (isset($planPackage) && !empty($planPackage)) {
            if ($planPackage['planNumber'] == '0') {
                $planMsg = __('Eniture - FedEx Small Package quotes is currently on the '.$planPackage['planName'].'. Your plan will expire within '.$planPackage['expireDays'].' days and plan renews on '.$planPackage['expiryDate'].'.');
            } elseif ($planPackage['planNumber'] == '1' || $planPackage['planNumber'] == '2' || $planPackage['planNumber'] == '3') {
                $planMsg = __('You are currently on the '.$planPackage['planName'].'. The plan renews on '.$planPackage['expiryDate'].'.');
            } else {
                $planMsg = __('Your current plan subscription is inactive. Please activate your plan subscription from <a target="_blank" href="http://eniture.com/plan/woocommerce-fedex-small-package-plugin/">here</a>.');
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
    
    public function getConfigData($confPath)
    {
        $scopeConfig = $this->context->getScopeConfig();
        return $scopeConfig->getValue($confPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function whPlanRestriction()
    {
        $planArr = $this->fedexSmallPlanName('ENFedExSmpkg');
        $warehouses = $this->fetchWarehouseSecData('warehouse');
        $planNumber = isset($planArr['planNumber']) ? $planArr['planNumber'] : '';

        if (($planNumber == 0 || $planNumber == 1) && count($warehouses) >= 1) {
            $this->canAddWh = 0;
        }
        return $this->canAddWh;
    }
    
    /**
     * @return int
     */
    public function checkAdvancePlan() {
        $advncPlan = 1;
        $planArr = $this->fedexSmallPlanName('ENFedExSmpkg');
        $planNumber = isset($planArr['planNumber']) ? $planArr['planNumber'] : '';

        if ($planNumber == 0 || $planNumber == 1 || $planNumber == 2) {
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
            $inStoreTitle = $array['InstorPickupLocalDelivery']['inStore']->checkout_desc_store_pickup;
            if (empty($inStoreTitle)) {
                $inStoreTitle = "In-Store Pick Up";
            }

            $locDelTitle = $array['InstorPickupLocalDelivery']['locDel']->checkout_desc_local_delivery;
            if (empty($locDelTitle)) {
                $locDelTitle = "Local Delivery";
            }

            /* Quotes array only to be made empty if Suppress other rates is ON and Instore Pickup and Local Delivery also carries some quotes. Else if Instore Pickup and Local Delivery does not have any quotes i.e Postal code or within miles does not match then the Quotes Array should be returned as it is. */
            if (isset($array['InstorPickupLocalDelivery']['locDel']->suppress_local_delivery) &&
                $array['InstorPickupLocalDelivery']['locDel']->suppress_local_delivery == 1) {

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
                    'title' => $inStoreTitle,
                    'serviceName' => 'fedexServices'
                ];
            }

            if (isset($instoreLd->localDelivery->status) && $instoreLd->localDelivery->status == 1) {
                $quotesarray[] = [
                    'serviceType' => 'LOCAL_DELIVERY',
                    'code' => 'LOCDEL',
                    'rate' => $array['InstorPickupLocalDelivery']['locDel']->fee_local_delivery,
                    'transitTime' => '',
                    'title' => $locDelTitle,
                    'serviceName' => 'fedexServices'
                ];
            }
        }
        return $quotesarray;
    }
}
