<?php
class FW_Shipping_Model_Carrier_Fedex_Rate extends Mage_Usa_Model_Shipping_Carrier_Fedex {
	protected $methodCode;
	
	//Override collectRates because using the core function won't run unless the FedEx shipping method is enabled.
	public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
    	//These are all functions in the Mage_Usa_Model_Shipping_Carrier_Fedex class.
        $this->setRequest($request);

        $this->_result = $this->_getQuotes();

        return $this->getResult();
    }

	//Sets the shipping method to use for calculation.
	public function setShippingMethod($methodCode) {
    	$this->methodCode = $methodCode;
    }

	//Overriding getConfigData to only grab a single method when the methodCode is set.
	//It's only when passing 'allowed_methods within the config file.'
	public function getConfigData($field) {
		if($field == 'allowed_methods' && $this->methodCode != null && $this->methodCode != '') {
			return $this->methodCode;
		}
		else {
			return parent::getConfigData($field);
		}
	}
}
?>
