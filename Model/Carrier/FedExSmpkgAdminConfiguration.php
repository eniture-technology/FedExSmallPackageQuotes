<?php
namespace Eniture\FedExSmallPackageQuotes\Model\Carrier;

/**
 * class for admin configuration that runs first
 */
class FedExSmpkgAdminConfiguration
{


    /**
     * @var $registry
     */
    public $registry;
    /**
     * @var $scopeConfig
     */
    public $scopeConfig;

    /**
     * @param type $scopeConfig
     * @param type $registry
     */
    public function _init($scopeConfig, $registry)
    {
        $this->registry = $registry;
        $this->scopeConfig = $scopeConfig;
        $this->setCarriersAndHelpersCodesGlobaly();
        $this->myUniqueLineItemAttribute();
    }

    /**
     * This functuon set unique Line Item Attributes of carriers
     */
    public function myUniqueLineItemAttribute()
    {
        $lineItemAttArr =  [];
        if ($this->registry->registry('UniqueLineItemAttributes') === null) {
            $this->registry->register('UniqueLineItemAttributes', $lineItemAttArr);
        }
    }

    /**
     * This function is for set carriers codes and helpers code globaly
     */
    public function setCarriersAndHelpersCodesGlobaly()
    {
        $this->setCodesGlobaly('enitureCarrierCodes', 'ENFedExSmpkg');
        $this->setCodesGlobaly('enitureCarrierTitle', 'Fedex Small Package Quotes');
        $this->setCodesGlobaly('enitureHelpersCodes', '\Eniture\FedExSmallPackageQuotes');
        $this->setCodesGlobaly('enitureActiveModules', $this->checkModuleIsEnabled());
        $this->setCodesGlobaly('enitureModuleTypes', 'small');
    }

    /**
     * return if this module is enable or not
     * @return boolean
     */
    public function checkModuleIsEnabled()
    {
        $grpSecPath = "carriers/ENFedExSmpkg/active";
        return $this->scopeConfig->getValue($grpSecPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * This function sets Codes Globaly e.g carrier code or helper code
     * @param $globArrayName
     * @param $arrValue
     */
    public function setCodesGlobaly($globArrayName, $arrValue)
    {
        if ($this->registry->registry($globArrayName) === null) {
            $codesArray = [];
            $codesArray['fedexSmall'] = $arrValue;
            $this->registry->register($globArrayName, $codesArray);
        } else {
            $codesArray = $this->registry->registry($globArrayName);
            $codesArray['fedexSmall'] = $arrValue;
            $this->registry->unregister($globArrayName);
            $this->registry->register($globArrayName, $codesArray);
        }
    }
}
