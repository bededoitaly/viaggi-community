<?php
/**
 * Shortcode [vc_field field="meta_key" post_id="123" format="..." esc="html|attr|raw" upper="1" fallback="..."]
 * Restituisce il valore di un post meta in modo sicuro e formattabile.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_shortcode( 'vc_field', function( $atts ) {
    $atts = shortcode_atts( array(
        'field'    => '',
        'post_id'  => '',
        'format'   => '',
        'esc'      => 'html',
        'upper'    => 0,
        'fallback' => '',
    ), $atts, 'vc_field' );

    $field = trim( $atts['field'] );
    if ( $field === '' ) {
        return '';
    }

    $post_id = intval( $atts['post_id'] );
    if ( $post_id <= 0 ) {
        $post = get_post();
        if ( ! $post ) return '';
        $post_id = $post->ID;
    }

    $value = get_post_meta( $post_id, $field, true );

    if ( ( $value === '' || $value === null ) && $atts['fallback'] !== '' ) {
        $value = $atts['fallback'];
    }

    if ( $atts['format'] && $value ) {
        $ts = strtotime( $value );
        if ( $ts !== false && $ts > 0 ) {
            $value = date_i18n( $atts['format'], $ts );
        }
    }

    if ( intval( $atts['upper'] ) === 1 && is_string( $value ) ) {
        if ( function_exists( 'mb_strtoupper' ) ) {
            $value = mb_strtoupper( $value, 'UTF-8' );
        } else {
            $value = strtoupper( $value );
        }
    }

    switch ( $atts['esc'] ) {
        case 'attr':
            $out = esc_attr( $value );
            break;
        case 'raw':
            $out = $value;
            break;
        case 'html':
        default:
            $out = esc_html( $value );
            break;
    }

    return $out;
} );
