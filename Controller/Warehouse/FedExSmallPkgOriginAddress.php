<?php
namespace Eniture\FedExSmallPackages\Controller\Warehouse;

use \Magento\Framework\App\Action\Action;

class FedExSmallPkgOriginAddress extends Action
{
	/**
         * class property that have google api url
         * @var string 
         */
        protected $_curlUrl = 'http://eniture.com/ws/addon/google-location.php';
        protected $_dataHelper;
        protected $_scopeConfig;

        /**
         * 
         * @param \Magento\Framework\App\Action\Context $context
         * @param \Eniture\FedExSmallPackages\Helper\Data $dataHelper
         * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
         */

        public function __construct(
                \Magento\Framework\App\Action\Context $context,
                \Eniture\FedExSmallPackages\Helper\Data $dataHelper,
                \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
        ) {
            $this->_dataHelper  = $dataHelper;
            $this->_scopeConfig = $scopeConfig;
            parent::__construct($context);
        }
        
        /**
         * @return string
         */
	function execute()
        {
            foreach ($this->getRequest()->getPostValue() as $key => $post){
                $data[$key] = filter_var( $post, FILTER_SANITIZE_STRING );
            }
            
            $originZip = isset( $data['origin_zip'] ) ? str_replace(' ', '', $data['origin_zip']) : '';
        
            if($originZip){
                $mapResult = $this->googleApiCurl( $originZip, $this->_curlUrl );
                $error = $this->errorChecking($mapResult);

                if (empty($error)) {
                    $addressArray = $this->addressArray( $mapResult );
                }else {
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
    public function googleApiCurl($originZip, $curlUrl) {
        $licnsKey = $this->_scopeConfig->getValue('carriers/fedexConnectionSettings/licnsKey');
        $post = array(
            'acessLevel'        => 'address',
            'address'           => $originZip,
            'eniureLicenceKey'  => $licnsKey,
            'ServerName'        => $_SERVER['SERVER_NAME'],
        );
        
        $response = $this->_dataHelper->fedexSmpkgSendCurlRequest($curlUrl, $post, true);
        return $response;
    }
    
    /**
     * Check If Error
     * @param $mapResult
     * @return array
     */
    public function errorChecking($mapResult) {
        $error = array();
        if( isset( $mapResult['error'] ) && !empty( $mapResult['error'] ) ) {
            echo json_encode(array( 'error' => $mapResult['error'] ) );
            exit;
        }

        if ( isset($mapResult['results']) && count( $mapResult['results'] ) == 0 ) {
            echo json_encode(array('result' => 'false'));
            exit;
        }
        
        return $error;
    }
    
    /**
     * Calculate Address
     * @param $mapResult
     * @return array
     */
    public function addressArray($mapResult) {
        
        $city                   = "";
        $state                  = "";
        $country                = "";
        $first_city             = "";
        $city_option            = "";
        $address_type           = "";
        $city_name              = "";
        $postcode_localities    = 0;
        
        $arrComponents = $mapResult['results'][0]['address_components'];
        $checkPostcodeLocalities = (isset($mapResult['results'][0]['postcode_localities']))?$mapResult['results'][0]['postcode_localities']:'';
        
        if ( $checkPostcodeLocalities ) {
            foreach ( $mapResult['results'][0]['postcode_localities'] as $index => $component ) {
                $first_city = ( $index == 0 ) ? $component : $first_city;
                $city_option .= '<option value="' . trim( $component ) . ' "> ' . $component . ' </option>';
            }

            $city = '<select id="' . $address_type . '_city" class="city-multiselect warehouse_multi_city select purolator_small_multi_state city_select_css" name="' . $address_type . '_city" aria-required="true" aria-invalid="false">
                ' . $city_option . '</select>';
            $postcode_localities = 1;
        } elseif ( $arrComponents ) {
            foreach ( $arrComponents as $index => $component ) {
                $type = $component['types'][0];
                if ( $city == "" && ( $type == "sublocality_level_1" || $type == "locality" ) ) {
                    $city_name = trim( $component['long_name'] );
                }
            }
        }
        
        if ($arrComponents) {
            foreach ($arrComponents as $index => $state_app) {
                $type = $state_app['types'][0];
                if ($state == "" && ( $type == "administrative_area_level_1" )) {
                    $state_name = trim($state_app['short_name']);
                    $state = $state_name;
                }

                if ($country == "" && ( $type == "country" )) {
                    $country_name = trim($state_app['short_name']);
                    $country = $country_name;
                }
            }
        }
        
        return $this->originAddressArray($first_city, $city_name, $city, $state, $this->getCountryCode($country), $postcode_localities);
    }
    
    /**
     * This function returns address array
     * @param $first_city
     * @param $city_name
     * @param $city
     * @param $state
     * @param $country
     * @param $postcode_localities
     * @return Array
     */
    public function originAddressArray($first_city, $city_name, $city, $state, $country, $postcode_localities) {
        return array(
            'first_city'            => $first_city, 
            'city'                  => $city_name, 
            'city_option'           => $city, 
            'state'                 => $state, 
            'country'               => $country, 
            'postcode_localities'   => $postcode_localities
        );
    }

    /**
     * 
     * @param type $country
     * @return string
     */
    public function getCountryCode( $country ) { 
	$countryCode = $country; 
	$country = strtolower( $country ); 
	switch ( $country ) { 
		case 'usa': $countryCode = 'US'; 
		break; 
		case 'can': $countryCode = 'CA'; 
		break; 
		case 'ca': $countryCode = 'CA'; 
		break; 
		case 'cn': $countryCode = 'CA'; 
		break; 
		default: $countryCode = strtoupper( $country ); 
		break; 
	} 
	return $countryCode; 
	}
    }
