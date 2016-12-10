<?php
namespace Demo\MaxQuantity\Setup;
 
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }
 
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
		//$setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

		$attrCode = 'max_qty';
		$attrLabel = 'Max Quantity';
		
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            $attrCode,
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => $attrLabel,
                'input' => 'text',
                'class' => 'validate-number',
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                'visible' => true,
                'required' => true,
                'user_defined' => false,
                'default' => -1,
                'searchable' => false,
                'filterable' => true,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
				'is_configurable'=>true
            ]
        );
		
		$attrData = array(
			'max_qty'=> -1,
		);
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$productObject = $objectManager->get('Magento\Catalog\Model\Product');
		$productIds = $productObject->getCollection()->getAllIds();
		$productActionObject = $objectManager->get('Magento\Catalog\Model\Product\Action');
		$storeId  = 1;
		
		//$productModel = new \Magento\Catalog\Model\Product;
		//$productActionModel = new \Magento\Catalog\Model\Product\Action;
		//$productIds = \Magento\Catalog\Model\Product::getCollection()->getAllIds();
		
		//\Magento\Catalog\Model\Product\Action::updateAttributes(
		$productActionObject->updateAttributes(
			$productIds, 
			$attrData,
			$storeId
		);
		//$setup->endSetup();
    }
}