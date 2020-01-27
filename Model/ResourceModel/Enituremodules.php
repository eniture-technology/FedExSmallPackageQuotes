<?php
namespace Eniture\FedExSmallPackageQuotes\Model\ResourceModel;

/**
 * Class Enituremodules
 * @package Eniture\FedExSmallPackageQuotes\Model\ResourceModel
 */
class Enituremodules extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('enituremodules', 'module_id');
    }
}
