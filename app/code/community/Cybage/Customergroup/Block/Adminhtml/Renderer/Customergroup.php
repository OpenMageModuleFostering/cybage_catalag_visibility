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

class Cybage_Customergroup_Block_Adminhtml_Renderer_Customergroup extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    const ZERO = 0;
    
    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        if(strlen($row->getCustomergroup())) {
            $customerGroups = explode(',',$row->getCustomergroup());
            $suppStr = "";
            if(count($customerGroups) > self::ZERO)
            {
                $suppStr = "<ul>";
                foreach($customerGroups as $groupId)
                {
                    $productModel = Mage::getModel('catalog/product');
                    $attr = $productModel->getResource()->getAttribute("customergroup");
                    if ($attr->usesSource()) {
                        $suppStr .= "<li>".$attr->getSource()->getOptionText("$groupId")."</li>";
                    }
                }
                $suppStr .= "</ul>";
            }
            return $suppStr;
        }
    }
}