<?php
namespace Eniture\FedExSmallPackageQuotes\Model\Carrier;

/**
 * class that generated request data
 */
class FedExSmpkgGenerateRequestData
{
    /**
     * @var
     */
    public $registry;
    /**
     * @var
     */
    public $moduleManager;
    /**
     * @var
     */
    public $scopeConfig;
    /**
     * @var
     */
    public $dataHelper;
    /**
     * @var
     */
    public $request;
    /**
     * @var string
     */
    public $FedexOneRatePricing = '0';
    /**
     * @var string
     */
    public $oneRatePricing = '0';
    /**
     * @var string
     */
    public $airServicesPricing = '0';
    /**
     * @var string
     */
    public $homeGroundPricing = '0';
    /**
     * This variable stores service type e.g domestic, international, both
     * @var string
     */
    public $serviceType;
    /**
     * @param type $scopeConfig
     * @param type $registry
     * @param type $moduleManager
     * @param type $dataHelper
     */
    public function _init(
        $scopeConfig,
        $registry,
        $moduleManager,
        $dataHelper,
        $request
    ) {
        $this->registry        = $registry;
        $this->scopeConfig     = $scopeConfig;
        $this->moduleManager   = $moduleManager;
        $this->dataHelper      = $dataHelper;
        $this->request         = $request;
    }

    /**
     * function that generates FedEx array
     * @return array
     */
    public function generateFedExSmpkgArray($request, $origin)
    {
        $getDistance = 0;
        $fedexSmpkgArr = [
            'licenseKey'    => $this->getConfigData('licnsKey'),
            'serverName'    => $this->request->getServer('SERVER_NAME'),
            'carrierMode'   => 'pro', // use test / pro
            'quotestType'   => 'small',
            'version'       => '1.2.0',
            'api'           => $this->getApiInfoArr($request->getDestCountryId(), $origin),
            'getDistance'   => $getDistance,
        ];
        return  $fedexSmpkgArr;
    }

    /**
     * Function that generates request array
     * @param $request
     * @param $FedExArr
     * @param $itemsArr
     * @return array
     */
    public function generateRequestArray($request, $fedexSmpkgArr, $itemsArr, $cart)
    {
        if (count($fedexSmpkgArr['originAddress']) > 1) {
            foreach ($fedexSmpkgArr['originAddress'] as $wh) {
                $whIDs[] = $wh['locationId'];
            }
            if (count(array_unique($whIDs)) > 1) {
                foreach ($fedexSmpkgArr['originAddress'] as $id => $wh) {
                    if (isset($wh['InstorPickupLocalDelivery'])) {
                        $fedexSmpkgArr['originAddress'][$id]['InstorPickupLocalDelivery'] = [];
                    }
                }
            }
        }
        $carriers = $this->registry->registry('enitureCarriers');
        $smartPost = $this->getConfigData('FedExSmartPost');
        if ($this->registry->registry('fedexSmartPost') === null) {
            $this->registry->register('fedexSmartPost', $smartPost);
        }
        $carriers['fedexSmall'] = $fedexSmpkgArr;
        $receiverAddress = $this->getReceiverData($request);

        $requestArr = [
            'apiVersion'        => '2.0',
            'platform'          => 'magento2',
            'binPackagingMultiCarrier' => $this->binPackSuspend(),

            'autoResidentials' => $this->autoResidentialDelivery(),
            'liftGateWithAutoResidentials' => $this->registry->registry('radForLiftgate'),
            'FedexOneRatePricing' => $this->FedexOneRatePricing,
            'FedexSmartPostPricing' => 0, //$smartPost,

            'requestKey'        => $cart->getQuote()->getId(),
            'carriers'          => $carriers,
            'receiverAddress'   => $receiverAddress,
            'commdityDetails'   => $itemsArr
        ];

        if ($this->moduleManager->isEnabled('Eniture_StandardBoxSizes')) {
            $binsData = $this->getSavedBins();
            $requestArr = array_merge($requestArr, isset($binsData) ? $binsData : []);
        }

        return  $requestArr;
    }

    /**
     * @return string
     */
    public function binPackSuspend()
    {
        $return = "0";
        if ($this->moduleManager->isEnabled('Eniture_StandardBoxSizes')) {
            $return = $this->scopeConfig->getValue("binPackaging/suspend/value", \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == "no" ? "1" : "0";
        }
        return $return;
    }

    /**
     * @return int
     */
    public function autoResidentialDelivery()
    {
        $autoDetectResidential = 0;
        if ($this->moduleManager->isEnabled('Eniture_ResidentialAddressDetection')) {
            $suspndPath = "resaddressdetection/suspend/value";
            $autoResidential = $this->scopeConfig->getValue($suspndPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if ($autoResidential != null && $autoResidential == 'no') {
                $autoDetectResidential = 1;
            }
        }
        if ($this->registry->registry('autoDetectResidential') === null) {
            $this->registry->register('autoDetectResidential', $autoDetectResidential);
        }

        return $autoDetectResidential ;
    }

    public function setValuesInRequest()
    {
        $domesticServList               = $this->getConfigData('FedExDomesticServices');
        $domesticServices               = empty($domesticServList) ? [] : explode(',', $domesticServList);
        $oneRateChecked                 = $this->getOneRateServices();
        $internationalServicesLength    = $this->getServiceOptionsLength('FedExInternationalServices');
        $oneRateServicesLength          = $this->getServiceOptionsLength('FedExOneRateServices');
        $boxSizeChecked                 = $this->getSavedBins();

        if ($oneRateServicesLength || $oneRateChecked) {
            $this->FedexOneRatePricing = '1' ;
            if ($oneRateServicesLength) {
                //set one rate pricing = 1
                $this->oneRatePricing = '1' ;
            }
            // $homeGround = isset($domesticServices[0]) ? $domesticServices[0] : '';
            // if ($boxSizeChecked && ($homeGround == 'GROUND_HOME_DELIVERY' || $homeGround == 'FEDEX_GROUND')) {
            //  // set home ground pricing = 1
            //     $this->homeGroundPricing = '1';
            // }

            foreach ($domesticServices as $key => $data) {
                if ($data == 'GROUND_HOME_DELIVERY' || $data == 'FEDEX_GROUND') {
                    // set home ground pricing = 1
                    $this->homeGroundPricing = '1';
                }

                if (($data == 'GROUND_HOME_DELIVERY') || ($data == 'FEDEX_GROUND')) {
                    unset($domesticServices[$key]);
                }
            }

            if (($internationalServicesLength || (!empty($domesticServices)))) {
                $this->airServicesPricing = '1' ;
            }
        }

        if ($this->registry->registry('FedexOneRatePricing') === null) {
            $this->registry->register('FedexOneRatePricing', $this->FedexOneRatePricing);
        }
    }

    /**
     * @param type $services
     * @return string
     */
    public function getServiceOptionsLength($services)
    {
        $servConfData = $this->getConfigData($services);
        if(empty($servConfData)){
            return 0;
        }else{
            return strlen($servConfData);
        }
    }

    /**
     * @return array
     */
    public function getOneRateServices()
    {
        $checked = [];
        if ($this->moduleManager->isEnabled('Eniture_StandardBoxSizes')) {
            $boxsizeHelper = $this->dataHelper->getBoxHelper('helper');
            $checked = $boxsizeHelper->getEnabledOneRateServices();
        }

        return $checked;
    }


    public function getSavedBins()
    {
        $savedBins = [];
        if ($this->moduleManager->isEnabled('Eniture_StandardBoxSizes')) {
            $boxSizeHelper = $this->dataHelper->getBoxHelper('helper');
            $savedBins = $boxSizeHelper->fillBoxingData();
        }
        return $savedBins;
    }

    /**
     * This function returns carriers array if have not empty origin address
     * @return array
     */
    public function getCarriersArray()
    {
        $carriersArr = $this->registry->registry('enitureCarriers');
        $newCarriersArr = [];
        foreach ($carriersArr as $carrkey => $carrArr) {
            $notHaveEmptyOrigin = true;
            foreach ($carrArr['originAddress'] as $key => $value) {
                if (empty($value['senderZip'])) {
                    $notHaveEmptyOrigin = false;
                }
            }
            if ($notHaveEmptyOrigin) {
                $newCarriersArr[$carrkey] = $carrArr;
            }
        }

        return $newCarriersArr;
    }

    /**
     * function that returns API array
     * @return array
     */
    public function getApiInfoArr($country, $origin)
    {
        $this->setValuesInRequest();

        if ($this->autoResidentialDelivery()) {
            $residential = 'off';
        } else {
            $residential = ($this->getConfigData('residentialDlvry'))?'on':'off';
        }
        $this->serviceType = $this->getServiceType($country, $origin);

        if ($this->registry->registry('fedexServiceType') === null) {
            $this->registry->register('fedexServiceType', $this->serviceType);
        }

        $smartPostData = ($this->getConfigData('FedExSmartPost')) ?
            ['hubId' => $this->getConfigData('hubId'), 'indicia' => 'PARCEL_SELECT'] : [];

        $endPoint = $this->getConfigData('fedexEndPoint');

        $apiArray = [
            'prefferedCurrency' => $this->registry->registry('baseCurrency'),
            'includeDeclaredValue' => $this->registry->registry('en_insurance'),
            'shipmentDate'  =>  date("d/m/y"),
            'pkgType' => '00',
            'residentialDelivery'   => $residential,
            'saturdayDelivery'      => 'on',
            'oneRatePricing'        => $this->oneRatePricing,
            'airServicesPricing'    => $this->airServicesPricing,
            'homeGroundPricing'     => $this->homeGroundPricing,
            'smartPostData'         => $smartPostData,
        ];

        if(empty($endPoint) || $endPoint == '1'){
            $apiArray['MeterNumber'] = $this->getConfigData('MeterNumber') ?? '';
            $apiArray['password'] = $this->getConfigData('ProdutionPassword') ?? '';
            $apiArray['key'] = $this->getConfigData('AuthenticationKey') ?? '';
            $apiArray['AccountNumber'] = $this->getConfigData('AccountNumber') ?? '';
        }else{
            $apiArray['requestForNewAPI'] = '1';
            $apiArray['clientId'] = $this->getConfigData('fedexClientId') ?? '';
            $apiArray['clientSecret'] = $this->getConfigData('fedexClientSecret') ?? '';
            $apiArray['accountNumber'] = $this->getConfigData('AccountNumber') ?? '';
        }

        return  $apiArray;
    }

    /**
     * This function returns Services Array
     * @return array
     */
    public function getServices()
    {
        $domesticArr = $international = [];
        if ($this->serviceType == 'domestic' || $this->serviceType == 'both') {
            $domesticArr = [
                // Domestic Services //
                'fedex_small_pkg_3_Day_Select'            => $this->isServiceActive('12'),
                'fedex_small_pkg_Ground'                  => $this->isServiceActive('03'),
                'fedex_small_pkg_2nd_Day_Air'             => $this->isServiceActive('02'),
                'fedex_small_pkg_2nd_Day_Air_AM'          => $this->isServiceActive('59'),
                'fedex_small_pkg_Next_Day_Air'            => $this->isServiceActive('01'),
                'fedex_small_pkg_Next_Day_Air_Saver'      => $this->isServiceActive('13'),
                'fedex_small_pkg_Next_Day_Air_Early_AM'   => $this->isServiceActive('14')
            ];
        }

        if ($this->serviceType == 'international' || $this->serviceType == 'both') {
            $international = [
                //International Services //
                'fedex_small_pkg_Standard'                => $this->isServiceActive('11'),
                'fedex_small_pkg_Worldwide_Express'       => $this->isServiceActive('07'),
                'fedex_small_pkg_Worldwide_Express_Plus'  => $this->isServiceActive('54'),
                'fedex_small_pkg_Worldwide_Expedited'     => $this->isServiceActive('08'),
                'fedex_small_pkg_Saver'                   => $this->isServiceActive('65'),
            ];
        }

        $servicesArr = array_merge($domesticArr, $international);
        $servicesArr['fedex_small_pkg_aditional_handling'] = 'N';
        return $servicesArr;
    }

    /**
     * Function that returns weather this service is active or not
     * @param string $serviceId
     * @return string
     */
    public function isServiceActive($serviceId)
    {
        $domesticServList               = $this->getConfigData('FedExDomesticServices');
        $domesticServices               = empty($domesticServList) ? [] : explode(',', $domesticServList);

        $internationalServList          = $this->getConfigData('FedExInternationalServices');
        $internationalServices          = empty($internationalServList) ? [] : explode(',', $internationalServList);

        $servicesArray = array_merge($domesticServices, $internationalServices);

        if (in_array($serviceId, $servicesArray)) {
            return 'yes';
        } else {
            return 'N';
        }
    }

    /**
     * function return service data
     * @param $fieldId
     * @return string
     */
    public function getConfigData($fieldId)
    {
        $secThreeIds = [
            'residentialDlvry',
            'FedExDomesticServices',
            'FedExInternationalServices',
            'FedExOneRateServices',
            'FedExSmartPost'
        ];
        if (in_array($fieldId, $secThreeIds)) {
            $sectionId = 'fedexQuoteSetting';
            $groupId = 'third';
        } else {
            $sectionId = 'fedexconnsettings';
            $groupId = 'first';
        }
        $confPath = "$sectionId/$groupId/$fieldId";
        return $this->scopeConfig->getValue($confPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * This function returns Receiver Data Array
     * @param $request
     * @return array
     */
    public function getReceiverData($request)
    {
        $addressType = $this->scopeConfig->getValue("resaddressdetection/addressType/value", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $receiverDataArr = [
            'addressLine'           => $request->getDestStreet(),
            'receiverCity'          => $request->getDestCity(),
            'receiverState'         => $request->getDestRegionCode(),
            'receiverZip'           => preg_replace('/\s+/', '', $request->getDestPostcode()),
            'receiverCountryCode'   => $request->getDestCountryId(),
            'defaultRADAddressType' => $addressType ?? 'residential', //get value from RAD
        ];

        return  $receiverDataArr;
    }

    /**
     * @param type $destinationCountry
     * @param type $originArr
     * @return string
     */
    public function getServiceType($destinationCountry, $originArr)
    {
        $serviceType = '';
        foreach ($originArr as $key => $value) {
            if ($value['senderCountryCode'] == $destinationCountry && $serviceType == '') {
                $serviceType = 'domestic';
            } elseif ($value['senderCountryCode'] != $destinationCountry && $serviceType == '') {
                $serviceType = 'international';
            } elseif ($serviceType == 'domestic' || $serviceType == 'international') {
                if ($serviceType == 'domestic' && $value['senderCountryCode'] != $destinationCountry) {
                    $serviceType = 'both';
                } elseif ($serviceType == 'international' && $value['senderCountryCode'] == $destinationCountry) {
                    $serviceType = 'both';
                }
            }
        }
        return $serviceType;
    }
}
