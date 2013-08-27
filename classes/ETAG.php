<?php

class ETAG
{
    public $time;
    public $permission;
    function __construct( $string = false ) {
        if ( $string === false )
        {
        	return;
        }
        $string = rtrim( $string, '"' );
        $string = ltrim( $string, '"' );
        $list = explode( "-", $string );
        if ( $list[0] )
        {
            $this->time = $list[0];
        }
        if ( $list[1] )
        {
            $this->permission = $list[1];
        }
    }
    function generate()
    {
        $str = '"';
        if ( $this->time )
        {
            $str .= $this->time;
        }
        $str .= '-';
        if ( $this->permission )
        {
            $str .= $this->permission;
        }
        $str .= '"';
    	return $str;
    }
}