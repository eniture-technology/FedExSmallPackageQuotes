<?php

namespace Eniture\FedExSmallPackages\Controller\Warehouse;

use \Magento\Framework\App\Action\Action;

class EditWarehouse extends Action
{
    protected $_dataHelper;

    /**
     * 
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Eniture\FedExSmallPackages\Helper\Data $dataHelper
     * @param \Eniture\FedExSmallPackages\Model\WarehouseFactory $warehouseFactory
     */
    public function __construct(
            \Magento\Framework\App\Action\Context $context,
            \Eniture\FedExSmallPackages\Helper\Data $dataHelper,
            \Eniture\FedExSmallPackages\Model\WarehouseFactory $warehouseFactory
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_warehouseFactory    = $warehouseFactory->create();
        parent::__construct($context);
    }
    
    /**
     * @return string
     */
    function execute()
    {
        foreach ($this->getRequest()->getPostValue() as $key => $post){
            $editWhData[$key] = filter_var( $post, FILTER_SANITIZE_STRING );
        }

        $getWarehouseId   = $editWhData['edit_id'];
        $warehousList   = $this->fetchWarehouseList('warehouse', $getWarehouseId);
        
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($warehousList)); 
    }
    
    /**
     * 
     * @param type $location
     * @param type $warehouseId
     * @return type
     */
    function fetchWarehouseList($location, $warehouseId) {
        $whCollection       = $this->_warehouseFactory->getCollection()
                                    ->addFilter('location', array('eq' => $location))
                                    ->addFilter('warehouse_id', array('eq' => $warehouseId));
        
        return $this->_dataHelper->purifyCollectionData($whCollection);
    }   
}
