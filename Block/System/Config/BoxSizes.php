<?php
namespace Eniture\FedExSmallPackages\Block\System\Config;

class BoxSizes extends \Magento\Config\Block\System\Config\Form\Field
{
    const BOXSIZES_TEMPLATE = 'system/config/boxsizes.phtml';
    
    protected $_moduleManager;
    public $enable = 'no';
    
    /**
     * 
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
            \Magento\Backend\Block\Template\Context $context,
            \Magento\Framework\Module\Manager $moduleManager,
            array $data = []
    )
    {
        $this->_moduleManager   = $moduleManager;
        $this->checkBinPackagingModule();
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
            $this->setTemplate(static::BOXSIZES_TEMPLATE);
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
   
    /**
     * checkBinPackagingModule
     */
    protected function checkBinPackagingModule() 
    {
        if($this->_moduleManager->isEnabled('Eniture_BoxSizes')){
            $this->enable = 'yes';
        }
    }
}