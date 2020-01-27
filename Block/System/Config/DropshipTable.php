<?php

namespace Eniture\FedExSmallPackageQuotes\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class DropshipTable
 * @package Eniture\FedExSmallPackageQuotes\Block\System\Config
 */
class DropshipTable extends Field
{
    /**
     *
     */
    const DROPSHIP_TEMPLATE = 'system/config/dropship.phtml';
    /**
     * @var \Eniture\FedExSmallPackageQuotes\Helper\Data
     */
    public $dataHelper;
    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Eniture\FedExSmallPackageQuotes\Helper\Data $dataHelper,
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
     * @return int
     */
    public function checkInstorePkpDlvry()
    {
        return $this->dataHelper->checkAdvancePlan();
    }
    
    /**
     * @return url
     */
    public function getAjaxDsAddressUrl()
    {
        return $this->getbaseUrl().'/fedexsmallpackagequotes/Warehouse/FedExSmallPkgOriginAddress/';
    }
    
    /**
     * @return url
     */
    public function saveDropshipAjaxCheckUrl()
    {
        return $this->getbaseUrl().'/fedexsmallpackagequotes/Dropship/SaveDropship/';
    }
    
    /**
     * @return url
     */
    public function editDropshipAjaxCheckUrl()
    {
        return $this->getbaseUrl().'/fedexsmallpackagequotes/Dropship/EditDropship/';
    }
    
    /**
     * @return url
     */
    public function deleteDropshipAjaxCheckUrl()
    {
        return $this->getbaseUrl().'/fedexsmallpackagequotes/Dropship/DeleteDropship/';
    }

    /**
     * @return url
     */
    public function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }
    
    /**
     * this function return the current plan active
     * @return mixed
     */
    public function getCurrentPlan()
    {
        return $this->dataHelper->checkAdvancePlan();
    }
}
