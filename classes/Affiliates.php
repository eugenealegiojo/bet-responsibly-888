<?php

namespace Parlay\Api;

final class Affiliates {

    private static $query_vars = [ 'a_aid', 'a_cid', 'a_bid' ];
    private static $system_code = 'PAP001';

    public static function init(){
        self::hooks();
    }

    public static function hooks() {
        add_action('wp', __CLASS__ . '::setup_tracking');
        // add_filter('query_vars', __CLASS__ . '::add_tracking_query_var');
    }
    
    public static function add_tracking_query_var( $vars ){
        $vars = array_merge( $vars, self::$query_vars );
        return $vars;
    }
    
    public static function setup_tracking() {
        $ids = [];

        // Make sure there is no existing tracking_id.
        if ( self::get_tracking_data() ) {
            error_log( 'tracking_id exists: ' . print_r( self::get_tracking_data(), true ) );
            return;
        }

        foreach( self::$query_vars as $var ) {
            // $value = get_query_var($var, null);
            if ( isset( $_GET[ $var ] ) && ! empty( $_GET[ $var ] ) ) {
                $ids[] = sanitize_text_field( $_GET[ $var ] );
            }
        }

        error_log( 'ids: ' . print_r( $ids, true ) );

        if ( count( $ids ) > 0 ) {
            $data['tracking_id'] = implode( '-', $ids );
            $data['affiliate_system_code'] = self::$system_code;

            $expire = time() + (6 * MONTH_IN_SECONDS); // 6 months from now
            setcookie('pgs_aff_tracking', json_encode( $data ), $expire, "/");
        }
    }

    public static function get_tracking_data() {
        if ( ! isset( $_COOKIE['pgs_aff_tracking'] ) ) {
            error_log('cookie pgs_aff_tracking not exists!');
            return;
        }

        return json_decode( stripslashes( $_COOKIE['pgs_aff_tracking'] ), true );
    }

    public static function reset_tracking(){
        error_log('Unset affiliate tracking');
        setcookie('pgs_aff_tracking', '', time() - 3600, '/');
    }
}