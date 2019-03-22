<?php 
namespace Eniture\FedExSmallPackages\Model\Source;
class InternationalServices
{
    /**
     * 
     * @return array
     */
	public function toOptionArray()
	{

            return array(
                'serviceOptions' => 
                    array('value' => 'FEDEX_GROUND',  'label'  => 'FedEx International Ground'),
                
                    array('value' => 'INTERNATIONAL_ECONOMY',  'label'  => 'FedEx International Economy'),
                    
                    array('value' => 'INTERNATIONAL_ECONOMY_DISTRIBUTION',  'label'  => 'FedEx International Economy Distribution'),
                    
                    array('value' => 'INTERNATIONAL_ECONOMY_FREIGHT',  'label'  => 'FedEx International Economy Freight'),
                    
                    array('value' => 'INTERNATIONAL_FIRST',  'label'  => 'FedEx International First'),
                    
                    array('value' => 'INTERNATIONAL_PRIORITY',  'label'  => 'FedEx International Priority'),
                        
                    array('value' => 'INTERNATIONAL_PRIORITY_DISTRIBUTION',  'label'  => 'FedEx International Priority Distribution'),
                    
                    array('value' => 'INTERNATIONAL_PRIORITY_FREIGHT',  'label'  => 'FedEx International Priority Freight'),
                    
                    array('value' => 'INTERNATIONAL_DISTRIBUTION_FREIGHT',  'label'  => 'FedEx International Distribution Freight'),
                );
        }

}