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

class Cybage_Customergroupcore_Helper_Data extends Mage_Core_Helper_Abstract
{    
    /**
     * Get store customer groups
     *
     * @return array
     */
    public function getStoreCustomerGroups() {
        $options = array();
        $customerGroup = Mage::getSingleton('customer/group');
        $allGroups = $customerGroup->getCollection()->toOptionHash();
        foreach ($allGroups as $key => $allGroup) {
            $options[$key] = array( 'value' => $key, 'label' => $allGroup );
        }
        return $options;
    }
}