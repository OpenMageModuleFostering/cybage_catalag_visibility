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

class Cybage_Customergroup_Helper_Data extends Mage_Core_Helper_Abstract
{
    const ONE = 1;
    protected $_isNotAllowed = false;
    protected $_noMessage = false;
    
    /**
     * check if module is enabled or not
     * 
     * @return boolean
     */
    public function isEnabled() {
        return Mage::getStoreConfig('customergroup/setting/visibility');
    }
    
    /**
     * check product's customer group
     * @param wishlist item
     * @return boolean
     */
    public function checkProductCustomerGroups($product) {
        $flag = false;
        if($product->getCustomergroup()) {
            $productCustGroupIds = explode(',', $product->getCustomergroup());        
            $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
            if(in_array($customerGroupId, $productCustGroupIds)) {            
                $flag = true;
            } else {
                $this->_isNotAllowed = true;
            }
        } else {
            $this->_isNotAllowed = true;            
        }
        if(count($product->getCategoryIds()) && $flag) {
            foreach($product->getCategoryIds() as $catId) {
                $category = Mage::getSingleton('catalog/category')->load($catId);
                if($category->getAddtocart() != self::ONE) {
                    $flag = false;
                    $this->_isNotAllowed = true;
                    $this->_noMessage = true;
                }
            }
        }
        return $flag;
    }

    /**
     * check if any product in wishlist is not allowed
     * 
     * @return boolean
     */
    public function checkIsAllowed() {
        return $this->_isNotAllowed;
    }
    
    /**
     * set message
     * 
     * @return string
     */
    public function checkMessage() {
        return $this->_noMessage;
    }
}