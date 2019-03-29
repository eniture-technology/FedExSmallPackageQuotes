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
    
    /**
     * @return array
     */
    public function getOneRateServices()
    {
        $checked = [];
        $moduleManager = $this->context->getModuleManager();
        if ($moduleManager->isEnabled('Eniture_BoxSizes')) {
            $defualtConn = \Magento\Framework\App\ResourceConnection::DEFAULTCONNECTION;
            $checked = $this->resource->getConnection($defualtConn)->fetchAll(
                $this->enituremodulesFactory->create()->getCollection()->getSelect()
                ->where("type=(?) AND boxavailable=(?)", 'onerate', '1')->limit(30)
            );
        }
        
        return $checked;
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
        $defualtConn = \Magento\Framework\App\ResourceConnection::DEFAULTCONNECTION;
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
        $defualtConn    = \Magento\Framework\App\ResourceConnection::DEFAULTCONNECTION;
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
        $defualtConn    = \Magento\Framework\App\ResourceConnection::DEFAULTCONNECTION;
        $whTableName    = $this->resource->getTableName('warehouse');
        return $this->resource->getConnection($defualtConn)->delete("$whTableName", $data);
    }
    
    /**
     * @return string
     */
    public function checkPickupDeliveryAddon()
    {
        $enable = 'no';
        $moduleManager = $this->context->getModuleManager();
        if ($moduleManager->isEnabled('ZEniture_InstorePickupLocalDelivery')) {
            $enable = 'yes';
        }
        return $enable;
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
        $moduleManager = $this->context->getModuleManager();
        if ($moduleManager->isEnabled('ZEniture_InstorePickupLocalDelivery')) {
            $pickupDelvryArr = [
                'enable_store_pickup'           => ($inputData['enable_instore'] === 'true')?1:0,
                'miles_store_pickup'            => $inputData['address_miles_instore'],
                'match_postal_store_pickup'     => $inputData['zipmatch_instore'],
                'checkout_desc_store_pickup'    => $inputData['desc_instore'],
                'enable_local_delivery'         => ($inputData['enable_delivery'] === 'true')?1:0,
                'miles_local_delivery'          => $inputData['address_miles_delivery'],
                'match_postal_local_delivery'   => $inputData['zipmatch_delivery'],
                'checkout_desc_local_delivery'  => $inputData['desc_delivery'],
                'fee_local_delivery'            => $inputData['fee_delivery'],
                'suppress_local_delivery'       => ($inputData['supppress_delivery'] === 'true')?1:0,
            ];
            $dataArr = array_merge($dataArr, $pickupDelvryArr);
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
     * FedEx Get Shipment Rated Array
     * @param $locationGroups
     */
    public function ratedShipmentDetails($locationGroups)
    {
        $rates_option = 'negotiated';
        ($rates_option == 'negotiated') ? $searchword = 'PAYOR_ACCOUNT' : $searchword = 'PAYOR_LIST';

        $allLocations = array_filter($locationGroups, function ($var) use ($searchword) {
            return preg_match(
                "/^$searchword/",
                $var->ShipmentRateDetail->RateType
            );
        });

        return $allLocations;
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
            if (isset($servicesList->serviceType, $servicesList->ratedShipmentDetails)
                && $servicesList->serviceType == "SMART_POST") {
                //this condtion is working for smart post feature
                $ratedShipmentDetails = $servicesList->ratedShipmentDetails;
                $serviceType = $servicesList->serviceType;
                $services = $idServices;
                $serviceKeyName = key($services);
                $service = $this->ratedShipmentDetails($ratedShipmentDetails);
                
                $serviceTitle = (isset($services[$serviceKeyName][$serviceType])) ?
                        $services[$serviceKeyName][$serviceType] : "";

                $service = (!empty($service)) ? reset($service) : [];
                
                $totalCharge = $this->getQuoteAmount($service);
                
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

        return $allServicesArray;
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
     * This function returns minimum array index from array
     * @param $servicesArr
     * @return array
     */
    public function findArrayMininum($servicesArr)
    {
        $counter = 1;
        $minIndex = [];
        foreach ($servicesArr as $key => $value) {
            if ($counter == 1) {
                $minimum =  $value['rate'];
                $minIndex = $value;
                $counter = 0;
            } else {
                if ($value['rate'] < $minimum) {
                    $minimum =  $value['rate'];
                    $minIndex = $value;
                }
            }
        }
        return $minIndex;
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
}
