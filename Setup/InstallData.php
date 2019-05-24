<?php
/**
 * @category   Shipping
 * @package    Eniture_FedExSmallPackages
 * @author     Eniture Technology : <sales@eniture.com>
 * @website    http://eniture.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
namespace Eniture\FedExSmallPackages\Setup;
 
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    /**
     * @var Tables to use
     */
    private $tableNames;
    /**
     * @var Attributes to create
     */
    private $attrNames;
    /**
     * @var DB Connection
     */
    private $connection;
    /**
     * @var Magento Version
     */
    private $mageVersion;
 
    private $collectionFactory;
    
    private $productloader;
    
    private $resource;


    private $palnUpgrade;
    
    /**
     * @param EavSetupFactory $eavSetupFactory
     * @param \Eniture\FedExSmallPackages\App\State $state
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $productloader
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        \Eniture\FedExSmallPackages\App\State $state,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\ProductFactory $productloader,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $resourceConfig,
        \Eniture\FedExSmallPackages\Cron\PlanUpgrade $planUpgrade
    ) {
        $this->eavSetupFactory      = $eavSetupFactory;
        $this->productMetadata      = $productMetadata;
        $this->collectionFactory    = $collectionFactory;
        $this->productloader        = $productloader;
        $this->resource             = $resource;
        $this->eavConfig            = $eavConfig;
        $this->state                = $state;
        $this->curl                 = $curl;
        $this->resourceConfig       = $resourceConfig;
        $this->palnUpgrade          = $planUpgrade;
    }
       
    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (!$this->state->validateAreaCode()) {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        }
        // Check plan info of current module
        $this->palnUpgrade->execute();
        
        $this->connection = $this->resource
        ->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $this->mageVersion      = $this->productMetadata->getVersion();
        
        $installer = $setup;
        $installer->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        
        $this->getTableNames();
        $this->attrNames();
        $this->renameOldAttributes();
        $this->createOrderDetailAttr($installer);
        
        $this->addFedExSmallAttributes($installer, $eavSetup);
        $this->createFedExSmallWarehouseTable($installer);
        $this->createEnitureModulesTable($installer);
        $this->updateProductDimensionalAttr($installer, $eavSetup);
        $this->checkLTLExistanceColumForEnModules($installer);
        $this->checkISLDColumForWarehouse($installer);
        $installer->endSetup();
    }
    
    /**
     * Set Attribute names globally
     */
    private function getTableNames()
    {
        $this->tableNames = [
            'eav_attribute'     => $this->resource->getTableName('eav_attribute'),
            'EnitureModules'    => $this->resource->getTableName('EnitureModules'),
        ];
    }
    
    /**
     * Set Attribute names globally
     */
    private function attrNames()
    {
        $dimAttr = [
            'length'            => 'length',
            'width'             => 'width',
            'height'            => 'height',
        ];
        $dsAttr = [
            'dropship'          => 'dropship',
            'dropship_location' => 'dropship_location'
        ];
        
        $this->attrNames = ($this->mageVersion >= '2.2.5') ? $dsAttr : array_merge($dsAttr, $dimAttr);
    }
    
    /**
     * Rename old attribute name
     */
    private function renameOldAttributes()
    {
        if ($this->mageVersion < '2.2.5') {
            $attributes = $this->attrNames;
            foreach ($attributes as $key => $attr) {
                $isExist = $this->eavConfig->getAttribute('catalog_product', 'wwe_'.$attr.'')->getAttributeId();
                if ($isExist != null) {
                    $updateSql = "UPDATE ".$this->tableNames['eav_attribute']." "
                            . "SET attribute_code = 'en_".$attr."', is_required = 0 "
                            . "WHERE attribute_code = 'wwe_".$attr."'";
                    $this->connection->query($updateSql);
                }
            }
        }
    }
    
    /**
     * @param type $installer
     */
    private function createOrderDetailAttr($installer)
    {
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'order_detail_data',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'default'   => '',
                'comment' => 'Order Detail Widget Data'
            ]
        );
    }
    
    /**
     * add custom product attributes required for product settings
     * @param $installer
     */
    private function addFedExSmallAttributes($installer, $eavSetup)
    {
        $attributes = $this->attrNames;
        if ($this->mageVersion < '2.2.5') {
            unset($attributes['dropship'], $attributes['dropship_location']);
            $count = 71;
            foreach ($attributes as $key => $attr) {
                $isExist = $this->eavConfig
                        ->getAttribute('catalog_product', 'en_'.$attr.'')->getAttributeId();
                if ($isExist == null) {
                    $this->getAttributeArray(
                        $eavSetup,
                        'en_'.$attr,
                        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        ucfirst($attr),
                        'text',
                        '',
                        $count
                    );
                }
                $count++;
            }
        }
        
        $isendropshipExist = $this->eavConfig->getAttribute('catalog_product', 'en_dropship')->getAttributeId();

        if ($isendropshipExist == null) {
            $this->getAttributeArray(
                $eavSetup,
                'en_dropship',
                'int',
                'Enable Dropship',
                'select',
                'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                76
            );
        }

        $isdropshiplocationExist = $this->eavConfig
                ->getAttribute('catalog_product', 'en_dropship_location')->getAttributeId();
        if ($isdropshiplocationExist == null) {
            $this->getAttributeArray(
                $eavSetup,
                'en_dropship_location',
                'int',
                'Drop Ship Location',
                'select',
                'Eniture\FedExSmallPackages\Model\Source\DropshipOptions',
                77
            );
        } else {
            $dataArr = [
                'source_model' => 'Eniture\FedExSmallPackages\Model\Source\DropshipOptions',
            ];
            $this->connection
                ->update($this->tableNames['eav_attribute'], $dataArr, "attribute_code = 'en_dropship_location'");
        }
        
        $isHazmatExist = $this->eavConfig->getAttribute('catalog_product', 'en_hazmat')->getAttributeId();

        if ($isHazmatExist == null) {
            $this->getAttributeArray(
                $eavSetup,
                'en_hazmat',
                'int',
                'Hazardous Material',
                'select',
                'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                78
            );
        }
        
        $isInsurance = $this->eavConfig->getAttribute('catalog_product', 'en_insurance')->getAttributeId();

        if ($isInsurance == null) {
            $this->getAttributeArray(
                $eavSetup,
                'en_insurance',
                'int',
                'Insure this item',
                'select',
                'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                79
            );
        }
        $installer->endSetup();
    }
    
    /**
     * @param type $eavSetup
     * @param type $code
     * @param type $type
     * @param type $label
     * @param type $input
     * @param type $source
     * @param type $order
     * @return type
     */
    private function getAttributeArray($eavSetup, $code, $type, $label, $input, $source, $order)
    {
        $attrArr = $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            $code,
            [
                'group'            => 'Product Details',
                'type'             => $type,
                'backend'          => '',
                'frontend'         => '',
                'label'            => $label,
                'input'            => $input,
                'class'            => '',
                'source'           => $source,
                'global'           => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'required'         => false,
                'visible_on_front' => false,
                'is_configurable'  => true,
                'sort_order'       => $order,
                'user_defined'     => true
            ]
        );
        
        return $attrArr;
    }
    
    /**
     * create warehouse db table for module warehouse section
     * @param $installer
     */

    private function createFedExSmallWarehouseTable($installer)
    {
        $tableName = $installer->getTable('warehouse');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn('warehouse_id', Table::TYPE_INTEGER, null, [
                    'identity'  => true,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => true,
                    ], 'Id')
                ->addColumn('city', Table::TYPE_TEXT, 200, [
                    'nullable'  => false,
                    ], 'city')
                ->addColumn('state', Table::TYPE_TEXT, 200, [
                    'nullable'  => false,
                    ], 'state')
                ->addColumn('zip', Table::TYPE_TEXT, 200, [
                        'nullable'  => false,
                        ], 'zip')
                ->addColumn('country', Table::TYPE_TEXT, 200, [
                        'nullable'  => false,
                        ], 'country')
                ->addColumn('location', Table::TYPE_TEXT, 200, [
                        'nullable'  => false,
                        ], 'location')
                ->addColumn('nickname', Table::TYPE_TEXT, 30, [
                        'nullable'  => false,
                        ], 'nickname')
                ->addColumn(
                    'in_store',
                    Table::TYPE_TEXT,
                    512,
                    [],
                    'in store pick up'
                )
                ->addColumn(
                    'local_delivery',
                    Table::TYPE_TEXT,
                    512,
                    [],
                    'local delivery'
                );
            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
    
    /**
     * create EnitureModules db table for Ective modules
     * @param $installer
     */
    private function createEnitureModulesTable($installer)
    {
        $moduleTableName = $installer->getTable('enituremodules');
        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($moduleTableName) != true) {
            $table = $installer->getConnection()
                ->newTable($moduleTableName)
                ->addColumn('module_id', Table::TYPE_INTEGER, null, [
                    'identity'  => true,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => true,
                    ], 'id')
                ->addColumn('module_name', Table::TYPE_TEXT, 200, [
                    'nullable'  => false,
                    ], 'module_name')
                ->addColumn('module_script', Table::TYPE_TEXT, 200, [
                    'nullable'  => false,
                    ], 'module_script')
                    ->addColumn('dropship_field_name', Table::TYPE_TEXT, 200, [
                    'nullable'  => false,
                    ], 'dropship_field_name')
                ->addColumn('dropship_source', Table::TYPE_TEXT, 200, [
                    'nullable'  => false,
                    ], 'dropship_source');
            $installer->getConnection()->createTable($table);
        }
        
        $newModuleName  = 'ENFedExSmpkg';
        $scriptName     = 'Eniture_FedExSmallPackages';
        $isNewModuleExist  = $this->connection->fetchOne(
            "SELECT count(*) AS count FROM ".$moduleTableName." WHERE module_name = '".$newModuleName."'"
        );
        if ($isNewModuleExist == 0) {
            $insertDataArr = [
                'module_name' => $newModuleName,
                'module_script' => $scriptName,
                'dropship_field_name' => 'en_dropship_location',
                'dropship_source' => 'Eniture\FedExSmallPackages\Model\Source\DropshipOptions'
            ];
            $this->connection->insert($moduleTableName, $insertDataArr);
        }
        
        $installer->endSetup();
    }
    
    /**
     * @param type $installer
     */
    private function updateProductDimensionalAttr($installer, $eavSetup)
    {
        $lengthChange = $widthChange = $heightChange = false;

        if ($this->mageVersion > '2.2.4') {
            $productCollection = $this->collectionFactory->create()->addAttributeToSelect('*');
            foreach ($productCollection as $_product) {
                $product = $this->productloader->create()->load($_product->getEntityId());

                $savedEnLength  = $_product->getData('en_length');
                $savedEnWidth   = $_product->getData('en_width');
                $savedEnHeight  = $_product->getData('en_height');

                if (isset($savedEnLength) && $savedEnLength) {
                    $product->setData('ts_dimensions_length', $savedEnLength)
                            ->getResource()->saveAttribute($product, 'ts_dimensions_length');
                    $lengthChange = true;
                }

                if (isset($savedEnWidth) && $savedEnWidth) {
                    $product->setData('ts_dimensions_width', $savedEnWidth)
                        ->getResource()->saveAttribute($product, 'ts_dimensions_width');
                    $widthChange = true;
                }

                if (isset($savedEnHeight) && $savedEnHeight) {
                    $product->setData('ts_dimensions_height', $savedEnHeight)
                        ->getResource()->saveAttribute($product, 'ts_dimensions_height');
                    $heightChange = true;
                }
            }
        }

        $this->removeEnitureAttr($installer, $lengthChange, $widthChange, $heightChange, $eavSetup);
    }
        
    /**
     * @param type $installer
     * @param type $lengthChange
     * @param type $widthChange
     * @param type $heightChange
     */
    private function removeEnitureAttr($installer, $lengthChange, $widthChange, $heightChange, $eavSetup)
    {
        if ($lengthChange == true) {
            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'en_length');
        }

        if ($widthChange == true) {
            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'en_width');
        }

        if ($heightChange == true) {
            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'en_height');
        }
    }
        
    /**
     * Add column to eniture modules table
     * @param $installer
     */
    private function checkLTLExistanceColumForEnModules($installer)
    {
        $tableName = $installer->getTable('enituremodules');

        if ($installer->getConnection()->isTableExists($tableName) == true) {
            if ($installer->getConnection()->tableColumnExists($tableName, 'is_ltl') === false) {
                $installer->getConnection()->addColumn($tableName, 'is_ltl', [
                    'type'      => Table::TYPE_BOOLEAN,
                    'comment'   => 'module type'
                    ]);
            }
        }

        $this->connection->update($tableName, ['is_ltl' => 0], "module_name = 'ENFedExSmpkg'");
        $installer->endSetup();
    }

    /**
     * @param type $path
     * @param type $value
     */
    function saveConfigurations($path, $value)
    {
        $this->resourceConfig->saveConfig(
            $path,
            $value,
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        );
    }
    
    /**
     * Add column to eniture modules table
     * @param $installer
     */
    private function checkISLDColumForWarehouse($installer)
    {
        $tableName = $installer->getTable('warehouse');
        if ($installer->getConnection()->isTableExists($tableName) == true) {
            if ($installer->getConnection()->tableColumnExists($tableName, 'in_store') === false &&
                $installer->getConnection()->tableColumnExists($tableName, 'local_delivery') === false) {
                $columns = [
                    'in_store' => [
                        'type'      => Table::TYPE_TEXT,
                        'comment'   => 'in store pick up'
                    ],
                    'local_delivery' => [
                        'type'      => Table::TYPE_TEXT,
                        'comment'   => 'local delivery'
                    ]

                ];
                $connection = $installer->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableName, $name, $definition);
                }
            }
        }
        $installer->endSetup();
    }
}
