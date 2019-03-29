<?php

namespace Eniture\FedExSmallPackages\Controller\Dropship;

use \Magento\Framework\App\Action\Action;

class DeleteDropship extends Action
{
    public $dataHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Eniture\FedExSmallPackages\Helper\Data $dataHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Eniture\FedExSmallPackages\Helper\Data $dataHelper
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
            $deleteDsData[$key] = filter_var($post, FILTER_SANITIZE_STRING);
        }
        $deleteID = $deleteDsData['delete_id'];
        if ($deleteDsData['action'] == 'delete_dropship') {
            $qry    = $this->dataHelper->deleteWarehouseSecData("warehouse_id='".$deleteID."'");
        }

        $response = ['deleteID' => $deleteID, 'qryResp' => $qry];
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($response));
    }
}
