<?php

namespace Eniture\FedExSmallPackageQuotes\Controller\Warehouse;

use \Magento\Framework\App\Action\Action;

/**
 * Class EditWarehouse
 * @package Eniture\FedExSmallPackageQuotes\Controller\Warehouse
 */
class EditWarehouse extends Action
{
    /**
     * @var \Eniture\FedExSmallPackageQuotes\Helper\Data
     */
    public $dataHelper;
    /**
     * @var \Eniture\FedExSmallPackageQuotes\Model\WarehouseFactory
     */
    public $warehouseFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Eniture\FedExSmallPackageQuotes\Helper\Data $dataHelper
     * @param \Eniture\FedExSmallPackageQuotes\Model\WarehouseFactory $warehouseFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Eniture\FedExSmallPackageQuotes\Helper\Data $dataHelper,
        \Eniture\FedExSmallPackageQuotes\Model\WarehouseFactory $warehouseFactory
    ) {
        $this->dataHelper = $dataHelper;
        $this->warehouseFactory    = $warehouseFactory;
        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function execute()
    {
        foreach ($this->getRequest()->getPostValue() as $key => $post) {
            $editWhData[$key] = filter_var($post, FILTER_SANITIZE_STRING);
        }

        $getWarehouseId   = $editWhData['edit_id'];
        $warehousList   = $this->fetchWarehouseList('warehouse', $getWarehouseId);
        
        //Get plan
        $plan = $this->dataHelper->fedexSmallPlanName('ENFedExSmpkg');
        if ($plan['planNumber'] != 3) {
            $warehousList[0]['in_store'] = null;
            $warehousList[0]['local_delivery'] = null;
        }
        
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($warehousList));
    }
    
    /**
     * @param type $location
     * @param type $warehouseId
     * @return type
     */
    public function fetchWarehouseList($location, $warehouseId)
    {
        $whCollection       = $this->warehouseFactory->create()->getCollection()
                                ->addFilter('location', ['eq' => $location])
                                ->addFilter('warehouse_id', ['eq' => $warehouseId]);
        
        return $this->dataHelper->purifyCollectionData($whCollection);
    }
}
