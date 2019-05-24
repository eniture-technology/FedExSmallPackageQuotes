<?php

namespace Eniture\FedExSmallPackages\Block\Adminhtml\Product;

use \Magento\Backend\Block\Template\Context;
use \Magento\Framework\Module\Manager;

class ProductPlanRestriction extends \Magento\Config\Block\System\Config\Form\Field
{
    const PRODUCT_TEMPLATE = 'product/productplanrestriction.phtml';
    
    public $enable = 'no';
    private $shipconfig;
    public $dataHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Shipping\Model\Config $shipconfig,
        \Eniture\FedExSmallPackages\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->shipconfig = $shipconfig;
        $this->context = $context;
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
            $this->setTemplate(static::PRODUCT_TEMPLATE);
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
     * @return array
     */
    public function getPlanInfo()
    {
        $activeCarriers = array_keys($this->shipconfig->getActiveCarriers());
        
        foreach ($activeCarriers as $carrierCode) {
            $enCarrier = substr($carrierCode, 0, 2);
            if ($enCarrier == 'EN') {
                $enitureCarriers[] = $carrierCode;

                $planArr = $this->dataHelper->fedexSmallPlanName($carrierCode);
                $planNumber = isset($planArr['planNumber']) ? $planArr['planNumber'] : '';
                
                if (count($enitureCarriers) > 1) {
                    $carrierLabel = $this->context->getScopeConfig()->getValue(
                        'eniture/'.$carrierCode.'/label'
                    );

                    $restriction[$carrierCode] = $this->enableAttForPlans($planNumber, $carrierLabel);
                } else {
                    $restriction[$carrierCode] = $this->enableAttForPlans($planNumber, '');
                }
            }
        }
        
        return $restriction;
    }
    
    /**
     * @param type $planNumber
     * @param type $carrierLabel
     * @return type
     */
    public function enableAttForPlans($planNumber, $carrierLabel)
    {
        if ($planNumber == 0 || $planNumber == 1) {
            $restriction = $this->createDataArray('', '', $carrierLabel);
        } else {
            $restriction = $this->createDataArray('Enabled', 'Enabled', $carrierLabel);
        }
        
        return $restriction;
    }
    
    /**
     * @param type $hazmat
     * @param type $insurance
     * @param type $label
     * @return array
     */
    public function createDataArray($hazmat, $insurance, $label)
    {
        return [
            'hazmat' => $hazmat,
            'insurance' => $insurance,
            'label' => $label
        ];
    }
}
