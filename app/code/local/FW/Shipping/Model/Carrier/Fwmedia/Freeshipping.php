<?php
/**
 * This class handles the logic for the Free Shipping to US (No Exclusions)
 */
class FW_Shipping_Model_Carrier_Fwmedia_Freeshipping extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'fwshipping_free';
    protected $_isFixed = true;

    /**
     * FreeShipping Rates Collector
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
    	//If the shipping method isn't enabled, then just return without providing this method as an option to the customer.
        if (!$this->getConfigFlag('active')) {
            return false;
        }

		//Create result object for return
        $result = Mage::getModel('shipping/rate_result');
        //Grab the package value to determine if the cart qualifies for free shipping.
        $packageValue = $request->getPackageValue();

		//If the cart is greater than or equal to the free_shipping_subtotal and the destination is US, then allow the customer to choose this method.
        $allow = ($packageValue >= $this->getConfigData('free_shipping_subtotal') && $request->getDestCountryId() == 'US');

        if ($allow) {
            $method = Mage::getModel('shipping/rate_result_method');

            $method->setCarrier('fwshipping');
            $method->setCarrierTitle($this->getConfigData('title'));
			
            $method->setMethod('9L');
            $method->setMethodTitle($this->getConfigData('name'));

            $method->setPrice('0.00');
            $method->setCost('0.00');

            $result->append($method);
        }

        return $result;
    }

	//Required function to implement from Mage_Shipping_Model_Carrier_Interface
    public function getAllowedMethods()
    {
        return array('freeshipping'=>$this->getConfigData('name'));
    }

}
