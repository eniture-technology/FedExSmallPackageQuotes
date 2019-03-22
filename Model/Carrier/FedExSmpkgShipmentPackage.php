<?php
namespace Eniture\FedExSmallPackages\Model\Carrier;
class FedExSmpkgShipmentPackage {

    /**
     * 
     * @param type $request
     * @param type $scopeConfig
     * @param type $dataHelper
     * @param type $productloader
     */
    public function _init($request, $scopeConfig, $dataHelper, $productloader) {
        $this->request          = $request;
        $this->scopeConfig      = $scopeConfig;
        $this->_dataHelper      = $dataHelper;
        $this->_productloader   = $productloader;
    }

    /**
     * fuction that returns address array
     * @param $_product
     * @param $receiverZipCode
     * @return array
     */
    function fedexSmpkgOriginAddress($_product, $receiverZipCode) {
        $whQuery        = $this->_dataHelper->fetchWarehouseSecData('warehouse');
        $enableDropship = $_product->getData('en_dropship');   

        if( $enableDropship ){
            $dropshipID = $_product->getData('en_dropship_location');
            $originList = $this->_dataHelper->fetchDropshipWithID($dropshipID);
            
            if(!$originList){
                $product = $this->_productloader->create()->load($_product->getEntityId());
                $product->setData('en_dropship', 0)->getResource()->saveAttribute($product, 'en_dropship');
                $origin    = $whQuery;         
            }else{
                $origin = $originList;
            }
        }else{
            $origin    = $whQuery;
        }
        
        if(!empty($origin)){ return $this->multiWarehouse($origin, $receiverZipCode); }
    }
    
    /**
     * This funtion returns the closest warehouse if multiple warehouse exists otherwise
     * return single.
     * @param $warehous_list
     * @param $receiverZipCode
     * @return array
     */
    function multiWarehouse($warehousList, $receiverZipCode)
    {
        if(!empty($warehousList)){
            $distance_array = array();
            if (count($warehousList) == 1) {
                $warehousList = reset($warehousList);
                return $this->fedexSmpkgOriginArray($warehousList);
            }

            $response  = $this->fedexSmpkgAddress($warehousList);
            if(!empty($response)){
                $originWithMinDist = (isset($response->origin_with_min_dist) && !empty($response->origin_with_min_dist))?(array)$response->origin_with_min_dist:array();
                return $this->fedexSmpkgOriginArray($originWithMinDist);
            }
        }
    }
    
    /**
     * fuction that returns shortest origin managed array
     * @param array $shortOrigin
     * @return array
     */
    function fedexSmpkgOriginArray($shortOrigin) {
        if(isset($shortOrigin) && count($shortOrigin) > 1 ){
            $origin = isset($shortOrigin['origin']) ? $shortOrigin['origin'] : $shortOrigin;
            $zip = isset($origin['zipcode']) ? $origin['zipcode'] : $origin['zip'];
            $city = $origin['city'];
            $state = $origin['state'];
            $country = ($origin['country'] == "CN") ? "CA" : $origin['country'];
            $location = isset($origin['location']) ? $origin['location'] : 'warehouse';
            $locationId = isset($shortOrigin['id']) ? $shortOrigin['id'] : $shortOrigin['warehouse_id'];
            return array(
                        'location' => $location,
                        'locationId' => $locationId,
                        'senderZip' => $zip, 
                        'senderCity' => $city, 
                        'senderState' => $state, 
                        'senderCountryCode' => $country
                    );
        }
    }
    
    /**
     * This function returns responce from google api
     * @param $originAddress
     * @param $accessLevel
     * @return array
     */
    function fedexSmpkgAddress($originAddress) {
        $originAddress = $this->changeWarehouseIdKey($originAddress);
        $post = array(
            'acessLevel'        => 'MultiDistance',
            'address'           => $originAddress,
            'originAddresses'   => (isset($originAddress)) ? $originAddress : "",
            'destinationAddress'=> array(
                'city'      => $this->request->getDestCity(),
                'state'     => $this->request->getDestRegionCode(),
                'zip'       => $this->request->getDestPostcode(),
                'country'   => $this->request->getDestCountryId(),
            ),
            'ServerName'        => $_SERVER['SERVER_NAME'],
            'eniureLicenceKey'  => $this->scopeConfig->getValue('carriers/fedexConnectionSettings/licnsKey', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
        );
        
        $url = 'http://eniture.com/ws/addon/google-location.php';
        $curlRes = $this->_dataHelper->fedexSmpkgSendCurlRequest($url,$post);
        if(!isset($curlRes->error)){
            $response = $curlRes;
        }else{
            $response = array();
        }
        return $response;
    }
    /**
     * 
     * @param type $origins
     * @return type
     */
    function changeWarehouseIdKey( $origins ) {
        foreach ($origins as $key => $origin) {
            if($origin['warehouse_id']){
                $origin['id'] = $origin['warehouse_id'];
                unset($origin['warehouse_id']);
            }
            $result[$key] = $origin;
        }
        
        return $result;
    }
}
