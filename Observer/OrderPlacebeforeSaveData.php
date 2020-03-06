<?php

namespace Eniture\FedExSmallPackageQuotes\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class OrderPlacebeforeSaveData
 * @package Eniture\FedExSmallPackageQuotes\Observer
 */
class OrderPlacebeforeSaveData implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
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
            $isMulti = '0';
            $order = $observer->getEvent()->getOrder();
            $quote = $order->getQuote();

            if (isset($quote)) {
                $isMulti = $quote->getIsMultiShipping();
            }

            $method =  $order->getShippingMethod();

            if (strpos($method, 'ENFedExSmpkg') !== false) {
                $orderDetailData = $this->coreSession->getFdxOrderDetailSession();
                $semiOrderDetailData = $this->coreSession->getSemiOrderDetailSession();

                if ($orderDetailData && $semiOrderDetailData == null) {
                    if (count($orderDetailData['shipmentData']) == 1) {
                        $orderDetailData['shipmentData'] = $this->setQuotesIfSingleShipment($orderDetailData, $order);
                    }
                } elseif ($semiOrderDetailData) {
                    $orderDetailData = $semiOrderDetailData;
                    $this->coreSession->unsSemiOrderDetailSession();
                }
                $order->setData('order_detail_data', json_encode($orderDetailData));
                $order->save();

                if (!$isMulti) {
                    $this->coreSession->unsFdxOrderDetailSession();
                }
            }
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
        $titlePart = "FedEx Small Package Quotes - ";
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
