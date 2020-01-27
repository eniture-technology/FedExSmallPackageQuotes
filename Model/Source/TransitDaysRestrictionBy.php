<?php

namespace Eniture\FedExSmallPackageQuotes\Model\Source;

/**
 * Class TransitDaysRestrictionBy
 * @package Eniture\FedExSmallPackageQuotes\Model\Source
 */
class TransitDaysRestrictionBy implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return  [
                    [
                        'value' => 'TransitTimeInDays',
                        'label' => __('Restrict the carriers in transit days metric')
                    ],
                    [
                        'value' => 'CalenderDaysInTransit',
                        'label' => __('Restrict by calendar days in transit')
                    ],
                ];
    }
}
