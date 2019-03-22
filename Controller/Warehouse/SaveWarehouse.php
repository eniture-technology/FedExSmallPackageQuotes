<?php

namespace Eniture\FedExSmallPackages\Controller\Warehouse;

use \Magento\Framework\App\Action\Action;

class SaveWarehouse extends Action
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
        $insertQry = 0;
        $updateQry = 0;

        foreach ($this->getRequest()->getPostValue() as $key => $post){
            $saveWhData[$key] = filter_var( $post, FILTER_SANITIZE_STRING );
        }

        $inputDataArr = $this->_dataHelper->fedexSmpkgOriginArray($saveWhData);
        $validateData = $this->_dataHelper->fedexSmpkgValidatedPostData($inputDataArr);

        extract($validateData);
        $getWarehouse  = $this->checkDropshipList($city, $state, $zip);
        
        // add instore pickup and local delivery data to array
        $updateInstrorePickupDelivery = $this->_dataHelper->checkUpdateInstrorePickupDelivery($getWarehouse, $validateData);

        if ( $city != 'Error' ) {
            $warehouseId    = isset( $saveWhData['originId']  ) ? intval( $saveWhData['originId'] ) : "";

            if ( $warehouseId && (empty( $getWarehouse ) || $updateInstrorePickupDelivery == 'yes') ) {
                $updateQry = $this->_dataHelper->updateWarehousData( $validateData, "warehouse_id='".$warehouseId."'" );
            }else{
                if ( empty( $getWarehouse ) ) {
                    $insertQry = $this->_dataHelper->insertWarehouseData( $validateData, $warehouseId );
                }
            }
        }

        $lastId = ($updateQry) ? $warehouseId : $insertQry['lastId'];

        $warehousList = array('origin_city' => $city, 'origin_state' => $state, 'origin_zip' => $zip, 'origin_country' => $country, 'insert_qry' => $insertQry['insertId'],'update_qry' => $updateQry, 'id' => $lastId);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($warehousList));           
    }
    
    /**
     * 
     * @param type $city
     * @param type $state
     * @param type $zip
     * @return type
     */
    function checkDropshipList($city, $state, $zip) {
        $whCollection       = $this->_warehouseFactory->getCollection()
                                    ->addFilter('location', array('eq' => 'warehouse'))
                                    ->addFilter('city', array('eq' => $city))
                                    ->addFilter('state', array('eq' => $state))
                                    ->addFilter('zip', array('eq' => $zip));
        
        return $this->_dataHelper->purifyCollectionData($whCollection);
    }
}
