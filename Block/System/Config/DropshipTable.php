<?php

namespace Eniture\FedExSmallPackages\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class DropshipTable extends Field
{
    const DROPSHIP_TEMPLATE = 'system/config/dropship.phtml';
    public $dataHelper;
    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Eniture\FedExSmallPackages\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }
    
    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::DROPSHIP_TEMPLATE);
        }
        return $this;
    }
    
    /**
     * @param AbstractElement $element
     * @return parent
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
    
    /**
     * @return url
     */
    public function getAjaxDsAddressUrl()
    {
        return $this->getbaseUrl().'/FedExSmallPackages/Warehouse/FedExSmallPkgOriginAddress/';
    }
    
    /**
     * @return url
     */
    public function saveDropshipAjaxCheckUrl()
    {
        return $this->getbaseUrl().'/FedExSmallPackages/Dropship/SaveDropship/';
    }
    
    /**
     * @return url
     */
    public function editDropshipAjaxCheckUrl()
    {
        return $this->getbaseUrl().'/FedExSmallPackages/Dropship/EditDropship/';
    }
    
    /**
     * @return url
     */
    public function deleteDropshipAjaxCheckUrl()
    {
        return $this->getbaseUrl().'/FedExSmallPackages/Dropship/DeleteDropship/';
    }

    /**
     * @return url
     */
    public function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }
}
