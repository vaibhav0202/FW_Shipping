<?php

class FW_Shipping_Model_Carrier_Fwmedia_Source_Method {
	//Generates all shipping methods that can be enabled or disabled within the system configuration section of magento.
    public function toOptionArray()
    {
        $rateTable = Mage::getSingleton('fw_shipping/carrier_fwmedia_rate');
        return $rateTable->toOptionArray();
    }	
}
?>
