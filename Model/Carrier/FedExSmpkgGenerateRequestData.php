<?php
namespace Eniture\FedExSmallPackages\Model\Carrier;
/**
 * class that generated request data
 */
class FedExSmpkgGenerateRequestData{
    protected $_registry;
    protected $_moduleManager;
    protected $FedexOneRatePricing = '0';
    protected $oneRatePricing = '0';
    protected $airServicesPricing = '0';
    protected $homeGroundPricing = '0';  
    /**
     * This var stores service type e.g domestic, international, both
     * @var string 
     */
    private $serviceType;
    
    /**
     * 
     * @param type $scopeConfig
     * @param type $registry
     * @param type $dataHelper
     * @param type $moduleManager
     * @param type $objectManager
     */
    public function _init(
            $scopeConfig, $registry, $dataHelper, $moduleManager, $objectManager
        ) {
        $this->_registry        = $registry;
        $this->_scopeConfig     = $scopeConfig;
        $this->_moduleManager   = $moduleManager;
        $this->_dataHelper      = $dataHelper;
        $this->_objectManager   = $objectManager;
    }
    
    /**
     * function that generates FedEx array
     * @return array
     */
    public function generateFedExSmpkgArray($request, $origin, $objectManager){
        $getDistance = 0;
        $fedexSmpkgArr = [
            'licenseKey'    => $this->getConfigData('licnsKey'),
            'serverName'    => $_SERVER['SERVER_NAME'],
            'carrierMode'   => 'pro', // use test / pro
            'quotestType'   => 'small',
            'version'       => '1.0.0',
            'api'           => $this->getApiInfoArr($request->getDestCountryId(), $origin, $objectManager),
            'getDistance'   => $getDistance,
        ];
        
        return  $fedexSmpkgArr;
    }
    
    /**
     * fuction that generates request array
     * @param $request
     * @param $FedExArr
     * @param $itemsArr
     * @return array
     */
    public function generateRequestArray($request,$fedexSmpkgArr,$itemsArr, $objectmanager, $cart){
        $carriers = $this->_registry->registry('enitureCarriers');

        $carriers['fedexSmall'] = $fedexSmpkgArr;
        $receiverAddress = $this->getReceiverData($request);
        
        $requestArr = [
            'apiVersion'        => '2.0',
            'platform'          => 'magento2',
            'binPackagingMultiCarrier' => $this->_moduleManager->isEnabled('Eniture_BoxSizes')?'1':'0',
            
            'autoResidentials' => $this->autoResidentialDelivery(),
            'liftGateWithAutoResidentials' => '0',
            'FedexOneRatePricing' => $this->FedexOneRatePricing,
            
            'requestKey'        => $cart->getQuote()->getId(),
            'carriers'          => $carriers,
            'receiverAddress'   => $receiverAddress,
            'commdityDetails'   => $itemsArr
        ];
        
        if($this->_moduleManager->isEnabled('Eniture_BoxSizes')){
            $binsData = $this->getSavedBins($objectmanager);
            $requestArr = array_merge($requestArr, isset($binsData)?$binsData:array());
        }
        
        return  $requestArr;
    }
    
    /**
     * 
     * @return int
     */
    public function autoResidentialDelivery(){
        $resDelevery = $this->getConfigData('residentialDlvry')?'on':'';
        
        $autoResidential =  $this->_scopeConfig->getValue("resaddressdetection/suspend/value", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if($autoResidential != NULL) {
            if(($autoResidential == 'yes') ){
                $autoDetectResidential =  0;
            }else if(($autoResidential == 'no') && ($resDelevery == 'on')){
                $autoDetectResidential =  0;
            }else{
                $autoDetectResidential =  1;
            }
        }else{
            $autoDetectResidential =  0 ;
        }
        if(is_null($this->_registry->registry('autoDetectResidential'))){
            $this->_registry->register('autoDetectResidential', $autoDetectResidential);
        }
        
        return $autoDetectResidential ;
    }
    
    /**
     * 
     * @param type $objectmanager
     */
    public function setValuesInRequest($objectmanager){
        $domesticServices = explode(',', $this->getConfigData('FedExDomesticServices'));
        $oneRateChecked                 = $this->getOneRateServices();
        $internationalServicesLength    = $this->getServiceOptionsLength('FedExInternationalServices');
        $oneRateServicesLength          = $this->getServiceOptionsLength('FedExOneRateServices');
        $boxSizeChecked                 = $this->getSavedBins($objectmanager);
         
         if($oneRateServicesLength || (count($oneRateChecked))){
             $this->FedexOneRatePricing = '1' ;
             if($oneRateServicesLength){
                 //set one rate pricing = 1
                 $this->oneRatePricing = '1' ;
             }

             if((count($boxSizeChecked)) && ($domesticServices[0] == 'GROUND_HOME_DELIVERY' || $domesticServices[0] == 'FEDEX_GROUND')){
              // set home ground pricing = 1 
                 $this->homeGroundPricing = '1'  ;
             }
             
            foreach($domesticServices as $key => $data){
                if(($data == 'GROUND_HOME_DELIVERY') || ($data == 'FEDEX_GROUND')){
                     unset($domesticServices[$key]);
                }
            }
           
            if(($internationalServicesLength || (!empty($domesticServices[0]))) ){
                $this->airServicesPricing = '1' ; 
            }
        }
        
        if(is_null($this->_registry->registry('FedexOneRatePricing'))){
            $this->_registry->register('FedexOneRatePricing', $this->FedexOneRatePricing);
        }
    }

    /**
     * 
     * @param type $services
     * @return string
     */
    public function getServiceOptionsLength($services){
        return strlen($this->getConfigData($services));
    } 
    
    /**
     * 
     * @return array
     */
    public function getOneRateServices(){
        $checked = array();
        if($this->_moduleManager->isEnabled('Eniture_BoxSizes')){
            $boxsizeHelper = $this->_objectManager->get('Eniture\BoxSizes\Helper\Data');
            $checked = $boxsizeHelper->getEnabledOneRateServices();
//            $checked = $this->_connection->fetchAll("SELECT * FROM `$this->boxSizesTable` WHERE type = 'onerate' AND boxavailable = 1");
        }
        
        return $checked;   
    }
    
    
    /**
     * 
     * @param type $objectmanager
     * @return type
     */
    function getSavedBins($objectmanager) {
        $savedBins = array();
        if($this->_moduleManager->isEnabled('Eniture_BoxSizes')){
            $boxSizeHelper = $objectmanager->get("Eniture\BoxSizes\Helper\Data");
            $savedBins = $boxSizeHelper->fillBoxingData();
        }
        return $savedBins;
    }
    
    /**
     * This function returns carriers array if have not empty origin address
     * @return array
     */
    public function getCarriersArray(){
        $carriersArr = $this->_registry->registry('enitureCarriers');
        $newCarriersArr = array();
        foreach ($carriersArr as $carrkey => $carrArr) {
            $notHaveEmptyOrigin = true;
            foreach ($carrArr['originAddress'] as $key => $value) {
                if(empty($value['senderZip'])){
                    $notHaveEmptyOrigin = false;
                }
            }
            if($notHaveEmptyOrigin){
                $newCarriersArr[$carrkey] = $carrArr;
            }
        }
        
        return $newCarriersArr;
    }

    /**
     * function that returns API array
     * @return array
     */
    public function getApiInfoArr($country, $origin, $objectManager){
        $this->setValuesInRequest($objectManager);
        
        $residential = $this->getConfigData('residentialDlvry')?'on':'';
        $this->serviceType = $this->getServiceType($country,$origin);
        
        if(is_null($this->_registry->registry('fedexServiceType'))){
            $this->_registry->register('fedexServiceType', $this->serviceType);
        }
        
        
        $apiArray = [
            'MeterNumber'       => $this->getConfigData('MeterNumber'),
            'password'          => $this->getConfigData('ProdutionPassword'),
            'key'               => $this->getConfigData('AuthenticationKey'),
            'AccountNumber'     => $this->getConfigData('AccountNumber'),
            'prefferedCurrency' => 'USD',
                'shipmentDate'  =>  date("d/m/y"),
                'pkgType' => '00',
                'residentialDelivery'   => $residential,
                'saturdayDelivery'      => 'on',
                'oneRatePricing'        => $this->oneRatePricing ,
                'airServicesPricing'    => $this->airServicesPricing ,
                'homeGroundPricing'     => $this->homeGroundPricing ,
        ];
        
        return  $apiArray;
       
    }
    
    /**
     * This function returns Services Array
     * @return array
     */
    public function getServices(){
        
        $domesticArr = $international = array();
        if($this->serviceType == 'domestic' || $this->serviceType == 'both'){
            $domesticArr = array(
                // Domestic Services //
                'fedex_small_pkg_3_Day_Select'            => $this->isServiceActive('12'),
                'fedex_small_pkg_Ground'                  => $this->isServiceActive('03'),
                'fedex_small_pkg_2nd_Day_Air'             => $this->isServiceActive('02'),
                'fedex_small_pkg_2nd_Day_Air_AM'          => $this->isServiceActive('59'),
                'fedex_small_pkg_Next_Day_Air'            => $this->isServiceActive('01'),
                'fedex_small_pkg_Next_Day_Air_Saver'      => $this->isServiceActive('13'),
                'fedex_small_pkg_Next_Day_Air_Early_AM'   => $this->isServiceActive('14')
            );
        }
        
        if($this->serviceType == 'international' || $this->serviceType == 'both'){
            $international = array(
                //International Services //
                'fedex_small_pkg_Standard'                => $this->isServiceActive('11'),
                'fedex_small_pkg_Worldwide_Express'       => $this->isServiceActive('07'),
                'fedex_small_pkg_Worldwide_Express_Plus'  => $this->isServiceActive('54'),
                'fedex_small_pkg_Worldwide_Expedited'     => $this->isServiceActive('08'),
                'fedex_small_pkg_Saver'                   => $this->isServiceActive('65'),
            );
        }
        
        $servicesArr = array_merge($domesticArr, $international);
        $servicesArr['fedex_small_pkg_aditional_handling'] = 'N';
        return $servicesArr;
    }
    
    /**
     * funtion that returns weather this service is active or not
     * @param string $serviceId
     * @return string
     */
    public function isServiceActive($serviceId){
        
        $domesticServices = explode(',', $this->getConfigData('FedExDomesticServices'));
        $internationalServices = explode(',', $this->getConfigData('FedExInternationalServices'));
        $servicesArray = array_merge($domesticServices,$internationalServices);
        
        if(in_array($serviceId, $servicesArray)){
            return 'yes';
        }else{
            return 'N';
        }
        
    }
   
    /**
     * function return service data
     * @param $fieldId
     * @return string
     */
    public function getConfigData($fieldId){
        
        $secThreeIds = ['residentialDlvry', 'FedExDomesticServices', 'FedExInternationalServices', 'FedExOneRateServices'];
        if (in_array($fieldId, $secThreeIds)){
            $sectionId = 'fedexQuoteSetting';
            $groupId = 'third';
        }else{
            $sectionId = 'carriers';
            $groupId = 'fedexConnectionSettings';
        }
        
        return $this->_scopeConfig->getValue("$sectionId/$groupId/$fieldId", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * This function returns Reveiver Data Array
     * @param $request
     * @return array
     */
    public function getReceiverData($request){
        $receiverDataArr = [
            'addressLine'           => $request->getDestStreet(),
            'receiverCity'          => $request->getDestCity(),
            'receiverState'         => $request->getDestRegionCode(),
            'receiverZip'           => preg_replace('/\s+/', '', $request->getDestPostcode()),
            'receiverCountryCode'   => $request->getDestCountryId()
        ];
        
        return  $receiverDataArr;
    }
    
    /**
     * 
     * @param type $destinationCountry
     * @param type $originArr
     * @return string
     */
    public function getServiceType($destinationCountry,$originArr){
        $serviceType = '';
        foreach ($originArr as $key => $value) {
            if($value['senderCountryCode'] == $destinationCountry && $serviceType == ''){
                $serviceType = 'domestic';
            }elseif($value['senderCountryCode'] != $destinationCountry && $serviceType == ''){
                $serviceType = 'international';
            }elseif ($serviceType == 'domestic' || $serviceType == 'international') {
                if($serviceType == 'domestic' && $value['senderCountryCode'] != $destinationCountry){
                    $serviceType = 'both';
                }elseif ($serviceType == 'international' && $value['senderCountryCode'] == $destinationCountry) {
                    $serviceType = 'both';
                }
            }
        }
        return $serviceType;
    }
}

