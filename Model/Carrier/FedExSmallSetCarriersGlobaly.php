<?php
/**
 * FedEx Small Package
 * @package     FedEx Small Package
 * @author      Eniture Technology
 */
namespace Eniture\FedExSmallPackageQuotes\Model\Carrier;

/**
 * Class for set carriers globally
 */
class FedExSmallSetCarriersGlobaly
{
    /**
     * @var
     */
    public $dataHelper;
    /**
     * @var
     */
    public $registry;

    /**
     * constructor of class
     */
    public function _init($dataHelper)
    {
        $this->dataHelper = $dataHelper;
    }
    
    /**
     * function for magange carriers globally
     * @param $FedExArr
     * @return boolean
     */
    public function manageCarriersGlobaly($FedExArr, $registry)
    {
        $this->registry = $registry;
        if ($this->registry->registry('enitureCarriers') === null) {
            $enitureCarriersArray = [];
            $enitureCarriersArray['fedexSmall'] = $FedExArr;
            $this->registry->register('enitureCarriers', $enitureCarriersArray);
        } else {
            $carriersArr = $this->registry->registry('enitureCarriers');
            $carriersArr['fedexSmall'] = $FedExArr;
            $this->registry->unregister('enitureCarriers');
            $this->registry->register('enitureCarriers', $carriersArr);
        }
        
        $activeEnitureModulesCount = $this->getActiveEnitureModulesCount();

        if (count($this->registry->registry('enitureCarriers')) < $activeEnitureModulesCount) {
            return false;
        } else {
            return true;
        }
    }
    /**
     * function that return count of active eniture modules
     * @return int
     */
    public function getActiveEnitureModulesCount()
    {
        $activeModules = array_keys($this->dataHelper->getActiveCarriersForENCount());
        $activeEnitureModulesArr = array_filter($activeModules, function ($moduleName) {
            if (substr($moduleName, 0, 2) == 'EN') {
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
    public function manageQuotes($quotes)
    {
        $helpersArr = $this->registry->registry('enitureHelpersCodes');
        $resultArr = [];
        foreach ($quotes as $key => $quote) {
            $helperId = $helpersArr[$key];
            $FedExResultData = $this->registry->helper($helperId)->getQuotesResults($quote);
            if ($FedExResultData != false) {
                $resultArr[$key] = $FedExResultData;
            }
        }
        
        return $resultArr;
    }
}
