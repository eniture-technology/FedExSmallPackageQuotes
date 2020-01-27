<?php

namespace Eniture\FedExSmallPackageQuotes\Block\System\Config;

use \Magento\Backend\Block\Template\Context;
use \Magento\Framework\Module\Manager;

/**
 * Class BoxSizes
 * @package Eniture\FedExSmallPackageQuotes\Block\System\Config
 */
class BoxSizes extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     *
     */
    const BOXSIZES_TEMPLATE = 'system/config/boxsizes.phtml';

    /**
     * @var Manager
     */
    private $moduleManager;
    /**
     * @var string
     */
    public $enable = 'no';
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(Context $context, Manager $moduleManager, array $data = [])
    {
        $this->moduleManager   = $moduleManager;
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
            $this->setTemplate(static::BOXSIZES_TEMPLATE);
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
        if ($this->moduleManager->isEnabled('Eniture_StandardBoxSizes')) {
            $this->enable = 'yes';
        }
    }
}
