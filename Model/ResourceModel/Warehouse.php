<?php
namespace Eniture\FedExSmallPackageQuotes\Model\ResourceModel;

/**
 * Class Warehouse
 * @package Eniture\FedExSmallPackageQuotes\Model\ResourceModel
 */
class Warehouse extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('warehouse', 'warehouse_id');
    }
}
