<?php
namespace Eniture\FedExSmallPackages\Model\Carrier;

class FedExSmpkgShipmentPackage
{

    /**
     * @param type $request
     * @param type $scopeConfig
     * @param type $dataHelper
     * @param type $productloader
     */
    public function _init(
        $request,
        $scopeConfig,
        $dataHelper,
        $productloader,
        $httpRequest
    ) {
        $this->request          = $request;
        $this->scopeConfig      = $scopeConfig;
        $this->dataHelper       = $dataHelper;
        $this->productloader    = $productloader;
        $this->httpRequest      = $httpRequest;
    }

    /**
     * fuction that returns address array
     * @param $_product
     * @param $receiverZipCode
     * @return array
     */
    public function fedexSmpkgOriginAddress($_product, $receiverZipCode)
    {
        $whQuery        = $this->dataHelper->fetchWarehouseSecData('warehouse');
        $enableDropship = $_product->getData('en_dropship');

        if ($enableDropship) {
            $dropshipID = $_product->getData('en_dropship_location');
            $originList = $this->dataHelper->fetchDropshipWithID($dropshipID);
            
            if (!$originList) {
                $product = $this->productloader->create()->load($_product->getEntityId());
                $product->setData('en_dropship', 0)->getResource()->saveAttribute($product, 'en_dropship');
                $origin    = $whQuery;
            } else {
                $origin = $originList;
            }
        } else {
            $origin    = $whQuery;
        }
        
        if (!empty($origin)) {
            return $this->multiWarehouse($origin, $receiverZipCode);
        }
    }
    
    /**
     * This funtion returns the closest warehouse if multiple warehouse exists otherwise
     * return single.
     * @param $warehous_list
     * @param $receiverZipCode
     * @return array
     */
    public function multiWarehouse($warehousList, $receiverZipCode)
    {
        $packageArray = $this->dataHelper->fedexSmallPlanName('ENFedExSmpkg');
        $planNumber = $packageArray['planNumber'];
        if (!empty($warehousList)) {
            $distance_array = [];
            if (count($warehousList) == 1) {
                $warehousList = reset($warehousList);
                return $this->fedexSmpkgOriginArray($warehousList, $receiverZipCode, $planNumber);
            } elseif (count($warehousList) > 1 && ($planNumber == 0 || $planNumber == 1)) {
                return $this->fedexSmpkgOriginArray($warehousList[0], $receiverZipCode, $planNumber);
            }

            $response  = $this->fedexSmpkgAddress($warehousList);
            if (!empty($response)) {
                $originWithMinDist = (isset($response->origin_with_min_dist)
                        && !empty($response->origin_with_min_dist)) ?
                        (array)$response->origin_with_min_dist: [];
                return $this->fedexSmpkgOriginArray($originWithMinDist, $receiverZipCode, $planNumber);
            }
        }
    }
    
    /**
     * fuction that returns shortest origin managed array
     * @param array $shortOrigin
     * @return array
     */
    public function fedexSmpkgOriginArray($shortOrigin, $receiverZipCode, $planNumber)
    {
        if (isset($shortOrigin) && count($shortOrigin) > 1) {
            $origin = isset($shortOrigin['origin']) ? $shortOrigin['origin'] : $shortOrigin;
            $zip = isset($origin['zipcode']) ? $origin['zipcode'] : $origin['zip'];
            $city = $origin['city'];
            $state = $origin['state'];
            $country = ($origin['country'] == "CN") ? "CA" : $origin['country'];
            $location = isset($origin['location']) ? $origin['location'] : 'warehouse';
            $locationId = isset($shortOrigin['id']) ? $shortOrigin['id'] : $shortOrigin['warehouse_id'];
            return [
                    'location' => $location,
                    'locationId' => $locationId,
                    'senderZip' => $zip,
                    'senderCity' => $city,
                    'senderState' => $state,
                    'senderCountryCode' => $country,
                    'InstorPickupLocalDelivery' => $planNumber==3 ? $this->instorePickupLdData($shortOrigin, $receiverZipCode) : '',
                ];
        }
    }
    
    /**
     * This function returns responce from google api
     * @param $originAddress
     * @param $accessLevel
     * @return array
     */
    public function fedexSmpkgAddress($originAddress)
    {
        $originAddress = $this->changeWarehouseIdKey($originAddress);
        $post = [
            'acessLevel'        => 'MultiDistance',
            'address'           => $originAddress,
            'originAddresses'   => (isset($originAddress)) ? $originAddress : "",
            'destinationAddress'=> [
                'city'      => $this->request->getDestCity(),
                'state'     => $this->request->getDestRegionCode(),
                'zip'       => $this->request->getDestPostcode(),
                'country'   => $this->request->getDestCountryId(),
            ],
            'ServerName'        => $this->httpRequest->getServer('SERVER_NAME'),
            'eniureLicenceKey'  => $this->scopeConfig->getValue(
                'carriers/ENFedExSmpkg/licnsKey',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ),
        ];
        $url = $this->dataHelper->wsHittingUrls('multiDistance');
        $curlRes = $this->dataHelper->fedexSmpkgSendCurlRequest($url, $post);
        if (!isset($curlRes->error)) {
            $response = $curlRes;
        } else {
            $response = [];
        }
        return $response;
    }
    /**
     * @param type $origins
     * @return type
     */
    public function changeWarehouseIdKey($origins)
    {
        foreach ($origins as $key => $origin) {
            if ($origin['warehouse_id']) {
                $origin['id'] = $origin['warehouse_id'];
                unset($origin['warehouse_id']);
            }
            $result[$key] = $origin;
        }
        
        return $result;
    }
    
    /**
     * @param type $shortOrigin
     * @param type $receiverZipCode
     * @return type
     */
    public function instorePickupLdData($shortOrigin, $receiverZipCode)
    {
        $array = [];
        if (!empty($shortOrigin['in_store'])) {
            $instore = json_decode($shortOrigin['in_store']);
            if ($instore->enable_store_pickup == 1) {
                $array['inStore'] = $instore;
                $array['inStorePickup'] = [
                    'addressWithInMiles' =>$instore->miles_store_pickup ,
                    'postalCodeMatch'    =>in_array($receiverZipCode, explode(',', $instore->match_postal_store_pickup))?1:0,
                ];
            }
        }

        if (!empty($shortOrigin['local_delivery'])) {
            $locDel = json_decode($shortOrigin['local_delivery']);
            if ($locDel->enable_local_delivery == 1) {
                $array['locDel'] = $locDel;
                $array['localDelivery'] = [
                    'addressWithInMiles' => $locDel->miles_local_delivery,
                    'postalCodeMatch'    =>in_array($receiverZipCode, explode(',', $locDel->match_postal_local_delivery))?1:0,
                    'suppressOtherRates' =>$locDel->suppress_local_delivery,
                ];
            }
        }
        return $array;
    }
}
