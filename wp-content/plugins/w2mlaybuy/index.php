<?php // Intentionally left this file empty

if( !defined( 'ABSPATH' ) ) exit;

function w2mlaybuy_is_there_remainder( $dividend, $divisor ) {
    $remainder = $dividend % $divisor;
    return ( 0 < $remainder ) ? true : false;
}

function w2mlaybuy_get_remainder( $dividend, $divisor ) {
    return $dividend % $divisor;
}

//
function w2mlaybuy_get_price( $dividend, $divisor ) {

    // if there is a remainder, get it the weekly payment and add the remainder to first payment.
    // if there is no so even, return the weekly payment

}