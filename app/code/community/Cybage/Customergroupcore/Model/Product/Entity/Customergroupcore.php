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

class Cybage_Customergroupcore_Model_Product_Entity_Customergroupcore extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
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
        if(Mage::registry('current_product')) {
            $_currentProduct = Mage::registry('current_product');
            $_categoryIds = $_currentProduct->getCategoryIds();
            if($_categoryIds) {
                $_customerGroup = array();
                foreach ($_categoryIds as $key => $value) {
                    $_parentCategory = Mage::getModel('catalog/category')->load($value);
                    if($_parentCategory->getCustomergroup()) {
                        $_customerGroup = array_merge($_customerGroup,$_parentCategory->getCustomergroup());
                    }                    
                }
                if($_customerGroup) {                    
                    $this->_options = array_intersect_key($this->_options,array_flip($_customerGroup));
                } else {
                    return false;
                }
            }
        }
        return $this->_options;
    }

    /**
     * Retrieve customer group option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    /**
     * Retrieve customer group option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $optionArray = $this->getAllOptions();
        $optionsArr = array();
        foreach($optionArray as $options) {
            $value = $options['value'];
            $label = $options['label'];
            $optionsArr[$value] =  $label;
        }
        return $optionsArr;
    }
}