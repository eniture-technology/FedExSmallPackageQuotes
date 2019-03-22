<?php 
namespace Eniture\FedExSmallPackages\Model\Source;
class HandlingFee
{
    /**
     * 
     * @return array
     */
	public function toOptionArray()
	{
            return array(
                'handlingFeeVal' => 
                    array(  'value' => 'flat',  'label'  => 'Flat Rate'),
                    array(  'value' => '%',     'label'  => 'Percentage ( % )'),
            );
    }

}