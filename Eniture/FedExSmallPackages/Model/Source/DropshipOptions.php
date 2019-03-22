<?php
namespace Eniture\FedExSmallPackages\Model\Source;
/**
 * Source class for Warehouse and Dropship
 */
class DropshipOptions extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    protected $_dataHelper;
    protected $_options = array();
    
    /**
     * 
     * @param \Eniture\FedExSmallPackages\Helper\Data $dataHelper
     */
    public function __construct(
        \Eniture\FedExSmallPackages\Helper\Data $dataHelper
    ) {
        $this->_dataHelper = $dataHelper;
    }
    /**
     * Abstract method of source class
     * @return type
     */
    public function getAllOptions()
    {
        $get_dropship = $this->_dataHelper->fetchWarehouseSecData('dropship');
        
        if(isset($get_dropship) && count($get_dropship) > 0){
            foreach ($get_dropship as $manufacturer) {
                ( isset( $manufacturer['nickname'] ) && $manufacturer['nickname'] == '' ) ? $nickname = '' : $nickname = html_entity_decode($manufacturer['nickname'],ENT_QUOTES).' - ';
                $city       = $manufacturer['city'];
                $state      = $manufacturer['state'];
                $zip        = $manufacturer['zip'];
                $dropship   = $nickname.$city.', '.$state.', '.$zip;
                $this->_options[] = array(
                        'label' => __($dropship),
                        'value' => $manufacturer['warehouse_id'],
                    );
            }
        }
        return $this->_options;
    }
    /**
     * Abstract method of source class that returns data
     * @param $value
     * @return boolean
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions(false);

        foreach ($options as $item) {
            if ($item['value'] == $value) {
                return $item['label'];
            }
        }
        return false;
    }
}