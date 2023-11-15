<?php

namespace Eniture\FedExSmallPackageQuotes\Controller\Dropship;

use \Magento\Framework\App\Action\Action;

/**
 * Class DeleteDropship
 * @package Eniture\FedExSmallPackageQuotes\Controller\Dropship
 */
class DeleteDropship extends Action
{
    /**
     * @var \Eniture\FedExSmallPackageQuotes\Helper\Data
     */
    public $dataHelper;

    /**
     * DeleteDropship constructor.
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
            $deleteDsData[$key] = htmlspecialchars($post, ENT_QUOTES);
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
