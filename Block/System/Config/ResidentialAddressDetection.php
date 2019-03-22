<?php
namespace Eniture\FedExSmallPackages\Block\System\Config;

use Magento\Mtf\Client\BrowserInterface;

class ResidentialAddressDetection extends \Magento\Config\Block\System\Config\Form\Field
{
    const RAD_TEMPLATE = 'system/config/resaddressdetection.phtml';
    
    protected $_moduleManager;
    public $enable = 'no';
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
        $this->_objectManager   = $objectmanager;
        $this->_moduleManager   = $moduleManager;
        $this->_licenseKey      = $context->getScopeConfig()->getValue("carriers/fedexConnectionSettings/licnsKey", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
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
            $this->setTemplate(static::RAD_TEMPLATE);
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
        if($this->_moduleManager->isEnabled('Eniture_AutoDetectResidential')){
            $this->enable = 'yes';
            
            $dataHelper             = $this->_objectManager->get("Eniture\AutoDetectResidential\Helper\Data");
            $this->resAddDetectData = $dataHelper->resAddDetectDataHandling($this->_licenseKey);
            $this->smallTrialMsg    = $dataHelper->checkSmallModuleTrial();
            $this->radUseSuspended  = $dataHelper->radUseSuspended();
        }
    }
    
    /**
     * 
     * @return url
     */
    public function suspendRADUrl()
    {
        return $this->getbaseUrl().'/AutoDetectResidential/RAD/SuspendedRAD/';
    }
    
    /**
     * 
     * @return url
     */
    public function autoRenewRADPlanUrl()
    {
        return $this->getbaseUrl().'/AutoDetectResidential/RAD/AutoRenewPlan/';
    }
}