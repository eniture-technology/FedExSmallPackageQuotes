<?php

namespace Eniture\FedExSmallPackages\Controller\Adminhtml\Product;

use Magento\Framework\Event\ObserverInterface;

class Edit extends \Magento\Catalog\Controller\Adminhtml\Product\Edit
{
    private $publicActions = ['edit'];
    private $conn;
    private $shipconfig;
    private $resource;
    private $enModuleFactory;
    private $attributeFactory;
    private $dsSourceModel = null;
    private $enDsSource;
    /**
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
    ) {
        parent::__construct($context, $productBuilder, $resultPageFactory);
        $this->resultPageFactory    = $resultPageFactory;
        $this->resource             = $resource;
        $this->shipconfig           = $shipconfig;
        $this->enModuleFactory      = $enModuleFactory;
        $this->attributeFactory     = $attributeFactory;
    }

    /**
     * @return type
     */
    public function execute()
    {
        $this->conn = $this->resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $this->enModuleFactoryCreate = $this->enModuleFactory->create();
        $haveEntry = $enitureModules = [];
        $collection = $this->enModuleFactoryCreate->getCollection()
            ->addFilter('module_name', ['eq' => 'ENFedExSmpkg']);
        foreach ($collection as $value) {
            $haveEntry[] = $value->getData();
        }
        
        $activeCarriers = array_keys($this->shipconfig->getActiveCarriers());
        
        foreach ($activeCarriers as $carrierCode) {
            $enCarrier = substr($carrierCode, 0, 2);
            if ($enCarrier == 'EN') {
                array_push($enitureModules, $carrierCode);
            }
        }

        if (count($enitureModules) == 0) {
            return parent::execute();
        }
        $activeModuleList = implode("','", $enitureModules);
        
        $enitureTableName = $this->resource->getTableName('enituremodules');

        $this->varifyModuleEntry($haveEntry);
        
        $eavTableName = $this->resource->getTableName('eav_attribute');
        $this->validateSourceModel($activeModuleList, $enitureTableName, $eavTableName, $enitureModules);
        return parent::execute();
    }
    
    /**
     * This function validate entry of this module in databebase
     */
    public function varifyModuleEntry($haveEntry)
    {
        if (empty($haveEntry)) {
            $data = [
                'module_name'           => 'ENFedExSmpkg',
                'module_script'         => 'Eniture_FedExSmallPackages',
                'dropship_field_name'   => 'en_dropship_location',
                'dropship_source'       => 'Eniture\FedExSmallPackages\Model\Source\DropshipOptions',
            ];
            
            $this->enModuleFactoryCreate->setData($data)->save();
        }
    }
    /**
     * this function update source model if required
     * @param $activeModuleList
     */
    public function validateSourceModel($activeModuleList, $enitureTableName, $eavTableName, $enitureModules)
    {
        $modulesCountDb = $this->conn->fetchAll(
            $this->enModuleFactoryCreate->getCollection()
            ->getSelect()->where('module_name NOT IN (?)', $activeModuleList)->limit(100)
        );
        
        if (!empty($modulesCountDb)) {
            foreach ($modulesCountDb as $value) {
                $id = $value['module_id'];
                $this->conn->delete($enitureTableName, "module_id='".(int)$id."'");
                
                $this->enDsSource = $value['dropship_source'];

                $attributeInfo = $this->attributeFactory->getCollection()
                                    ->addFieldToFilter('attribute_code', ['eq' => 'en_dropship_location'])
                                    ->addFieldToFilter('source_model', ['eq' => $this->enDsSource]);
                foreach ($attributeInfo as $key => $value) {
                    $attrData = $value->getData();
                    $this->dsSourceModel = $attrData['source_model'];
                }
            }

            $ltlExist = $this->enModuleFactoryCreate->getCollection()->addFilter('is_ltl', ['eq' => '1'])->count();
            if (!$ltlExist) {
                $this->conn->delete($eavTableName, "attribute_code='en_freight_class'");
            }

            if ($this->dsSourceModel == null) {
                $dropshipSource = $this->conn->fetchAll(
                    $this->enModuleFactoryCreate->getCollection()
                    ->getSelect()->where('module_name = (?)', "$enitureModules[0]")->limit(100)
                );
                $dataArr = [
                    'source_model' => $dropshipSource[0]['dropship_source'],
                ];
                $this->conn->update($eavTableName, $dataArr, "attribute_code = 'en_dropship_location'");
            }
        }
    }
}
