<?php

namespace Eniture\FedExSmallPackages\Observer;

use Magento\Framework\Event\ObserverInterface;

class OrderPlacebeforeSaveData implements ObserverInterface
{
    private $coreSession;

    /**
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     */
    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $coreSession
    ) {
        $this->coreSession = $coreSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $orderDetailData = $this->coreSession->getOrderDetailSession();
            $order = $observer->getEvent()->getOrder();
            
            if (count($orderDetailData['shipmentData']) == 1) {
                $orderDetailData['shipmentData'] = $this->setQuotesIfSingleShipment($orderDetailData, $order);
            }
            
            $order->setData('order_detail_data', json_encode($orderDetailData));
            $order->save();
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    
    /**
     * @param type $orderDetailData
     * @param type $order
     * @return type
     */
    private function setQuotesIfSingleShipment($orderDetailData, $order)
    {
        $shippingMethod = explode('_', $order->getShippingMethod());
        $titlePart = "FedEx Small Packages Quotes - ";
        $newData = [];
        foreach ($orderDetailData['shipmentData'] as $key => $data) {
            $newData[$key]['quotes'] = [
                                        'code'  => $shippingMethod[1],
                                        'title' => str_replace($titlePart, "", $order->getShippingDescription()),
                                        'rate'  => number_format((float)$order->getShippingAmount(), 2, '.', '')
                                    ];
        }
        
        $mergedNewData = array_replace_recursive($orderDetailData['shipmentData'], $newData);
        return $mergedNewData;
    }
}
