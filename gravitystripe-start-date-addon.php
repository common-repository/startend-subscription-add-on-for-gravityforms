<?php

/*
Plugin Name: STARTEND Subscription Add-On for GravityForms
Author: Frog Eat Fly
Author URI: https://www.gravitystripe.com/
Version: 4.0.6
Description: Easily customize the start and end date of subscriptions created with Gravity Forms.
Tested up to: 6.0.2
*/
define( 'GSE_DIR', plugin_dir_path( __FILE__ ) );
define( 'GSE_URL', plugin_dir_url( __FILE__ ) );
define( 'GSE_VER', '4.0.6' );
function gse_load_start_end_date()
{
    if ( !method_exists( 'GFForms', 'include_addon_framework' ) ) {
        return;
    }
    require_once GSE_DIR . '/includes/class-gse-addon.php';
    require_once GSE_DIR . '/includes/plugin-functions.php';
    GFAddOn::register( 'GFStartEnd' );
    GFStartEnd::get_instance();
    $base_url = GFCommon::get_base_url();
    define( "GSE_CALENDAR_ICON_URL", $base_url . "/images/calendar.png" );
}

add_action( 'gform_loaded', 'gse_load_start_end_date', 101 );
add_action( 'plugins_loaded', 'gse_load_plugin_text_domain', 0 );
function gse_load_plugin_text_domain()
{
    load_plugin_textdomain( 'gse', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}


if ( !function_exists( 'gsda_fs' ) ) {
    // Create a helper function for easy SDK access.
    function gsda_fs()
    {
        global  $gsda_fs ;
        
        if ( !isset( $gsda_fs ) ) {
            // Include Freemius SDK.
            require_once GSE_DIR . '/freemius/start.php';
            $gsda_fs = fs_dynamic_init( array(
                'id'               => '6435',
                'slug'             => 'gravitystripe-start-date-addon',
                'type'             => 'plugin',
                'public_key'       => 'pk_582cb8a64c34156abafe364058518',
                'is_premium'       => false,
                'premium_suffix'   => 'Professional',
                'has_paid_plans'   => true,
                'is_org_compliant' => true,
                'menu'             => array(
                'first-path' => 'plugins.php',
                'support'    => false,
            ),
                'is_live'          => true,
            ) );
        }
        
        return $gsda_fs;
    }
    
    // Init Freemius.
    gsda_fs();
    // Signal that SDK was initiated.
    do_action( 'gsda_fs_loaded' );
}

add_filter(
    'plugin_action_links_' . plugin_basename( __FILE__ ),
    'gfsd_plugin_action_links',
    1,
    2
);
function gfsd_plugin_action_links( $links )
{
    $url = gsda_fs()->get_upgrade_url();
    $settings_link = "<a href='{$url}'>" . __( 'Upgrade', 'gravitystripe' ) . '</a>';
    array_push( $links, $settings_link );
    return $links;
}

add_action(
    'gform_enqueue_scripts',
    'fn_gform_enqueue_scripts',
    100,
    2
);
function fn_gform_enqueue_scripts( $form, $ajax )
{
    if ( is_array( $form['fields'] ) ) {
        foreach ( $form['fields'] as $field ) {
            if ( isset( $field->fields ) && is_array( $field->fields ) ) {
                return fn_gform_enqueue_scripts( array(
                    'fields' => $field->fields,
                ) );
            }
            
            if ( RGFormsModel::get_input_type( $field ) == 'gfsd_date' ) {
                $asset = new GF_Script_Asset( 'gform_datepicker_init' );
                $asset->enqueue_asset();
                return;
            }
        
        }
    }
}
