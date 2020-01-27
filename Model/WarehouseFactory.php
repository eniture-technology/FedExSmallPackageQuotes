<?php

namespace Eniture\FedExSmallPackageQuotes\Model;

/**
 * Class WarehouseFactory
 * @package Eniture\FedExSmallPackageQuotes\Model
 */
class WarehouseFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create new country model
     *
     * @param array $arguments
     * @return \Magento\Directory\Model\Country
     */
    public function create(array $arguments = [])
    {
        return $this->objectManager->create('Eniture\FedExSmallPackageQuotes\Model\Warehouse', $arguments, false);
    }
}
