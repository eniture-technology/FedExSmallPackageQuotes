<?php
/**
 * @category   Shipping
 * @package    Eniture_FedExSmallPackages
 * @author     Iqbal: <iqbal@alignpx.com>
 * @website    http://ess.eniture.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Eniture\FedExSmallPackages\Model\Carrier;
 
use Magento\Quote\Model\Quote\Address\RateRequest;
 
class FedExSmpkgShipping extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    protected $_code = 'ENFedExSmpkg';
 
    protected $_isFixed = true; /** @todo testing **/
 
    protected $_rateResultFactory;
 
    protected $_rateMethodFactory;
    
    protected $_scopeConfig;
    
    protected $_dataHelper;
    
    protected $_registry;
    
    protected $_moduleManager;
    
    protected $_qty;
    
    protected $session;
    
    protected $_productloader;
    
    protected $_mageVersion;
    
    protected $_resourceConnection;
    
    protected $_objectManager;
 
    /**
     * 
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Eniture\FedExSmallPackages\Helper\Data $dataHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Catalog\Model\ProductFactory $productloader
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     * @param \Eniture\FedExSmallPackages\Model\Carrier\FedExSmpkgAdminConfiguration $fedExAdminConfig
     * @param \Eniture\FedExSmallPackages\Model\Carrier\FedExSmpkgShipmentPackage $fedExShipPkg
     * @param \Eniture\FedExSmallPackages\Model\Carrier\FedExSmpkgGenerateRequestData $fedExReqData
     * @param \Eniture\FedExSmallPackages\Model\Carrier\FedExSmallSetCarriersGlobaly $fedExSetGlobalCarrier
     * @param \Eniture\FedExSmallPackages\Model\Carrier\FedExSmpkgManageAllQuotes $fedexMangQuotes
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Eniture\FedExSmallPackages\Helper\Data $dataHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Catalog\Model\ProductFactory $productloader,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Eniture\FedExSmallPackages\Model\Carrier\FedExSmpkgAdminConfiguration $fedExAdminConfig,
        \Eniture\FedExSmallPackages\Model\Carrier\FedExSmpkgShipmentPackage $fedExShipPkg,
        \Eniture\FedExSmallPackages\Model\Carrier\FedExSmpkgGenerateRequestData $fedExReqData,
        \Eniture\FedExSmallPackages\Model\Carrier\FedExSmallSetCarriersGlobaly $fedExSetGlobalCarrier,
        \Eniture\FedExSmallPackages\Model\Carrier\FedExSmpkgManageAllQuotes $fedexMangQuotes,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_scopeConfig       = $scopeConfig;
        $this->_checkoutSession   = $checkoutSession;
        $this->_cart              = $cart;
        $this->_regionFactory     = $regionFactory;
        $this->_dataHelper        = $dataHelper;
        $this->_registry          = $registry;
        $this->_moduleManager     = $moduleManager;
        $this->_urlInterface      = $urlInterface;
        $this->session            = $session;
        $this->_productloader     = $productloader;
        $this->_mageVersion        = $productMetadata->getVersion();
        $this->_resourceConnection = $resource;
        $this->_objectManager       = $objectmanager;
        $this->_fedExAdminConfig       = $fedExAdminConfig;
        $this->_fedExShipPkg       = $fedExShipPkg;
        $this->_fedExReqData       = $fedExReqData;
        $this->_fedExSetGlobalCarrier       = $fedExSetGlobalCarrier;
        $this->_fedexMangQuotes       = $fedexMangQuotes;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }
 
    /**
     * 
     * @param RateRequest $request
     * @return boolean
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->_scopeConfig->getValue('carriers/fedexConnectionSettings/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return false;
        }
        
        if(empty($request->getDestPostcode()) || empty($request->getDestCountryId())){
            return false;
        }
        
        // Admin Configuration Class call
        $this->_fedExAdminConfig->_init($this->_scopeConfig, $this->_registry);
        
        $ItemsList          = $request->getAllItems();
        $receiverZipCode    = $request->getDestPostcode();

        $package            = $this->GetFedExSmpkgShipmentPackage($ItemsList,$receiverZipCode,$request);
        
        //Generate Request Data Class Initialization
        $this->_fedExReqData->_init($this->_scopeConfig, $this->_registry, $this->_dataHelper, $this->_moduleManager, $this->_objectManager);
        $fedexSmpkgArr        = $this->_fedExReqData->generateFedExSmpkgArray($request, $package['origin'], $this->_objectManager);

        $fedexSmpkgArr['originAddress'] = $package['origin'];

        $this->_fedExSetGlobalCarrier->_init($this->_dataHelper);
        $resp = $this->_fedExSetGlobalCarrier->manageCarriersGlobaly($fedexSmpkgArr, $this->_registry);

        $getQuotesFromSession = $this->quotesFromSession();
        if(null !== $getQuotesFromSession){
            return $getQuotesFromSession;
        }
        
        if(!$resp){
            return FALSE;
        }
        
        $requestArr = $this->_fedExReqData->generateRequestArray($request,$fedexSmpkgArr,$package['items'], $this->_dataHelper, $this->_objectManager, $this->_cart);

        if(empty($requestArr)){
            return FALSE;
        }

        $quotes = $this->_dataHelper->fedexSmpkgSendCurlRequest('https://eniture.com/ws/v2.0/index.php',$requestArr);

        $this->_fedexMangQuotes->_init($quotes, $this->_dataHelper, $this->_scopeConfig, $this->_registry, $this->_moduleManager, $this->_objectManager);
        $quotesResult = $this->_fedexMangQuotes->getQuotesResultArr($request);
        
        $this->session->setEnShippingQuotes($quotesResult);
        
        $fedexSmpkgQuotes = (!empty($quotesResult))?$this->setCarrierRates($quotesResult):'';
        return $fedexSmpkgQuotes;
    }
    
    /**
     * 
     * @return type
     */
    function quotesFromSession() {
        $currentAction = $this->_urlInterface->getCurrentUrl();
        $currentAction = strtolower($currentAction);
        if(strpos($currentAction, 'shipping-information') !== false || strpos($currentAction, 'payment-information') !== false){
            $availableSessionQuotes = $this->session->getEnShippingQuotes(); // FROM SESSSION
            $availableQuotes = (!empty($availableSessionQuotes))?$this->setCarrierRates($availableSessionQuotes):null;
        }else{
            $availableQuotes = NULL;
        }
        return $availableQuotes;
    }
    
    /**
     * 
     * @return type
     */
    function getAllowedMethods() {
        $allowed = explode(',', $this->getConfigData('allowed_methods'));
        $arr = [];
        foreach ($allowed as $k) {
            $arr[$k] = $this->getCode('method', $k);
        }

        return $arr;
    }
    
    /**
     * Get configuration data of carrier
     *
     * @param string $type
     * @param string $code
     * @return array|false
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getCode($type, $code = '')
    {
        $codes = [
            'method' => $this->_dataHelper->fedexCarriersWithTitle(),
        ];
        
        if (!isset($codes[$type])) {
            return false;
        } elseif ('' === $code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            return false;
        } else {
            return $codes[$type][$code];
        }
    }
    
    /**
     * This function returns package array
     * @param $items
     * @param $receiverZipCode
     * @param $request
     * @return array
     */
    public function GetFedExSmpkgShipmentPackage($items, $receiverZipCode,$request) {
        
        $this->_fedExShipPkg->_init($request, $this->_scopeConfig, $this->_dataHelper, $this->_productloader);
        
        $freightClass = '';
        
        $weightConfigExeedOpt = $this->_scopeConfig->getValue('fedexQuoteSetting/third/weightExeeds', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        foreach($items as $key => $item) {
            $locationId = 0;
            if($item->getRealProductType() == 'configurable'){
                $this->_qty = $item->getQty();     
            }
            if($item->getRealProductType() == 'simple'){
                
                $productQty = ( $this->_qty > 0 ) ? $this->_qty : $item->getQty();
                
                $_product       = $this->_productloader->create()->load($item->getProductId());
               
                $isEnableLtl    = $_product->getData('en_ltl_check');

                $lineItemClass  = $_product->getData('en_freight_class');
                
                if ( ($isEnableLtl) || ( $_product->getWeight() > 150 && $weightConfigExeedOpt) ) {
                    $freightClass = 'ltl';
                }else{
                    $freightClass = '';
                }
                
                switch ($lineItemClass) {
                    case 77:
                        $lineItemClass = 77.5;
                        break;
                    case 92:
                        $lineItemClass = 92.5;
                        break;
                    default:
                        break;
                }
                
                $originAddress  = $this->_fedExShipPkg->fedexSmpkgOriginAddress($_product, $receiverZipCode);
                
                $hazordousData[][$originAddress['senderZip']] = $this->setHazmatArray($_product);
                
                $package['origin'][$_product->getId()] = $originAddress;
                
                $orderWidget[$originAddress['senderZip']]['origin'] = $originAddress;
                
                $length = ( $this->_mageVersion < '2.2.5' ) ? $_product->getData('en_length') : $_product->getData('ts_dimensions_length');
                $width = ( $this->_mageVersion < '2.2.5' ) ? $_product->getData('en_width') : $_product->getData('ts_dimensions_width');
                $height = ( $this->_mageVersion < '2.2.5' ) ? $_product->getData('en_height') : $_product->getData('ts_dimensions_height');

                $lineItems = array(
                        'lineItemClass'          => ($lineItemClass == 'No Freight Class' || $lineItemClass == 'No') ? 0 : $lineItemClass,
                        'freightClass'           => $freightClass,
                        'lineItemId'             => $_product->getId(),
                        'lineItemName'           => $_product->getName(),
                        'piecesOfLineItem'       => $productQty,
                        'lineItemPrice'          => $_product->getPrice(),
                        'lineItemWeight'         => number_format($_product->getWeight(), 2, '.', ''),
                        'lineItemLength'         => number_format($length, 2, '.', ''),
                        'lineItemWidth'          => number_format($width, 2, '.', ''),
                        'lineItemHeight'         => number_format($height, 2, '.', ''),
                        'hazardousMaterial'      => ($_product->getData('en_hazmat'))?'Y':'N',
                        'shipBinAlone'           => $_product->getData('en_own_package'),
                        'vertical_rotation'      => $_product->getData('en_vertical_rotation'),
                      );

                $package['items'][$_product->getId()] = array_merge($lineItems);
                $orderWidget[$originAddress['senderZip']]['item'][] = $package['items'][$_product->getId()];
            }
        }
        
        $this->setDataInRegistry($package['origin'], $hazordousData, $orderWidget);
        
        return $package;
    }
    
    /**
     * 
     * @param type $_product
     * @return type
     */
    function setHazmatArray($_product) {
        $hazmat = $_product->getData('en_hazmat') ? 'isHazmat' : '';
        return array(
            'lineItemId'    => $_product->getId(),
            'isHazordous'   => !empty($hazmat) ? '1' : '0' ,
        );
    }
    
    /**
     * 
     * @param type $origin
     * @param type $hazordousData
     * @param type $setPackageDataForOrderDetail
     */
    function setDataInRegistry($origin, $hazordousData, $orderWidget) {
        // set order detail widget data
        if(is_null($this->_registry->registry('setPackageDataForOrderDetail'))){
            $this->_registry->register('setPackageDataForOrderDetail', $orderWidget);
        }
        
        // set hazardous data globally
        if(is_null($this->_registry->registry('hazardousShipment'))){
            $this->_registry->register('hazardousShipment', $hazordousData);
        }
        // set shipment origin globally for instore pickup and local delivery
        if(is_null($this->_registry->registry('shipmentOrigin'))){
            $this->_registry->register('shipmentOrigin', $origin);
        }
    }
    
    /**
     * string
     */
    public function fedexSmpkgGetConfigVal() {
        $this->_scopeConfig->getValue('dev/debug/template_hints', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
        
    /**
     * 
     * @param type $quotes
     * @return type
     */
    function setCarrierRates($quotes) {
        $carrersArray   = $this->_registry->registry('enitureCarrierCodes');
        $carrersTitle   = $this->_registry->registry('enitureCarrierTitle');
        
        $result = $this->_rateResultFactory->create();

        foreach ($quotes as $carrierkey => $quote) {
            foreach ($quote as $key => $carreir) {
                $method = $this->_rateMethodFactory->create();
                $carrierCode    = (isset($carrersTitle[$carrierkey]))? $carrersTitle[$carrierkey] : $this->_code;
                $carrierTitle   = (isset($carrersArray[$carrierkey]))? $carrersArray[$carrierkey] : $this->getConfigData('title');
                $method->setCarrierTitle($carrierCode);
                $method->setCarrier($carrierTitle);
                $method->setMethod($carreir['code']);
                $method->setMethodTitle($carreir['title']);
                $method->setPrice($carreir['rate']);

                $result->append($method);
            }
        }
        
        return $result;
    }
    
}