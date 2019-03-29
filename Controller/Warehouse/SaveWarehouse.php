<?php

namespace Eniture\FedExSmallPackages\Controller\Warehouse;

use \Magento\Framework\App\Action\Action;

class SaveWarehouse extends Action
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
        $insertQry = 0;
        $updateQry = 0;

        foreach ($this->getRequest()->getPostValue() as $key => $post) {
            $saveWhData[$key] = filter_var($post, FILTER_SANITIZE_STRING);
        }

        $inputDataArr = $this->dataHelper->fedexSmpkgOriginArray($saveWhData);
        $validateData = $this->dataHelper->fedexSmpkgValidatedPostData($inputDataArr);

        $city = $validateData['city'];
        $state = $validateData['state'];
        $zip = $validateData['zip'];
        $country = $validateData['country'];
        $getWarehouse  = $this->checkWarehouseList($city, $state, $zip);
        
        // add instore pickup and local delivery data to array
        $updateInstrorePickupDelivery = $this->dataHelper->checkUpdatePickupDelivery($getWarehouse, $validateData);

        if ($city != 'Error') {
            $warehouseId    = isset($saveWhData['originId']) ? (int)($saveWhData['originId']) : "";

            if ($warehouseId && (empty($getWarehouse) || $updateInstrorePickupDelivery == 'yes')) {
                $updateQry = $this->dataHelper->updateWarehousData($validateData, "warehouse_id='".$warehouseId."'");
            } else {
                if (empty($getWarehouse)) {
                    $insertQry = $this->dataHelper->insertWarehouseData($validateData, $warehouseId);
                }
            }
        }

        $lastId = ($updateQry) ? $warehouseId : $insertQry['lastId'];

        $warehousList = $this->warehousListData($validateData, $insertQry, $updateQry, $lastId);

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
    public function warehousListData($validateData, $insertQry, $updateQry, $lastId)
    {
        return [
            'origin_city' => $validateData['city'],
            'origin_state' => $validateData['state'],
            'origin_zip' => $validateData['zip'],
            'origin_country' => $validateData['country'],
            'insert_qry' => $insertQry['insertId'],
            'update_qry' => $updateQry,
            'id' => $lastId
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
        $whCollection       = $this->_warehouseFactory->create()->getCollection()
                                    ->addFilter('location', ['eq' => 'warehouse'])
                                    ->addFilter('city', ['eq' => $city])
                                    ->addFilter('state', ['eq' => $state])
                                    ->addFilter('zip', ['eq' => $zip]);
        
        return $this->dataHelper->purifyCollectionData($whCollection);
    }
}
