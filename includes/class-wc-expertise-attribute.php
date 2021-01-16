<?php
if ( class_exists( 'WC_Product_Attribute' ) ) :

class Expertise_Attribute extends WC_Product_Attribute
{
    public function __construct()
    {
        parent::__construct();
        array_merge(
            $this->data,
			array('is_cert'=> $this->get_cert())
        );
        
    }

    public function get_cert()
    {
        return $this->data['is_cert'];
    }

    public function set_cert( $value )
    {
        $this->data['is_cert'] = $value;
    }
}

endif;