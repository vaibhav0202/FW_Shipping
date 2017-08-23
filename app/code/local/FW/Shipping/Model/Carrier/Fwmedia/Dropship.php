<?php
/**
 * This class calculates the dropshipping costs for all of the dropship items in the cart.
 */
class FW_Shipping_Model_Carrier_Fwmedia_Dropship {

	public function getRate(Mage_Shipping_Model_Rate_Request $request) {
		//Start the price off to be 0.
		$price = 0;
		
		if ($request->getAllItems()) {
			$shipments = array();
			//Grab all the dropship items and add their weight in a shipement array using the vendor id as the array key 
			foreach ($request->getAllItems() as $item) {
				//Load the product from the cart item
				$tmpProduct = Mage::getModel('catalog/product')->load($item->getProductId());
				  
				if($tmpProduct->getDropshipVendorId() != 0 && $tmpProduct->getDropshipVendorId() != '') {
					$tmpWeight = $item->getProduct()->getWeight() * $item->getQty();
					$vendorId = $tmpProduct->getDropshipVendorId();
					//If shipment already exists for the specified vendor, add the product weight to the current weight in the array.
					if(array_key_exists($vendorId, $shipments)) {
						$shipments[$vendorId] += $tmpWeight;
					}
					//Create a new shipment element using the vendor id and assign it the weight of the product in the cart.
					else {
						$shipments[$vendorId] = $tmpWeight;
					}
				}
			}
			
			//If shipment is going outside of US, shipping will be calculated as using Priority Mail International (USPS)
			$shipOutsideOfUs = ($request->getDestCountryId() != 'US');
			
			//Loop through all the shipments to calculate the real time rates.
			foreach($shipments as $vendorId=>$packageWeight) {
				$shippingCarrier = "";
				
				//Load the vendor from database
				$vendor  = Mage::getModel('dropship/vendor')->load($vendorId);
				
				//get the shipping method assigned to the vendor
				$vendorShippingMethod = $vendor->getShippingMethod();

				$carrierObject = null;
				if($shipOutsideOfUs) {
					//If destination is outside of US, use USPS Priority Mail International method always.
					$shippingCarrier = "USPS";
					$carrierObject = Mage::getSingleton('fw_shipping/carrier_usps_rate');
					
					//Set vendorShippingMethod to INT_2 ONLY if it's not already 0
					$vendorShippingMethod = ($vendorShippingMethod == '0') ? $vendorShippingMethod : 'INT_2';
				} 
				else {
					//Get the carrier the vendor uses in order to grab the proper core shipping object to calculate the shipping.
					$shippingCarrier = $vendor->getShippingCarrier();
					
					switch($shippingCarrier) {
						case 'ups':
							$carrierObject = Mage::getSingleton('fw_shipping/carrier_ups_rate');
							break;
						case 'fedex':
							$carrierObject = Mage::getSingleton('fw_shipping/carrier_fedex_rate');
							break;
						case 'usps':
							$carrierObject = Mage::getSingleton('fw_shipping/carrier_usps_rate');
							break;
						case 'none':
							//Shipping is free so go to next package.
							continue;
							break;
						default:
							$carrierObject = null;
							$logMessage = "Carrier was {$shippingCarrier} and not a valid option";
							Mage::log($logMessage, null, 'FW_Vendor.log');
							continue;
							break;
					}
				}
				
				//Get address information from the vendor to know where the shipment is shipping from.
				$origCity = $vendor->getCity();
				$origStateCode = $vendor->getStateCode();
				$origPostalCode = $vendor->getPostalCode();
				$origCountry = $vendor->getCountryCode();
				
				//Change the origin address in the request to calculate the shipping from the vendor's address.
				$request->setOrigPostcode($origPostalCode);
				$request->setOrigRegionCode($origStateCode);
				$request->setOrigCity($origCity);
				$request->setOrigCountryId($origCountry);
				$request->setPackageWeight($packageWeight);
				
				//Check to see if vendor has a shipping method assigned. If not, shipping is free for the dropship items.
				if($vendorShippingMethod != '0' && $vendorShippingMethod != '' && $carrierObject != null) {
					
					$carrierObject->setShippingMethod($vendorShippingMethod);
					$result = $carrierObject->collectRates($request);
					if(!$result->getError()) {
						//Vendor only has one method attached, so this will grab the rate.
						$price += $result->getCheapestRate()->getPrice();
					} else {
						//Log error and notify managers of error via email.
						$logMessage = "There has been an error calculating dropshipping.  The shipping details are as follows: \r\n";
						$logMessage .= "Vendor: " . $vendor->getName() . "\r\n";
						$logMessage .= "Shipping Carrier: $shippingCarrier\r\n";
						$logMessage .= "Shipping Method: $vendorShippingMethod\r\n";
						$logMessage .= "Package Weight: $packageWeight\r\n";
						$logMessage .= "Destination: " . $request->getDestCountryId() . ", " . $request->getDestPostcode() . "\r\n";
						  
						Mage::log($logMessage, null, 'FW_Dropshipping.log');
						
						$emailErrorList = $vendor->getErrorEmailList();
						$emailErrorListArray = explode(",", $emailErrorList);
						$fromEmail = Mage::getStoreConfig('trans_email/ident_support/email');
						
				        $mail = new Zend_Mail();
				        
				        $mail->setBodyText($logMessage);
				        $mail->setFrom($fromEmail, "Magento Dropshipper Calculation Error")
				            ->setSubject("Magento Dropshipper Calculation Error");
				    	
				    	foreach($emailErrorListArray as $emailAddress) {
				    		$mail->addTo(trim($emailAddress), $emailAddress);
				    	}
				    	try {
				    		$mail->send();
				    	} catch (Exception $e) {
				    		//Log
				    		Mage::log("Error sending shipping error notification", null, 'FW_Dropshipping.log');
				    	}						
					}
				}
			}			
		}
		//Price is returned even if there is an error.
		return $price;
	}
}
?>
