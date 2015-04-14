<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

global $wpdb;

$form_table 	= $wpdb->prefix . 'dynamic_form_maker_fields';
$fields_table 	= $wpdb->prefix . 'dynamic_form_maker_forms';
$entries_table 	= $wpdb->prefix . 'dynamic_form_maker_entries';

$wpdb->query( "DROP TABLE IF EXISTS $form_table" );
$wpdb->query( "DROP TABLE IF EXISTS $fields_table" );
$wpdb->query( "DROP TABLE IF EXISTS $entries_table" );

delete_option( 'dfm_db_version' );
delete_option( 'dynamic-form-maker-screen-options' );
delete_option( 'dfm_dashboard_widget_options' );
delete_option( 'dfm-settings' );

$wpdb->query( "DELETE FROM " . $wpdb->prefix . "usermeta WHERE meta_key IN ( 'dfm-form-settings', 'dfm_entries_per_page', 'dfm_forms_per_page', 'managedynamic-form-maker_page_dfm-entriescolumnshidden' )" );
