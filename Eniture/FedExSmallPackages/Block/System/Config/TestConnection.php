<?php
namespace Eniture\FedExSmallPackages\Block\System\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Mtf\Client\BrowserInterface;

class TestConnection extends \Magento\Config\Block\System\Config\Form\Field
{
    const BUTTON_TEMPLATE = 'system/config/testconnection.phtml';
 
    /**
     * 
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::BUTTON_TEMPLATE);
        }
        return $this;
    }
    
    /**
     * 
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
     * 
     * @return url
     */
    public function getAjaxCheckUrl()
    {
        return $this->getbaseUrl().'/FedExSmallPackages/Test/TestConnection/'; 
    }
    
    /**
     * 
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return array
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
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
}