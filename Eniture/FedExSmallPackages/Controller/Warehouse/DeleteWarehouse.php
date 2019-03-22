<?php

namespace Eniture\FedExSmallPackages\Controller\Warehouse;

use \Magento\Framework\App\Action\Action;

class DeleteWarehouse extends Action
{
    /**
     * 
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Eniture\FedExSmallPackages\Helper\Data $dataHelper
     */
    public function __construct(
            \Magento\Framework\App\Action\Context $context,
            \Eniture\FedExSmallPackages\Helper\Data $dataHelper
    ) {
        $this->_dataHelper = $dataHelper;
        parent::__construct($context);
    }
    
    /**
     * @return string
     */
    function execute()
    {
        foreach ($this->getRequest()->getPostValue() as $key => $post){
            $deleteWhData[$key] = filter_var( $post, FILTER_SANITIZE_STRING );
        }
        
        $deleteID = $deleteWhData['delete_id'];
        if($deleteWhData['action'] == 'delete_warehouse'){
            $qry    = $this->_dataHelper->deleteWarehouseSecData("warehouse_id='".$deleteID."'");
        }
        
        $response = array('deleteID' => $deleteID, 'qryResp' => $qry);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($response)); 
    }
}

