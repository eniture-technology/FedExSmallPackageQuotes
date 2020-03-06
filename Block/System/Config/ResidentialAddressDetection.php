<?php
namespace Eniture\FedExSmallPackageQuotes\Block\System\Config;

/**
 * Class ResidentialAddressDetection
 * @package Eniture\FedExSmallPackageQuotes\Block\System\Config
 */
class ResidentialAddressDetection extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     *
     */
    const RAD_TEMPLATE = 'system/config/resaddressdetection.phtml';

    /**
     * @var \Magento\Framework\Module\Manager
     */
    public $moduleManager;
    /**
     * @var string
     */
    public $enable = 'no';
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;
    /**
     * @var \Eniture\FedExSmallPackageQuotes\Helper\Data
     */
    public $dataHelper;
    /**
     * @var \Magento\Backend\Block\Template\Context
     */
    public $context;
    /**
     * @var
     */
    public $resAddDetectData;
    /**
     * @var
     */
    public $trialMsg;
    /**
     * @var
     */
    public $radUseSuspended;
    /**
     * @var
     */
    public $licenseKey;
    public $addressType;


    /**
     * ResidentialAddressDetection constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     * @param \Eniture\FedExSmallPackageQuotes\Helper\Data $dataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Eniture\FedExSmallPackageQuotes\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->moduleManager   = $moduleManager;
        $this->context         = $context;
        $this->objectManager   = $objectmanager;
        $this->dataHelper      = $dataHelper;
        $this->planRstrctnQuoteSettng();
        
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
     * This function returns the HTML, used in the Block
     * @return mixed
     */

    public function getHtml()
    {
        return $this->_toHtml();
    }
   
    /**
     * checkBinPackagingModule
     */
    public function checkBinPackagingModule()
    {
        if ($this->moduleManager->isEnabled('Eniture_ResidentialAddressDetection')) {
            $scopeConfig           = $this->context->getScopeConfig();
            $configPath            = "fedexconnsettings/first/licnsKey";
            $this->licenseKey = $scopeConfig->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
            $this->enable = 'yes';
            $dataHelper             = $this->objectManager->get("Eniture\ResidentialAddressDetection\Helper\Data");
            $this->resAddDetectData = $dataHelper->resAddDetectDataHandling($this->licenseKey);
            $this->radUseSuspended  = $dataHelper->radUseSuspended();
            $this->addressType      = $dataHelper->getAddressType();
            if ($dataHelper->checkModuleTrial()) {
                $this->trialMsg = 'The Small Package Quotes module must have active paid license to continue to use this feature.';
            }
        }
    }
    
    /**
     * @return url
     */
    public function suspendRADUrl()
    {
        return $this->getbaseUrl().'/ResidentialAddressDetection/RAD/SuspendedRAD/';
    }
    
    /**
     * @return url
     */
    public function autoRenewRADPlanUrl()
    {
        return $this->getbaseUrl().'/ResidentialAddressDetection/RAD/AutoRenewPlan/';
    }

    /**
     * @return string
     */
    public function addressTypeUrl()
    {
        return $this->getbaseUrl().'/ResidentialAddressDetection/RAD/DefaultAddressType/';
    }
    
    /**
     * Show FedEx Small Plan Notice
     * @return string
     */
    public function fedexSmallPlanNotice()
    {
        $planMsg = $this->dataHelper->fedexSmallSetPlanNotice();
        return $planMsg;
    }
    
    /**
     * @return array
     */
    public function planRstrctnQuoteSettng()
    {
        return json_encode($this->dataHelper->quoteSettingFieldsToRestrict());
    }
}
