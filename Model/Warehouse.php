<?php
namespace Eniture\FedExSmallPackageQuotes\Model;

/**
 * Class Warehouse
 * @package Eniture\FedExSmallPackageQuotes\Model
 */
class Warehouse extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Eniture\FedExSmallPackageQuotes\Model\ResourceModel\Warehouse');
    }
}
