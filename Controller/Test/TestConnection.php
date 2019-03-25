<?php
/**
* 
*/
namespace Eniture\FedExSmallPackages\Controller\Test;

use \Magento\Framework\App\Action\Action;

class TestConnection extends Action
{
    protected $_curlUrl = 'http://eniture.com/ws/s/fedex/fedex_shipment_rates_test.php';

    protected $_dataHelper;
    
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
        foreach ($this->getRequest()->getPostValue() as $key => $data) {
            $credentials[$key] = filter_var($data, FILTER_SANITIZE_STRING);
        }
        
        extract($credentials);
        $postData = array(
            'platform'              => 'magento2',
            'fedex_user_id'         => $authenticationKey,
            'fedex_password'        => $productionPass,
            'fedex_account_number'  => $accountNumber,
            'fedex_meter_number'    => $meterNumber,
            'licence_key'           => $pluginLicenceKey,
            'server_name'           => $_SERVER['SERVER_NAME'],
        );

        $response = $this->_dataHelper->fedexSmpkgSendCurlRequest($this->_curlUrl,$postData);
        $result = $this->fedexSmpkgLtlTestConnResponse($response);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($result);
    }

    /**
    * @param type $post
    */

    function fedexSmpkgLtlTestConnResponse($responce) 
    {
        $responce1 = array();
        $successMsg = 'The test resulted in a successful connection.';
        $errorMsg = 'The credentials entered did not result in a successful test. Confirm your credentials and try again.';

        if(isset($responce->error) && $responce->error == 1){
           $responce1['Error'] =  $errorMsg;
        }

        elseif((isset($responce->error) && isset($responce->success)) && $responce->error == 1){
            $responce1['Error'] =  $errorMsg;
        }

        elseif(isset($responce->error) && !is_int($responce->error)){
            $responce1['Error'] =  $responce->error;
        }

        else{
            $responce1['Success'] =  $successMsg;
        }

        return json_encode($responce1);
    }
}