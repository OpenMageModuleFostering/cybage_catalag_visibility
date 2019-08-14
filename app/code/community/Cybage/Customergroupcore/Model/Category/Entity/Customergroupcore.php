<?php
/**
 * Cybage Customer Group Specific Products Plugin
 *
 * @category   Customer Group Specific Products Plugin
 * @package    Cybage_Customergroupcore
 * @copyright  Copyright (c) 2015 Cybage Software Pvt. Ltd., India
 *             http://www.cybage.com/coe/e-commerce
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Cybage Software Pvt. Ltd. 
 */

class Cybage_Customergroupcore_Model_Category_Entity_Customergroupcore extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{    
    /**
     * Retrieve customer group option array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ( !$this->_options ) {
            $this->_options = Mage::helper('customergroupcore')->getStoreCustomerGroups();
        }
        $_currentCategory = Mage::registry('current_category');
        if($_currentCategory->getParentId()) {
            $_parentCategory = Mage::getModel('catalog/category')->load($_currentCategory->getParentId());
            if($_parentCategory->getCustomergroup()) {
                $_customerGroup = array_flip($_parentCategory->getCustomergroup());
                $this->_options = array_intersect_key($this->_options,$_customerGroup);
            } else {
                $emptyOption[] = array("value"=>'-1', "label"=>'Assign customer groups to parent category first');
                return $emptyOption;
            }
        } else if(Mage::app()->getRequest()->getParam('parent')) {
            $_parentCategoryId = Mage::app()->getRequest()->getParam('parent');
            $_parentCategory = Mage::getModel('catalog/category')->load($_parentCategoryId);
            if($_parentCategory->getCustomergroup()) {
                $_customerGroup = array_flip($_parentCategory->getCustomergroup());
                $this->_options = array_intersect_key($this->_options,$_customerGroup);
            } else {
                $emptyOption[] = array("value"=>'-1', "label"=>'Assign customer groups to parent category first');
                return $emptyOption;
            }
        }

        return $this->_options;
    }
}