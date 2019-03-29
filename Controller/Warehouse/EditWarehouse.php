<?php

namespace Eniture\FedExSmallPackages\Controller\Warehouse;

use \Magento\Framework\App\Action\Action;

class EditWarehouse extends Action
{
    public $dataHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Eniture\FedExSmallPackages\Helper\Data $dataHelper
     * @param \Eniture\FedExSmallPackages\Model\WarehouseFactory $warehouseFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Eniture\FedExSmallPackages\Helper\Data $dataHelper,
        \Eniture\FedExSmallPackages\Model\WarehouseFactory $warehouseFactory
    ) {
        $this->dataHelper = $dataHelper;
        $this->_warehouseFactory    = $warehouseFactory;
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
        $whCollection       = $this->_warehouseFactory->create()->getCollection()
                                    ->addFilter('location', ['eq' => $location])
                                    ->addFilter('warehouse_id', ['eq' => $warehouseId]);
        
        return $this->dataHelper->purifyCollectionData($whCollection);
    }
}
