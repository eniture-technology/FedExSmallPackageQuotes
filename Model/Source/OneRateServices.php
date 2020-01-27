<?php
namespace Eniture\FedExSmallPackageQuotes\Model\Source;

/**
 * Class OneRateServices
 * @package Eniture\FedExSmallPackageQuotes\Model\Source
 */
class OneRateServices
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            'serviceOptions' =>
                ['value' => 'FEDEX_EXPRESS_SAVER',  'label'  => 'FedEx Express Saver'],

                ['value' => 'FEDEX_2_DAY',  'label'  => 'FedEx 2Day'],

                ['value' => 'FEDEX_2_DAY_AM',  'label'  => 'FedEx 2Day AM'],

                ['value' => 'STANDARD_OVERNIGHT',  'label'  => 'FedEx Standard Overnight'],

                ['value' => 'PRIORITY_OVERNIGHT',  'label'  => 'FedEx Priority Overnight'],

                ['value' => 'FIRST_OVERNIGHT',  'label'  => 'FedEx First Overnight'],
            ];
    }
}
