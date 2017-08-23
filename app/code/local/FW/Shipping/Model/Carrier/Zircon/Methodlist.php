<?php
/**
 * Created by PhpStorm.
 * User: dmatriccino
 * Date: 11/20/14
 * Time: 11:28 AM
 */

class FW_Shipping_Model_Carrier_Zircon_Methodlist {

    public function toOptionArray() {

        $path = 'carriers/zirconshipping/';

        $tableRateLabel = Mage::getStoreConfig($path . 'tablerate_label');
        $tableRateCode = Mage::getStoreConfig($path . 'tablerate_code');

        $upsGroundLabel = Mage::getStoreConfig($path . 'upsground_label');
        $upsGroundCode = Mage::getStoreConfig($path . 'upsground_code');

        $ups2DayLabel = Mage::getStoreConfig($path . 'ups2day_label');
        $ups2DayCode = Mage::getStoreConfig($path . 'ups2day_code');

        $upsNextDayLabel = Mage::getStoreConfig($path . 'upsnextday_label');
        $upsNextDayCode = Mage::getStoreConfig($path . 'upsnextday_code');

        $methodList = array(
            array('label'=>$tableRateLabel, 'value'=>$tableRateCode),
            array('label'=>$upsGroundLabel, 'value'=>$upsGroundCode),
            array('label'=>$ups2DayLabel, 'value'=>$ups2DayCode),
            array('label'=>$upsNextDayLabel, 'value'=>$upsNextDayCode),
        );

        return $methodList;
    }
}