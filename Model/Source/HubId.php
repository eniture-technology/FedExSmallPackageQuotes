<?php

namespace Eniture\FedExSmallPackages\Model\Source;

class HubId
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
                ['value' => '0', 'label' => 'Select'],

                ['value' => '5185', 'label' => '5185 ALPA Allentown'],

                ['value' => '5303', 'label' => '5303 ATGA Atlanta'],

                ['value' => '5281', 'label' => '5281 CHNC Charlotte'],

                ['value' => '5929', 'label' => '5929 COCA Chino'],

                ['value' => '5751', 'label' => '5751 DLTX Dallas'],

                ['value' => '5802', 'label' => '5802 DNCO Denver'],

                ['value' => '5481', 'label' => '5481 DTMI Detroit'],

                ['value' => '5087', 'label' => '5087 EDNJ Edison'],

                ['value' => '5431', 'label' => '5431 GCOH Grove City'],

                ['value' => '5771', 'label'=> '5771 HOTX Houston'],

                ['value' => '5436', 'label'=> '5436 GPOH Groveport Ohio'],

                ['value' => '5902', 'label' => '5902 LACA Los Angeles'],

                ['value' => '5465', 'label' => '5465 ININ Indianapolis'],

                ['value' => '5648', 'label' => '5648 KCKS Kansas City'],

                ['value' => '5254', 'label' => '5254 MAWV Martinsburg'],

                ['value' => '5379', 'label' => '5379 METN Memphis'],

                ['value' => '5552', 'label' => '5552 MPMN Minneapolis'],

                ['value' => '5531', 'label' => '5531 NBWI New Berlin'],

                ['value' => '5110', 'label' => '5110 NENY Newburgh'],

                ['value' => '5015', 'label' => '5015 NOMA Northborough'],

                ['value' => '5327', 'label' => '5327 ORFL Orlando'],

                ['value' => '5194', 'label' => '5194 PHPA Philadelphia'],

                ['value' => '5854', 'label' => '5854 PHAZ Phoenix'],

                ['value' => '5150', 'label' => '5150 PTPA Pittsburgh'],

                ['value' => '5958', 'label' => '5958 SACA Sacramento'],

                ['value' => '5843', 'label' => '5843 SCUT Salt Lake City'],

                ['value' => '5983', 'label' => '5983 SEWA Seattle'],

                ['value' => '5631', 'label' => '5631 STMO St. Louis'],

                ['value' => '5893', 'label' => '5893 RENV Reno'],
            ];
    }
}
