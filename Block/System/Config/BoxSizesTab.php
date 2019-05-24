<?php

namespace Eniture\FedExSmallPackages\Block\System\Config;

use Magento\Mtf\Client\BrowserInterface;

class BoxSizesTab extends \Magento\Config\Block\System\Config\Form\Field
{
    const BOXSIZESTAB_TEMPLATE = 'system/config/boxsizestab.phtml';

    private $moduleManager;
    public $enable = 'no';
    public $boxSizeData;
    private $objectManager;
    private $dataHelper;
    public $licenseKey;

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
        \Eniture\FedExSmallPackages\Helper\Data $dataHelper,
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
     * checkBinPackagingModule
     */
    public function checkBinPackagingModule()
    {
        if ($this->moduleManager->isEnabled('Eniture_BoxSizes')) {
            $scopeConfig            = $this->context->getScopeConfig();
            $configPath             = "carriers/ENFedExSmpkg/licnsKey";
            $this->licenseKey = $scopeConfig->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $this->enable           = 'yes';
            $dataHelper             = $this->objectManager->get("Eniture\BoxSizes\Helper\Data");
            $this->boxSizeData      = $dataHelper->boxSizesDataHandling($this->licenseKey);
            $this->smallTrialMsg    = $dataHelper->checkSmallModuleTrial();
            $this->boxUseSuspended  = $dataHelper->boxUseSuspended();
            $this->getBoxSizes      = $dataHelper->getBoxSizes();
            $this->loadOneRateBoxes = $dataHelper->oneRateData();
            $this->fedexOneRateImg  = $dataHelper->fedexOneRateImg();
        }
    }

    /**
     * @return url
     */
    public function saveBoxUrl()
    {
        return $this->getbaseUrl().'/BoxSizes/Box/SaveBoxsize/';
    }

    /**
     * @return url
     */
    public function deleteBoxUrl()
    {
        return $this->getbaseUrl().'/BoxSizes/Box/DeleteBoxsize/';
    }

    /**
     * @return url
     */
    public function editBoxUrl()
    {
        return $this->getbaseUrl().'/BoxSizes/Box/EditBoxsize/';
    }

    /**
     * @return url
     */
    public function boxAvailableUrl()
    {
        return $this->getbaseUrl().'/BoxSizes/Box/BoxAvailability/';
    }

    /**
     * @return url
     */
    public function suspendBoxUrl()
    {
        return $this->getbaseUrl().'/BoxSizes/Box/SuspendedBoxSizes/';
    }

    /**
     * @return url
     */
    public function autoRenewBoxPlanUrl()
    {
        return $this->getbaseUrl().'/BoxSizes/Box/AutoRenewPlan/';
    }

    /**
     * @return url
     */
    public function loadOneRateBoxesUrl()
    {
        return $this->getbaseUrl().'/BoxSizes/FedExOneRate/OneRateLoadBoxes/';
    }

    /**
     * @return url
     */
    public function saveFedExOneRateUrl()
    {
        return $this->getbaseUrl().'/BoxSizes/FedExOneRate/OneRateSaveBoxes/';
    }
}
