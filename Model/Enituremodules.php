<?php
namespace Eniture\FedExSmallPackages\Model;

class Enituremodules extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Eniture\FedExSmallPackages\Model\ResourceModel\Enituremodules');
    }
}
