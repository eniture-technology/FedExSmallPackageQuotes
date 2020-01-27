<?php

namespace Eniture\FedExSmallPackageQuotes\Controller\Dropship;

use \Magento\Framework\App\Action\Action;

/**
 * Class EditDropship
 * @package Eniture\FedExSmallPackageQuotes\Controller\Dropship
 */
class EditDropship extends Action
{
    /**
     * @var \Eniture\FedExSmallPackageQuotes\Helper\Data
     */
    public $dataHelper;
    /**
     * @var \Eniture\FedExSmallPackageQuotes\Model\WarehouseFactory
     */
    private $warehouseFactory;

    /**
     * EditDropship constructor.
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
            $editDsData[$key] = filter_var($post, FILTER_SANITIZE_STRING);
        }

        $getDropshipId  = $editDsData['edit_id'];
        $dropshipList   = $this->fetchDropshipList('dropship', $getDropshipId);
        
        //Get plan
        $plan = $this->dataHelper->fedexSmallPlanName('ENFedExSmpkg');
        if ($plan['planNumber'] != 3) {
            $dropshipList[0]['in_store'] = null;
            $dropshipList[0]['local_delivery'] = null;
        }
        
        //Change html entities code
        $nick = $dropshipList[0]['nickname'];
        $dropshipList[0]['nickname'] = html_entity_decode($nick, ENT_QUOTES);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($dropshipList));
    }
    
    /**
     * @param type $location
     * @param type $warehouseId
     * @return type
     */
    public function fetchDropshipList($location, $warehouseId)
    {
        $whCollection       = $this->warehouseFactory->create()->getCollection()
                                    ->addFilter('location', ['eq' => $location])
                                    ->addFilter('warehouse_id', ['eq' => $warehouseId]);
        return $this->dataHelper->purifyCollectionData($whCollection);
    }
}
