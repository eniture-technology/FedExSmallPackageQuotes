<?php
namespace Eniture\FedExSmallPackageQuotes\Controller\Test;

use \Magento\Framework\App\Action\Action;

/**
 * Class TestConnection
 * @package Eniture\FedExSmallPackageQuotes\Controller\Test
 */
class TestConnection extends Action
{
    /**
     * @var \Eniture\FedExSmallPackageQuotes\Helper\Data
     */
    public $dataHelper;
    /**
     * @var
     */
    public $request;

    /**
     * TestConnection constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Eniture\FedExSmallPackageQuotes\Helper\Data $dataHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Eniture\FedExSmallPackageQuotes\Helper\Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
        $this->request = $context->getRequest();
        parent::__construct($context);
    }


    /**
     *
     */
    public function execute()
    {
        foreach ($this->getRequest()->getPostValue() as $key => $data) {
            $credentials[$key] = $data;
        }
        $postData = [
            'platform'              => 'magento2',
            'licence_key'           => $credentials['pluginLicenceKey'],
            'server_name'           => $this->request->getServer('SERVER_NAME'),
        ];

        if(isset($credentials['endPoint']) && 'new' == $credentials['endPoint']){
            $postData['requestForNewAPI'] = '1';
            $postData['clientId'] = $credentials['clientId'];
            $postData['clientSecret'] = $credentials['clientSecret'];
            $postData['accountNumber'] = $credentials['accountNumber'];
        }else{
            $postData['fedex_user_id'] = $credentials['authenticationKey'];
            $postData['fedex_password'] = $credentials['productionPass'];
            $postData['fedex_account_number'] = $credentials['accountNumber'];
            $postData['fedex_meter_number'] = $credentials['meterNumber'];
        }

        $response = $this->dataHelper->fedexSmpkgSendCurlRequest($this->dataHelper->wsHittingUrls('testConnection'), $postData);
        $result = $this->fedexSmpkgLtlTestConnResponse($response);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($result);
    }


    /**
     * @param $responce
     * @return false|string
     */
    public function fedexSmpkgLtlTestConnResponse($responce)
    {
        $responce1 = [];
        $successMsg = 'The test resulted in a successful connection.';
        $erMsg = 'Empty response from API';

        if(empty($responce)){
            $responce1['Error'] =  $erMsg;
        } elseif ( isset($responce->severity) && $responce->severity == 'SUCCESS' ) {
            $responce1['Success'] =  $successMsg;
        } elseif ( isset($responce->success) && $responce->success == 1 ) {
            $responce1['Success'] =  $successMsg;
        } elseif (( isset($responce->error) && $responce->error == 1 ) 
        || ( isset($responce->severity) && ($responce->severity == 'ERROR' || $responce->severity == 'error'))) {
            $responce1['Error'] =  $responce->Message ?? $erMsg;
        } elseif (isset($responce->error) && !is_int($responce->error)) {
            $responce1['Error'] =  $responce->error;
        } else {
            $responce1['Error'] =  'An empty or unknown response format, therefore we are unable to determine whether it was successful or an error';
        }
        return json_encode($responce1);
    }
}
