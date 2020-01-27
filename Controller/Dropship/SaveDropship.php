<?php

namespace Eniture\FedExSmallPackageQuotes\Controller\Dropship;

use \Magento\Framework\App\Action\Action;

/**
 * Class SaveDropship
 * @package Eniture\FedExSmallPackageQuotes\Controller\Dropship
 */
class SaveDropship extends Action
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
     * SaveDropship constructor.
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
        $updateInspLd = 'no';

        foreach ($this->getRequest()->getPostValue() as $key => $post) {
            $saveDsData[$key] = filter_var($post, FILTER_SANITIZE_STRING);
        }

        $inputDataArr = $this->dataHelper->fedexSmpkgOriginArray($saveDsData);
        $validateData = $this->dataHelper->fedexSmpkgValidatedPostData($inputDataArr);
        $city = $validateData['city'];
        $state = $validateData['state'];
        $zip = $validateData['zip'];
        $nickname = $validateData['nickname'] = $this->nicknameValid(trim($validateData['nickname']), $zip, $city);

        if ($city != 'Error') {
            $dropshipId   = isset($saveDsData['dropshipId']) ? intval($saveDsData['dropshipId']) : "";
            $getDropship  = $this->checkDropshipList($city, $state, $zip, $nickname);

            if (!empty($getDropship)) {
                $dsId = reset($getDropship)['warehouse_id'];
                if ($dropshipId == $dsId) {
                    // check any change in InspLd data
                    $updateInspLd = $this->dataHelper->checkUpdateInstrorePickupDelivery($getDropship, $validateData);
                }
            }

            if ($dropshipId && (empty($getDropship) || $updateInspLd == 'yes')) {
                $updateQry = $this->dataHelper->updateWarehousData($validateData, "warehouse_id='".$dropshipId."'");
            } else {
                if (empty($getDropship) && ($this->countNickname($nickname) == 0 || $nickname == "")) {
                    $insertQry = $this->dataHelper->insertWarehouseData($validateData, $dropshipId);
                }
            }
        }

        $lastId = ($updateQry) ? $dropshipId : $insertQry['lastId'];

        $dropshipList = $this->dropshipListData($validateData, $insertQry, $updateQry, $lastId);

        if ($updateQry == 0 && $dropshipId) {
            $dropshipList['dsID'] = $dropshipId;
            if ($getDropship[0]['warehouse_id'] != $dropshipId) {
                $dropshipList['dsID'] = 0;
            }
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($dropshipList));
    }
    
    /**
     * @param type $validateData
     * @param type $insertQry
     * @param type $updateQry
     * @param type $lastId
     * @return type
     */
    public function dropshipListData($validateData, $insertQry, $updateQry, $lastId)
    {
        return [
            'origin_city' => $validateData['city'],
            'origin_state' => $validateData['state'],
            'origin_zip' => $validateData['zip'],
            'origin_country' => $validateData['country'],
            'nickname' =>$validateData['nickname'],
            'insert_qry' => $insertQry['insertId'],
            'update_qry' => $updateQry,
            'id' => $lastId
        ];
    }
    
    /**
     * @param type $city
     * @param type $state
     * @param type $zip
     * @param type $nickname
     * @return type
     */
    public function checkDropshipList($city, $state, $zip, $nickname)
    {
        $dsCollection       = $this->warehouseFactory->create()->getCollection()
                                    ->addFilter('location', ['eq' => 'dropship'])
                                    ->addFilter('city', ['eq' => $city])
                                    ->addFilter('state', ['eq' => $state])
                                    ->addFilter('zip', ['eq' => $zip])
                                    ->addFilter('nickname', ['eq' => $nickname]);
        
        return $this->dataHelper->purifyCollectionData($dsCollection);
    }
    
    /**
     * @param type $nickname
     * @return type
     */
    public function countNickname($nickname)
    {
        if (!empty($nickname)) {
            $dsCollection       = $this->warehouseFactory->create()->getCollection()
                                        ->addFilter('location', ['eq' => 'dropship'])
                                        ->addFilter('nickname', ['eq' => $nickname]);
            return count($this->dataHelper->purifyCollectionData($dsCollection));
        }
    }

    public function nicknameValid($nickname, $zip, $city)
    {
        $dafaultRegex = "/DS_[0-9 a-z A-Z]+_[a-z A-Z]*/";
        if (preg_match($dafaultRegex, $nickname) || empty($nickname)) {
            $nickname = 'DS_'.$zip.'_'.$city;
        }
        return $nickname;
    }
}
