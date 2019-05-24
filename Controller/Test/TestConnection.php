<?php
namespace Eniture\FedExSmallPackages\Controller\Test;

use \Magento\Framework\App\Action\Action;

class TestConnection extends Action
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
        $this->request = $context->getRequest();
        parent::__construct($context);
    }
    
    /**
     * @return string
     */
    public function execute()
    {
        foreach ($this->getRequest()->getPostValue() as $key => $data) {
            $credentials[$key] = filter_var($data, FILTER_SANITIZE_STRING);
        }
        $postData = [
            'platform'              => 'magento2',
            'fedex_user_id'         => $credentials['authenticationKey'],
            'fedex_password'        => $credentials['productionPass'],
            'fedex_account_number'  => $credentials['accountNumber'],
            'fedex_meter_number'    => $credentials['meterNumber'],
            'licence_key'           => $credentials['pluginLicenceKey'],
            'server_name'           => $this->request->getServer('SERVER_NAME'),
        ];

        $response = $this->dataHelper->fedexSmpkgSendCurlRequest($this->dataHelper->wsHittingUrls('testConnection'), $postData);
        $result = $this->fedexSmpkgLtlTestConnResponse($response);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($result);
    }

    /**
     * @param type $responce
     */
    public function fedexSmpkgLtlTestConnResponse($responce)
    {
        $responce1 = [];
        $successMsg = 'The test resulted in a successful connection.';
        $erMsg = 'The credentials entered did not result in a successful test. Confirm your credentials and try again.';

        if (isset($responce->error) && $responce->error == 1) {
            $responce1['Error'] =  $erMsg;
        } elseif ((isset($responce->error) && isset($responce->success)) && $responce->error == 1) {
            $responce1['Error'] =  $erMsg;
        } elseif (isset($responce->error) && !is_int($responce->error)) {
            $responce1['Error'] =  $responce->error;
        } else {
            $responce1['Success'] =  $successMsg;
        }
        return json_encode($responce1);
    }
}
