<?php

namespace Eniture\FedExSmallPackages\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class WarehouseTable extends \Magento\Config\Block\System\Config\Form\Field
{
    
    const WAREHOUSE_TEMPLATE = 'system/config/warehouse.phtml';
    
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
    
    public function addWhRestriction()
    {
        return $this->dataHelper->whPlanRestriction();
    }

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
        return $this->getbaseUrl().'/fedexsmallpackages/Warehouse/FedExSmallPkgOriginAddress/';
    }
    
    /**
     * @return url
     */
    public function saveFedExSmpkgWarehouseAjaxCall()
    {
        return $this->getbaseUrl().'/fedexsmallpackages/Warehouse/SaveWarehouse/';
    }
    
    /**
     * @return url
     */
    public function editWarehouseAjaxCall()
    {
        return $this->getbaseUrl().'/fedexsmallpackages/Warehouse/EditWarehouse/';
    }
    
    /**
     * @return url
     */
    public function deleteWarehouseAjaxCall()
    {
        return $this->getbaseUrl().'/fedexsmallpackages/Warehouse/DeleteWarehouse/';
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
