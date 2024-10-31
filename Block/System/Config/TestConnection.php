<?php
namespace Eniture\FedExSmallPackageQuotes\Block\System\Config;

use \Magento\Backend\Block\Template\Context;
use Eniture\FedExSmallPackageQuotes\Helper\Data;

/**
 * Class TestConnection
 * @package Eniture\FedExSmallPackageQuotes\Block\System\Config
 */
class TestConnection extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     *
     */
    const BUTTON_TEMPLATE = 'system/config/testconnection.phtml';

    /**
     * @var Data
     */
    private $dataHelper;
    /**
     * @var Context
     */
    public $context;


    /**
     * TestConnection constructor.
     * @param Context $context
     * @param Data $dataHelper
     * @param array $data
     */
    public function __construct(Context $context, Data $dataHelper, array $data = [])
    {
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
            $this->setTemplate(static::BUTTON_TEMPLATE);
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
     * @return url
     */
    public function getAjaxCheckUrl()
    {
        return $this->getbaseUrl().'/fedexsmallpackagequotes/Test/TestConnection/';
    }
    
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return array
     */
    public function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->addData(
            [
                'id'            => 'test_fedexsmpkg_connection',
                'button_label'  => 'Test Connection',
            ]
        );
        return $this->_toHtml();
    }
    
    /**
     * Show FedEx Small Plan Notice
     * @return string
     */
    public function fedexSmallPlanNotice()
    {
        $planRefreshUrl = $this->getPlanRefreshUrl();
        $planMsg = $this->dataHelper->fedexSmallSetPlanNotice($planRefreshUrl);
        return $planMsg;
    }

    /**
     * @return url
     */
    public function getPlanRefreshUrl()
    {
        return $this->getbaseUrl().'fedexsmallpackagequotes/Test/PlanRefresh/';
    }
}
