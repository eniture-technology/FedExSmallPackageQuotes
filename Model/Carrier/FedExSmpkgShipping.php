<?php
/**
 * @category   Shipping
 * @package    Eniture_FedExSmallPackageQuotes
 * @author     Iqbal: <iqbal@alignpx.com>
 * @website    http://ess.eniture.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Eniture\FedExSmallPackageQuotes\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;

/**
 * Class FedExSmpkgShipping
 * @package Eniture\FedExSmallPackageQuotes\Model\Carrier
 */
class FedExSmpkgShipping extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var string
     */
    public $code = 'ENFedExSmpkg';

    /**
     * @var bool
     */
    public $isFixed = true;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    public $rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    public $rateMethodFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var \Eniture\FedExSmallPackageQuotes\Helper\Data
     */
    public $dataHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    public $moduleManager;

    /**
     * @var
     */
    public $qty = 0;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    public $session;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    public $productloader;

    /**
     * @var
     */
    public $mageVersion;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    public $cart;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    public $urlInterface;
    /**
     * @var FedExSmpkgAdminConfiguration
     */
    public $fedExAdminConfig;
    /**
     * @var FedExSmpkgShipmentPackage
     */
    public $fedExShipPkg;
    /**
     * @var FedExSmpkgGenerateRequestData
     */
    public $fedExReqData;
    /**
     * @var FedExSmallSetCarriersGlobaly
     */
    public $fedExSetGlobalCarrier;
    /**
     * @var FedExSmpkgManageAllQuotes
     */
    public $fedexMangQuotes;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    public $httpRequest;
    /**
     * @var bool
     */
    private $freeShipping = false;


    /**
     * FedExSmpkgShipping constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Eniture\FedExSmallPackageQuotes\Helper\Data $dataHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Catalog\Model\ProductFactory $productloader
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     * @param FedExSmpkgAdminConfiguration $fedExAdminConfig
     * @param FedExSmpkgShipmentPackage $fedExShipPkg
     * @param FedExSmpkgGenerateRequestData $fedExReqData
     * @param FedExSmallSetCarriersGlobaly $fedExSetGlobalCarrier
     * @param FedExSmpkgManageAllQuotes $fedexMangQuotes
     * @param \Magento\Framework\App\RequestInterface $httpRequest
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Eniture\FedExSmallPackageQuotes\Helper\Data $dataHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Catalog\Model\ProductFactory $productloader,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Eniture\FedExSmallPackageQuotes\Model\Carrier\FedExSmpkgAdminConfiguration $fedExAdminConfig,
        \Eniture\FedExSmallPackageQuotes\Model\Carrier\FedExSmpkgShipmentPackage $fedExShipPkg,
        \Eniture\FedExSmallPackageQuotes\Model\Carrier\FedExSmpkgGenerateRequestData $fedExReqData,
        \Eniture\FedExSmallPackageQuotes\Model\Carrier\FedExSmallSetCarriersGlobaly $fedExSetGlobalCarrier,
        \Eniture\FedExSmallPackageQuotes\Model\Carrier\FedExSmpkgManageAllQuotes $fedexMangQuotes,
        \Magento\Framework\App\RequestInterface $httpRequest,
        array $data = []
    ) {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->scopeConfig       = $scopeConfig;
        $this->cart              = $cart;
        $this->dataHelper        = $dataHelper;
        $this->registry          = $registry;
        $this->moduleManager     = $moduleManager;
        $this->urlInterface      = $urlInterface;
        $this->session            = $session;
        $this->productloader     = $productloader;
        $this->mageVersion        = $productMetadata->getVersion();
        $this->objectManager       = $objectmanager;
        $this->fedExAdminConfig       = $fedExAdminConfig;
        $this->fedExShipPkg       = $fedExShipPkg;
        $this->fedExReqData       = $fedExReqData;
        $this->fedExSetGlobalCarrier       = $fedExSetGlobalCarrier;
        $this->fedexMangQuotes       = $fedexMangQuotes;
        $this->httpRequest = $httpRequest;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @param RateRequest $request
     * @return boolean
     */
    public function collectRates(RateRequest $request)
    {
        $this->freeShipping = $request->getFreeShipping();

        if (!$this->scopeConfig->getValue(
            'carriers/ENFedExSmpkg/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )
        ) {
            return false;
        }

        if (empty($request->getDestPostcode()) || empty($request->getDestCountryId()) ||
            empty($request->getDestCity()) || empty($request->getDestRegionId())) {
            return false;
        }
        // set base currency
        if ($this->registry->registry('baseCurrency') === null) {
            $this->registry->register('baseCurrency', $this->dataHelper->getBaseCurrencyCode());
        }
        // Admin Configuration Class call
        $this->fedExAdminConfig->_init($this->scopeConfig, $this->registry);

        $ItemsList          = $request->getAllItems();
        $receiverZipCode    = $request->getDestPostcode();

        $package            = $this->getFedExSmpkgShipmentPackage($ItemsList, $receiverZipCode, $request);

        //Generate Request Data Class Initialization
        $this->fedExReqData->_init(
            $this->scopeConfig,
            $this->registry,
            $this->moduleManager,
            $this->dataHelper,
            $this->httpRequest
        );
        $fedexSmpkgArr = $this->fedExReqData->generateFedExSmpkgArray(
            $request,
            $package['origin']
        );

        $fedexSmpkgArr['originAddress'] = $package['origin'];

        $this->fedExSetGlobalCarrier->_init($this->dataHelper);
        $resp = $this->fedExSetGlobalCarrier->manageCarriersGlobaly($fedexSmpkgArr, $this->registry);
        $getQuotesFromSession = $this->quotesFromSession();
        if (null !== $getQuotesFromSession) {
            return $getQuotesFromSession;
        }

        if (!$resp) {
            return false;
        }

        $requestArr = $this->fedExReqData->generateRequestArray(
            $request,
            $fedexSmpkgArr,
            $package['items'],
            $this->cart
        );
        if (empty($requestArr)) {
            return false;
        }

        $url  = $this->dataHelper->wsHittingUrls('getQuotes');
        $quotes = $this->dataHelper->fedexSmpkgSendCurlRequest($url, $requestArr);

        // Debug point will print data if en_print_query=1
        if ($this->printQuery()) {
            $printData = ['url' => $url,
                'buildQuery' => http_build_query($requestArr),
                'request' => $requestArr,
                'quotes' => $quotes];
            print_r('<pre>');
            print_r($printData);
            print_r('</pre>');
            return;
        }

        $this->fedexMangQuotes->_init(
            $quotes,
            $this->dataHelper,
            $this->scopeConfig,
            $this->registry,
            $this->session,
            $this->moduleManager,
            $this->objectManager
        );
        $quotesResult = $this->fedexMangQuotes->getQuotesResultArr($request);
        $this->session->setEnShippingQuotes($quotesResult);

        $fedexSmpkgQuotes = (!empty($quotesResult)) ? $this->setCarrierRates($quotesResult) : '';

        return $fedexSmpkgQuotes;
    }

    /**
     * @return type
     */
    public function quotesFromSession()
    {
        $currentAction = $this->urlInterface->getCurrentUrl();
        $currentAction = strtolower($currentAction);
        if (strpos($currentAction, 'shipping-information') !== false
            || strpos($currentAction, 'payment-information') !== false) {
            $availableSessionQuotes = $this->session->getEnShippingQuotes();
            $availableQuotes = (!empty($availableSessionQuotes)) ?
                $this->setCarrierRates($availableSessionQuotes) : null;
        } else {
            $availableQuotes = null;
        }
        return $availableQuotes;
    }

    /**
     * @return type
     */
    public function getAllowedMethods()
    {
        $allowed = explode(',', $this->getConfigData('allowed_methods'));
        $arr = [];
        foreach ($allowed as $k) {
            $arr[$k] = $this->getCode('method', $k);
        }

        return $arr;
    }

    /**
     * Get configuration data of carrier
     * @param string $type
     * @param string $code
     * @return array|false
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getCode($type, $code = '')
    {
        $codes = [
            'method' => $this->dataHelper->fedexCarriersWithTitle(),
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
    public function getFedExSmpkgShipmentPackage($items, $receiverZipCode, $request)
    {
        $this->fedExShipPkg->_init(
            $request,
            $this->scopeConfig,
            $this->dataHelper,
            $this->productloader,
            $this->httpRequest
        );

        $freightClass = '';
        $orderWidget = $uniqueOrigins = [];

        $weightConfigExeedOpt = $this->scopeConfig->getValue(
            'fedexQuoteSetting/third/weightExeeds',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        foreach ($items as $key => $item) {


            $locationId = 0;
            $_product       = $this->productloader->create()->load($item->getProductId());
            $product_type =$item->getRealProductType() ?? $_product->getTypeId();

            if ($product_type == 'configurable') {
                $this->qty = $item->getQty();
            }
            if ($product_type == 'simple') {
                $productQty = ($this->qty > 0) ? $this->qty : $item->getQty();
                $this->qty = 0;

                $isEnableLtl    = $_product->getData('en_ltl_check');

                $lineItemClass  = $_product->getData('en_freight_class');

                if (($isEnableLtl) || ($_product->getWeight() > 150 && $weightConfigExeedOpt)) {
                    $freightClass = 'ltl';
                } else {
                    $freightClass = '';
                }


                //Checking if plan is at least Standard
                $plan = $this->dataHelper->fedexSmallPlanName("ENFedExSmpkg");
                if ($plan['planNumber'] < 2) {
                    $insurance =  0;
                    $hazmat = 'N';
                } else {
                    $hazmat = ($_product->getData('en_hazmat'))?'Y':'N';
                    $insurance = $_product->getData('en_insurance');
                    if ($insurance && $this->registry->registry('en_insurance') === null) {
                        $this->registry->register('en_insurance', $insurance);
                    }
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

                $originAddress  = $this->fedExShipPkg->fedexSmpkgOriginAddress($_product, $receiverZipCode);

                $hazordousData[][$originAddress['senderZip']] = $this->setHazmatArray($_product, $hazmat);

                $package['origin'][$_product->getId()] = $originAddress;

                $orderWidget[$originAddress['senderZip']]['origin'] = $originAddress;

                $length = ($_product->getData('en_length') != null) ?
                    $_product->getData('en_length') : $_product->getData('ts_dimensions_length');
                $width = ( $_product->getData('en_width') != null) ?
                    $_product->getData('en_width') : $_product->getData('ts_dimensions_width');
                $height = ( $_product->getData('en_height') != null) ?
                    $_product->getData('en_height') : $_product->getData('ts_dimensions_height');


                $lineItems = [
                    'lineItemClass'          => ($lineItemClass == 'No Freight Class'
                        || $lineItemClass == 'No') ?
                        0 : $lineItemClass,
                    'freightClass'              => $freightClass,
                    'lineItemId'                => $_product->getId(),
                    'lineItemName'              => $_product->getName(),
                    'piecesOfLineItem'          => $productQty,
                    'lineItemPrice'             => $_product->getPrice(),
                    'lineItemWeight'            => number_format($_product->getWeight(), 2, '.', ''),
                    'lineItemLength'            => number_format($length, 2, '.', ''),
                    'lineItemWidth'             => number_format($width, 2, '.', ''),
                    'lineItemHeight'            => number_format($height, 2, '.', ''),
                    'isHazmatLineItem'          => $hazmat,
                    'product_insurance_active'  => $insurance,
                    'shipBinAlone'              => $_product->getData('en_own_package'),
                    'vertical_rotation'         => $_product->getData('en_vertical_rotation'),
                ];

//                $package['items'][$_product->getId()] = array_merge($lineItems);
                $package['items'][$_product->getId()] = $lineItems;
                $orderWidget[$originAddress['senderZip']]['item'][] = $package['items'][$_product->getId()];
            }
        }
        foreach ($orderWidget as $data) {
            $uniqueOrigins []= $data['origin'];
        }
        $this->setDataInRegistry($uniqueOrigins, $hazordousData, $orderWidget);

        return $package;
    }

    /**
     * @param type $_product
     * @return type
     */
    public function setHazmatArray($_product, $hazmat)
    {
        return [
            'lineItemId'    => $_product->getId(),
            'isHazordous'   => $hazmat == 'Y' ? '1' : '0' ,
        ];
    }

    /**
     * @param type $origin
     * @param type $hazordousData
     * @param type $setPackageDataForOrderDetail
     */
    public function setDataInRegistry($origin, $hazordousData, $orderWidget)
    {
        // set order detail widget data
        if ($this->registry->registry('setPackageDataForOrderDetail') === null) {
            $this->registry->register('setPackageDataForOrderDetail', $orderWidget);
        }

        // set hazardous data globally
        if ($this->registry->registry('hazardousShipment') === null) {
            $this->registry->register('hazardousShipment', $hazordousData);
        }
        // set shipment origin globally for instore pickup and local delivery
        if ($this->registry->registry('shipmentOrigin') === null) {
            $this->registry->register('shipmentOrigin', $origin);
        }
    }

    /**
     * @param type $quotes
     * @return type
     */
    public function setCarrierRates($quotes)
    {
        $carrersArray   = $this->registry->registry('enitureCarrierCodes');
        $carrersTitle   = $this->registry->registry('enitureCarrierTitle');

        $result = $this->rateResultFactory->create();

        foreach ($quotes as $carrierkey => $quote) {
            foreach ($quote as $key => $carrier) {
                $method = $this->rateMethodFactory->create();
                $carrierCode    = $carrersTitle[$carrierkey] ?? $this->code;
                $carrierTitle   = $carrersArray[$carrierkey] ?? $this->getConfigData('title');
                $price = $this->freeShipping ? 0 : $carrier['rate'];
                $method->setCarrierTitle($carrierCode);
                $method->setCarrier($carrierTitle);
                $method->setMethod($carrier['code']);
                $method->setMethodTitle($carrier['title']);
                $method->setPrice($price);
                $method->setCost($price);

                $result->append($method);
            }
        }
        $this->registry->unregister('enitureCarriers');

        return $result;
    }

    public function printQuery()
    {
        $printQuery = 0;
        parse_str(parse_url($this->httpRequest->getServer('HTTP_REFERER'), PHP_URL_QUERY), $query);

        if (!empty($query)) {
            $printQuery = ($query['en_print_query']) ?? 0;
        }
        return $printQuery;
    }
}
