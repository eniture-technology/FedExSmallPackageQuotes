<?php

namespace Eniture\FedExSmallPackageQuotes\Block\System\Config;

use Eniture\FedExSmallPackageQuotes\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class WarehouseTable
 * @package Eniture\FedExSmallPackageQuotes\Block\System\Config
 */
class WarehouseTable extends Field
{

    /**
     *
     */
    const WAREHOUSE_TEMPLATE = 'system/config/warehouse.phtml';

    /**
     * @var Data
     */
    public $dataHelper;
    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $dataHelper,
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
            $this->setTemplate(static::WAREHOUSE_TEMPLATE);
        }
        return $this;
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
     * @return int
     */
    public function addWhRestriction()
    {
        return $this->dataHelper->whPlanRestriction();
    }

    /**
     * @return int
     */
    public function checkInstorePkpDlvry()
    {
        return $this->dataHelper->checkAdvancePlan();
    }

    /**
     * @param AbstractElement $element
     * @return element
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
    public function getAjaxAddressUrl()
    {
        return $this->getbaseUrl().'/fedexsmallpackagequotes/Warehouse/FedExSmallPkgOriginAddress/';
    }
    
    /**
     * @return url
     */
    public function saveFedExSmpkgWarehouseAjaxCall()
    {
        return $this->getbaseUrl().'/fedexsmallpackagequotes/Warehouse/SaveWarehouse/';
    }
    
    /**
     * @return url
     */
    public function editWarehouseAjaxCall()
    {
        return $this->getbaseUrl().'/fedexsmallpackagequotes/Warehouse/EditWarehouse/';
    }
    
    /**
     * @return url
     */
    public function deleteWarehouseAjaxCall()
    {
        return $this->getbaseUrl().'/fedexsmallpackagequotes/Warehouse/DeleteWarehouse/';
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
