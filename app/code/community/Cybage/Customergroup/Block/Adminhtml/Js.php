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

class Cybage_Customergroup_Block_Adminhtml_Js extends Mage_Adminhtml_Block_Catalog_Product_Edit_Js
{
    /**
     * Check current product is assigned to any category
     *
     * @return  boolean
     */
    public function isAssignedToCategory()
    {
        $_currentProduct = $this->getProduct();
        $_categoryIds = $_currentProduct->getCategoryIds();
        if($_categoryIds) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check and return if current product is assigned to any customer group
     *
     * @return  array
     */
    public function getAssignedGroups()
    {
        $_currentProduct = $this->getProduct();
        $_customerGroups = $_currentProduct->getCustomergroup();
        if($_customerGroups) {
            return implode(',' , $_customerGroups);
        } else {
            return false;
        }
    }
}