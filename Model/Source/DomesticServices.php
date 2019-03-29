<?php

namespace Eniture\FedExSmallPackages\Model\Source;

class DomesticServices
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            'serviceOptions' =>
                ['value' => 'GROUND_HOME_DELIVERY',  'label'  => 'FedEx Home Delivery'],

                ['value' => 'FEDEX_GROUND',  'label'  => 'FedEx Ground'],

                ['value' => 'FEDEX_EXPRESS_SAVER',  'label'  => 'FedEx Express Saver'],

                ['value' => 'FEDEX_2_DAY',  'label'  => 'FedEx 2Day'],

                ['value' => 'FEDEX_2_DAY_AM',  'label'  => 'FedEx 2Day AM'],

                ['value' => 'STANDARD_OVERNIGHT',  'label'  => 'FedEx Standard Overnight'],

                ['value' => 'PRIORITY_OVERNIGHT',  'label'  => 'FedEx Priority Overnight'],

                ['value' => 'FIRST_OVERNIGHT',  'label'  => 'FedEx First Overnight'],
            ];
    }
}
