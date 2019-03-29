<?php

namespace Eniture\FedExSmallPackages\Block\System\Config;

use \Magento\Backend\Block\Template\Context;
use \Magento\Framework\Module\Manager;

class BoxSizes extends \Magento\Config\Block\System\Config\Form\Field
{
    const BOXSIZES_TEMPLATE = 'system/config/boxsizes.phtml';
    
    private $moduleManager;
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
        if ($this->moduleManager->isEnabled('Eniture_BoxSizes')) {
            $this->enable = 'yes';
        }
    }
}
