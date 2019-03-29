<?php
/**
 * FedEx Small Packages
 * @package EnableCity
 * @author Eniture
 * @license https://eniture.com
 */
 
namespace Eniture\FedExSmallPackages\Model\Checkout\Block\Cart;
 
/**
 * Checkout cart shipping block plugin
 */
class Shipping extends \Magento\Checkout\Block\Cart\LayoutProcessor
{
    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Checkout\Block\Checkout\AttributeMerger $merger,
        \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection,
        \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection
    ) {
        parent::__construct($merger, $countryCollection, $regionCollection);
    }
 
    /**
     * Show City in Shipping Estimation
     *
     * @return bool
     * @codeCoverageIgnore
     */
    protected function isCityActive()
    {
        return true;
    }
}
