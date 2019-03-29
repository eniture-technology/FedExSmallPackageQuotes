<?php
namespace Eniture\FedExSmallPackages\Model;

class Warehouse extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Eniture\FedExSmallPackages\Model\ResourceModel\Warehouse');
    }
}
