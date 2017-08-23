<?php
/**
 * Calculates non dropshipped shipping based on the defined shipping matrix map.
 */
class FW_Shipping_Model_Carrier_Fwmedia_Rate
{
	/**
	 * The shipping matrix map.
	 * Each shipping method has it's own definition using the shipping code as the map key.
	 * Each shipping code map has the keys label, default_rate, handling_fee, product_level_rate(optional), free_method(optional), allowed_countries
	 * label - Shipping name
	 * handling_fee - the handling fee of the shipping method.
	 * default_rate - the rate charged per item on that shipping method.
	 * product_level_rate - exists if different vista edition types should charge different shipping than the default rate and contains a map of the edition
	 *                      type as the key and the shipping price as the value.
	 * free_method - exists if the shipping method is free for F+W Media product types, but not for dropshipping.
	 * allowed_countries - Currently the only options are 'US, CA, ~US'.  'US' = United States, 'CA' = Canada and ~US = anywhere outside of US.
	 * */
	var	$rateTable = array(		
		'9A' => array('label'=> 'Standard Shipping', 
					  'default_rate' => '0.99', 
					  'handling_fee'=>'3.00',
					  'product_level_rate'=>array('29'=>'0.12'),
					  'allowed_countries'=>'US'),
					  
		'9B' => array('label'=> 'Ground', 
					  'default_rate' => '0.99', 
				      'handling_fee'=>'8.99',
				      'product_level_rate'=>array('29'=>'0.12'),
				      'allowed_countries'=>'US'),
				      
		'9C' => array('label'=> '2 Day',
					  'default_rate' => '1.99', 
					  'handling_fee'=>'9.99',
					  'product_level_rate'=>array('29'=>'0.25'),
				      'allowed_countries'=>'US'),
					  
		'9D' => array('label'=> 'Standard Overnight',
					  'default_rate' => '4.99', 
					  'handling_fee'=>'12.99',
					  'product_level_rate'=>array('29'=>'0.62'),
				      'allowed_countries'=>'US'),
					  
		'9G' => array('label'=> 'Free Standard Shipping to US',
					  'default_rate' => '0.00', 
					  'handling_fee'=>'0.00',
					  'free_method' => true,
				      'allowed_countries'=>'US'),
					  
		'9F' => array('label'=> 'International Air Printed Matter',
					  'default_rate' => '9.00', 
					  'handling_fee'=>'2.00',
					  'product_level_rate'=>array('1'=>'5.00', '3'=>'5.00', '7'=>'5.00', '21'=>'5.00', '24'=>'5.00', '25'=>'5.00', '29'=>'0.63', '30' => '5.00'),
				      'allowed_countries'=>'~US'),

		'9I' => array('label'=> 'Ground Holiday Special',
					  'default_rate' => '0.99', 
					  'handling_fee'=>'3.00',
					  'product_level_rate'=>array('29'=>'0.12'),
				      'allowed_countries'=>'US'),

		'9J' => array('label'=> 'Free Air Printed Matter to Canada',
					  'default_rate' => '0.00', 
					  'handling_fee'=>'0.00',
					  'free_method' => true,
				      'allowed_countries'=>'CA'),
		
		'9M' => array('label'=> 'Flat Rate US Postal Service',
					  'default_rate' => '0.00', 
					  'handling_fee'=>'5.00',
					  'allowed_countries'=>'US'),
					  
		'9N' => array('label'=> 'US Postal Service Parcel Post', 
					  'default_rate' => '0.99', 
					  'handling_fee'=>'5.99',
					  'product_level_rate'=>array('29'=>'0.12'),
					  'allowed_countries'=>'US'),
					  
		'9O' => array('label'=> 'Standard Shipping with Tracking', 
					  'default_rate' => '0.99',
					  'handling_fee'=>'4.99',
					  'product_level_rate'=>array('29'=>'0.12'),
					  'allowed_countries'=>'US'),
                      
        '9Q' => array('label'=> 'Free Standard Shipping with Tracking',
                      'default_rate' => '0.00', 
                      'handling_fee'=>'0.00',
                      'free_method' => true,
                      'allowed_countries'=>'US'),	  
	);
	
	private function getRateTable() {
	    return $this->rateTable;
	}

	/**
	 * Calculates the shipping costs based on the cart items.
	 * @param shippingCode - the shipping carrier code that is to be calculated
	 * @param cartItems - all of the items in the shopping cart
	 * @param freeVistaEditionTypes - any vista edition types that are to be excluded in shipping costs.  They will also not count as "Free Shippable".
	 *                                defaults are 'O, R, T, W' vista edition types.
	 * @return rate - shipping rate based on defined shipping matrix map.
	 */
    public function getRate($shippingCode, $cartItems, $freeVistaEditionTypes = array('15', '17', '19', '22')) {

		//Initiate rate, handling, shipping rate and apply handling variables.
    	$rate = 0.00;
    	$handling = $this->rateTable[$shippingCode]['handling_fee'];
		
	$shippingRate = $this->rateTable[$shippingCode];
		
	$applyHandlingFee = false;
	$configurables = array();
    	foreach($cartItems as $item) {
    		//Load the product based off the cart item. 
    		$product = Mage::getModel('catalog/product')->load( $item->getProductId() );
    		if($item->getParentItem()) {
			continue;
		}
    		//Skips to the next item if the product belongs to a vendor. It's possible to get here if a cart has mixed items of dropshipped and F+W Media products.
    		if($product->getDropshipVendorId() != 0 && $product->getDropshipVendorId() != null && $product->getDropshipVendorId() != '') {
    			continue;
    		}
    		
    		$productType = $product->getVistaEditionType();
    		
			//Skips to the next item if product vista edition type is in the array freeVistaEditionTypes
	    	if(in_array($productType, $freeVistaEditionTypes)) {
	    		continue;
	    	}
	    	
	    	//We reached an item that is not assigned a dropshipper and isn't considered a free shipping type so set applyHandlingFee to free and get the rate. 
	    	$applyHandlingFee = true;
	    	$itemQty = $item->getQty();
	    	
	    	//First check to see if the vistaEditionType is a special rate, if so use that rate instead of default.
	    	if(isset($shippingRate['product_level_rate']) && isset($shippingRate['product_level_rate'][$productType])) {
	    		$rate += $shippingRate['product_level_rate'][$productType] * $itemQty;
	    	} else {
	    		//Use default rate based on shipping code that was passed.
	    		$rate += $shippingRate['default_rate'] * $itemQty;
	    	}
    	}
    	
    	if($applyHandlingFee == true) {
    		$rate += $shippingRate['handling_fee'];
    	}
    	
    	return $rate;
    }	
	
	public function canShipToCountry($shippingCode, $destCountryCode) {
		$allowedCountry = $this->rateTable[$shippingCode]['allowed_countries'];
		$ret = false;
		//The code ~US means anywhere outside of the US
		if(($allowedCountry == $destCountryCode) || ($allowedCountry == '~US' && $destCountryCode != 'US')) {
			$ret = true;
		}
		return $ret;
	}
	
	//Used in FW_Shipping_Model_Carrier_Fwmedia_Source_Method to generate the selection list in the admin.
	public function toOptionArray() {
		$ret = array();
		foreach($this->rateTable as $k=>$v) {
			$ret[] = array('label'=>$v['label'], 'value'=>$k);
		}
		return $ret;
	}
	
	public function getLabel($shippingCode) {
		return $this->rateTable[$shippingCode]['label'];
	}
	
	//Determines if the shipping method is a free shipping method that still charges for dropship.
	public function isFreeShipMethod($shippingCode) {
		return (isset($this->rateTable[$shippingCode]['free_method'])) ? $this->rateTable[$shippingCode]['free_method'] : false; 
	}
}
