<?php

namespace Eniture\FedExSmallPackageQuotes\Block\System\Config;

/**
 * Class BoxSizesTab
 * @package Eniture\FedExSmallPackageQuotes\Block\System\Config
 */
class BoxSizesTab extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     *
     */
    const BOXSIZESTAB_TEMPLATE = 'system/config/boxsizestab.phtml';

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;
    /**
     * @var string
     */
    public $enable = 'no';
    /**
     * @var string
     */
    public $enableForFedExSm = 'NO';
    /**
     * @var
     */
    public $boxSizeData;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var \Eniture\FedExSmallPackageQuotes\Helper\Data
     */
    private $dataHelper;
    /**
     * @var
     */
    public $licenseKey;
    /**
     * @var bool
     */
    public $isFedExModule = true;
    /**
     * @var \Magento\Backend\Block\Template\Context
     */
    public $context;
    /**
     * @var
     */
    public $smallTrialMsg;
    /**
     * @var
     */
    public $boxUseSuspended;
    /**
     * @var
     */
    public $getBoxSizes;
    /**
     * @var
     */
    public $loadOneRateBoxes;
    /**
     * @var
     */
    public $fedexOneRateImg;


    /**
     * BoxSizesTab constructor.
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
        $this->moduleManager    = $moduleManager;
        $this->objectManager    = $objectmanager;
        $this->context          = $context;
        $this->dataHelper      = $dataHelper;
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
            $this->setTemplate(static::BOXSIZESTAB_TEMPLATE);
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
        if ($this->moduleManager->isEnabled('Eniture_StandardBoxSizes')) {
            $scopeConfig            = $this->context->getScopeConfig();
            $configPath             = "fedexconnsettings/first/licnsKey";
            $this->licenseKey = $scopeConfig->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $this->enable           = 'yes';
            $this->enableForFedExSm = 'YES';
            $dataHelper             = $this->objectManager->get("Eniture\StandardBoxSizes\Helper\Data");
            $this->boxSizeData      = $dataHelper->boxSizesDataHandling($this->licenseKey);
            $this->smallTrialMsg    = $dataHelper->checkSmallModuleTrial();
            $this->boxUseSuspended  = $dataHelper->boxUseSuspended();
            $this->getBoxSizes      = $dataHelper->getBoxSizes();
            $this->loadOneRateBoxes = $dataHelper->oneRateData();
        }
    }

    /**
     * @return url
     */
    public function saveBoxUrl()
    {
        return $this->getbaseUrl().'/StandardBoxSizes/Box/SaveBoxsize/';
    }

    /**
     * @return url
     */
    public function deleteBoxUrl()
    {
        return $this->getbaseUrl().'/StandardBoxSizes/Box/DeleteBoxsize/';
    }

    /**
     * @return url
     */
    public function editBoxUrl()
    {
        return $this->getbaseUrl().'/StandardBoxSizes/Box/EditBoxsize/';
    }

    /**
     * @return url
     */
    public function boxAvailableUrl()
    {
        return $this->getbaseUrl().'/StandardBoxSizes/Box/BoxAvailability/';
    }

    /**
     * @return url
     */
    public function suspendBoxUrl()
    {
        return $this->getbaseUrl().'/StandardBoxSizes/Box/SuspendedBoxSizes/';
    }

    /**
     * @return url
     */
    public function autoRenewBoxPlanUrl()
    {
        return $this->getbaseUrl().'/StandardBoxSizes/Box/AutoRenewPlan/';
    }

    /**
     * @return url
     */
    public function loadOneRateBoxesUrl()
    {
        return $this->getbaseUrl().'/StandardBoxSizes/FedExOneRate/OneRateLoadBoxes/';
    }

    /**
     * @return url
     */
    public function saveFedExOneRateUrl()
    {
        return $this->getbaseUrl().'/StandardBoxSizes/FedExOneRate/OneRateSaveBoxes/';
    }
}
