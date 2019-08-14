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

class Cybage_Customergroup_Model_Observer
{
    /**
     * Customer group id
     */
    const NOT_LOGGED_IN = 0;
    const ZERO = 0;
    const ONE = 1;
    const VISIBLE = 4;
    
    /**
     * Check module status
     */    
    public function checkModuleStatus()
    {
        $categoryAttribute = Mage::getModel('catalog/resource_eav_attribute')->loadByCode('catalog_category', 'customergroup');
        $productAttribute = Mage::getModel('catalog/resource_eav_attribute')->loadByCode('catalog_product', 'customergroup');
        if(Mage::helper('customergroup')->isEnabled()) {
            $categoryAttribute->setIsVisible(self::ONE);
            $productAttribute->setIsVisible(self::ONE);
            $categoryAttribute->save();
            $productAttribute->save();
        } else {
            $categoryAttribute->setIsVisible(self::ZERO);
            $productAttribute->setIsVisible(self::ZERO);
            $categoryAttribute->save();
            $productAttribute->save();
        }
    }
    
    /**
     * Check whether specified product is eligible for customer to view or not
     *
     * @param Varien_Event_Observer $observer
     */
    public function checkCustomerGroupOnProductView($observer)
    {
        if(Mage::helper('customergroup')->isEnabled()) {
            $action = $observer->getEvent()->getControllerAction();

            if ($action instanceof Mage_Catalog_ProductController && $action->getRequest()->getActionName() == 'view' ) {
                $productId  = (int) $action->getRequest()->getParam('id');
                $product = Mage::getSingleton('catalog/product')->load($productId);

                $error = false;
                $loginStatus = Mage::getSingleton('customer/session')->isLoggedIn();
                if ($loginStatus) {
                    $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
                    if(!in_array($customerGroupId, $product->getCustomergroup())) {
                        $error = true;
                    }
                } else {
                    if(!in_array(self::NOT_LOGGED_IN, $product->getCustomergroup())) {
                        $error = true;
                    }
                }
                if ($error) {
                    Mage::getSingleton('core/session')->addError(Mage::helper('customergroup')->__('The selected product is no longer available and hence cannot be viewed'));
                    $url = Mage::helper('core/http')->getHttpReferer() ? Mage::helper('core/http')->getHttpReferer() : Mage::getUrl();
                    Mage::app()->getFrontController()->getResponse()->setRedirect($url);
                    Mage::app()->getResponse()->sendResponse();
                    exit;
                }
            }
        }
    }

    /**
     * Append customer group column into product grid
     *
     * @param Varien_Event_Observer $observer
     */
    public function appendCustomerGroupColumn($observer)
    {
        if(Mage::helper('customergroup')->isEnabled()) {
            $block = $observer->getBlock();
            if (!isset($block)) {
                return $this;
            }

            $block->addColumn('customergroup', array(
                'header' => Mage::helper('catalog')->__('Customer Group'),
                'width' => '80px',
                'index' => 'customergroup',
                'type' => 'options',
                'options' => Mage::getSingleton('customergroupcore/product_entity_customergroupcore')->getOptionArray(),
                'filter_condition_callback' => array($this, 'filterGroupsCondition'),
                'renderer' => 'Cybage_Customergroup_Block_Adminhtml_Renderer_Customergroup',
            ));

            $block->addColumnsOrder('customergroup', 'status')->sortColumnsByOrder();

            return $this;
        }
    }
    
    /**
     * Filter product collection using customer group
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     */    
    public function filterGroupsCondition($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if (!$value && $value != self::ZERO) {
            return;
        }
        
        $collection->addAttributeToFilter(
            array(
                array('attribute'=> 'customergroup', 'like' => $value),
                array('attribute'=> 'customergroup', 'like' => $value.',%'),
                array('attribute'=> 'customergroup', 'like' => '%,'.$value),
                array('attribute'=> 'customergroup', 'like' => '%,'.$value.',%'),
            )
        );
    }

    /**
     * Append customer group attribute into product collection
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function appendCustomerGroupAttribute($observer)
    {
        if(Mage::helper('customergroup')->isEnabled()) {
            $storeId = (int) Mage::app()->getRequest()->getParam('store', self::ZERO);
            $collection = $observer->getCollection();
            $collection->joinAttribute(
                'customergroup', 'catalog_product/customergroup', 'entity_id', null, 'inner', $storeId
            );

            return $this;
        }
    }
    
    /**
     * Added Customer group filter on catalog category collection
     * @param Varien_Event_Observer $observer
     * @return : Object
     */
    public function checkCustomerGroupOnCategory($observer)
    {
        if(Mage::helper('customergroup')->isEnabled()) {
            $collection = $observer->getEvent()->getCategoryCollection();
            $this->applyCustomerGroupFilter($collection);
            return $this;
        }
    }
    
    /**
     * Added Customer group filter on catalog product collection
     * @param Varien_Event_Observer $observer
     * @return : Object
     */
    public function checkCustomerGroupOnProduct($observer)
    {
        if(Mage::helper('customergroup')->isEnabled()) {
            $collection = $observer->getCollection();
            $beforeProducts = array();
            foreach($collection->getData() as $product) {
                $beforeProducts[$product['entity_id']] = $product;
            }
            $this->applyCustomerGroupFilter($collection);
            if(Mage::app()->getRequest()->getControllerName() == "cart" && Mage::getSingleton('customer/session')->isLoggedIn()) {
                $afterProducts = array();            
                $connection = Mage::getModel('core/resource')->getConnection('core_read');     
                $products = $connection->fetchAll($collection->getSelect());
                foreach($products as $product) {
                    $afterProducts[$product['entity_id']] = $product;
                }
                $remainingProducts = array_diff_key($beforeProducts, $afterProducts);
                if($remainingProducts) {
                    $name = array();
                    foreach($remainingProducts as $product) {
                        $product = Mage::getModel('catalog/product')->load($product['entity_id']);                        
                        if($product->getVisibility() == self::VISIBLE) {
                            $name[] = $product->getName();
                        }                        
                    }
                    $message = Mage::getStoreConfig('customergroup/setting/front_cart_message') ? Mage::getStoreConfig('customergroup/setting/front_cart_message') : "The below products are no longer available and hence have been removed from your cart:";
                    Mage::getSingleton('checkout/session')->addError(Mage::helper('customergroup')->__($message . " </br>'%s'", implode(", ", $name)));
                }
            }            
            return $this;
        }
    }
    
    /**
     * Add Filter For Customer Group on product or category collection
     * @param  : Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     */
    public function applyCustomerGroupFilter($collection)
    {   
        $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $customerGroup = Mage::getModel('customer/group')->load($customerGroupId);
        $collection->addAttributeToFilter(
            array(
                array('attribute'=> 'customergroup', 'like' => $customerGroupId),
                array('attribute'=> 'customergroup', 'like' => $customerGroupId.',%'),
                array('attribute'=> 'customergroup', 'like' => '%,'.$customerGroupId),
                array('attribute'=> 'customergroup', 'like' => '%,'.$customerGroupId.',%'),
            )
        );        
    }

    /**
     * Check Customer group on grouped, bundle and configurable product
     * @param Varien_Event_Observer $observer
     * @return : Object
     */
    public function checkCustomerGroupOnParentProduct($observer)
    {
        if(Mage::helper('customergroup')->isEnabled()) {
            $product = $observer->getProduct();
            switch ($product->getTypeId()) {
                case "grouped":
                    if (strlen($product->getCustomergroup())) {
                        $associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
                        if(count($associatedProducts) > self::ZERO) {
                            $parentCustomerGroupIds = $this->checkCustomerGroupOnChildProduct($product, $associatedProducts);        
                            $product->setCustomergroup(implode(',', $parentCustomerGroupIds));
                            $product->getResource()->saveAttribute($product, 'customergroup');
                        }
                    }
                    break;

                case "bundle":
                    if (strlen($product->getCustomergroup())) {
                        $associatedProducts = $product->getTypeInstance(true)->getSelectionsCollection(
                            $product->getTypeInstance(true)->getOptionsIds($product), $product);
                        foreach($associatedProducts as $option) {
                            $associatedProductsIds[] = $option['entity_id'];
                        }
                        if(count($associatedProducts) > self::ZERO) {
                            $parentCustomerGroupIds = $this->checkCustomerGroupOnChildProduct($product, $associatedProducts);
                            $product->setCustomergroup(implode(',', $parentCustomerGroupIds));
                            $product->getResource()->saveAttribute($product, 'customergroup');
                        }
                    }
                    break;

                case "configurable":
                    if (strlen($product->getCustomergroup())) {
                        $conf = Mage::getModel('catalog/product_type_configurable')->setProduct($product);
                        $associatedProducts = $conf->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();                    
                        if(count($associatedProducts) > self::ZERO) {
                            $parentCustomerGroupIds = $this->checkCustomerGroupOnChildProduct($product, $associatedProducts);                        
                            $product->setCustomergroup(implode(',', $parentCustomerGroupIds));
                            $product->getResource()->saveAttribute($product, 'customergroup');
                        }
                    }
                    break;
                    
                case "simple":
                    $parentIds = array(); 
                    $parentIds = array_merge($parentIds, Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($product->getId()));
                    $parentIds = array_merge($parentIds, Mage::getSingleton("catalog/product_type_configurable")->getParentIdsByChild($product->getId()));
                    $parentIds = array_merge($parentIds, Mage::getSingleton("bundle/product_type")->getParentIdsByChild($product->getId()));
                    if($parentIds) {
                        $parentUrl = array();
                        foreach($parentIds as $parentId) {                        
                            $product = Mage::getModel('catalog/product')->load($parentId);
                            $parentUrl[] = sprintf("<a href=%s target='_blank'>%s</a>", Mage::getUrl('*/*/edit',array("id"=>$parentId)) , $product->getName()); 
                        }
                    }
                    if(!is_null($parentUrl)) {
                        $parentUrl = implode(", ", $parentUrl);
                        Mage::getSingleton('core/session')->addNotice(Mage::helper('customergroup')->__("If you have added or removed any customer group from this product then please ensure the same is done for the respective parent product. Click on the below link if you wish to proceed to make changes: </br> %s", $parentUrl));
                    }
                    break;

                default:
                    break;
            }
        }
    }

    /**
     * Check Customer group is assigned to atleast single child product
     * @param Varien_Event_Observer $observer
     * @return : Object
     */    
    public function checkCustomerGroupOnChildProduct($product, $associatedProducts)
    {
        $parentCustomerGroupIds = explode(',' , $product->getCustomergroup());
        $customerGroupLabel = array();
        $productModel = Mage::getModel('catalog/product');
        $attr = $productModel->getResource()->getAttribute("customergroup");
        foreach($parentCustomerGroupIds as $key => $id) {
            $flag = false;            
            foreach($associatedProducts as $childProduct) {
                $childCustomerGroupIds = explode(',' , $childProduct->getCustomergroup());
                if(in_array($id, $childCustomerGroupIds)) {
                    $flag = true;
                }
            }
            if(!$flag) {
                unset($parentCustomerGroupIds[$key]);
                $customerGroupLabel[] = $attr->getSource()->getOptionText("$id");
            }
        }
        if($customerGroupLabel) {
            $customerGroupLabel = implode(", ", $customerGroupLabel);
            Mage::getSingleton('core/session')->addNotice(Mage::helper('customergroup')->__("The below customer groups would be available for assignment only when they are assigned to any of the respective child products: </br> %s", $customerGroupLabel));
        }
        return $parentCustomerGroupIds;
    }
    
    /**
     * remove customer group from child categories if it is removed from parent categories
     * @param array
     * @return : array
     */    
    public function checkCustomerGroupOnChildCategories($observer)
    {
        if(Mage::helper('customergroup')->isEnabled()) {
            $category = $observer->getEvent()->getCategory();
            $params = Mage::app()->getRequest()->getParam('general');
            if($params['customergroup']) {
                $children = $category->getResource()->getChildren($category, true);                
                if(count($children)) {
                    $category = Mage::getSingleton('catalog/category')->load($params['id']);
                    if(count($category->getCustomergroup())) {
                        $removedCategory = array_diff($category->getCustomergroup(), $params['customergroup']);
                        if(count($removedCategory)) {
                            foreach($children as $child) {
                                $childCategory = Mage::getSingleton('catalog/category')->load($child);
                                if(count($childCategory->getCustomergroup())) {
                                    $diffCategory = array_diff($childCategory->getCustomergroup(), $removedCategory);
                                    if(count($diffCategory)) {
                                        $childCategory->setCustomergroup(implode(",", $diffCategory));
                                    } else {
                                        $childCategory->setCustomergroup("");
                                    }
                                    $childCategory->getResource()->saveAttribute($childCategory, 'customergroup');
                                }
                            }
                        }                        
                    }
                }
                $children[] = $params['id'];
                $productCollection = Mage::getModel('catalog/product')->getCollection()
                        ->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id = entity_id', null, 'left')
                        ->addAttributeToFilter('category_id', array('in' => $children));
                if(count($productCollection->getData())) {
                    $category = Mage::getSingleton('catalog/category')->load($params['id']);
                    if(count($category->getCustomergroup())) {
                        $removedCategory = array_diff($category->getCustomergroup(), $params['customergroup']);
                        if(count($removedCategory)) {
                            foreach($productCollection->getData() as $product) {
                                $product = Mage::getSingleton('catalog/product')->load($product['entity_id']);
                                if(count($product->getCustomergroup())) {
                                    $diffCategory = array_diff($product->getCustomergroup(), $removedCategory);
                                    if(count($diffCategory)) {
                                        $product->setCustomergroup(implode(",", $diffCategory));
                                    } else {
                                        $product->setCustomergroup("");
                                    }
                                    $product->getResource()->saveAttribute($product, 'customergroup');
                                }
                            }
                        }
                    }
                }
            } else {
                $children = $category->getResource()->getChildren($category, true);
                if(count($children)) {                    
                    foreach($children as $child) {
                        $childCategory = Mage::getSingleton('catalog/category')->load($child);
                        $childCategory->setCustomergroup("");
                        $childCategory->getResource()->saveAttribute($childCategory, 'customergroup');                        
                    }
                }                
                $children[] = $params['id'];
                $productCollection = Mage::getModel('catalog/product')->getCollection()
                        ->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id = entity_id', null, 'left')
                        ->addAttributeToFilter('category_id', array('in' => $children));
                if(count($productCollection->getData())) {
                    foreach($productCollection->getData() as $product) {
                        $product = Mage::getSingleton('catalog/product')->load($product['entity_id']);
                        $product->setCustomergroup("");
                        $product->getResource()->saveAttribute($product, 'customergroup');
                    }
                }
            }
            if(!$params['addtocart']) {
                $children = $category->getResource()->getChildren($category, true);
                if(count($children)) {                    
                    foreach($children as $child) {
                        $childCategory = Mage::getSingleton('catalog/category')->load($child);
                        $childCategory->setAddtocart(false);
                        $childCategory->getResource()->saveAttribute($childCategory, 'addtocart');
                    }                            
                }
            }
            if($params['path']) {
                $category = $observer->getEvent()->getCategory();
                $path = explode('/', $params['path']);
                unset($path[self::ZERO]);
                unset($path[self::ONE]);
                $flag = false;
                foreach($path as $id) {
                    if($id != $params['id']) {
                        $parentCategory = Mage::getSingleton('catalog/category')->load($id);
                        if($parentCategory->getAddtocart() != self::ONE) {
                            $flag = true;                        
                        }
                    }
                }
                if($flag) {
                    $category->setAddtocart(self::ZERO);
                    $message = Mage::getStoreConfig('customergroup/setting/addtocart_message') ? Mage::getStoreConfig('customergroup/setting/addtocart_message') : "Enable 'Add to Cart Button' on the parent category";
                    Mage::getSingleton('core/session')->addNotice($message);
                }
            }
            return $this;
        }
    }
}