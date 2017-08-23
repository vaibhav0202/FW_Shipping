<?php
//Created a Vista Edition Type class to generate the dropdown for the shipping settings.  This class might not be used since Allen also created one.
//Will need to discuss this.
class FW_Shipping_Model_Vistaeditiontype_Source_Option {
        var $_options = array(
                array(
                    'value' => '1',
                    'label' => 'A',
                    'description' => 'Ancillary premium product to sell',
                ),
                array(
                    'value' => '2',
                    'label' => 'B',
                    'description' => 'Boxed',
                ),
                array(
                    'value' => '3',
                    'label' => 'C',
                    'description' => 'CD',
                ),
                array(
                    'value' => '4',
                    'label' => 'D',
                    'description' => 'Deluxe',
                ),
                array(
                    'value' => '5',
                    'label' => 'E',
                    'description' => 'Encased Sprial Bind',
                ),
                array(
                    'value' => '6',
                    'label' => 'F',
                    'description' => 'Paperback with flaps',
                ),
                array(
                    'value' => '7',
                    'label' => 'G',
                    'description' => 'DVD',
                ),
                array(
                    'value' => '8',
                    'label' => 'H',
                    'description' => 'Hardback',
                ),
                array(
                    'value' => '9',
                    'label' => 'I',
                    'description' => 'Saddle Stitch',
                ),
                array(
                    'value' => '10',
                    'label' => 'J',
                    'description' => 'Hardback with jacket',
                ),
                array(
                    'value' => '11',
                    'label' => 'K',
                    'description' => 'Paperback with jacket',
                ),
                array(
                    'value' => '12',
                    'label' => 'L',
                    'description' => 'Hardback with insert',
                ),
                array(
                    'value' => '13',
                    'label' => 'M',
                    'description' => 'Value Paperback',
                ),
                array(
                    'value' => '14',
                    'label' => 'N',
                    'description' => 'Paperback with insert',
                ),
                array(
                    'value' => '15',
                    'label' => 'O',
                    'description' => 'Subscription',
                ),
                array(
                    'value' => '16',
                    'label' => 'P',
                    'description' => 'Paperback',
                ),
                array(
                    'value' => '17',
                    'label' => 'R',
                    'description' => 'Premium',
                ),
                array(
                    'value' => '18',
                    'label' => 'S',
                    'description' => 'Sprial Bind',
                ),
                array(
                    'value' => '19',
                    'label' => 'T',
                    'description' => 'Streaming Data',
                ),
                array(
                    'value' => '20',
                    'label' => 'U',
                    'description' => 'Subright',
                ),
                array(
                    'value' => '21',
                    'label' => 'V',
                    'description' => 'Video Tape (VHS)',
                ),
                array(
                    'value' => '22',
                    'label' => 'W',
                    'description' => 'Downloadable & MP3',
                ),
                array(
                    'value' => '23',
                    'label' => 'X',
                    'description' => 'Soft/Vinyl/Flex-bind',
                ),
                array(
                    'value' => '24',
                    'label' => 'Y',
                    'description' => 'Blue Ray DVD',
                ),
                array(
                    'value' => '25',
                    'label' => 'Z',
                    'description' => 'Magazine',
                ),
                array(
                    'value' => '29',
                    'label' => '4',
                    'description' => 'Magazine',
                ),
                array(
                    'value' => '30',
                    'label' => '5',
                    'description' => 'USB/Flash drive containing audio, data/text, software or video files',
                )
            );
        	
    public function toOptionArray()
    {
        $arr = array();
        foreach ($this->_options as $k=>$v) {
            $arr[] = array('value'=>$v['value'], 'label'=>$v['description']);
        }
        return $arr;
    }	
}
?>
