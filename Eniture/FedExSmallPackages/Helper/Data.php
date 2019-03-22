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
    protected $_connection;
    protected $_WHtableName;
    protected $_shippingConfig;
    protected $_storeManager;
    protected $_currencyFactory;
    protected $_priceCurrency;
    protected $_registry;
    protected $_coreSession;
    protected $originZip;
    protected $residentialDelivery;
    
    /**
     * 
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
        \Eniture\FedExSmallPackages\Model\EnituremodulesFactory $enituremodulesFactory
    ) {
        $this->_connection          =  $resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION); 
        $this->_WHtableName         = $resource->getTableName('warehouse');
        $this->_shippingConfig      = $shippingConfig;
        $this->_storeManager        = $storeManager;
        $this->_currencyFactory     = $currencyFactory;
        $this->currenciesModel      = $currencyModel;
        $this->_priceCurrency       = $priceCurrency;
        $this->directoryHelper      = $directoryHelper;
        $this->_registry            = $registry;
        $this->_coreSession         = $coreSession;
        $this->_warehouseFactory    = $warehouseFactory->create();
        $this->_enituremodulesFactory    = $enituremodulesFactory->create();
        $this->_moduleManager       = $context->getModuleManager();
        parent::__construct($context);
    }
    
    /**
     * 
     * @return array
     */
    public function getOneRateServices(){
        $checked = array();
        if($this->_moduleManager->isEnabled('Eniture_BoxSizes')){
            $checked = $this->_connection->fetchAll($this->_enituremodulesFactory->getCollection()->getSelect()->where("type=(?) AND boxavailable=(?)", 'onerate', '1'));
        }
        
        return $checked;   
    }
    
    /**
     * 
     * @param $section
     * @param $whereClause
     * @return array
     */
    function fetchWarehouseSecData($location) {
        $whCollection       = $this->_warehouseFactory->getCollection()->addFilter('location', array('eq' => $location));
        $warehouseSecData   = $this->purifyCollectionData($whCollection);
        
        return $warehouseSecData;
    }
    
    function purifyCollectionData($whCollection) {
        $warehouseSecData = array();
        foreach($whCollection as $wh){
            $warehouseSecData[] = $wh->getData();
        }
        return $warehouseSecData;
    }
    /**
     * 
     * @param $section
     * @param $whereClause
     * @return array
     */
    function fetchDropshipWithID($warehouseId) {
        $whFactory = $this->_warehouseFactory;
        $dsCollection  = $whFactory->getCollection()
                            ->addFilter('location', array('eq' => 'dropship'))
                            ->addFilter('warehouse_id', array('eq' => $warehouseId));
        
        $dropshipSecData   = $this->purifyCollectionData($dsCollection);
        
        return $dropshipSecData;
    }
    
    /**
     * 
     * @param type $data
     * @param type $whereClause
     * @return type
     */
    function updateWarehousData($data, $whereClause) {
        return $this->_connection->update( "$this->_WHtableName", $data, "$whereClause" );
    }
    
    /**
     * 
     * @param type $id
     * @param type $data
     * @return type
     */
    function insertWarehouseData($data, $id) {
        $insertQry = $this->_connection->insert( "$this->_WHtableName", $data );
        if ($insertQry == 0) {
            $lastid = $id;
        }else{
            $lastid = $this->_connection->lastInsertId();
        }
        return array('insertId' => $insertQry, 'lastId' => $lastid);
    }
    
    /**
     * 
     * @param type $data
     * @return type
     */
    function deleteWarehouseSecData($data) {
        return $this->_connection->delete("$this->_WHtableName", $data);
    }
    
    /**
     * 
     * @return string
     */
    function checkPickupDeliveryAddon() {
        $enable = 'no';
        if($this->_moduleManager->isEnabled('ZEniture_InstorePickupLocalDelivery')){
            $enable = 'yes';
        }
        return $enable;
    }
    
    /**
     * Data Array
     * @param $inputData
     * @return array
     */
    
    function fedexSmpkgOriginArray($inputData) {
        $dataArr = array(
                'city'                          => $inputData['city'],
                'state'                         => $inputData['state'],
                'zip'                           => $inputData['zip'],
                'country'                       => $inputData['country'],
                'location'                      => $inputData['location'], 
                'nickname'                      => (isset($inputData['nickname']))?$inputData['nickname']:'',
            );
                        
        if($this->_moduleManager->isEnabled('ZEniture_InstorePickupLocalDelivery')){
            $pickupDelvryArr = array(
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
            );
            $dataArr = array_merge($dataArr, $pickupDelvryArr);
        }
        return $dataArr;
    }
    
    /**
     * 
     * @param type $scopeConfig
     */
    function quoteSettingsData($scopeConfig) {
        $fields = array(
            'residentialDlvry'  => 'residentialDlvry',
            'fedexRates'        => 'fedexRates',
            'onlyGndService'    => 'onlyGndService',
            'gndHzrdousFee'     => 'gndHzrdousFee',
            'airHzrdousFee'     => 'airHzrdousFee',
        );
        foreach ($fields as $key => $field) {
            $this->$key = $this->adminConfigData($field, $scopeConfig);
        }
        
        // Get origin zipcode array for onerate settings
        $this->getOriginZipCodeArr();
    }
    
    /**
     * getOriginZipCodeArr
     */
    function getOriginZipCodeArr() {
        if(!is_null($this->_registry->registry('shipmentOrigin'))){
            $originArr = $this->_registry->registry('shipmentOrigin');
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
    function fedexSmpkgValidatedPostData($sPostData)
    {
        foreach ($sPostData as $key => $tag) 
        {            
            $check_characters = (!$key == 'nickname') ? preg_match('/[#$%@^&_*!()+=\-\[\]\';,.\/{}|":<>?~\\\\]/', $tag) : '';
            if ($check_characters != 1 ) 
            {
                if ($key === 'city' || $key === 'nickname' || $key === 'checkout_desc_store_pickup' || $key === 'checkout_desc_local_delivery' )
                {
                    $data[$key] = $tag;
                } else {
                    $data[$key] = preg_replace( '/\s+/', '', $tag);
                }
            } else {
                $data[$key] = 'Error';
            }
        }
   
        return $data;
    }
        
    /**
     * 
     * @param type $getWarehouse
     * @param type $validateData
     * @return string
     */
        function checkUpdateInstrorePickupDelivery($getWarehouse, $validateData){
            $update = 'no';

            if(empty($getWarehouse)){
                return $update;
            }
            
            $newData = array();
            $oldData = array();

            $getWarehouse = reset($getWarehouse);
            unset($getWarehouse['warehouse_id']);
            unset($getWarehouse['nickname']);
            unset($validateData['nickname']);

            foreach ($getWarehouse as $key => $value) {
                if(empty($value) || is_null($value)){
                    $newData[$key] = 'empty'; 
                }else{
                    $oldData[$key] = $value;
                }
            }

            $whData = array_merge($newData, $oldData);
            $diff1 = array_diff($whData, $validateData);
            $diff2 = array_diff($validateData, $whData);

            if((is_array($diff1) && !empty($diff1)) || (is_array($diff2) && !empty($diff2)) ){
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
    public function fedexSmpkgSendCurlRequest($url,$postData,$isAssocArray=FALSE){
        $field_string = http_build_query($postData);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        $result = json_decode($output,$isAssocArray);
        return $result;  
    }
    
    /**
     * 
     * @param type $key
     * @return string|empty
     */
    public function getZipcode($key)
    {
        $key =  explode("_" , $key);
        return (isset($key[0])) ? $key[0] : "";
    }
    
    /**
     * FedEx Get Shipment Rated Array
     * @param $locationGroups
     */
    function RatedShipmentDetails($locationGroups)
    {
        $rates_option = 'negotiated';
        ( $rates_option == 'negotiated' ) ? $searchword = 'PAYOR_ACCOUNT' : $searchword = 'PAYOR_LIST';

        $allLocations = array_filter($locationGroups,
            function( $var ) use ( $searchword )
        {
            return preg_match("/^$searchword/",
                $var->ShipmentRateDetail->RateType);
        });

        return $allLocations;
    }


    /**
    * Method Quotes
    * @param $quotes
    * @param $getMinimum
    * @return array
    */
    public function getQuotesResults($quotes,$getMinimum,$isMultishipmentQuantity, $scopeConfig, $registry) {
        
        $allConfigServices = $this->getAllConfigServicesArray($scopeConfig);
        $this->quoteSettingsData($scopeConfig);
        
        if($isMultishipmentQuantity){
            return $this->getOriginsMinimumQuotes($quotes,$allConfigServices, $scopeConfig);
        }
        
        $servicesArr = array();
        $multiShipment = (count($quotes)>1 ? true : false);
        $totalMultiRate = 0;
        $filteredQuotes = array();

        foreach ($quotes as $key => $quote) {
            if(isset($quote->severity) && $quote->severity == 'ERROR') { return array(); }
            $binPackaging[] = $this->setBinPackagingData($quote,$key);

            $shipment = $quote->shipment;
            $quoteServices[$shipment] = (isset($allConfigServices[$shipment]))?$allConfigServices[$shipment]:array();
            $onerateServices['fedexOneRate'] = $allConfigServices['onerateServices'];
            
            $filteredQuotes[$key] = $this->parseFedexSmallOutput($quote , $quoteServices, $onerateServices, $scopeConfig);
        }
        
        $this->_coreSession->start();
        $this->_coreSession->setOrderBinPackaging($binPackaging);
        
        if(!$multiShipment){
            $this->setOrderDetailWidgetData(array(), $scopeConfig);
            return reset($filteredQuotes);
        }else{
            $multiShipQuotes = $this->getMultishipmentQuotes($filteredQuotes);
            $this->setOrderDetailWidgetData($multiShipQuotes['orderWidgetQ'], $scopeConfig);
            return $multiShipQuotes['multiShipQ'];
        }
    }
    
    /**
     * 
     * @param type $filteredQuotes
     * @return array
     */
    function getMultishipmentQuotes($filteredQuotes) {
        $totalRate = 0;
        foreach ($filteredQuotes as $key => $multiQuotes) {
            if(isset($multiQuotes[0])){
                $totalRate += $multiQuotes[0]['rate'];
                $multiship[$key]['quotes'] = $multiQuotes[0];
            }
        }
        
        $response['multiShipQ']['fedexSmall'] = $this->getFinalQuoteArray($totalRate, 'FedExSPMS', 'Shipping '.$this->residentialDelivery);
        $response['orderWidgetQ'] = $multiship;
        
        return $response;
    }
    
    /**
     * 
     * @param type $quote
     * @param type $key
     * @return array
     */
    function setBinPackagingData($quote,$key) {
        $binPackaging = array();
        isset($quote->fedexServices->binPackagingData)?
            $binPackaging[$key]['fedexServices'] = $quote->fedexServices->binPackagingData : array();
        
        isset($quote->fedexOneRate->binPackagingData)?
            $binPackaging[$key]['fedexOneRate'] = $quote->fedexOneRate->binPackagingData : array();
        
        isset($quote->fedexAirServices->binPackagingData)?
            $binPackaging[$key]['fedexAirServices'] = $quote->fedexAirServices->binPackagingData : array();
        
        return $binPackaging;
    }
    
    /**
     * Get Shipping Array For Single Shipment
     * @param $result
     * @param $serviceType
     * @return array
     */
    function parseFedexSmallOutput($result , $idServices , $oneRateServices,$scopeConfig){  
        $allServicesArray = array();
        $transitTime = "";
        $accessorials = array();
        ($this->residentialDlvry == "1") ? $accessorials[] = "R" : "";
        
        $quote = array();

        if(isset($result->fedexServices) && !(isset($result->fedexAirServices,$result->fedexOneRate)))
        {
            $quote['fedexServices'] = $this->quoteDetail($result->fedexServices);
            $simpleQuotes = 1;
        }
        else
        {
            isset($result->fedexOneRate) ? 
                $quote['fedexOneRate'] = $this->quoteDetail($result->fedexOneRate) : "";

            isset($result->fedexAirServices) ? 
                $quote['fedexAirServices'] = $this->quoteDetail($result->fedexAirServices) : "";

            isset($result->fedexServices) ? 
                $quote['fedexServices'] = $this->quoteDetail($result->fedexServices) : "";
        }

        foreach ($quote as $serviceName => $servicesList) 
        {   
            if(isset($servicesList->serviceType,$servicesList->RatedShipmentDetails) && $servicesList->serviceType == "SMART_POST")
            {
                //this condtion is working for smart post feature
                $RatedShipmentDetails = $servicesList->RatedShipmentDetails;
                $serviceType = $servicesList->serviceType;
                $services = $idServices;
                $serviceKeyName = key($services);
                $service = $this->RatedShipmentDetails($RatedShipmentDetails);
                
                $serviceTitle = (isset($services[$serviceKeyName][$serviceType])) ? $services[$serviceKeyName][$serviceType] : "";

                $service = (!empty($service)) ? reset($service) : array();
                
                $totalCharge = $this->getQuoteAmount($service);
                
                $addedHandling = $this->calculateHandlingFee($totalCharge, $scopeConfig);
                $grandTotal = $this->calculateHazardousFee($serviceType, $addedHandling);

                $allServicesArray[$serviceType] = $this->getAllServicesArr($serviceType . "_" . $serviceName, $this->getQuoteServiceCode($serviceType . "_" . $serviceName), $grandTotal, $transitTime, $serviceTitle, $serviceName);
            }
            elseif(isset($servicesList) && (!empty($servicesList)))
            {
                $services = ($serviceName == "fedexOneRate") ? $oneRateServices : $idServices;

                $serviceKeyName = key($services);
                
                if($serviceKeyName != "international" && (!isset($simpleQuotes)))
                {
                    if($serviceName == "fedexAirServices")
                    {
                        $homeGrdServices = array();
                        (isset($services[$serviceKeyName]['GROUND_HOME_DELIVERY'])) ? $homeGrdServices[$serviceKeyName]['GROUND_HOME_DELIVERY'] = 'FedEx Home Delivery' : "";
                        (isset($services[$serviceKeyName]['FEDEX_GROUND'])) ? $homeGrdServices[$serviceKeyName]['FEDEX_GROUND'] = 'FedEx Ground' : "";
                        $services = $homeGrdServices;
                    }
                    elseif($serviceName == "fedexServices")
                    {
                        if(isset($services[$serviceKeyName]['GROUND_HOME_DELIVERY'])) unset ($services[$serviceKeyName]['GROUND_HOME_DELIVERY']);
                        if(isset($services[$serviceKeyName]['FEDEX_GROUND'])) unset($services[$serviceKeyName]['FEDEX_GROUND']);
                    }
                }   

                foreach ($servicesList as $serviceKey => $service) 
                {
                    if($serviceTitle = (isset($services[$serviceKeyName][$service->serviceType])) ? $services[$serviceKeyName][$service->serviceType] : "")
                    {
                    
                    $autoResTitle = $this->getAutoResidentialTitle($service);
                    
                    if($serviceKeyName == "international" && $serviceName != "fedexAirServices" || $serviceKeyName != "international")
                    {
                        $transitTime = ( isset($service->transitTime) ) ? $service->transitTime : '';

                        $serviceType = $service->serviceType;

                        $totalCharge = $this->getQuoteAmount($service);

                        $addedHandling = $this->calculateHandlingFee($totalCharge, $scopeConfig);
                        $grandTotal = $this->calculateHazardousFee($serviceType, $addedHandling);

                        
                        $allServicesArray[] = $this->getAllServicesArr($serviceType . "_" . $serviceName, $this->getQuoteServiceCode($serviceType . "_" . $serviceName), $grandTotal, $transitTime, $serviceTitle.' '.$autoResTitle, $serviceName);
                    }
                }
            }
        }
        }

        $priceSortedKey = array();
        $allServicesArray = array_filter($allServicesArray);
        foreach ($allServicesArray as $key => $costCarrier) {
                $priceSortedKey[$key] = $costCarrier['rate'];
        }
        array_multisort($priceSortedKey, SORT_ASC, $allServicesArray);

        return $allServicesArray;
    }
    
    /**
     * 
     * @param type $service
     * @return string
     */
    function getAutoResidentialTitle($service) {
        $append = '';

        if($this->_moduleManager->isEnabled('Eniture_AutoDetectResidential') && (is_null($this->residentialDlvry) || $this->residentialDlvry == '0')){
            if(isset($service->Surcharges->SurchargeType) && $service->Surcharges->SurchargeType == 'RESIDENTIAL_DELIVERY'){
                $append = ' with residential delivery';
            }else{
	        if(isset($service->Surcharges))
                foreach ($service->Surcharges as $key => $surcharge) {
                    if(isset($surcharge->SurchargeType) && $surcharge->SurchargeType == 'RESIDENTIAL_DELIVERY'){
                        $append = ' with residential delivery';
                    }
                }
            }
            $this->residentialDelivery = $append;
        }
        return $append;
    }
    
    /**
     * 
     * @param type $result
     * @return array
     */
    public function quoteDetail($result){
        return isset($result->RateReplyDetails) ? $result->RateReplyDetails : ((isset($result->q)) ? $result->q : array());
    }
    
    /**
     * 
     * @param type $serviceType
     * @param type $code
     * @param type $grandTotal
     * @param type $transitTime
     * @param type $serviceTitle
     * @param type $serviceName
     * @return array
     */
    function getAllServicesArr($serviceType, $code, $grandTotal, $transitTime, $serviceTitle, $serviceName) {
        $allowed = array();
        if( $grandTotal > 0 ) {
            $allowed = array(
                'serviceType'   => $serviceType,
                'code'          => $code,
                'rate'          => $grandTotal,
                'transitTime'   => $transitTime,
                'title'         => $serviceTitle,
                'serviceName'   => $serviceName,
            );
        }
        return $allowed;
    }
    
    /**
     * 
     * @param type $serviceType
     * @return string
     */
    function getQuoteServiceCode($serviceType) {
        $explode = explode('_', $serviceType);
        $lastKey = end($explode);         // move the internal pointer to the end of the array
        $lastKeyCode = $this->getLastKeyCode($lastKey);
        
        array_pop($explode);
        $first  = (isset($explode[0]))?substr($explode[0], 0, 1):'';
        $second = (isset($explode[1]))?substr($explode[1], 0, 1):'';
        $third  = (isset($explode[2]))?substr($explode[2], 0, 1):'';
        $fourth = (isset($explode[3]))?substr($explode[3], 0, 1):'';
        
        $code = $first.$second.$third.$fourth.$lastKeyCode; 
        return $code;
    }
    
    /**
     * 
     * @param type $lastKey
     * @return string
     */
    function getLastKeyCode($lastKey) {
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
     * 
     * @param type $serviceType
     * @param type $addedHandling
     * @return type
     */
    function calculateHazardousFee($serviceType, $addedHandling) {
        $hazourdous = $this->checkHazardousShipment();
        if(count($hazourdous) > 0){
            $ground = ($serviceType == 'FEDEX_GROUND' || $serviceType == 'GROUND_HOME_DELIVERY')?true:false;
            $addedHazardous = 0 ;
            if($this->onlyGndService == '1' ){
                if($ground){
                    $addedHazardous = $this->gndHzrdousFee + $addedHandling; 
                }
                else if(!$ground && strlen($this->airHzrdousFee ) > 0 ){
                        $addedHazardous = 0 ; 
                }
            }
            else{
                if($ground && strlen($this->gndHzrdousFee ) > 0 ){
                    $addedHazardous = $this->gndHzrdousFee + $addedHandling; 
                }else if(!$ground && strlen($this->airHzrdousFee ) > 0 ){
                        $addedHazardous = $this->airHzrdousFee + $addedHandling; 
                }else{
                    $addedHazardous = $addedHandling;
                }
            }
        }else{
            $addedHazardous = $addedHandling;
        }
        return $addedHazardous;
    }
    
    /**
     * 
     * @return type
     */
    public function checkHazardousShipment() {
        $hazourdous = array();
        $checkHazordous = $this->_registry->registry('hazardousShipment');
        if(isset($checkHazordous)){
            foreach($checkHazordous as $key => $data){
                foreach ($data as $k => $d){
                    if($d['isHazordous'] == '1'){
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
    function getQuoteAmount($availableServ) {
        $fedexRateSource = $this->fedexRates;
        if($fedexRateSource == 'negotiate'){
            if($availableServ->NegotiatedRates->Amount){
                $quoteCurrency = $availableServ->NegotiatedRates->Currency;
                $quoteAmmount = $availableServ->NegotiatedRates->Amount;
            }else{
                $quoteCurrency = $availableServ->totalNetCharge->Currency;
                $quoteAmmount = $availableServ->totalNetCharge->Amount;
            }
        }else{
            $quoteCurrency = $availableServ->totalNetCharge->Currency;
            $quoteAmmount = $availableServ->totalNetCharge->Amount;
        }
        
        return $quoteAmmount;
    }
    
    /**
     * 
     * @param type $serviceType
     * @param type $serviceTitle
     * @param type $minInQ
     * @return type
     */
    function multishipSetOrderData($serviceType, $serviceTitle, $minInQ) {
        $servicesArr['quotes'] = $this->getFinalQuoteArray($minInQ, $serviceType, $serviceTitle);
        return $servicesArr;
    }
    
    /**
     * 
     * @param type $servicesArr
     * @param type $QCount
     */
    function setOrderDetailWidgetData($servicesArr, $scopeConfig) {
        $orderDetail['residentialDelivery'] = ($this->residentialDelivery != '' || $this->residentialDlvry == '1')?'Residential Delivery':'';
        $setPkgForOrderDetailReg = null !== $this->_registry->registry('setPackageDataForOrderDetail')?$this->_registry->registry('setPackageDataForOrderDetail'):array();
        $orderDetail['shipmentData'] = array_replace_recursive($setPkgForOrderDetailReg, $servicesArr);
        
        // set order detail widget data
        $this->_coreSession->start();
        $this->_coreSession->setOrderDetailSession($orderDetail);
    }
    
    /**
     * This function returns minimum array index from array
     * @param $servicesArr
     * @return array
     */
    public function findArrayMininum($servicesArr){
        $counter = 1;
        $minIndex = array();
        foreach ($servicesArr as $key => $value) {
            if($counter == 1){
               $minimum =  $value['rate'];
               $minIndex = $value;
               $counter = 0;
            }else{
               if($value['rate'] < $minimum){
                   $minimum =  $value['rate'];
                   $minIndex = $value;
               }
            }
        }
        return $minIndex;
    }
    
    /**
     * 
     * @param array $quotes
     * @param array $allConfigServices
     * @param array $hazardousOriginArr
     * @return array
     */
    public function getOriginsMinimumQuotes($quotes,$allConfigServices, $scopeConfig) {

        $minIndexArr = array();
        foreach ($quotes as $key => $quote) {
            $minInQ = $counter = 0;
            if(isset($quote->q)){
                foreach ($quote->q as $servkey => $availableServ) {
                    if( isset($availableServ->serviceType) && in_array( $availableServ->serviceType, $allConfigServices ) ){
                        $curruntAmount = $availableServ->totalNetCharge->Amount;
                        if($counter == 0){
                            $minInQ = $curruntAmount;
                        }else{
                            $minInQ = ($curruntAmount < $minInQ ? $curruntAmount : $minInQ);
                        }   

                        $counter ++;
                    }
                }
                if($minInQ > 0){
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

    function fedexSmpkgLtlGetAvgRate($allServices, $numberOption, $activCarriers) {
        $price = 0;
        $totalPrice = 0;
        if(count($allServices) > 0){
            foreach ($allServices as $services) {
                $totalPrice += $services['rate'];
            }

            if ($numberOption < count($activCarriers) && $numberOption < count($allServices)) {
                $slicedArray = array_slice($allServices, 0, $numberOption);
                foreach ($slicedArray as $services) {
                    $price += $services['rate'];
                }
                $totalPrice = $price / $numberOption;
            } else if(count($activCarriers) < $numberOption && count($activCarriers) < count($allServices)) {
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
    public function getAllConfigServicesArray($scopeConfig){
        $domesticServices     = $scopeConfig->getValue('fedexQuoteSetting/third/FedExDomesticServices', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $domesticServices     = explode(',', $domesticServices);
        
        $internationalServices     = $scopeConfig->getValue('fedexQuoteSetting/third/FedExInternationalServices', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $internationalServices     = explode(',', $internationalServices);
        
        $onerateServices     = $scopeConfig->getValue('fedexQuoteSetting/third/FedExOneRateServices', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $onerateServices     = explode(',', $onerateServices);
        
        foreach ($domesticServices as $key => $service) {
            $domestic[$service] = $this->getServiceTitle($service, 'domestic');
        }
        foreach ($internationalServices as $key => $service) {
            $international[$service] = $this->getServiceTitle($service, 'international');
        }
        foreach ($onerateServices as $key => $service) {
            $onerate[$service] = $this->getServiceTitle($service, 'onerate');
        }
        
        $allConfigServices = array(
            'domestic' => $domestic,
            'international' => $international,
            'onerateServices' => $onerate
        );
        
        return $allConfigServices;
    }
    
    /**
    * Final quotes array
    * @param $grandTotal
    * @param $code
    * @param $title
    * @return array
    */    
    public function getFinalQuoteArray($grandTotal, $code, $title) {
        $allowed = array();
        if( $grandTotal > 0 ) {
            $allowed = array(
                'code'  => $code,// or carrier name
                'title' => $title,
                'rate'  => $grandTotal
            );
        }
        
        return $allowed;
    }
    
    /**
    * Calculate Handling Fee
    * @param $cost
    * @return int
    */
    public function calculateHandlingFee($totalPrice, $scopeConfig) {
        $hndlngFeeMarkup = $scopeConfig->getValue('fedexQuoteSetting/third/hndlngFee', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);;
        $symbolicHndlngFee = $scopeConfig->getValue('fedexQuoteSetting/third/symbolicHndlngFee', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);;

        if( strlen( $hndlngFeeMarkup ) > 0 ){
            if( $symbolicHndlngFee == '%' ){
                $prcntVal = $hndlngFeeMarkup / 100 * $totalPrice;
                $grandTotal = $prcntVal + $totalPrice;
            }else{
                $grandTotal = $hndlngFeeMarkup + $totalPrice;
            }
        }else{
            $grandTotal = $totalPrice;
        }
        return $grandTotal;
    }
    
    /**
     * 
     * @param type $fieldId
     * @param type $scopeConfig
     * @return type
     */
    function adminConfigData($fieldId, $scopeConfig) {
        return $scopeConfig->getValue("fedexQuoteSetting/third/$fieldId", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    /**
     * 
     * @return type
     */
    function getActiveCarriersForENCount() {
        return $this->_shippingConfig->getActiveCarriers();
    }
    
    /**
     * This Function returns service title
     * @param $serviceId
     * @return array
     */
    public function getServiceTitle($serviceId, $getServiceOf){
        if($getServiceOf == 'onerate'){
            $haystack = $this->fedexOnerateCarriersWithTitle();
        }elseif($getServiceOf == 'international'){
            $haystack = $this->fedexInternationalCarriersWithTitle();
        }else{
            $haystack = $this->fedexCarriersWithTitle();
        }
        
        if(isset($haystack[$serviceId]))
        {
            return $haystack[$serviceId];
        }
    }
    
    /**
     * fedex carrier codes with title
     * @return array
     */
    function fedexCarriersWithTitle() {
        return [
                'GROUND_HOME_DELIVERY'  => 'FedEx Home Delivery',
                'FEDEX_GROUND'          => 'FedEx Ground',
                'FEDEX_EXPRESS_SAVER'   => 'FedEx Express Saver',
                'FEDEX_2_DAY'           => 'FedEx 2Day',
                'FEDEX_2_DAY_AM'        => 'FedEx 2Day AM',
                'STANDARD_OVERNIGHT'    => 'FedEx Standard Overnight',
                'PRIORITY_OVERNIGHT'    => 'FedEx Priority Overnight',
                'FIRST_OVERNIGHT'       => 'FedEx First Overnight',
            ];
    }
    
    function fedexInternationalCarriersWithTitle(){
        return array(
            //international services
            'FEDEX_GROUND'                          => 'FedEx International Ground',
            'INTERNATIONAL_ECONOMY'                 => 'FedEx International Economy',
            'INTERNATIONAL_ECONOMY_DISTRIBUTION'    => 'FedEx International Economy Distribution',
            'INTERNATIONAL_ECONOMY_FREIGHT'         => 'FedEx International Economy Freight',
            'INTERNATIONAL_FIRST'                   => 'FedEx International First',
            'INTERNATIONAL_PRIORITY'                => 'FedEx International Priority',
            'INTERNATIONAL_PRIORITY_DISTRIBUTION'   => 'FedEx International Priority Distribution',
            'INTERNATIONAL_PRIORITY_FREIGHT'        => 'FedEx International Priority Freight',
            'INTERNATIONAL_DISTRIBUTION_FREIGHT'    => 'FedEx International Distribution Freight',
        );
    }
    function fedexOnerateCarriersWithTitle() {
        return array(
            //onerate services
            'FEDEX_EXPRESS_SAVER'   => 'FedEx One Rate Express Saver',
            'FEDEX_2_DAY'           => 'FedEx One Rate 2Day',
            'FEDEX_2_DAY_AM'        => 'FedEx One Rate 2Day AM',
            'STANDARD_OVERNIGHT'    => 'FedEx One Rate Standard Overnight',
            'PRIORITY_OVERNIGHT'    => 'FedEx One Rate Priority Overnight',
            'FIRST_OVERNIGHT'       => 'FedEx One Rate First Overnight',
        );
    }
}
