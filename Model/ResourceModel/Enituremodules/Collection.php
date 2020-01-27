<?php

namespace Eniture\FedExSmallPackageQuotes\Model\ResourceModel\Enituremodules;

/**
 * Class Collection
 * @package Eniture\FedExSmallPackageQuotes\Model\ResourceModel\Enituremodules
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Eniture\FedExSmallPackageQuotes\Model\Enituremodules', 'Eniture\FedExSmallPackageQuotes\Model\ResourceModel\Enituremodules');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }
}
