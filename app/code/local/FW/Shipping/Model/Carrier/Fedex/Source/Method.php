<?php
/**
 * This class uses the core FedEx class to generate the list of FedEx methods for the dropdown in the vendor form.
 * I add a "FedEx - " to the prefix of the method so the user knows which carrier the method belongs to.
 */
class FW_Shipping_Model_Carrier_Fedex_Source_Method
{
    public function toOptionArray()
    {
        $fedex = Mage::getSingleton('usa/shipping_carrier_fedex');
        $arr = array();
        foreach ($fedex->getCode('method') as $k=>$v) {
            $arr[] = array('value'=>$k, 'label'=>'FedEx - ' . $v);
        }
        return $arr;
    }
}
