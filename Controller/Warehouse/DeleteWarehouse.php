<?php

namespace Eniture\FedExSmallPackageQuotes\Controller\Warehouse;

use \Magento\Framework\App\Action\Action;

/**
 * Class DeleteWarehouse
 * @package Eniture\FedExSmallPackageQuotes\Controller\Warehouse
 */
class DeleteWarehouse extends Action
{
    /**
     * @var \Eniture\FedExSmallPackageQuotes\Helper\Data
     */
    public $dataHelper;

    /**
     * DeleteWarehouse constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Eniture\FedExSmallPackageQuotes\Helper\Data $dataHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Eniture\FedExSmallPackageQuotes\Helper\Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
        parent::__construct($context);
    }
    
    /**
     * @return string
     */
    public function execute()
    {
        foreach ($this->getRequest()->getPostValue() as $key => $post) {
            $deleteWhData[$key] = htmlspecialchars($post, ENT_QUOTES);
        }
        
        $deleteID = $deleteWhData['delete_id'];
        if ($deleteWhData['action'] == 'delete_warehouse') {
            $qry    = $this->dataHelper->deleteWarehouseSecData("warehouse_id='".$deleteID."'");
        }
        
        $canAddWh = $this->dataHelper->whPlanRestriction();
        $response = ['deleteID' => $deleteID, 'qryResp' => $qry, 'canAddWh' => $canAddWh];
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($response));
    }
}
