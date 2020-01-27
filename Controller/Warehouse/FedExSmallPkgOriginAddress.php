<?php
namespace Eniture\FedExSmallPackageQuotes\Controller\Warehouse;

use \Magento\Framework\App\Action\Action;

/**
 * Class FedExSmallPkgOriginAddress
 * @package Eniture\FedExSmallPackageQuotes\Controller\Warehouse
 */
class FedExSmallPkgOriginAddress extends Action
{

    /**
     * @var \Eniture\FedExSmallPackageQuotes\Helper\Data
     */
    public $dataHelper;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;
    /**
     * @var
     */
    public $request;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Eniture\FedExSmallPackageQuotes\Helper\Data $dataHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Eniture\FedExSmallPackageQuotes\Helper\Data $dataHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->dataHelper  = $dataHelper;
        $this->scopeConfig = $scopeConfig;
        $this->request = $context->getRequest();
        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function execute()
    {
        foreach ($this->getRequest()->getPostValue() as $key => $post) {
            $data[$key] = filter_var($post, FILTER_SANITIZE_STRING);
        }

        $originZip = isset($data['origin_zip']) ? str_replace(' ', '', $data['origin_zip']) : '';

        if ($originZip) {
            $mapResult = $this->googleApiCurl($originZip, $this->dataHelper->wsHittingUrls('getAddress'));
            $error = $this->errorChecking($mapResult);

            if (empty($error)) {
                $addressArray = $this->addressArray($mapResult);
            } else {
                $addressArray = $error;
            }

            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(json_encode($addressArray));
        }
    }

    /**
     * Google API Curl
     * @param $originZip
     * @param $curlUrl
     * @return array
     */
    public function googleApiCurl($originZip, $curlUrl)
    {
        $licnsKey = $this->scopeConfig->getValue('fedexconnsettings/first/licnsKey');
        $post = [
            'acessLevel'        => 'address',
            'address'           => $originZip,
            'eniureLicenceKey'  => $licnsKey,
            'ServerName'        => $this->request->getServer('SERVER_NAME'),
        ];
        
        $response = $this->dataHelper->fedexSmpkgSendCurlRequest($curlUrl, $post, true);
        return $response;
    }
    
    /**
     * Check If Error
     * @param $mapResult
     */
    public function errorChecking($mapResult)
    {
        $error = [];
        if (isset($mapResult['error']) && !empty($mapResult['error'])) {
            $error = ['error' => 'true',
                'msg' => $mapResult['error']];
        }
        
        if (isset($map_result['results'], $map_result['status']) && (empty($map_result['results'])) && ($map_result['status'] == "ZERO_RESULTS")) {
            $error = ['error' => 'true'];
        }
        
        if (empty($mapResult)) {
            $error = ['error' => 'true'];
        }

        if (isset($mapResult['results']) && count($mapResult['results']) == 0) {
            $error = ['error' => 'false'];
        }
        return $error;
    }
    
    /**
     * Calculate Address
     * @param $mapResult
     * @return array
     */
    public function addressArray($mapResult)
    {
        $city = $state = $country = $fstcity = $city_option = $cityName = "";
        $zipcodeLocalities    = 0;
        
        $arrComponents = isset($mapResult['results'][0]) ?
                $mapResult['results'][0]['address_components'] : '';
        $checkPostcodeLocalities = (isset($mapResult['results'][0]['postcode_localities'])) ?
                $mapResult['results'][0]['postcode_localities'] : '';
        
        if ($checkPostcodeLocalities) {
            foreach ($mapResult['results'][0]['postcode_localities'] as $index => $component) {
                $fstcity = ($index == 0) ? $component : $fstcity;
                $city_option .= '<option value="' . trim($component) . ' "> ' . $component . ' </option>';
            }

            $city = '<select id="_city" class="city-multiselect city_select_css" name="_city">
                ' . $city_option . '</select>';
            $zipcodeLocalities = 1;
        } elseif ($arrComponents) {
            foreach ($arrComponents as $index => $component) {
                $type = $component['types'][0];
                if ($city == "" && ( $type == "sublocality_level_1" || $type == "locality")) {
                    $cityName = trim($component['long_name']);
                }
            }
        }
        
        if ($arrComponents) {
            foreach ($arrComponents as $index => $state_app) {
                $type = $state_app['types'][0];
                if ($state == "" && ($type == "administrative_area_level_1")) {
                    $state_name = trim($state_app['short_name']);
                    $state = $state_name;
                }

                if ($country == "" && ($type == "country")) {
                    $country_name = trim($state_app['short_name']);
                    $country = $country_name;
                }
            }
        }
        
        return $this->originAddressArray(
            $fstcity,
            $cityName,
            $city,
            $state,
            $this->getCountryCode($country),
            $zipcodeLocalities
        );
    }
    
    /**
     * This function returns address array
     * @param $fstcity
     * @param $cityName
     * @param $city
     * @param $state
     * @param $country
     * @param $zipcodeLocalities
     * @return Array
     */
    public function originAddressArray($fstcity, $cityName, $city, $state, $country, $zipcodeLocalities)
    {
        return [
            'first_city'            => $fstcity,
            'city'                  => $cityName,
            'city_option'           => $city,
            'state'                 => $state,
            'country'               => $country,
            'postcode_localities'   => $zipcodeLocalities
        ];
    }

    /**
     * @param type $country
     * @return string
     */
    public function getCountryCode($country)
    {
        $country = strtoupper($country);
        $countryCode = $country;
        switch ($country) {
            case 'USA':
                $countryCode = 'US';
                break;
            case 'CAN':
                $countryCode = 'CA';
                break;
            case 'CA':
                $countryCode = 'CA';
                break;
            case 'CN':
                $countryCode = 'CA';
                break;
            default:
                break;
        }
        return $countryCode;
    }
}
