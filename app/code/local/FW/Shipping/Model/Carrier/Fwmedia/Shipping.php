<?php
class FW_Shipping_Model_Carrier_Fwmedia_Shipping extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface {
	protected $_code = 'fwshipping';


	public function collectRates(Mage_Shipping_Model_Rate_Request $request)
	{
		if (!Mage::getStoreConfig('carriers/'.$this->_code.'/active')) {
			return false;
		}
		
		$cartItems = $request->getAllItems();
		$result = Mage::getModel('shipping/rate_result');
		
		//Check for product destination restrictions and if so don't allow customer to continue.
		$destinationCountry = $request->getDestCountryId();
		
        //Get any items that might be restricted to the selected country
		$restrictedItems = $this->_getItemCountryRestrictions($cartItems, $destinationCountry);
        
        //If there are restrictions create error message and return the error 
		if($restrictedItems !== false) 
        {
            $marketRestriction = Mage::getSingleton('fw_shipping/marketrestriction');
			$errorMessageArray = array();
            foreach($restrictedItems as $marketRestrictionValue=>$productArray)
            {   
                //Add error message to errorMessageArray to create an error string later using the
                //marketRestrictionValue and the productArray that's in restrictedItems 
                $errorMessageArray[] = $marketRestriction->getErrorMessage($marketRestrictionValue, $productArray);
            }
            
            //Separate all the error messages with a <br /> to have one line per error when displaying to the customer.
            $errorMessage = implode('<br />', $errorMessageArray);
			
			//Create a result error to show to customer when trying to calculate shipping or checkout.			
	        $error = Mage::getModel('shipping/rate_result_error');
	        $error->setCarrier('fwshipping');
	        $error->setCarrierTitle('fwshipping');
	        $error->setErrorMessage($errorMessage);
	        
	        //Return error, appending the error to the $result object won't show the error message on the checkout page.
			return $error;
		}

		if($this->_cartHasAllFreeProductTypes($cartItems)) {
			$method = Mage::getSingleton('shipping/rate_result_method');
			$method->setCarrier('fwshipping');
			$method->setMethod('9A');
			$method->setCarrierTitle('F+W Media');
			$method->setMethodTitle('Free Shipping');
			$method->setPrice("0.00");
			$method->setCost("0.00");
			$result->append($method);
			return $result;				
		}


		
		$rateTable = Mage::getModel('fw_shipping/carrier_fwmedia_rate');
		$dropship = Mage::getSingleton('fw_shipping/carrier_fwmedia_dropship');
		//NOTE: This changes the request variable.  If we want to start using real time shipping rates from own warehouse, 
		//then we will need send a clone of the request object.
		$dropshipCost = $dropship->getRate($request);
		
		//Check if the cart only has dropshipping items in it.
		if($this->_cartHasAllDropship($cartItems)) {
			$method = Mage::getSingleton('shipping/rate_result_method');
			$method->setCarrier('fwshipping');
			$method->setMethod('9A');
			$method->setCarrierTitle('F+W Media');
			$method->setMethodTitle('Standard Shipping');
			$method->setPrice($dropshipCost);
			$method->setCost($dropshipCost);
			$result->append($method);			
		}
		else {
			//Grab all the free product types
                    $freeProductTypes = $this->getConfigData('free_product_types');
                    $freeProductTypesArray = explode(',', $freeProductTypes);
                    $allowedMethods = $this->getAllowedMethods($request->getDestCountryId());

    		//Loop through all the allowed methods and grab the rate based on the shipping matrix
            foreach($allowedMethods as $carrierCode=>$shippingLabel) {
				
				//Grab the price for the specific method.
				$price = $rateTable->getRate($carrierCode, $cartItems, $freeProductTypesArray);
				$dropshipCostAdded = '';
				
				//Initialize the method object and set appropriate properties.
				$method = Mage::getModel('shipping/rate_result_method');
				$method->setCarrier('fwshipping');
				$method->setMethod($carrierCode);

				$method->setCarrierTitle('Shipping');
				
				//Determines if the method should be added to the result object or not. Default is true
				$shippingMethodAllowed = true;
				
				//If the method is a free ship method and the promotion is triggered.
				if($request->getFreeShipping() && $rateTable->isFreeShipMethod($carrierCode)) {
					//Check to see if dropship cost was added in order to notify the customer.
					$dropshipCostAdded = ($dropshipCost > 0) ? '(Cost for dropship items has been added)' : '';
					//Set the shipping price to 0.00
					$price = 0;
				} else if($rateTable->isFreeShipMethod($carrierCode)) {
					//If the rate is a free shipping method but the promotion wasn't triggered, don't add the method to the result.
					$shippingMethodAllowed = false;
				}
				
				//Only add the shipping method to the result if true.
				if($shippingMethodAllowed == true) {
					
					//Add the dropshipCost to the shipping.
					$price += $dropshipCost;
					
					//Set the methodTitle (dropshipCostAdded is a string in case it's a free shipping method but still charging for dropshipped items)
					$method->setMethodTitle($shippingLabel . $dropshipCostAdded);
					$method->setPrice($price);
					$method->setCost($price);
					
					$result->append($method);
				}
			}
		}
		return $result;
	}
	
	//Gets allowed methods based on admin configuration and destination country.
    public function getAllowedMethods($destCountryId = 'US')
    {
    	//All of the allowed methods that are selected in the shipping method configuration of magento admin.
    	//The methods are comma separated by carrier code.
         $allowed = explode(',', $this->getConfigData('allowed_methods'));
         //Returned map where the key is the method carrier code and the value is the shipping method name.
         $arr = array();
         
         //Check to see if the allowed array's first item isn't null or blank
         if($allowed[0] != null && $allowed[0] != '') {
	         $rateTable = Mage::getSingleton('fw_shipping/carrier_fwmedia_rate');
	         
	         //Loop through the allowed array and add it to the return array if it's allowed to ship to the destiniation country.
	         foreach ($allowed as $k) {
	         	if($rateTable->canShipToCountry($k, $destCountryId)) {
	         		$arr[$k] = $rateTable->getLabel($k);
	         	}
	         }
         }
         return $arr;
    }
    
    //Determines if the cart has all dropship items.
    private function _cartHasAllDropship($cartItems) {
        $ret = true;
    	$freeProductTypes = $this->getConfigData('free_product_types');
    	$freeProductTypesArray = explode(',', $freeProductTypes);
        
        foreach($cartItems as $item) {
            //If the item is a child item, skip it as it doesn't have the correct vendor information and will lead to
            //thinking the cart has an item that is not dropshipped. Also if it's a free shipping item, you want to skip it
            //as well.
            if($item->getParentItem() || $this->_isProductFreeType($item, $freeProductTypesArray)) {
                continue;
            }
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $vendorId = $product->getDropshipVendorId();
            //If an item is found that isn't assigned a vendor, then return false
            if($vendorId == '0' || $vendorId == null || $vendorId == '') {
                $ret = false;
                break;
            }
        }
        return $ret;
    }

    //Check if product is a free type. Accepts product and freeProductType Array.
    //Function takes in the array as a parameter instead of loading from config each call.
    private function _isProductFreeType($item, $freeProductTypesArray) {
        $product = Mage::getModel('catalog/product')->load($item->getProductId());
        $productType = $product->getVistaEditionType();
 	return in_array($productType, $freeProductTypesArray);
    }
    
    //Determines if cart has all free product types.
    private function _cartHasAllFreeProductTypes($cartItems) {
    	$ret = true;
    	$freeProductTypes = $this->getConfigData('free_product_types');
    	$freeProductTypesArray = explode(',', $freeProductTypes);
		
    	foreach($cartItems as $item) {
    		//If an item is found that isn't a free product type, then return false.
    		if(!$this->_isProductFreeType($item, $freeProductTypesArray)) {
    			$ret = false;
    			break;
    		}
    	}
    	return $ret;    	
    }
    
    //Checks to see if items can be sent to destiniation country or not.
    private function _getItemCountryRestrictions($cartItems, $destinationCountry) {
    	$ret = array();
    	
        //Get singleton of the Marketrestriction Object
        $marketRestriction = Mage::getSingleton('fw_shipping/marketrestriction');

        //Loop through cart items and check for restrictions.
    	foreach($cartItems as $item) {
    		$product = Mage::getModel('catalog/product')->load($item->getProductId());
            
            //Grab the market restriction value that the product is set to.
            $marketRestrictionValue = $product->getMarketRestriction();
            
            //If the value is 0 there are no restrictions, so move on to next product
            if($marketRestrictionValue == 0) 
            {
                continue;
            }
            
            //Check to see if the product is restricted based on the market restriction value and destination country.
            if($marketRestriction->isRestricted($marketRestrictionValue, $destinationCountry))
            {
                //Place in return array with the marketRestrictionValue as the key and the product name as the value.
                $ret[$marketRestrictionValue][] = $product->getName();
            }
    	}
    	//If data in $ret then return the array otherwise return false.
    	return (count($ret) > 0) ? $ret : false;
    }
    
}