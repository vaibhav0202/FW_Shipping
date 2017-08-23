<?php
/**
 * This class uses the core UPS class to generate the list of UPS methods for the dropdown in the vendor form.
 * I add a "UPS - " to the prefix of the method so the user knows which carrier the method belongs to.
 */
class FW_Shipping_Model_Carrier_Ups_Source_Method
{
    public function toOptionArray()
    {
        $ups = Mage::getSingleton('usa/shipping_carrier_ups');
        $arr = array();
        foreach ($ups->getCode('method') as $k=>$v) {
            $arr[] = array('value'=>$k, 'label'=>'UPS - ' . $v);
        }
        return $arr;
    }
    
}
