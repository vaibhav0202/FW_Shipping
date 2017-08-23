<?php

class FW_Shipping_Model_Carrier_Zircon_Source_Method {
	//Generates all shipping methods that can be enabled or disabled within the system configuration section of magento.
    public function toOptionArray()
    {
        $methodList = Mage::getSingleton('fw_shipping/carrier_zircon_methodlist');
        return $methodList->toOptionArray();
    }	
}
?>
