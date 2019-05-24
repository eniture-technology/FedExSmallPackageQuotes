<?php
namespace Eniture\FedExSmallPackages\Block\System\Config;

use \Magento\Backend\Block\Template\Context;
use Eniture\FedExSmallPackages\Helper\Data;
use Magento\Mtf\Client\BrowserInterface;

class TestConnection extends \Magento\Config\Block\System\Config\Form\Field
{
    const BUTTON_TEMPLATE = 'system/config/testconnection.phtml';
    
    private $dataHelper;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
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
        return $this->getbaseUrl().'/fedexsmallpackages/Test/TestConnection/';
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
                'onclick'       => 'javascript:fedexSmpkgTestConn(); return false;'
            ]
        );
        return $this->_toHtml();
    }
    
    /**
     * Show FedEx Small Plan Notice
     * @return string
     */
    function fedexSmallPlanNotice()
    {
        $planMsg = $this->dataHelper->fedexSmallSetPlanNotice();
        return $planMsg;
    }
}
