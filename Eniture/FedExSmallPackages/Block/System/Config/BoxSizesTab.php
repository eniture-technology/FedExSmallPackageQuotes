<?php
namespace Eniture\FedExSmallPackages\Block\System\Config;

use Magento\Mtf\Client\BrowserInterface;

class BoxSizesTab extends \Magento\Config\Block\System\Config\Form\Field
{
    const BOXSIZESTAB_TEMPLATE = 'system/config/boxsizestab.phtml';
    
    protected $_moduleManager;
    public $enable = 'no';
    public $boxSizeData;
    protected $_objectManager;
    
    /**
     * 
     * @param \Magento\Backend\Block\Template\Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     * @param array $data
     */
    public function __construct(
            \Magento\Backend\Block\Template\Context $context,
            \Magento\Framework\Module\Manager $moduleManager,
            \Magento\Framework\ObjectManagerInterface $objectmanager,
            array $data = []
    )
    {
        $this->_moduleManager       = $moduleManager;
        $this->_objectManager       = $objectmanager;
        $this->_scopeConfig         = $context->getScopeConfig();
        $this->_licenseKey          = $this->_scopeConfig->getValue("carriers/fedexConnectionSettings/licnsKey", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        $this->checkBinPackagingModule();
        parent::__construct($context, $data);
    }

    /**
     * 
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
                $this->setTemplate(static::BOXSIZESTAB_TEMPLATE);
        }
        return $this;
    }
  
    /**
     * 
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return html
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }
   
    /**
     * checkBinPackagingModule
     */
    protected function checkBinPackagingModule() 
    {
        if($this->_moduleManager->isEnabled('Eniture_BoxSizes')){
            $this->enable           = 'yes';
            $dataHelper             = $this->_objectManager->get("Eniture\BoxSizes\Helper\Data");
            $this->boxSizeData      = $dataHelper->boxSizesDataHandling($this->_licenseKey);
            $this->smallTrialMsg    = $dataHelper->checkSmallModuleTrial();
            $this->boxUseSuspended  = $dataHelper->boxUseSuspended();
            $this->getBoxSizes      = $dataHelper->getBoxSizes();
            $this->loadOneRateBoxes = $dataHelper->oneRateData();
            $this->fedexOneRateImg  = $dataHelper->fedexOneRateImg();
        }
    }
    
    /**
     * 
     * @return url
     */
    public function saveBoxUrl()
    {
        return $this->getbaseUrl().'/BoxSizes/Box/SaveBoxsize/';
    }
    
    /**
     * 
     * @return url
     */
    public function deleteBoxUrl()
    {
        return $this->getbaseUrl().'/BoxSizes/Box/DeleteBoxsize/';
    }
    
    /**
     * 
     * @return url
     */
    public function editBoxUrl()
    {
        return $this->getbaseUrl().'/BoxSizes/Box/EditBoxsize/';
    }
    
    /**
     * 
     * @return url
     */
    public function boxAvailableUrl()
    {
        return $this->getbaseUrl().'/BoxSizes/Box/BoxAvailability/';
    }
    
    /**
     * 
     * @return url
     */
    public function suspendBoxUrl()
    {
        return $this->getbaseUrl().'/BoxSizes/Box/SuspendedBoxSizes/';
    }
    
    /**
     * 
     * @return url
     */
    public function autoRenewBoxPlanUrl()
    {
        return $this->getbaseUrl().'/BoxSizes/Box/AutoRenewPlan/';
    }
    
    /**
     * 
     * @return url
     */
    public function loadOneRateBoxesUrl()
    {
        return $this->getbaseUrl().'/BoxSizes/FedExOneRate/OneRateLoadBoxes/';
    }
    
    /**
     * 
     * @return url
     */
    public function saveFedExOneRateUrl()
    {
        return $this->getbaseUrl().'/BoxSizes/FedExOneRate/OneRateSaveBoxes/';
    }
}