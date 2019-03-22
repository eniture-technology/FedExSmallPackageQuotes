<?php
namespace Eniture\FedExSmallPackages\Controller\Dropship;

use \Magento\Framework\App\Action\Action;

class SaveDropship extends Action
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
            $saveDsData[$key] = filter_var( $post, FILTER_SANITIZE_STRING );
        }

        $inputDataArr = $this->_dataHelper->fedexSmpkgOriginArray($saveDsData);
        $validateData = $this->_dataHelper->fedexSmpkgValidatedPostData($inputDataArr);
        extract($validateData);

        $getDropship  = $this->checkDropshipList($city, $state, $zip, $nickname);
                
        // add instore pickup and local delivery data to array
        $updateInstrorePickupDelivery = $this->_dataHelper->checkUpdateInstrorePickupDelivery($getDropship, $validateData);

        if ( $city != 'Error' ) {
            $dropshipId    = isset( $saveDsData['dropshipId'] ) ? intval( $saveDsData['dropshipId'] ) : "";

            if ( $dropshipId && (empty( $getDropship ) || $updateInstrorePickupDelivery == 'yes') ) {
                $updateQry = $this->_dataHelper->updateWarehousData( $validateData, "warehouse_id='".$dropshipId."'" );
            }else{
                
                if ( empty( $getDropship ) && ($this->countNickname($nickname) == 0 || $nickname == "")) {
                    $insertQry = $this->_dataHelper->insertWarehouseData( $validateData, $dropshipId );
                }
            }
        }

        $lastId = ($updateQry) ? $dropshipId : $insertQry['lastId'];

        $dropshipList = array('origin_city' => $city, 'origin_state' => $state, 'origin_zip' => $zip, 'origin_country' => $country, 'nickname' =>$nickname, 'insert_qry' => $insertQry['insertId'],'update_qry' => $updateQry, 'id' => $lastId);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($dropshipList));   
    }
    
    /**
     * 
     * @param type $city
     * @param type $state
     * @param type $zip
     * @param type $nickname
     * @return type
     */
    function checkDropshipList($city, $state, $zip, $nickname) {
        $dsCollection       = $this->_warehouseFactory->getCollection()
                                    ->addFilter('location', array('eq' => 'dropship'))
                                    ->addFilter('city', array('eq' => $city))
                                    ->addFilter('state', array('eq' => $state))
                                    ->addFilter('zip', array('eq' => $zip))
                                    ->addFilter('nickname', array('eq' => $nickname));
        
        return $this->_dataHelper->purifyCollectionData($dsCollection);
    }
    
    /**
     * 
     * @param type $nickname
     * @return type
     */
    function countNickname($nickname) {
        if(!empty($nickname)){
            $dsCollection       = $this->_warehouseFactory->getCollection()
                                        ->addFilter('location', array('eq' => 'dropship'))
                                        ->addFilter('nickname', array('eq' => $nickname));

            return $this->_dataHelper->purifyCollectionData($dsCollection);
        }
    }
}
