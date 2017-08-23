<?php
class FW_Shipping_Model_Marketrestriction
{
    //Array table to define the different market restrictions
    //label is used for the product create/edit page in admin for the new Market Restriction attribute
    //allowed_countries is an array of country codes that the product is allowed to ship to.
    //the index value of each array (0, 1, 2, 3) are the values set to the product for the given Market Restriction attribute. 
    var $restrictionTable = array(
        0 => array('label' => 'No Restrictions',
              'allowed_countries' => array()),
              
        1 => array('label' => 'US',
              'code'  => 'us',
              'allowed_countries' => array('US')
        ),
              
        2 => array('label' => 'US & Canada',
              'allowed_countries' => array('US', 'CA')
        ),
              
        3 => array('label' => 'North America',
              'allowed_countries' => array('US', 'CA', 'MX')
        )
    );
    
    
    //Builds the optionsArray for the FW_ProductCustom_Model_Resource_Eav_Source_Restrictedmarkets class to use
    public function getOptionsArray() 
    {
        $options = array();
        
        foreach($this->restrictionTable as $marketRestrictionValue=>$v) 
        {
            $options[] = array('value' => $marketRestrictionValue, 'label' => $v['label']);
        }
        
        return $options;
    }
    
    //Returns an error message based off the given marketRestrictionValue and the productString
    public function getErrorMessage($marketRestrictionValue, $productArray) 
    {
        if($marketRestrictionValue == 0) 
        {
            return "";
        }
        $restrictionArray = $this->restrictionTable[$marketRestrictionValue];
        
        //Separates the values in the product array with a comma.
        $productString = implode(", ", $productArray);
        //Separates the values in the allowed_countries array with a comma.
        $countryString = implode(", ", $restrictionArray['allowed_countries']);
        $message = "The product(s) " . $productString . " are restricted to " . $countryString . " only.";
        return $message;
    }
    
    //Determines if the given marketRestrictionValue is allowed to ship to the given destinationCountryCode or not
    public function isRestricted($marketRestrictionValue, $destinationCountryCode) 
    {
        return !in_array($destinationCountryCode, $this->restrictionTable[$marketRestrictionValue]['allowed_countries']);
    }
    
    //Returns notification message for the product page if there is a restriction on it.
    public function getNotificationMessageForProductPage($marketRestrictionValue) 
    {
        if($marketRestrictionValue == 0)
        {
            return "";
        }
        $restrictionArray = $this->restrictionTable[$marketRestrictionValue];
        $countryString = implode(", ", $restrictionArray['allowed_countries']);
        $message = "This product is not available outside of {$countryString}.";
    }
}

?>
