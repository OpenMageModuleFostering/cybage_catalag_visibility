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

class Cybage_Customergroupcore_Model_Category_Attribute_Backend_Customergroup
    extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    const ATTRIBUTE_CODE = 'customergroup';

    public function validate($object)
    {
    }

    /**
     * Before Attribute Save Process
     *
     * @param Varien_Object $object
     * @return Cybage_Customergroupcore_Model_Category_Attribute_Backend_Customergroup
     */
    public function beforeSave($object) {
        $attributeCode = $this->getAttribute()->getName();
        if ($attributeCode == self::ATTRIBUTE_CODE) {
            $data = $object->getData($attributeCode);
            if (!is_array($data)) {
                $data = array();
            }
            $object->setData($attributeCode, join(',', $data));
        }
        if (is_null($object->getData($attributeCode))) {
            $object->setData($attributeCode, false);
        }
        return $this;
    }

    /**
     * After Attribute Load Process
     *
     * @param Varien_Object $object
     * @return Cybage_Customergroupcore_Model_Category_Attribute_Backend_Customergroup
     */
    public function afterLoad($object) {
        $attributeCode = $this->getAttribute()->getName();
        if ($attributeCode == self::ATTRIBUTE_CODE) {
            $data = $object->getData($attributeCode);            
            if (strlen($data)) {
                $object->setData($attributeCode, explode(',', $data));
            }
        }
        return $this;
    }
}