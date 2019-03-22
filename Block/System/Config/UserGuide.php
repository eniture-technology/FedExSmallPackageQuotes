<?php
namespace Eniture\FedExSmallPackages\Block\System\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Mtf\Client\BrowserInterface;

class UserGuide extends \Magento\Config\Block\System\Config\Form\Field
{
    const GUIDE_TEMPLATE = 'system/config/userguide.phtml';
 
    /**
     * 
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::GUIDE_TEMPLATE);
        }
        return $this;
    }
  
    /**
     * 
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return html
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }
}