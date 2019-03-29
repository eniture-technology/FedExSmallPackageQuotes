<?php
namespace Eniture\FedExSmallPackages\Block\System\Config;

use Magento\Mtf\Client\BrowserInterface;

class ResidentialAddressDetection extends \Magento\Config\Block\System\Config\Form\Field
{
    const RAD_TEMPLATE = 'system/config/resaddressdetection.phtml';
    
    public $moduleManager;
    public $enable = 'no';
    public $objectManager;
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        array $data = []
    ) {
        $this->moduleManager   = $moduleManager;
        $this->context         = $context;
        $this->objectManager   = $objectmanager;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        $this->checkBinPackagingModule();
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::RAD_TEMPLATE);
        }
        return $this;
    }
  
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return html
     */
    public function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }
   
    /**
     * checkBinPackagingModule
     */
    public function checkBinPackagingModule()
    {
        if ($this->moduleManager->isEnabled('Eniture_AutoDetectResidential')) {
            $scopeConfig           = $this->context->getScopeConfig();
            $configPath            = "carriers/fedexConnectionSettings/licnsKey";
            $this->licenseKey = $scopeConfig->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
            $this->enable = 'yes';
            $dataHelper             = $this->objectManager->get("Eniture\AutoDetectResidential\Helper\Data");
            $this->resAddDetectData = $dataHelper->resAddDetectDataHandling($this->licenseKey);
            $this->smallTrialMsg    = $dataHelper->checkSmallModuleTrial();
            $this->radUseSuspended  = $dataHelper->radUseSuspended();
        }
    }
    
    /**
     * @return url
     */
    public function suspendRADUrl()
    {
        return $this->getbaseUrl().'/AutoDetectResidential/RAD/SuspendedRAD/';
    }
    
    /**
     * @return url
     */
    public function autoRenewRADPlanUrl()
    {
        return $this->getbaseUrl().'/AutoDetectResidential/RAD/AutoRenewPlan/';
    }
}
