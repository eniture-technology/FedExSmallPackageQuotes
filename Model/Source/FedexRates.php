<?php

namespace Eniture\FedExSmallPackages\Model\Source;

class FedexRates implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return  [
                    [
                        'value' => 'publish',
                        'label' => __('Use Published List Rates')
                    ],
                    [
                        'value' => 'negotiate',
                        'label' => __('Use Negotiated List Rates')
                    ],
                ];
    }
}
