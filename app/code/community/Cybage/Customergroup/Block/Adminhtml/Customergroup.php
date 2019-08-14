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

class Cybage_Customergroup_Block_Adminhtml_Customergroup extends Mage_Adminhtml_Block_Template
{
    /**
     * Retrieve customer group option array
     *
     * @return array
     */
    public function getSelectedOptions()
    {
        $options = array();
        if($this->getRequest()->getParam('categoryIds')) {
            $options = Mage::helper('customergroupcore')->getStoreCustomerGroups();
            $_categoryIds = array_unique(explode(',' , $this->getRequest()->getParam('categoryIds')));
            $_customerGroup = array();
            foreach ($_categoryIds as $key => $value) {
                $_parentCategory = Mage::getModel('catalog/category')->load($value);
                if($_parentCategory->getCustomergroup()) {
                    $_customerGroup = array_merge($_customerGroup,$_parentCategory->getCustomergroup());
                }
            }

            if($_customerGroup) {
                $options = array_intersect_key($options,array_flip($_customerGroup));
                return $options;
            }
        }
    }

    /**
     * Retrieve already assigned customer group option array
     *
     * @return array
     */
    public function getAssignedGroups()
    {  
        if($this->getRequest()->getParam('assigned_groups')) {
            return explode(',' , $this->getRequest()->getParam('assigned_groups'));
        }
        return false;
    }
}