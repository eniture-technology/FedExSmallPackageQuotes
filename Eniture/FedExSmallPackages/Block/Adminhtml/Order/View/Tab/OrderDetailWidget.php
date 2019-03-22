<?php
namespace Eniture\FedExSmallPackages\Block\Adminhtml\Order\View\Tab;

class OrderDetailWidget extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'order/view/tab/orderdetailwidget.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Additional Order Details');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Additional Order Details');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        if(is_null($this->coreRegistry->registry('orderWidgetFlag'))){
            $this->coreRegistry->register('orderWidgetFlag', 'yes');
            // For me, I wanted this tab to always show
            // You can play around with the ACL settings 
            // to selectively show later if you want
            return true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        // For me, I wanted this tab to always show
        // You can play around with conditions to
        // show the tab later
        return false;
    }

    /**
     * Get Tab Class
     *
     * @return string
     */
    public function getTabClass()
    {
        // I wanted mine to load via AJAX when it's selected
        // That's what this does
        return 'ajax only';
    }

    /**
     * Get Class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->getTabClass();
    }

    /**
     * Get Tab Url
     *
     * @return string
     */
    public function getTabUrl()
    {
        // customtab is a adminhtml router we're about to define
        // the full route can really be whatever you want
        return $this->getUrl('orderdetailwidget/*/OrderDetailWidget', ['_current' => true]);
    }
    
    public function displayOrderDetailData($order, $shimentNumber, $residential) 
    {        
        if(isset($order) && !empty($order)){
            $title = (isset($order['quotes']['title']))?$order['quotes']['title'].' :':'';
            $rate = (isset($order['quotes']['rate']))?number_format($order['quotes']['rate'], 2, '.', ''):'0.00';
            echo ($shimentNumber > 1 ) ? '<hr class="order-info-hr">' : '';
            echo '<div class="order-detail-block">';
            echo "<h4 class='order-detail-hdng'>Shipment ". $shimentNumber ." > Origin & Services</h4>";
                echo '<div class="order-shipment-block">';
                    echo '<span class="list-item">'.ucfirst($order['origin']['location']) .': '. $order['origin']['senderCity'] .', '. $order['origin']['senderState'] .' '. $order['origin']['senderZip'] .'</span>';
                    echo '<span class="list-item">'.$title .' $' . $rate.'</span>';
                echo '</div>';
                $this->additionalInformation($residential, $order['item']);
                echo "<h4 class='order-detail-hdng'>Shipment ". $shimentNumber ." > items</h4>";
                foreach($order['item'] as $key=>$lineItem){
                    echo '<div class="order-item-block">'
                        . '<span class="list-item">'. $lineItem['piecesOfLineItem'] .' x '. $lineItem['lineItemName'] .'</span>';
                    echo '</div>';
                }
               
            /* Clear the float effect */
            echo '<div class="en-clear"></div>';
            echo '</div>';
        }
    }
    
    public function additionalInformation($residential, $lineItems) 
    {
        $findHazardous = array_column($lineItems, 'hazardousMaterial');
        
        $residential = ($residential)?'Residential Delivery':'';
        $hazordusFee = ($findHazardous[0] == 'Y')?'Hazardous material':'';

        if($residential || $hazordusFee){
            echo "<h4 class='order-detail-hdng'>Additional Information</h4>";
            echo '<div class="order-additionalInfo-block">';
                    if($residential){
                        echo '<span class="list-item">'. $residential .'</span>';
                    }
                    if($hazordusFee){
                        echo '<span class="list-item">'. $hazordusFee .' </span>';
                    }
            echo '</div>';
        }
    }
}