<?php

namespace Eniture\FedExSmallPackages\Model\ResourceModel\Enituremodules;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Eniture\FedExSmallPackages\Model\Enituremodules', 'Eniture\FedExSmallPackages\Model\ResourceModel\Enituremodules');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}