<?php
namespace Eniture\FedExSmallPackages\Model\Carrier;
/**
 * This class manages all quotes values
 */
class FedExSmpkgManageAllQuotes{
    
    /**
     * stores array of quotes
     * @var array 
     */
    protected $quotes;
    protected $_dataHelper;
    protected $_scopeConfig;
    protected $_registry;
    protected $_moduleManager;
    protected $_objectManager;
    
    /**
     * 
     * @param type $quotes
     * @param type $helper
     * @param type $scopeConfig
     * @param type $registry
     * @param type $moduleManager
     * @param type $objectManager
     */
    public function _init(
            $quotes, $helper, $scopeConfig, $registry, $moduleManager, $objectManager
        ) {
        $this->quotes           = $quotes;
        $this->_dataHelper      = $helper;
        $this->_scopeConfig     = $scopeConfig;
        $this->_registry        = $registry;
        $this->_moduleManager   = $moduleManager;
        $this->_objectManager     = $objectManager;
    }
    
    /**
     * This function accepts all quotes data and sends to its respective module functions to
     * process and return final result array.
     * @param $quotes
     * @return Array
     */
    public function getQuotesResultArr($request){
        $moduleTypesArr = $this->_registry->registry('enatureModuleTypes');
        $quotesArr      = (array)$this->quotes;
        $quotesArr      = $this->removeErrorFromQuotes($quotesArr);

        $this->quotes = $quotesArr;

        $quotesCount = count($quotesArr);
        
        if($quotesCount == 1){
            $servicesArr = $this->FedExGetAllQuotes();
            
            if($this->_moduleManager->isEnabled('ZEniture_InstorePickupLocalDelivery')){
                $servicesArr = $this->_dataHelper->supperessOtherRates($request, $servicesArr, $this->_registry);
            }
            
            return $servicesArr;
        }else if($quotesCount > 1){
            $smallModulesArr = array_filter($moduleTypesArr, function($value){
                return ($value == 'small');                      
            });
            
            $ltlModulesArr = array_filter($moduleTypesArr, function($value){
                return ($value == 'ltl');                      
            });
            
            $this->smallPackagesQuotes    = array_intersect_key($quotesArr, $smallModulesArr);
            $this->ltlPackagesQuotes      = array_intersect_key($quotesArr, $ltlModulesArr);
            
            if(count($this->smallPackagesQuotes) == 0 || count($this->ltlPackagesQuotes) == 0){
                return $this->FedExGetAllQuotes();
            }else{
                $multishipmentValue = $this->checkMultiPackaging();
                if($multishipmentValue == 'all'){
                    return $this->FedExGetAllQuotes();
                }else if($multishipmentValue == 'semi'){
                    $resultQuotes =  $this->FedExGetAllQuotes(false,true);
                    return $this->getQuotesForMultiShipment($resultQuotes,$smallModulesArr,$ltlModulesArr);
                }
                else{
                    $resultQuotesArr =  $this->FedExGetAllQuotes(true,false);
                    $smallQuotesArr = array_intersect_key($resultQuotesArr, $smallModulesArr);
                    $ltlQuotesArr = array_intersect_key($resultQuotesArr, $ltlModulesArr);
                    $minsmallRate = $this->findMininumSmall($smallQuotesArr);
                    return $this->updateLtlQuotes($ltlQuotesArr,$minsmallRate);
                }
            }
        }else{
            return FALSE;
        }
        
    }
    
    /**
     * This function returns final quotes to show
     * @param array $resultQuotes
     * @param array $smallModulesArr
     * @param array $ltlModulesArr
     * @return array
     */
    public function getQuotesForMultiShipment($resultQuotes,$smallModulesArr,$ltlModulesArr) {
        $smallQuotesArr = array_intersect_key($resultQuotes, $smallModulesArr);
        $ltlQuotesArr = array_intersect_key($resultQuotes, $ltlModulesArr);
        $allLtlQuotesArr    = $this->getAllQuotes($ltlQuotesArr);
        $allSmallQuotesArr  = $this->getAllQuotes($smallQuotesArr);
        $commonQuotesArr    = array_intersect_key($allLtlQuotesArr, $allSmallQuotesArr);
        $minimumCommonArr   = $this->getMinimumCommonQuotes($commonQuotesArr,$resultQuotes);
        $minimumSmallRate   = $this->getMinimumSmallQuotesRate($minimumCommonArr,$smallQuotesArr);
        $ltlQuotesArray     = $this->getLtlQuoteForMultishipping($minimumCommonArr,$ltlQuotesArr,$minimumSmallRate);
    
        return $ltlQuotesArray;
    }
    
    /**
     * This function returns Ltl quotes for multi shipping
     * @param array $minimumCommonArr
     * @param array $ltlQuotesArr
     * @param float $minimumSmallRate
     * @return array
     */
    public function getLtlQuoteForMultishipping($minimumCommonArr,$ltlQuotesArr,$minimumSmallRate){
        $ltlQuotesFinalArr = array();

        // if ltl quotes return nothing
        if(count($ltlQuotesArr) > 0){
            foreach ($ltlQuotesArr as $mainkey => $originArr) {
                $ltlRate = $minimumSmallRate;
                
                foreach ($originArr as $key => $value) {
                    if(!array_key_exists($key,$minimumCommonArr)){
                        $ltlRate = $ltlRate + $value;
                    }
                }
                $ltlQuotesFinalArr[$mainkey][] = [
                    'code'  => 'Freight',
                    'title' => 'Freight',  
                    'rate'  => $ltlRate
                    ];
            }
        }else{
            if($minimumSmallRate > 0){
                foreach ($ltlQuotesArr as $key => $value) {
                    $ltlQuotesFinalArr[$key][] = [
                        'code'  => 'Freight',
                        'title' => 'Freight',  
                        'rate'  => $minimumSmallRate
                        ];
                }
            }
        }
        return $ltlQuotesFinalArr;
    }

    /**
     * This function returns minimum in common quotes
     * @param array $commonQuotesArr
     * @param array $resultQuotes
     * @return array
     */
    public function getMinimumCommonQuotes($commonQuotesArr,$resultQuotes) {
        if(count($commonQuotesArr) > 0 ){
            $minimumCommonQuotesArr = $commonQuotesArr;
            
            foreach ($resultQuotes as $mainkey => $originArr) {
                foreach ($originArr as $key => $value) {
                    if(count($minimumCommonQuotesArr) == 0){
                        $minimumCommonQuotesArr[$key] = $value;
                    }
                    if(array_key_exists($key,$minimumCommonQuotesArr)){
                        if($value == '0'){
                            if(isset($this->quotes[$mainkey][$key]->severity) && $this->quotes[$mainkey][$key]->severity == 'ERROR'){
                                continue;
                            }
                        }
                        if($value < $minimumCommonQuotesArr[$key]){
                            $minimumCommonQuotesArr[$key] = $value;
                        }  
                    }
                }
            }
            
        }else{
            $minimumCommonQuotesArr = array();
        }
        
        return $minimumCommonQuotesArr;
    }
    
    /**
     * This function returns the minimum from common and small quotes
     * @param array $minimumCommonArr
     * @param array $smallQuotes
     * @return float
     */
    public function getMinimumSmallQuotesRate($minimumCommonArr,$smallQuotes) {
        $minimumSmallQuotes = array();
        if(isset($smallQuotes) && !empty($smallQuotes)){
            foreach ($smallQuotes as $mainkey => $originArr) {
                foreach ($originArr as $key => $value) {
                    if(!array_key_exists($key,$minimumCommonArr)){
                        if(array_key_exists($key,$minimumSmallQuotes)){
                            if($value < $minimumSmallQuotes[$key]){
                                $minimumSmallQuotes[$key] = $value;
                            }
                        }else{
                            $minimumSmallQuotes[$key] = $value;
                        }
                    }
                }
            }
        }
        $minSmallQuotesArray = array_merge($minimumSmallQuotes,$minimumCommonArr);
        $sumMinSmall = array_sum($minSmallQuotesArray);
        return $sumMinSmall;
    }
    
    /**
     * This function put specific array to common array
     * @param array $quotesArr
     * @return array
     */
    public function getAllQuotes($quotesArr) {
        $newQuotesArr = array();
        foreach ($quotesArr as $value) {
            foreach ($value as $key => $originArr) {
                $newQuotesArr[$key] = $originArr;
            }
        }
        return $newQuotesArr;
    }
    
    /**
     * This function returns is quotes have multipackaging or not 
     * @return string
     */
    public function checkMultiPackaging(){
        $ltlOriginArr = $smallOriginArr = $commonValuesArr = array();
        $multiPackage = 'no';
        
        foreach ($this->ltlPackagesQuotes as $mainKey => $mainValue) {
            foreach ($mainValue as $key => $value) {
                array_push($ltlOriginArr, $key);
            }
        }

        foreach ($this->smallPackagesQuotes as $mainKey => $mainValue) {
            foreach ($mainValue as $key => $value) {
                array_push($smallOriginArr, $key);
            }
        }
        
        $commonValuesArr = array_intersect($ltlOriginArr, $smallOriginArr);
        if(count($commonValuesArr)>0){
            $multiPackage = 'semi';
            if(count($commonValuesArr) == count($ltlOriginArr) && count($commonValuesArr) == count($smallOriginArr)){
                $multiPackage = 'all';
            }
        }
        
        return $multiPackage;
    }

    /**
     * This funtion removes errors from quotes array
     * @param $QuotesArr
     * @return array
     */
    public function removeErrorFromQuotes($QuotesArr){
        $updatedArr = array();
        
        if(isset($QuotesArr->error) && $QuotesArr->error){
            $updatedArr = $QuotesArr;
        }
        foreach ($QuotesArr as $mainkey => $mainvalue) {
            
            if(isset($mainvalue->severity) && $mainvalue->severity == 'ERROR'){
                $updatedArr[$mainkey] = $mainvalue;
            }
            
            if(isset($mainvalue->error) && $mainvalue->error){
                $updatedArr[$mainkey] = $mainvalue;
            }
            
            if((is_object($mainvalue) || is_array($mainvalue)) && !empty($mainvalue)){
                foreach ($mainvalue as $key => $value) {
                    if(isset($value->error) && $value->error == 1 && isset($value->dismissedProduct)){
                        continue;
                    }else if(isset($value->severity) && $value->severity == 'ERROR' && isset($value->dismissedProduct)){
                        continue;
                    }else{
                        $updatedArr[$mainkey][$key] = $value;
                    }
                }  
            }
        }
        return $updatedArr;
    }

    /**
     * This funtion add small min small quotes value in Ltl quotes
     * @param array $ltlQuotesArr
     * @param int $minsmallRate
     * @return string
     */
    public function updateLtlQuotes($ltlQuotesArr,$minsmallRate){
        $updatedltlQuotesArr = array();
        if(count($ltlQuotesArr) > 0){
            foreach ($ltlQuotesArr as $key => $value) {
                if($value[0]){
                    $updatedltlQuotesArr[$key][] = [
                        'code'  => $value[0]['code'],
                        'title' => 'Freight',  
                        'rate'  => ($value[0]['rate'] + $minsmallRate)
                        ];
                }
            }
        }else{
            foreach ($this->ltlPackagesQuotes as $key => $value) {
                $updatedltlQuotesArr[$key][] = [
                    'code'  => 'Freight',
                    'title' => 'Freight',  
                    'rate'  => $minsmallRate
                    ];
            }
        }
        
        return $updatedltlQuotesArr;
    }
    
    /**
     * This function finds the minimum rates value from small quotes
     * @param array $smallArr
     * @return int
     */
    public function findMininumSmall($smallArr){
        $counter = 1;
        $minimum = '0';
        foreach ($smallArr as $key => $fedexSmall) {
            foreach ($fedexSmall as $key => $value) {
                if($counter == 1){
                   $minimum =  $value['rate'];
                   $counter = 0;
                }else{
                   if($value['rate'] < $minimum){
                       $minimum =  $value['rate'];
                   }
                }
            }
        }
        return $minimum;
    }
    
    /**
     * This function gets quotes result from all active modules
     * @param boolean $getMinimum
     * @return array
     */
    public function FedExGetAllQuotes($getMinimum=false,$isMultishipment=false){
        $helpersArr = $this->_registry->registry('enitureHelpersCodes');
        $resultArr = array();
        foreach ($this->quotes as $key => $quote) {
            $helperId = $helpersArr[$key];
            $dataHelper = $this->_objectManager->get("$helperId\Helper\Data");
            $fedexSmpkgResultData = $dataHelper->getQuotesResults($quote,$getMinimum,$isMultishipment, $this->_scopeConfig, $this->_registry);
            if($fedexSmpkgResultData != False && !is_null($fedexSmpkgResultData)){
                $resultArr[$key] = $fedexSmpkgResultData;
            }   
        }
        return $resultArr;
    }
    
}

