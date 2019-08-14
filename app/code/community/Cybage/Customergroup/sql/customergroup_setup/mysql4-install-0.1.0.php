<?php
/**
 * Cybage Customer Group Specific Products Plugin
 *
 * @category   Customer Group Specific Products Plugin
 * @package    Cybage_Customergroup
 * @copyright  Copyright (c) 2015 Cybage Software Pvt. Ltd., India
 *             http://www.cybage.com/coe/e-commerce
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Cybage Software Pvt. Ltd. 
 */

/** @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = new Mage_Catalog_Model_Resource_Setup();
$installer->startSetup();
$installer->addAttribute('catalog_product', 'customergroup', array(
    'attribute_set' => 'Default',
    'group' => 'General',
    'label' => 'Customer Group',
    'visible' => 1,
    'type' => 'varchar',
    'input' => 'multiselect',
    'system' => false,
    'required' => false,
    'is_configurable' => false,
    'user_defined' => true,
    'source' => 'customergroupcore/product_entity_customergroupcore',
    'backend' => 'customergroupcore/category_attribute_backend_customergroup',
    'default' => 0
));
$installer->addAttribute('catalog_category', 'addtocart', array(
    'type' => 'int',
    'label'=> 'Add To Cart Button',
    'input' => 'select',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible' => true,
    'required' => false,
    'user_defined' => true,
    'default' => 1,
    'source' => 'eav/entity_attribute_source_boolean',
    'group' => "Display Settings"
));
$installer->addAttribute('catalog_category', 'customergroup', array(
    'type' => 'varchar',
    'label'=> 'Customer Group',
    'input' => 'multiselect',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible' => true,
    'required' => false,
    'user_defined' => true,
    'default' => 0,
    'source' => 'customergroupcore/category_entity_customergroupcore',    
    'backend' => 'customergroupcore/category_attribute_backend_customergroup',
    'group' => "Display Settings"
));

$installer->endSetup();

$customerGroup = Mage::getSingleton('customer/group');
$allGroups = $customerGroup->getCollection()->toOptionHash();
$allGroups = implode(",", array_flip($allGroups));

$currentStore = Mage::app()->getStore()->getId();
Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);
$category = Mage::getModel('catalog/category');
$categoryTree = $category->getTreeModel()->load();
$categoryIds = $categoryTree->getCollection()->getAllIds();
if ($categoryIds) {
    try {
        foreach($categoryIds as $id){
            $singleCategory = Mage::getModel('catalog/category')->load($id);
            $singleCategory->setCustomergroup($allGroups);
            $singleCategory->getResource()->saveAttribute($singleCategory, 'customergroup');
            $singleCategory->setAddtocart(true);
            $singleCategory->getResource()->saveAttribute($singleCategory, 'addtocart');
        }
    } catch (Mage_Core_Exception $e) {
        Mage::log($e->__toString(), null, 'customergroup_cat.log');
    }
}
Mage::app()->getStore()->setId($currentStore);

$productIds = Mage::getResourceModel('catalog/product_collection')
    ->getAllIds();

$attributeData = array("customergroup" => $allGroups );
$storeId = 0;
try {
    Mage::getSingleton('catalog/product_action')
        ->updateAttributes($productIds, $attributeData, $storeId);
} catch (Mage_Core_Exception $e) {
    Mage::log($e->__toString(), null, 'customergroup.log');
}