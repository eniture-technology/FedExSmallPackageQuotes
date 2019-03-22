<?php

namespace Eniture\FedExSmallPackages\Observer;

use Magento\Framework\Event\ObserverInterface;

class OrderPlacebeforeSaveData implements ObserverInterface
{
    
    protected $_coreSession;

    /**
     * 
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     */
    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $coreSession
        
    ) {
        $this->_coreSession = $coreSession;
    }

    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $orderDetailData = $this->_coreSession->getOrderDetailSession();
            $order = $observer->getEvent()->getOrder();
            
            if(count($orderDetailData['shipmentData']) == 1){
                $orderDetailData['shipmentData'] = $this->setQuotesIfSingleShipment($orderDetailData, $order);
            }
            
            $order->setData('order_detail_data', json_encode($orderDetailData));
            $order->save();
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }
    
    /**
     * 
     * @param type $orderDetailData
     * @param type $order
     * @return type
     */
    public function setQuotesIfSingleShipment($orderDetailData, $order) {
        $shippingMethod = explode('_', $order->getShippingMethod());
        $newData = array();
        foreach ($orderDetailData['shipmentData'] as $key => $data) {
            $newData[$key]['quotes'] = array(
                                        'code'  => $shippingMethod[1],
                                        'title' => str_replace("FedEx Small Packages Quotes - ", "", $order->getShippingDescription()),
                                        'rate'  => number_format((float)$order->getShippingAmount(), 2, '.', '')
                                    );
            
        }
        
        $mergedNewData = array_replace_recursive($orderDetailData['shipmentData'], $newData);
        return $mergedNewData;
    }
}