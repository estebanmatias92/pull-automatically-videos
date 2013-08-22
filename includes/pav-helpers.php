<?php
/**
 * Help functions.
 *
 * @package    Pull Automatically Videos
 * @author     Matias Esteban <estebanmatias92@gmail.com>
 * @license    MIT License
 * @link       http://example.com
 * @copyright  2013 Matias Esteban
 */

if ( ! function_exists( 'seconds_to_hms' ) ) {

    /**
     * Convert $num_secs to Hours:Minutes:Seconds
     *
     * @since  0.1.0
     *
     * @param  integer    $num_secs     The number to modify.
     *
     * @return integer    The time modified to Hours.
     */
    function seconds_to_hms( $num_secs ){

        $result = '';

        $hours   = intval( intval( $num_secs ) / 3600 );
        $result .= $hours.':';

        $minutes = intval( ( ( intval( $num_secs ) / 60 ) % 60 ) );
        if ( $minutes < 10 ) $result .= '0';
        $result .= $minutes.':';

        $seconds = intval( intval( ( $num_secs % 60 ) ) );
        if ( $seconds < 10 ) $result .= '0';
        $result .= $seconds;

        return $result;

    }

}

if ( ! function_exists( 'string_have_some_term' ) ) {

    /**
     * Help to find the taxonomy term (of many terms) in a string.
     *
     * @since  0.1.0
     *
     * @param  string    $string       String where search the term.
     * @param  array     $terms        Terms array to search in the string.
     *
     * @return boolean   If doesn't found some term in the string, returns false.
     */
    function string_have_some_term( $string, $terms ) {

        foreach ( $terms as $term ) {

            if ( stripos( $string, $term ) !== FALSE ) {
                return true;
            }

        }

        return false;

    }

}
