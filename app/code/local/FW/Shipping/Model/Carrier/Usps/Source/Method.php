<?php
/**
 * This class uses the core USPS class to generate the list of USPS methods for the dropdown in the vendor form.
 * I add a "USPS - " to the prefix of the method so the user knows which carrier the method belongs to.
 */
class FW_Shipping_Model_Carrier_Usps_Source_Method
{
    public function toOptionArray()
    {
        $usps = Mage::getSingleton('usa/shipping_carrier_usps');
        $arr = array();
        foreach ($usps->getCode('method') as $k => $v) {
            $arr[] = array('value'=>$k, 'label'=>'USPS - ' . $v);
        }
        return $arr;
    }
}
