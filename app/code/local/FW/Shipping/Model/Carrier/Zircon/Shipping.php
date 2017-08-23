<?php
/**
 * Created by PhpStorm.
 * User: dmatriccino
 * Date: 11/20/14
 * Time: 11:22 AM
 */

class FW_Shipping_Model_Carrier_Zircon_Shipping extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface {
    protected $_code = 'zirconshipping';
    protected $_default_condition_name = 'package_value';
    /**
     * Collect and get rates
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result|bool|null
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if(!$this->getConfigData('active')) {
            return false;
        }

        $destinationCountryId = $request->getDestCountryId();

        $result = Mage::getModel('shipping/rate_result');

        $additionalShipping = 0;

        // exclude downloadable/virtual products price from Package value if pre-configured and set shipping markup
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        $additionalShipping += ($child->getProduct()->load()->getAdditionalShipping() * $child->getQty());
                        if ($child->getProduct()->isVirtual()) {
                            $request->setPackageValue($request->getPackageValue() - $child->getBaseRowTotal());
                        }
                    }
                } else {
                    Mage::log($item->getProductId());
                    $product = Mage::getModel('catalog/product')->load($item->getProductId());
                    if($item->getProduct()->isVirtual()) {
                        $request->setPackageValue($request->getPackageValue() - $item->getBaseRowTotal());
                    }
                    Mage::log($item->getProduct()->load()->getAdditionalShipping());
                    $additionalShipping += ($item->getProduct()->load()->getAdditionalShipping() * $item->getQty());
                }
            }
        }
        Mage::log($additionalShipping);
        $request->setConditionName($this->_default_condition_name);

        //Get table rate method to retrieve the rates
        $tableRateMethod = Mage::getModel('shipping/carrier_tablerate');

        $tableRateResult = Mage::getResourceModel('shipping/carrier_tablerate')->getRate($request);

        //Save original shipping cost, in case there is a free shipping coupon. This will be used to add
        //the appropriate markups to for available UPS methods.
        $originalShippingCost = $tableRateResult['price'];

        //Check if shipping is free, if so set base shipping cost to 0.00
        $baseShippingCost = ($request->getFreeShipping()) ? 0 : $originalShippingCost;

        //Check for international shipping and add markup even if free shipping coupon is used
        //Add special markup if destination is Canada
        if($destinationCountryId == 'CA') {
            $canadaMarkup = $this->getConfigData('canada_markup');
            $baseShippingCost += $canadaMarkup;
        }
        //Destination is outside of CA and US
        elseif($destinationCountryId != "US") {
            $internationalMarkup = $this->getConfigData('international_markup');
            $baseShippingCost += $internationalMarkup;
        }

        $baseShippingCost += $additionalShipping;
        $originalShippingCost += $additionalShipping;

        $tableRate = Mage::getModel('shipping/rate_result_method');
        $tableRate->setPrice($baseShippingCost);
        $tableRate->setCost($baseShippingCost);
        $tableRate->setMethod($this->getConfigData('tablerate_code'));
        $tableRate->setMethodTitle($this->getConfigData('tablerate_label'));
        $tableRate->setCarrier('zirconshipping');

        $result->append($tableRate);

        //Add other shipping methods only if there is already a shipping cost
        if($originalShippingCost > 0) {
            $allowedMethods = $this->getAllowedMethods($destinationCountryId);

            foreach($allowedMethods as $shippingCode=>$method) {
                $shippingMethod = Mage::getModel('shipping/rate_result_method');
                $shippingMethod->setCarrier('zirconshipping');
                $shippingMethod->setMethod($shippingCode);
                $shippingMethod->setMethodTitle($method['label']);
                $shippingMethod->setPrice($method['markup'] + $originalShippingCost);
                $shippingMethod->setCost($method['markup'] + $originalShippingCost);
                $result->append($shippingMethod);
            }
        }

        return $result;
    }

    /**
     * Get allowed shipping methods aside from default table rate method
     *
     * @return array
     */
    public function getAllowedMethods($destCountryId = 'US')
    {
        //TODO: Implement method to retrieve available methods by active and destination country

        //Placeholder for now until more requirements are defined
        $upsGroundCountries  = explode(',', $this->getConfigData('upsground_allowed_countries'));
        $ups2DayCountries    = explode(',', $this->getConfigData('ups2day_allowed_countries'));
        $upsNextDayCountries = explode(',', $this->getConfigData('upsnextday_allowed_countries'));
        $allowedMethods = array();

        if(in_array($destCountryId, $upsGroundCountries)) {
            $allowedMethods[$this->getConfigData('upsground_code')] =  array(
                'label'=>$this->getConfigData('upsground_label'),'markup' => $this->getConfigData('upsground_markup')
            );
        }

        if(in_array($destCountryId, $ups2DayCountries)) {
            $allowedMethods[$this->getConfigData('ups2day_code')] =  array(
                'label'=>$this->getConfigData('ups2day_label'),'markup' => $this->getConfigData('ups2day_markup')
            );
        }

        if(in_array($destCountryId, $upsNextDayCountries)) {
            $allowedMethods[$this->getConfigData('upsnextday_code')] =  array(
                'label'=>$this->getConfigData('upsnextday_label'),'markup' => $this->getConfigData('upsnextday_markup')
            );
        }
        return $allowedMethods;
    }

}