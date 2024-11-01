<?php

GFForms::include_addon_framework();
class GFStartEnd extends GFAddon
{
    protected  $_version = GSE_VER ;
    protected  $_min_gravityforms_version = '1.9' ;
    protected  $_full_path = __FILE__ ;
    protected  $_title = "Start End Date" ;
    protected  $_short_title = "Start End Date" ;
    protected  $_slug = "gse" ;
    protected  $_capabilities_form_settings = 'gravityforms_stripe' ;
    private static  $_instance = null ;
    public static function get_instance()
    {
        if ( self::$_instance == null ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function pre_init()
    {
        parent::pre_init();
        if ( $this->is_gravityforms_supported() && class_exists( 'GF_Field' ) ) {
            require_once GSE_DIR . '/includes/class-gse-field.php';
        }
        if ( $this->is_gravityforms_supported() && class_exists( 'GF_Field' ) ) {
            // require_once GSE_DIR . '/includes/class-gse-date-field.php';
        }
    }
    
    public function init()
    {
        parent::init();
        $base_url = GFCommon::get_base_url();
        /* REGISTER STYLES. USED IN : admin_enqueue_scripts, gform_noconflict_styles */
        add_action(
            'admin_enqueue_scripts',
            array( $this, "admin_enqueue_scripts" ),
            10,
            5
        );
        add_action(
            'wp_enqueue_scripts',
            array( $this, "frontend_enqueue_scripts" ),
            9999,
            5
        );
        wp_register_style(
            "gse-css",
            GSE_URL . "css/gse.css",
            array(),
            GSE_VER
        );
        /* REGISTER SCRIPTS. USED IN : admin_enqueue_scripts, gform_noconflict_scripts */
        wp_register_script(
            'gse-custom',
            GSE_URL . "js/gse.js",
            array( 'jquery', 'gform_gravityforms' ),
            GSE_VER,
            true
        );
        wp_register_script(
            'gse-frontend',
            GSE_URL . "js/gse.js",
            array( 'jquery' ),
            GSE_VER,
            true
        );
        /* ADD OUR ASSETS IN NO COFLICT MODE */
        add_filter(
            "gform_noconflict_styles",
            array( $this, "gform_noconflict_styles" ),
            10,
            1
        );
        add_filter(
            "gform_noconflict_scripts",
            array( $this, "gform_noconflict_scripts" ),
            10,
            1
        );
        /* INTEGRATION WITH GS */
        add_filter(
            'gss_set_result_array_values',
            array( $this, "gss_set_result_array_values" ),
            10,
            5
        );
        /* SETTINGS SETUP IN STRIPE FEED */
        add_filter(
            'gform_gravityformsstripe_feed_settings_fields',
            array( $this, "gse_start_date_fields" ),
            10,
            2
        );
        add_filter(
            'gform_gravityformsstripe_feed_settings_fields',
            array( $this, "gse_end_date_fields" ),
            10,
            2
        );
        /* FIELD HOOKS */
        add_filter(
            'gform_field_groups_form_editor',
            array( $this, "gform_field_groups_form_editor" ),
            10,
            1
        );
        /* START DATE HOOKS */
        add_filter(
            'gform_submission_data_pre_process_payment',
            array( $this, "gform_submission_data_pre_process_payment" ),
            10,
            4
        );
        add_filter(
            'gform_gravityformsstripe_pre_process_feeds',
            array( $this, "gform_gravityformsstripe_pre_process_feeds" ),
            10,
            3
        );
        add_filter(
            'gform_stripe_subscription_params_pre_update_customer',
            array( $this, 'gform_stripe_subscription_params_pre_update_customer' ),
            10,
            7
        );
        /* End DATE HOOKS */
        add_action(
            "gform_post_payment_action",
            array( $this, "gform_cancel_using_field_settings" ),
            10,
            2
        );
        add_action(
            'gform_post_add_subscription_payment',
            array( $this, "gse_cancel_after_number_of_subscription" ),
            10,
            1
        );
        if ( isset( $_GET['gse_test_nos'] ) && $_GET['gse_test_nos'] ) {
            add_action(
                'template_redirect',
                array( $this, "gse_cancel_after_number_of_subscription" ),
                10,
                1
            );
        }
        /* LOAD JS IN EDITOR */
        add_action( 'gform_editor_js_set_default_values', array( $this, 'gform_editor_js_set_default_values' ) );
        add_action( "gform_editor_js", array( $this, "gform_editor_js" ), 12 );
    }
    
    /* INITIALIZE ADMIN ONLY PROCESS */
    public function init_admin()
    {
        parent::init_admin();
        add_filter( 'gform_tooltips', array( $this, 'tooltips' ) );
    }
    
    /* ADD CUSTOM TOOLTIP */
    public function tooltips( $tooltips )
    {
        $gse_tooltips = array(
            'form_gravitystripe_fields' => sprintf( '<h6>%s</h6>%s', 'Gravity Stripe Fields', 'GravityStripe Fields allow you to perform operations with GravityStripe plugin' ),
        );
        return array_merge( $tooltips, $gse_tooltips );
    }
    
    /* INHERIT FORM SETTINGS FIELD */
    public function form_settings_fields( $form )
    {
        $settings = $this->add_form_settings_fields();
        $settings = apply_filters( "gravitystrip_date_addon_fields", $settings );
        return $settings;
    }
    
    /* USED IN : form_settings_fields */
    public function add_form_settings_fields( $settings = array() )
    {
        $new_settings = array(
            "title"  => __( 'Start/End Date Settings', 'gse' ),
            "fields" => array(
            "field_type" => array(
            "label"   => __( 'Field Type', "gse" ),
            "type"    => "select",
            "name"    => "gse_field_type",
            "choices" => array(
            array(
            "label" => __( "Select One", "gse" ),
            "value" => "",
        ),
            array(
            "label" => __( "Start Date Only", "gse" ),
            "value" => "start_date_only",
        ),
            array(
            "label" => __( "End Date Only", "gse" ),
            "value" => "end_date_only",
        ),
            array(
            "label" => __( "Both", "gse" ),
            "value" => "both_types",
        )
        ),
        ),
            "end_type"   => array(
            "label"   => __( 'Ending Type', "gse" ),
            "type"    => "select",
            "name"    => "gse_end_type",
            "choices" => array( array(
            "label" => __( "Select One", "gse" ),
            "value" => "",
        ), array(
            "label" => __( "Select Date(s)", "gse" ),
            "value" => "select_dates",
        ) ),
        ),
        ),
        );
        $settings[] = $new_settings;
        return $settings;
    }
    
    /* ENQUEUE ADMIN SCRIPTS - REGISTERED IN init */
    public function admin_enqueue_scripts()
    {
        
        if ( is_admin() ) {
            wp_enqueue_style( "gse-css" );
            wp_enqueue_style( "gse-datepicker" );
            wp_enqueue_style( "gform_datepicker_customstyle" );
            wp_enqueue_script( 'jquery-ui-datepicker' );
            wp_enqueue_script( 'gform_datepicker_custom' );
        }
        
        wp_enqueue_script( 'gse-custom' );
    }
    
    /* ENQUEUE ADMIN SCRIPTS - REGISTERED IN init */
    public function frontend_enqueue_scripts()
    {
        wp_enqueue_script( 'gse-frontend' );
    }
    
    /* ADD ENQUEUED SCRIPTS IN NO COFLICT MODE */
    public function gform_noconflict_scripts( $scripts )
    {
        $scripts[] = 'jquery-ui-datepicker';
        $scripts[] = 'gform_datepicker_custom';
        $scripts[] = 'gse-custom';
        return $scripts;
    }
    
    /* ADD ENQUEUED STYLES IN NO COFLICT MODE */
    public function gform_noconflict_styles( $styles )
    {
        $styles[] = 'gse-css';
        $styles[] = 'gse-datepicker';
        $styles[] = 'gform_datepicker_customstyle';
        return $styles;
    }
    
    /* UPDATE START DATE IN GS TABLE LIST */
    public function gss_set_result_array_values(
        $result,
        $entry,
        $form,
        $is_admin,
        $feed
    )
    {
        if ( !isset( $form['gse'] ) ) {
            return $result;
        }
        $settings = $form['gse'];
        if ( !$settings ) {
            return $result;
        }
        $end_date_type = $settings['gse_field_type'];
        if ( !$end_date_type ) {
            return $result;
        }
        if ( $end_date_type != "start_date_only" && $end_date_type != "both_types" ) {
            return $result;
        }
        $enable_start_date = rgars( $feed, 'meta/enable_gse' );
        
        if ( $enable_start_date && $enable_start_date == 1 ) {
            $old_date_timestamp = strtotime( $result['gss_new_date'] );
            $date_format = get_option( 'date_format' );
            $date_format = ( $date_format != "" ? $date_format : 'm/d/y' );
            $start_date_field = rgars( $feed, 'meta/gse_map_sub_start_date' );
            $start_date_field = $this->get_field_value( $form, $entry, $start_date_field );
            $start_date = ( $start_date_field && $start_date_field != "" ? strtotime( $start_date_field ) : false );
            $current_date = current_time( 'timestamp' );
            
            if ( $start_date && $start_date > $old_date_timestamp ) {
                $date_create = date( $date_format, $start_date );
                $result['gss_new_date'] = $date_create;
                if ( time() <= $old_date_timestamp ) {
                    $result['gss_renewal_date'] = $date_create;
                }
                if ( $is_admin && is_admin() && $result['gss_status'] == 'active' && time() < $start_date ) {
                    $result['gss_status_value'] .= __( ' (Starts on ' . $date_create . ')' );
                }
            }
        
        }
        
        return $result;
    }
    
    /* SETUP START DATE FIELD SETTINGS IN STRIPE FEED */
    public function gse_start_date_fields( $feed_settings_fields, $addon )
    {
        $form = $this->get_current_form();
        $settings = $form['gse'];
        if ( !isset( $form['gse'] ) || !$form['gse'] ) {
            return $feed_settings_fields;
        }
        $end_date_type = $settings['gse_field_type'];
        if ( $end_date_type != "start_date_only" && $end_date_type != "both_types" ) {
            return $feed_settings_fields;
        }
        $gss_settings = array();
        $gss_settings['title'] = __( 'GF Start Date Settings', 'gse' );
        $gss_settings['dependency'] = array(
            'field'  => 'transactionType',
            'values' => array( 'subscription' ),
        );
        $fields = array( array(
            "name"    => "enable_gse_input",
            "label"   => "",
            "type"    => "checkbox",
            "choices" => array( array(
            "label" => __( "Enable Subscription Start date", 'gse' ),
            "name"  => "enable_gse",
        ) ),
        ), array(
            'name'      => 'gse_map',
            'label'     => __( 'Assign Date Field', 'gse' ),
            'type'      => 'field_map',
            'tooltip'   => '<h6>' . __( 'GravityStripe Date Fields can be selected for the mapping.', 'gse' ) . '</h6>',
            'field_map' => $this->standard_fields_for_feed_mapping_start(),
        ) );
        $gss_settings['fields'] = $fields;
        $current_addon_settings = $addon->get_current_settings();
        if ( rgar( $current_addon_settings, "transactionType" ) == "subscription" ) {
            $feed_settings_fields[] = $gss_settings;
        }
        return $feed_settings_fields;
    }
    
    /* DATE FIELD SETUP IN STRIPE FEED. USED IN gse_start_date_fields */
    public function standard_fields_for_feed_mapping_start()
    {
        return array( array(
            'name'          => 'sub_start_date',
            'label'         => __( 'Subscription Start Date', 'gse' ),
            'field_type'    => array( 'gfsd_date' ),
            'default_value' => $this->get_first_field_by_type( 'gfsd_date', 3 ),
        ) );
    }
    
    /* SETUP END DATE FIELD SETTINGS IN STRIPE FEED */
    public function gse_end_date_fields( $feed_settings_fields, $addon )
    {
        $form = $this->get_current_form();
        if ( !isset( $form['gse'] ) || !$form['gse'] ) {
            return $feed_settings_fields;
        }
        $settings = $form['gse'];
        $end_date_type = $settings['gse_field_type'];
        $end_type = $settings['gse_end_type'];
        if ( $end_date_type != "end_date_only" && $end_date_type != "both_types" ) {
            return $feed_settings_fields;
        }
        $gss_settings = array();
        $gss_settings['title'] = __( 'GF End Date Settings', 'gse' );
        $gss_settings['dependency'] = array(
            'field'  => 'transactionType',
            'values' => array( 'subscription' ),
        );
        $fields = array();
        $fields[] = array(
            "name"    => "enable_gse_end_date_input",
            "label"   => "",
            "type"    => "checkbox",
            "choices" => array( array(
            "label" => __( "Enable Subscription End date", "gse" ),
            "name"  => "enable_gse_end_date",
        ) ),
        );
        
        if ( $end_type == "number_of_payments" ) {
            $fields[] = array(
                "name"  => "gse_end_date_type_payment_number",
                "label" => __( "Number of Payments", "gse" ),
                "type"  => "text",
            );
        } else {
            
            if ( $end_type == "term_limit" ) {
                $fields[] = array(
                    "name"  => "gse_end_date_type_duration",
                    "label" => __( "Select Duration", "gse" ),
                    "type"  => "billing_cycle",
                );
            } else {
                
                if ( $end_type == "select_dates" ) {
                    $fields[] = array(
                        'name'      => 'gse_map_end',
                        'label'     => 'Assign Date Field',
                        'type'      => 'field_map',
                        'tooltip'   => '<h6>Date Fields can be selected for the mapping. </h6>',
                        'field_map' => $this->standard_fields_for_feed_mapping_end(),
                    );
                } else {
                    
                    if ( $end_type != "" ) {
                        $fields[] = array(
                            "name"    => "gse_end_date_type",
                            "label"   => __( "End Date Type", "gse" ),
                            "type"    => "select",
                            "choices" => array( array(
                            "label" => "Number of Payments",
                            "value" => "end_by_number_of_payments",
                        ), array(
                            "label" => "Term Limit",
                            "value" => "end_by_duration",
                        ), array(
                            "label" => "Select End Date(s)",
                            "value" => "end_by_selected_date",
                        ) ),
                        );
                        $fields[] = array(
                            "name"  => "gse_end_date_type_duration",
                            "label" => __( "Select Duration", "gse" ),
                            "type"  => "billing_cycle",
                        );
                        $fields[] = array(
                            "name"  => "gse_end_date_type_payment_number",
                            "label" => __( "Number of Payments", "gse" ),
                            "type"  => "text",
                        );
                        $fields[] = array(
                            'name'      => 'gse_map_end',
                            'label'     => 'Assign Date Field',
                            'type'      => 'field_map',
                            'tooltip'   => '<h6>' . __( 'Date Fields can be selected for the mapping.', 'gse' ) . '</h6>',
                            'field_map' => $this->standard_fields_for_feed_mapping_end(),
                        );
                    }
                
                }
            
            }
        
        }
        
        $gss_settings['fields'] = $fields;
        $current_addon_settings = $addon->get_current_settings();
        if ( rgar( $current_addon_settings, "transactionType" ) == "subscription" ) {
            $feed_settings_fields[] = $gss_settings;
        }
        return $feed_settings_fields;
    }
    
    /* DATE FIELD SETUP IN STRIPE FEED. USED IN gse_end_date_fields */
    public function standard_fields_for_feed_mapping_end()
    {
        return array( array(
            'name'          => 'sub_end_date',
            'label'         => __( 'Subscription End Date', 'gse' ),
            'field_type'    => array( 'gfsd_date' ),
            'default_value' => $this->get_first_field_by_type( 'gfsd_date', 3 ),
        ) );
    }
    
    /* ADD NEW GROUP IN FIELD LIST. */
    public function gform_field_groups_form_editor( $field_groups )
    {
        $field_groups['gravitystripe_fields'] = array(
            "name"   => "gravitystripe_fields",
            "label"  => "Start / End Fields",
            "fields" => array(),
        );
        return $field_groups;
    }
    
    /* ADD RADIO OR SELECT CHOICE FIELD. DEPRECATED */
    public function gform_field_standard_settings( $position, $form_id )
    {
    }
    
    public function get_field_value( $form, $entry, $field )
    {
        $value = parent::get_field_value( $form, $entry, $field );
        $field = GFAPI::get_field( $form['id'], $field );
        
        if ( $field->gse_type == 'datepicker' ) {
            $dateFormat = $field['dateFormat'];
            $parse_date = GFCommon::parse_date( $value, $dateFormat );
            $display = GFCommon::date_display( $value, 'ymd_slash' );
            $value = $display;
        }
        
        return $value;
    }
    
    /* SET TRIAL TIME FOR A SUBSCRIPTION IF START DATE IS SET. SIMILAR BUT TO ENSURE TRIAL IS SET */
    public function gform_submission_data_pre_process_payment(
        $submission_data,
        $feed,
        $form,
        $entry
    )
    {
        if ( !isset( $form['gse'] ) || !$form['gse'] ) {
            return $submission_data;
        }
        $settings = $form['gse'];
        $end_date_type = $settings['gse_field_type'];
        if ( $end_date_type != "start_date_only" && $end_date_type != "both_types" ) {
            return $submission_data;
        }
        $enable_start_date = rgars( $feed, 'meta/enable_gse' );
        $start_date_field = rgars( $feed, 'meta/gse_map_sub_start_date' );
        $start_date_field = $this->get_field_value( $form, $entry, $start_date_field );
        $this->log_debug( __METHOD__ . "() original submission_data..." . print_r( $submission_data, true ) );
        
        if ( $enable_start_date == 1 ) {
            $current_time = current_time( 'h:i:s' );
            $start_date_field = $start_date_field . ' ' . $current_time;
            $start_date = strtotime( $start_date_field );
            $current_date = current_time( 'timestamp' );
            $diff = $start_date - $current_date;
            $days = ceil( $diff / 86400 );
            $this->log_debug( __METHOD__ . "() start_date..." . print_r( date( 'm/d/Y h:i:s', $start_date ), true ) );
            $this->log_debug( __METHOD__ . "() current_date..." . print_r( date( 'm/d/Y h:i:s', $current_date ), true ) );
            $this->log_debug( __METHOD__ . "() diff..." . print_r( $diff, true ) );
            $this->log_debug( __METHOD__ . "() days..." . print_r( $days, true ) );
            $allow_start_date = true;
            if ( isset( $feed['meta']['trial_enabled'] ) && $feed['meta']['trial_enabled'] == 1 ) {
                if ( isset( $feed['meta']['trialPeriod'] ) && $feed['meta']['trialPeriod'] ) {
                    $allow_start_date = apply_filters( 'gs_startend_override_trial', true, $feed );
                }
            }
            
            if ( $start_date && $days > 0 && $allow_start_date ) {
                $submission_data['trial'] = $days;
                $submission_data['trial_period_days'] = $days;
                $submission_data['trial_from_plan'] = false;
            }
            
            // exit;
        }
        
        $this->log_debug( __METHOD__ . "() modified submission_data..." . print_r( $submission_data, true ) );
        return $submission_data;
    }
    
    /* SET TRIAL TIME FOR A SUBSCRIPTION IF START DATE IS SET */
    public function gform_gravityformsstripe_pre_process_feeds( $feeds, $entry, $form )
    {
        if ( !isset( $form['gse'] ) || !$form['gse'] ) {
            return $feeds;
        }
        $settings = $form['gse'];
        $end_date_type = $settings['gse_field_type'];
        if ( $end_date_type != "start_date_only" && $end_date_type != "both_types" ) {
            return $feeds;
        }
        
        if ( $feeds && is_array( $feeds ) ) {
            $org_feeds = $feeds;
            foreach ( $feeds as $key => $feed ) {
                $enable_start_date = rgars( $feed, 'meta/enable_gse' );
                $start_date_field = rgars( $feed, 'meta/gse_map_sub_start_date' );
                
                if ( $enable_start_date == 1 && $start_date_field ) {
                    $start_date_field = rgars( $feed, 'meta/gse_map_sub_start_date' );
                    $start_date_field = $this->get_field_value( $form, $entry, $start_date_field );
                    $current_time = current_time( 'h:i:s' );
                    $start_date_field = $start_date_field . ' ' . $current_time;
                    $start_date = strtotime( $start_date_field );
                    $current_date = current_time( 'timestamp' );
                    $diff = $start_date - $current_date;
                    $days = ceil( $diff / 86400 );
                    $allow_start_date = true;
                    if ( isset( $feed['meta']['trial_enabled'] ) && $feed['meta']['trial_enabled'] == 1 ) {
                        if ( isset( $feed['meta']['trialPeriod'] ) && $feed['meta']['trialPeriod'] ) {
                            $allow_start_date = apply_filters( 'gs_startend_override_trial', true, $feed );
                        }
                    }
                    
                    if ( $start_date && $days > 0 && $allow_start_date ) {
                        $feed['meta']['trial_enabled'] = 1;
                        $feed['meta']['trialPeriod'] = $days;
                    }
                
                }
                
                $feeds[$key] = $feed;
            }
        }
        
        $this->log_debug( __METHOD__ . "() Processed Feeds..." . print_r( $feeds, true ) );
        // echo "<pre>"; print_r($feeds); echo "</pre>"; exit;
        return $feeds;
    }
    
    public function gform_stripe_subscription_params_pre_update_customer(
        $subscription_params,
        $customer,
        $plan,
        $feed,
        $entry,
        $form,
        $trial_period_days
    )
    {
        $this->log_debug( __METHOD__ . "() Original Pre update customer..." . print_r( $subscription_params, true ) );
        if ( !isset( $form['gse'] ) ) {
            return $subscription_params;
        }
        $settings = $form['gse'];
        $end_date_type = $settings['gse_field_type'];
        if ( $end_date_type != "start_date_only" && $end_date_type != "both_types" ) {
            return $subscription_params;
        }
        $enable_start_date = rgars( $feed, 'meta/enable_gse' );
        $start_date_field = rgars( $feed, 'meta/gse_map_sub_start_date' );
        
        if ( $enable_start_date == 1 && $start_date_field ) {
            $start_date_field = rgars( $feed, 'meta/gse_map_sub_start_date' );
            $start_date_field = $this->get_field_value( $form, $entry, $start_date_field );
            $current_time = current_time( 'h:i:s' );
            $start_date_field = $start_date_field . ' ' . $current_time;
            $start_date = strtotime( $start_date_field );
            $current_date = current_time( 'timestamp' );
            $diff = $start_date - $current_date;
            $days = ceil( $diff / 86400 );
            $allow_start_date = true;
            if ( isset( $feed['meta']['trial_enabled'] ) && $feed['meta']['trial_enabled'] == 1 ) {
                if ( isset( $feed['meta']['trialPeriod'] ) && $feed['meta']['trialPeriod'] ) {
                    $allow_start_date = apply_filters( 'gs_startend_override_trial', true, $feed );
                }
            }
            
            if ( $start_date && $days > 0 && $allow_start_date ) {
                $subscription_params['trial_from_plan'] = false;
                $subscription_params['trial_period_days'] = $days;
            }
        
        }
        
        $this->log_debug( __METHOD__ . "() Modified Pre update customer..." . print_r( $subscription_params, true ) );
        return $subscription_params;
    }
    
    /* START END DATE PROCESS IF PAYMENT IS SUCCESFUL */
    public function gform_cancel_using_field_settings( $entry, $action )
    {
        if ( $action['type'] != 'create_subscription' ) {
            return;
        }
        if ( is_wp_error( $entry ) ) {
            return;
        }
        if ( !$entry ) {
            return;
        }
        
        if ( isset( $entry['transaction_id'] ) && $entry['transaction_id'] != "" && strpos( $entry['transaction_id'], 'sub_' ) !== false ) {
            global  $wpdb ;
            $entry_id = $entry['id'];
            $form = GFAPI::get_form( $entry['form_id'] );
            $processed_feeds = gform_get_meta( $entry_id, 'processed_feeds' );
            $feed_objects = array();
            
            if ( isset( $processed_feeds['gravityformsstripe'] ) ) {
                $feeds = $processed_feeds['gravityformsstripe'];
                
                if ( $feeds ) {
                    foreach ( $feeds as $feed_id ) {
                        $sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}gf_addon_feed WHERE id=%d", $feed_id );
                        $feed = $wpdb->get_row( $sql, ARRAY_A );
                        if ( $feed ) {
                            $feed_objects[] = $feed;
                        }
                    }
                    if ( !empty($feed_objects) ) {
                        $this->gform_cancel_by_feeds( $feed_objects, $entry, $form );
                    }
                }
            
            }
        
        }
    
    }
    
    /* PROCESS SELECTED STRIPE FEED. USED IN gform_cancel_using_field_settings */
    public function gform_cancel_by_feeds( $feeds, $entry, $form )
    {
        if ( !isset( $form['gse'] ) || !$form['gse'] ) {
            return $feeds;
        }
        $settings = $form['gse'];
        $end_date_type = $settings['gse_field_type'];
        $end_type = $settings['gse_end_type'];
        if ( $end_date_type != "end_date_only" && $end_date_type != "both_types" ) {
            return $feeds;
        }
        if ( $end_type == "" || !$end_type ) {
            return $feeds;
        }
        $form_id = $entry['form_id'];
        $subscription_id = $entry['transaction_id'];
        $cancel_processed = false;
        foreach ( $feeds as $feed ) {
            $feed['meta'] = json_decode( $feed['meta'], true );
            $enable_end_date = rgars( $feed, 'meta/enable_gse_end_date' );
            $gse_end_date_type = rgars( $feed, 'meta/gse_end_date_type' );
            $enable_start_date = rgars( $feed, 'meta/enable_gse' );
            if ( $enable_end_date && $enable_end_date == 1 ) {
                
                if ( $end_type && $end_type == "select_dates" ) {
                    $end_date_field = rgars( $feed, 'meta/gse_map_end_sub_end_date' );
                    //$selected_start_date = $entry[$end_date_field];
                    $selected_end_date = $this->get_field_value( $form, $entry, $end_date_field );
                    $cancellable_end_date = false;
                    $field = GFAPI::get_field( $form_id, $end_date_field );
                    
                    if ( $end_date_type == "end_date_only" ) {
                        $cancellable_end_date = $selected_end_date;
                        
                        if ( $cancellable_end_date ) {
                            $fetched_end_date_time = strtotime( $cancellable_end_date );
                            $this->set_stripe_cancel_time( $feed, $subscription_id, $fetched_end_date_time );
                            $cancel_processed = true;
                        }
                    
                    } else {
                        
                        if ( $end_date_type == "both_types" && $field->gse_type == 'datepicker' ) {
                            $cancellable_end_date = $selected_end_date;
                            
                            if ( $cancellable_end_date ) {
                                $fetched_end_date_time = strtotime( $cancellable_end_date );
                                $this->set_stripe_cancel_time( $feed, $subscription_id, $fetched_end_date_time );
                                $cancel_processed = true;
                            }
                        
                        } else {
                            
                            if ( $end_date_type == "both_types" ) {
                                $field = GFAPI::get_field( $form_id, $end_date_field );
                                
                                if ( isset( $field->enableChoiceValue ) && $field->enableChoiceValue == 1 ) {
                                    if ( isset( $field->choices ) && $field->choices ) {
                                        foreach ( $field->choices as $choice ) {
                                            $text = $choice['text'];
                                            $value = $choice['value'];
                                            
                                            if ( $text != $value && $selected_end_date == $text ) {
                                                $cancellable_end_date = $value;
                                                break;
                                            }
                                        
                                        }
                                    }
                                } else {
                                    if ( $field->enableChoiceValue != 1 && !$cancellable_end_date ) {
                                        if ( $enable_start_date != 1 ) {
                                            $cancellable_end_date = $selected_end_date;
                                        }
                                    }
                                }
                                
                                
                                if ( $cancellable_end_date ) {
                                    $selected_start_date_time = strtotime( $selected_end_date );
                                    $fetched_end_date_time = strtotime( $cancellable_end_date );
                                    $current_time = time();
                                    // echo "<pre>"; print_r($cancellable_end_date); echo "</pre>";
                                    
                                    if ( $fetched_end_date_time > $current_time + 3600 ) {
                                        $this->set_stripe_cancel_time( $feed, $subscription_id, $fetched_end_date_time );
                                        $cancel_processed = true;
                                    }
                                
                                }
                            
                            }
                        
                        }
                    
                    }
                
                }
            
            }
            if ( $cancel_processed ) {
                break;
            }
        }
    }
    
    /* SET SUBSCRIPTION CANCEL TIME BY SUBSCRIPTION ID. USED IN gform_cancel_by_feeds */
    public function set_stripe_cancel_time(
        $feed,
        $subscription_id,
        $cancel_time,
        $duration_str = false
    )
    {
        $gf_stripe = gf_stripe();
        $gf_stripe->include_stripe_api();
        if ( !class_exists( '\\Stripe\\Stripe' ) ) {
            require_once $gf_stripe->get_base_path() . '/includes/autoload.php';
        }
        try {
            $meta = $feed['meta'];
            $duration_length = rgars( $feed, 'meta/billingCycle_length' );
            $duration_unit = rgars( $feed, 'meta/billingCycle_unit' );
            $feed_duration_str = $duration_length . " " . $duration_unit;
            $subscription = \Stripe\Subscription::retrieve( $subscription_id );
            $trial_end = $subscription->trial_end;
            $start_date = $subscription->start_date;
            
            if ( $cancel_time == false ) {
                $include_feed_duration = apply_filters( 'gss_term_limit_include_feed_duration_term', true, $feed );
                
                if ( $trial_end && $trial_end != "" ) {
                    $trial_end_date = date( 'm/d/Y h:i:s', $trial_end );
                    $calc_days = strtotime( $trial_end_date . " +" . $duration_str );
                    $final_days = date( 'm/d/Y h:i:s', $calc_days );
                    
                    if ( $include_feed_duration ) {
                        $calc_days = strtotime( $final_days . " +" . $feed_duration_str );
                        $final_days = date( 'm/d/Y h:i:s', $calc_days );
                    }
                    
                    $cancel_time = strtotime( $final_days ) + 10;
                } else {
                    $start_date = date( 'm/d/Y h:i:s', $start_date );
                    $calc_days = strtotime( $start_date . " +" . $duration_str );
                    $final_days = date( 'm/d/Y h:i:s', $calc_days );
                    
                    if ( $include_feed_duration ) {
                        $calc_days = strtotime( $final_days . " +" . $feed_duration_str );
                        $final_days = date( 'm/d/Y h:i:s', $calc_days );
                    }
                    
                    $cancel_time = strtotime( $final_days ) + 10;
                }
            
            } else {
                $start_time = date( 'h:i:s', $start_date );
                $cancel_date = date( 'm/d/Y', $cancel_time );
                $final_cancel_date_time = $cancel_date . ' ' . $start_time;
                $cancel_time = strtotime( $final_cancel_date_time ) + 10;
            }
            
            $cancel_time = apply_filters(
                'gss_cancel_time',
                $cancel_time,
                $feed,
                $subscription
            );
            $result = $subscription->update( $subscription_id, [
                'cancel_at' => $cancel_time,
            ] );
        } catch ( \Exception $e ) {
            print_r( $e->getMessage() );
            exit;
        }
        return true;
    }
    
    /* CANCEL SUBSCRIPTION IF TERM LIMIT REACHED */
    public function gse_cancel_after_number_of_subscription( $entry = 0 )
    {
    }
    
    /* INITIALIZE DATEPICKER AND ALL JS OPERATION FOR CUSTOM FIELD WE MADE. */
    public function gform_editor_js()
    {
        $form = $this->get_current_form();
        if ( !isset( $form['gse'] ) || !$form['gse'] ) {
            return;
        }
        $settings = $form['gse'];
        $end_date_type = $settings['gse_field_type'];
        $end_type = $settings['gse_end_type'];
        require_once GSE_DIR . '/includes/script.php';
    }
    
    /* DEFAULT DATES WHILE FIELD DROPPED. */
    public function gform_editor_js_set_default_values()
    {
        $date_default = date( 'Y-m-d', time() + 86400 );
        $date_1 = date( 'Y-m-d', time() + 2 * 86400 );
        $date_2 = date( 'Y-m-d', time() + 3 * 86400 );
        ?>
		
		case "gse_date" :
		if (!field.label)
			field.label = <?php 
        echo  json_encode( esc_html__( 'Untitled', 'gravityforms' ) ) ;
        ?>;

		field.inputs = null;
		if (!field.choices) {
			if (field.type === 'quantity') {
				field.choices = [new Choice('1'), new Choice('2'), new Choice('3')];
			} else {
				field.choices = field["enablePrice"] ? new Array(new Choice(<?php 
        echo  json_encode( $date_default ) ;
        ?>, "", "0.00"), new Choice(<?php 
        echo  json_encode( $date_1 ) ;
        ?>, "", "0.00"), new Choice(<?php 
        echo  json_encode( $date_2 ) ;
        ?>, "", "0.00"))	: new Array(new Choice(<?php 
        echo  json_encode( $date_default ) ;
        ?>), new Choice(<?php 
        echo  json_encode( $date_1 ) ;
        ?>), new Choice(<?php 
        echo  json_encode( $date_2 ) ;
        ?>));
			}
		}
		
		break;
		
		case "gfsd_date" :
			
			if (!field.label)
				field.label = <?php 
        echo  json_encode( esc_html__( 'Date', 'gravityforms' ) ) ;
        ?>;
			
			if (!field.gse_type) {
				field.gse_type = 'select';
				jQuery("#gse_type").val('select');
			}
			
			if (!field.field_date_format) {
				field.field_date_format = 'mdy';
				jQuery("#field_date_format").val('mdy');
			}

			field.inputs = null;
			if (!field.choices) {
				if (field.type === 'quantity') {
					field.choices = [new Choice('1'), new Choice('2'), new Choice('3')];
				} else {
					field.choices = field["enablePrice"] ? 
						new Array(
							new Choice(<?php 
        echo  json_encode( $date_default ) ;
        ?>, "", "0.00"), 
							new Choice(<?php 
        echo  json_encode( $date_1 ) ;
        ?>, "", "0.00"), 
							new Choice(<?php 
        echo  json_encode( $date_2 ) ;
        ?>, "", "0.00"))	
						: 
						new Array(
							new Choice(<?php 
        echo  json_encode( $date_default ) ;
        ?>), 
							new Choice(<?php 
        echo  json_encode( $date_1 ) ;
        ?>), 
							new Choice(<?php 
        echo  json_encode( $date_2 ) ;
        ?>)
						);
				}
				
				console.log( new Choice(<?php 
        echo  json_encode( $date_default ) ;
        ?>) );
				console.log( field.choices );
			}
			
			if( field.gse_type == 'datepicker' ) {
				jQuery('.choices-ui__trigger-section').hide();
				jQuery('.date_format_setting').show();
			}
			else {
				jQuery('.choices-ui__trigger-section').show();
				jQuery('.date_format_setting').hide();
			}
		
		break;
		<?php 
    }

}