<?php
namespace Eniture\FedExSmallPackageQuotes\Model;

/**
 * Class Enituremodules
 * @package Eniture\FedExSmallPackageQuotes\Model
 */
class Enituremodules extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Eniture\FedExSmallPackageQuotes\Model\ResourceModel\Enituremodules');
    }
}
