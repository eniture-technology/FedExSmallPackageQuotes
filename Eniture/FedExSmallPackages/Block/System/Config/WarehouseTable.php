<?php
namespace Eniture\FedExSmallPackages\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class WarehouseTable extends \Magento\Config\Block\System\Config\Form\Field
{
    
    const WAREHOUSE_TEMPLATE = 'system/config/warehouse.phtml';
    
    public $_dataHelper;
    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Eniture\FedExSmallPackages\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->_dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }
    
    /**
     * 
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::WAREHOUSE_TEMPLATE);
        }
        return $this;
    }
    
    /**
     * 
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
     * 
     * @return url
     */
    public function getAjaxAddressUrl()
    {
        return $this->getbaseUrl().'/FedExSmallPackages/Warehouse/FedExSmallPkgOriginAddress/'; 
    }
    
    /**
     * 
     * @return url
     */
    public function saveFedExSmpkgWarehouseAjaxCall ()
    {
        return $this->getbaseUrl().'/FedExSmallPackages/Warehouse/SaveWarehouse/'; 
    }
    
    /**
     * 
     * @return url
     */
    public function editWarehouseAjaxCall ()
    {
        return $this->getbaseUrl().'/FedExSmallPackages/Warehouse/EditWarehouse/'; 
    }
    
    /**
     * 
     * @return url
     */
    public function deleteWarehouseAjaxCall ()
    {
        return $this->getbaseUrl().'/FedExSmallPackages/Warehouse/DeleteWarehouse/'; 
    }

    /**
     * 
     * @return url
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }
    
}