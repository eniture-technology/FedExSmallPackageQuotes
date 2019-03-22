<?php
/**
 * FedEx Small Package
 * @package     FedEx Small Package
 * @author      Eniture Technology 
 */
namespace Eniture\FedExSmallPackages\Model\Carrier;
/**
 * Class for set carriers globally
 */
class FedExSmallSetCarriersGlobaly{
    protected $_dataHelper;
    /**
     * constructor of class
     */
    public function _init($dataHelper) {
        $this->_dataHelper = $dataHelper;
    }
    
    /**
     * function for magange carriers globally
     * @param $FedExArr
     * @return boolean
     */
    public function manageCarriersGlobaly($FedExArr, $registry){
        $this->_registry = $registry;
        if(is_null($this->_registry->registry('enitureCarriers'))){
            $enitureCarriersArray = array();
            $enitureCarriersArray['fedexSmall'] = $FedExArr;
            $this->_registry->register('enitureCarriers', $enitureCarriersArray);
        }else{
            $carriersArr = $this->_registry->registry('enitureCarriers');
            $carriersArr['fedexSmall'] = $FedExArr;
            $this->_registry->unregister('enitureCarriers');
            $this->_registry->register('enitureCarriers', $carriersArr);
        }
        
        $activeEnitureModulesCount = $this->getActiveEnitureModulesCount();

        if(count($this->_registry->registry('enitureCarriers')) < $activeEnitureModulesCount){
            return False;
        }else{
            return TRUE;
        }
    }
    /**
     * function that return count of active eniture modules
     * @return int
     */
    public function getActiveEnitureModulesCount(){
        $activeModules = array_keys($this->_dataHelper->getActiveCarriersForENCount());
        $activeEnitureModulesArr = array_filter($activeModules, function($moduleName){
            if(substr($moduleName, 0, 2) == 'EN'){
                return true;
            }
                return false;                                
        });
            
        return count($activeEnitureModulesArr);
        
    }
    
    /**
     * This function accepts all quotes data and sends to its respective module functions to
     * process and return final result array.
     * @param $quotes
     * @return array
     */
    public function manageQuotes($quotes){
        $helpersArr = $this->_registry->registry('enitureHelpersCodes');
        $resultArr = array();
        foreach ($quotes as $key => $quote) {
            $helperId = $helpersArr[$key];
            $FedExResultData = $this->_registry->helper($helperId)->getQuotesResults($quote);
            if($FedExResultData != False){
                $resultArr[$key] = $FedExResultData;
            }   
        }
        
        return $resultArr;
    }   
}