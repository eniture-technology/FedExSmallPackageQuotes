<?php

namespace Eniture\FedExSmallPackageQuotes\Model\Source;

/**
 * Class HandlingFee
 * @package Eniture\FedExSmallPackageQuotes\Model\Source
 */
class HandlingFee
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            'handlingFeeVal' =>
                ['value' => 'flat', 'label'  => 'Flat Rate'],
                ['value' => '%', 'label'  => 'Percentage ( % )'],
        ];
    }
}
