<?php
namespace Eniture\FedExSmallPackages\Model\Carrier;
/**
 * class for admin configuration that runs first
 */
class FedExSmpkgAdminConfiguration {
   
    /**
     * @var \Magento\Framework\Registry
     */

    protected $_registry;
    protected $_scopeConfig;

    /**
     * 
     * @param type $scopeConfig
     * @param type $registry
     */
    public function _init($scopeConfig,$registry) {
        $this->_registry = $registry;
        $this->_scopeConfig = $scopeConfig;
        $this->setCarriersAndHelpersCodesGlobaly();
        $this->myUniqueLineItemAttribute();
        $this->updateActiveCarriersArray();
    }
    
    /**
     * This functuon set unique Line Item Attributes of carriers
     */
    function myUniqueLineItemAttribute(){
        $lineItemAttArr =  array();
        if(is_null($this->_registry->registry('UniqueLineItemAttributes'))){
            $this->_registry->register('UniqueLineItemAttributes', $lineItemAttArr);
        }
    }
    
    /**
     * This function is for set carriers codes and helpers code globaly
     */
    public function setCarriersAndHelpersCodesGlobaly(){
        $this->setCodesGlobaly('enitureCarrierCodes','ENFedExSmpkg');
        $this->setCodesGlobaly('enitureCarrierTitle', 'FedEx Small Packages Quotes');
        $this->setCodesGlobaly('enitureHelpersCodes','\Eniture\FedExSmallPackages');
        $this->setCodesGlobaly('enitureActiveModules', $this->checkModuleIsEnabled());
        $this->setCodesGlobaly('enatureModuleTypes', 'small');
    }
    
    /**
     * return if this module is enable or not
     * @return boolean
     */
    public function checkModuleIsEnabled(){
        return $this->_scopeConfig->getValue("carriers/fedexConnectionSettings/active", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    /**
     * This function sets Codes Globaly e.g carrier code or helper code
     * @param $globArrayName
     * @param $arrValue
     */
    public function setCodesGlobaly($globArrayName,$arrValue){
        
        if(is_null($this->_registry->registry($globArrayName))){
            $codesArray = array();
            $codesArray['fedexSmall'] = $arrValue;
            $this->_registry->register($globArrayName, $codesArray);
        }else{
            $codesArray = $this->_registry->registry($globArrayName);
            $codesArray['fedexSmall'] = $arrValue;
            $this->_registry->unregister($globArrayName);
            $this->_registry->register($globArrayName, $codesArray);
        } 
    }
    
    /**
     * function that returns global active carrier array
     */
    public function updateActiveCarriersArray(){
        $isThisActive = $this->_scopeConfig->getValue('carriers/fedexConnectionSettings/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $isAllowOthers = $this->_scopeConfig->getValue('fedexQuoteSetting/third/allowOther', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        if($isThisActive){
           if(is_null($this->_registry->registry('EnActiveModules'))){
                $codesArray = array();
                $codesArray['ENFedExSmpkg'] = $isAllowOthers;
                $this->_registry->register('EnActiveModules', $codesArray);
            }else{
                $codesArray = $this->_registry->registry('EnActiveModules');
                $codesArray['ENFedExSmpkg'] = $isAllowOthers;
                $this->_registry->unregister('EnActiveModules');
                $this->_registry->register('EnActiveModules', $codesArray);
            } 
        }
    }
}

