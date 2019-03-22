<?php 
namespace Eniture\FedExSmallPackages\Model\Source;
class DomesticServices
{
    /**
     * 
     * @return array
     */
    public function toOptionArray()
    {

        return array(
            'serviceOptions' => 
                array('value' => 'GROUND_HOME_DELIVERY',  'label'  => 'FedEx Home Delivery'),

                array('value' => 'FEDEX_GROUND',  'label'  => 'FedEx Ground'),

                array('value' => 'FEDEX_EXPRESS_SAVER',  'label'  => 'FedEx Express Saver'),

                array('value' => 'FEDEX_2_DAY',  'label'  => 'FedEx 2Day'),

                array('value' => 'FEDEX_2_DAY_AM',  'label'  => 'FedEx 2Day AM'),

                array('value' => 'STANDARD_OVERNIGHT',  'label'  => 'FedEx Standard Overnight'),

                array('value' => 'PRIORITY_OVERNIGHT',  'label'  => 'FedEx Priority Overnight'),

                array('value' => 'FIRST_OVERNIGHT',  'label'  => 'FedEx First Overnight'),
            );
    }
}