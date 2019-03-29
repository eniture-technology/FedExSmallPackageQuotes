<?php
namespace Eniture\FedExSmallPackages\Block\System\Config;

use Magento\Backend\Block\Template\Context;

class StandrdBoxSize extends \Magento\Config\Block\System\Config\Form\Field
{
    
    const STNDRDBOX_TEMPLATE = 'system/config/standrdboxsize.phtml';
    
    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $data);
    }
    
    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->moduleManager->isOutputEnabled('Vendor_Module')) {
            if (!$this->getTemplate()) {
                $this->setTemplate(static::STNDRDBOX_TEMPLATE);
            }
        }
        return $this;
    }
    
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return element
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
    
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return html
     */
    public function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }
}
