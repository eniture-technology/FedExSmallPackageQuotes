<?php
namespace Eniture\FedExSmallPackages\Controller\Adminhtml\Product;

use Magento\Framework\Event\ObserverInterface;

class Edit extends \Magento\Catalog\Controller\Adminhtml\Product\Edit
{
    protected $_publicActions = ['edit'];
    protected $_connection;
    protected $shipconfig;
    protected $_resource;
    protected $_enModuleFactory;
    protected $_attributeFactory;
    protected $dsSourceModel = NULL;
    protected $enDsSource;
    /**
     * 
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Shipping\Model\Config $shipconfig
     * @param \Eniture\FedExSmallPackages\Model\EnituremodulesFactory $enModuleFactory
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory
     */
    public function __construct(
            \Magento\Backend\App\Action\Context $context,
            \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory,
            \Magento\Framework\App\ResourceConnection $resource,
            \Magento\Shipping\Model\Config $shipconfig,
            \Eniture\FedExSmallPackages\Model\EnituremodulesFactory $enModuleFactory,
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory

        )
    {
        parent::__construct($context, $productBuilder,$resultPageFactory );
        $this->resultPageFactory    = $resultPageFactory;
        $this->_resource            = $resource;
        $this->_connection          = $resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION); 
        $this->shipconfig           = $shipconfig;
        $this->_enModuleFactory     = $enModuleFactory->create();
        $this->_attributeFactory = $attributeFactory;
    }

    /**
     * 
     * @return type
     */
    public function execute()
    {  
        $haveEntry = array();
        $collection = $this->_enModuleFactory->getCollection()->addFilter('module_name', array('eq' => 'ENFedExSmpkg'));
        foreach ($collection as $value) {
            $haveEntry[] = $value->getData();
        }
        
        $activeCarriers = array_keys($this->shipconfig->getActiveCarriers());
        $enitureModules = array();

        foreach ($activeCarriers as $carrierCode) {
            $enCarrier = substr($carrierCode, 0, 2);
            if($enCarrier == 'EN'){
                array_push($enitureModules, $carrierCode);
            }
        }

        if(count($enitureModules) == 0){
            return parent::execute();
        }
        $activeModuleList = implode("','", $enitureModules);
        
        $enitureTableName = $this->_resource->getTableName('enituremodules');

        $this->varifyModuleEntry($haveEntry);
        
        $eavTableName = $this->_resource->getTableName('eav_attribute');
        $this->validateSourceModel($activeModuleList, $enitureTableName, $eavTableName, $enitureModules);
        return parent::execute();
    }
    
        
        
    /**
     * This function validate entry of this module in databebase
     */
    function varifyModuleEntry($haveEntry){
        if(empty($haveEntry)){
            $data = array(
                'module_name'           => 'ENFedExSmpkg',
                'module_script'         => 'Eniture_FedExSmallPackages',
                'dropship_field_name'   => 'en_dropship_location',
                'dropship_source'       => 'Eniture\FedExSmallPackages\Model\Source\DropshipOptions',
            );
            
            $this->_enModuleFactory->setData($data)->save();
        }
    }
    /**
     * this function update source model if required
     * @param $activeModuleList
     */
    public function validateSourceModel($activeModuleList, $enitureTableName, $eavTableName, $enitureModules){
        $modulesCountDb = $this->_connection->fetchAll($this->_enModuleFactory->getCollection()->getSelect()->where('module_name NOT IN (?)', $activeModuleList));
        
        if(count($modulesCountDb) > 0){
            foreach ($modulesCountDb as $value) {
                $id = $value['module_id'];
                $this->_connection->delete($enitureTableName, "module_id='".(int)$id."'");
                
                $this->enDsSource = $value['dropship_source'];

                $attributeInfo = $this->_attributeFactory->getCollection()
                                    ->addFieldToFilter('attribute_code', array('eq' => 'en_dropship_location'))
                                    ->addFieldToFilter('source_model', array('eq' => $this->enDsSource));
                foreach ($attributeInfo as $key => $value) {
                    $attrData = $value->getData();
                    $this->dsSourceModel = $attrData['source_model'];
                    
                }
            }

            $ltlExist = $this->_enModuleFactory->getCollection()->addFilter('is_ltl', array('eq' => '1'))->count();

            if(!$ltlExist){
                $this->_connection->delete($eavTableName, "attribute_code='en_freight_class'");
            }

            if($this->dsSourceModel == NULL){
                $dropshipSource = $this->_connection->fetchAll($this->_enModuleFactory->getCollection()->getSelect()->where('module_name = (?)', "$enitureModules[0]"));
                $dataArr = array(
                    'source_model' => $dropshipSource[0]['dropship_source'],
                );
                $this->_connection->update( $eavTableName, $dataArr, "attribute_code = 'en_dropship_location'" );
            }
        }
    } 
}