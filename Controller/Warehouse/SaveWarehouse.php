<?php

namespace Eniture\FedExSmallPackageQuotes\Controller\Warehouse;

use \Magento\Framework\App\Action\Action;

/**
 * Class SaveWarehouse
 * @package Eniture\FedExSmallPackageQuotes\Controller\Warehouse
 */
class SaveWarehouse extends Action
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
     * SaveWarehouse constructor.
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
        $insertQry = 0;
        $updateQry = 0;
        $updateInspld = 'no';

        foreach ($this->getRequest()->getPostValue() as $key => $post) {
            $saveWhData[$key] = filter_var($post, FILTER_SANITIZE_STRING);
        }

        $inputDataArr = $this->dataHelper->fedexSmpkgOriginArray($saveWhData);
        $validateData = $this->dataHelper->fedexSmpkgValidatedPostData($inputDataArr);

        $city = $validateData['city'];
        $state = $validateData['state'];
        $zip = $validateData['zip'];

        if ($city != 'Error') {
            $warehouseId  = isset($saveWhData['originId']) ? intval($saveWhData['originId']) : "";
            $getWarehouse = $this->checkWarehouseList($city, $state, $zip);

            if (!empty($getWarehouse)) {
                $whId = reset($getWarehouse)['warehouse_id'];
                if ($warehouseId == $whId) {
                    // check any change in InspLd data
                    $updateInspld = $this->dataHelper->checkUpdateInstrorePickupDelivery($getWarehouse, $validateData);
                }
            }

            if ($warehouseId && (empty($getWarehouse) || $updateInspld == 'yes')) {
                $updateQry = $this->dataHelper->updateWarehousData($validateData, "warehouse_id='".$warehouseId."'");
            } else {
                if (empty($getWarehouse)) {
                    $insertQry = $this->dataHelper->insertWarehouseData($validateData, $warehouseId);
                }
            }
        }

        $lastId = ($updateQry) ? $warehouseId : $insertQry['lastId'];
        $canAddWh = $this->dataHelper->whPlanRestriction();
        $warehousList = $this->warehousListData($validateData, $insertQry, $updateQry, $lastId, $canAddWh);

        if ($updateQry == 0 && $warehouseId) {
            $warehousList['whID'] = $warehouseId;
            if ($getWarehouse[0]['warehouse_id'] != $warehouseId) {
                $warehousList['whID'] = 0;
            }
        }



        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($warehousList));
    }
    
    /**
     * @param type $validateData
     * @param type $insertQry
     * @param type $updateQry
     * @param type $lastId
     * @return array
     */
    public function warehousListData($validateData, $insertQry, $updateQry, $lastId, $canAddWh)
    {
        return [
            'origin_city' => $validateData['city'],
            'origin_state' => $validateData['state'],
            'origin_zip' => $validateData['zip'],
            'origin_country' => $validateData['country'],
            'insert_qry' => $insertQry['insertId'],
            'update_qry' => $updateQry,
            'id' => $lastId,
            'canAddWh' => $canAddWh
        ];
    }
    
    /**
     * @param type $city
     * @param type $state
     * @param type $zip
     * @return type
     */
    public function checkWarehouseList($city, $state, $zip)
    {
        $whCollection       = $this->warehouseFactory->create()->getCollection()
                                    ->addFilter('location', ['eq' => 'warehouse'])
                                    ->addFilter('city', ['eq' => $city])
                                    ->addFilter('state', ['eq' => $state])
                                    ->addFilter('zip', ['eq' => $zip]);

        return $this->dataHelper->purifyCollectionData($whCollection);
    }
}
