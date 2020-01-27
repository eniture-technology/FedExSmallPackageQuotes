<?php

namespace Eniture\FedExSmallPackageQuotes\Model\Source;

/**
 * Class InternationalServices
 * @package Eniture\FedExSmallPackageQuotes\Model\Source
 */
class InternationalServices
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            'serviceOptions' =>
                ['value' => 'FEDEX_GROUND',  'label'  => 'FedEx International Ground'],

                ['value' => 'INTERNATIONAL_ECONOMY',  'label'  => 'FedEx International Economy'],

                ['value' => 'INTERNATIONAL_ECONOMY_DISTRIBUTION', 'label' => 'FedEx International Economy Distribution'],

                ['value' => 'INTERNATIONAL_ECONOMY_FREIGHT',  'label'  => 'FedEx International Economy Freight'],

                ['value' => 'INTERNATIONAL_FIRST',  'label'  => 'FedEx International First'],

                ['value' => 'INTERNATIONAL_PRIORITY',  'label'  => 'FedEx International Priority'],

                ['value' => 'INTERNATIONAL_PRIORITY_DISTRIBUTION', 'label' => 'FedEx International Priority Distribution'],

                ['value' => 'INTERNATIONAL_PRIORITY_FREIGHT',  'label'  => 'FedEx International Priority Freight'],

                ['value' => 'INTERNATIONAL_DISTRIBUTION_FREIGHT', 'label' => 'FedEx International Distribution Freight'],
            ];
    }
}
