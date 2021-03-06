<?php
global $wpdb, $post;

if ( !isset( $_POST['dfm-user-submit'] ) )
	return;

if ( !isset( $_POST['userRegiForm'] ) )
	return;

$required 		= ( isset( $_POST['_dfm-required-secret'] ) && $_POST['_dfm-required-secret'] == '0' ) ? false : true;
$secret_field 	= ( isset( $_POST['_dfm-secret'] ) ) ? esc_html( $_POST['_dfm-secret'] ) : '';
$honeypot 		= ( isset( $_POST['dfm-spam'] ) ) ? esc_html( $_POST['dfm-spam'] ) : '';
$referrer 		= ( isset( $_POST['_wp_http_referer'] ) ) ? esc_html( $_POST['_wp_http_referer'] ) : false;
$wp_get_referer = wp_get_referer();

// If the verification is set to required, run validation check
if ( true == $required && !empty( $secret_field ) ) :
	if ( !empty( $honeypot ) )
		wp_die( __( 'Security check: hidden spam field should be blank.' , 'dynamic-form-maker'), '', array( 'back_link' => true ) );
	if ( !is_numeric( $_POST[ $secret_field ] ) || strlen( $_POST[ $secret_field ] ) !== 2 )
		wp_die( __( 'Security check: failed secret question. Please try again!' , 'dynamic-form-maker'), '', array( 'back_link' => true ) );
endif;

// Basic security check before moving any further
if ( !isset( $_POST['dfm-user-submit'] ) )
	return;

// Get global settings
$dfm_settings 	= get_option( 'dfm-settings' );

// Settings - Max Upload Size
$settings_max_upload    = isset( $dfm_settings['max-upload-size'] ) ? $dfm_settings['max-upload-size'] : 25;

// Settings - Spam word sensitivity
$settings_spam_points    = isset( $dfm_settings['spam-points'] ) ? $dfm_settings['spam-points'] : 4;

// Set submitted action to display success message
$this->submitted = true;

// Tells us which form to get from the database
$form_id = absint( $_POST['form_id'] );

$skip_referrer_check = apply_filters( 'dfm_skip_referrer_check', false, $form_id );

// Test if referral URL has been set
if ( !$referrer )
	wp_die( __( 'Security check: referal URL does not appear to be set.' , 'dynamic-form-maker'), '', array( 'back_link' => true ) );

// Allow referrer check to be skipped
if ( !$skip_referrer_check ) :
	// Test if the referral URL matches what sent from WordPress
	if ( $wp_get_referer )
		wp_die( __( 'Security check: referal does not match this site.' , 'dynamic-form-maker'), '', array( 'back_link' => true ) );
endif;

// Test if it's a known SPAM bot
if ( $this->isBot() )
	wp_die( __( 'Security check: looks like you are a SPAM bot. If you think this is an error, please email the site owner.' , 'dynamic-form-maker' ), '', array( 'back_link' => true ) );

// Query to get all forms
$order = sanitize_sql_orderby( 'form_id DESC' );
$form = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->form_table_name WHERE form_id = %d ORDER BY $order", $form_id ) );

$form_settings = (object) array(
	'form_title' 					=> stripslashes( html_entity_decode( $form->form_title, ENT_QUOTES, 'UTF-8' ) ),		
	'form_type' 					=> stripslashes( $form->form_type ),
	'form_user_role' 				=> stripslashes( $form->form_user_role ),	
	'form_notification_message' 	=> stripslashes( $form->form_notification_message )	
);
// Allow the form settings to be filtered (ex: return $form_settings->'form_title' = 'Hello World';)
$form_settings = (object) apply_filters_ref_array( 'dfm_email_form_settings', array( $form_settings, $form_id ) );

$form_title	= $form_settings->form_title;
$form_type	= $form_settings->form_type;
$form_user_role	= $form_settings->form_user_role;
$form_notification_message	= $form_settings->form_notification_message;

// Query to get all forms
$order = sanitize_sql_orderby( 'field_sequence ASC' );
$fields = $wpdb->get_results( $wpdb->prepare( "SELECT field_id, field_key, field_name, field_type, field_options, field_parent, field_required FROM $this->field_table_name WHERE form_id = %d ORDER BY $order", $form_id ) );

// Setup counter for alt rows


foreach ( $fields as $field ) {
	$value = ( isset( $_POST[ 'dfm-' . $field->field_id ] ) ) ? $_POST[ 'dfm-' . $field->field_id ] : $_POST[ 'dfm-' . $field->field_type ];

		// If time field, build proper output
		if ( is_array( $value ) && $field->field_type == 'time' )
			$value = $this->build_array_form_item( $value, $field->field_type );
		// If address field, build proper output
		elseif ( is_array( $value ) && $field->field_type == 'address' )
			$value = $this->build_array_form_item( $value, $field->field_type );
		// If multiple values, build the list
		elseif ( is_array( $value ) )
			$value = $this->build_array_form_item( $value, $field->field_type );
		elseif ( 'radio' == $field->field_type )
			$value = wp_specialchars_decode( stripslashes( esc_html( $value ) ), ENT_QUOTES );
		// Lastly, handle single values
		else
			$value = html_entity_decode( stripslashes( esc_html( $value ) ), ENT_QUOTES, 'UTF-8' );
	//Sanitize input
	$value = $this->sanitize_input( $value, $field->field_type );
	// Validate input
	$this->validate_user_check( $value, $field->field_name, $field->field_type, $field->field_required );
}


	$form_table_name = $wpdb->prefix . "dynamic_form_maker_forms";				
	$form_table = $wpdb->get_results( "SELECT * FROM $form_table_name WHERE form_id = $form_id" );
	$form_user_role = $form_table[0]->form_user_role;
	$username = $_POST['dfm-username'];
	$firstname = $_POST['dfm-firstname'];
	$lastname = $_POST['dfm-lastname'];
	$email = $_POST['dfm-email'];
	$url = $_POST['dfm-url'];
	$password = $_POST['dfm-password'];					

	$newUserData = array(
		'user_login' => $username,
		'first_name' => $firstname,
		'last_name' => $lastname,
		'user_pass' => $password,
		'user_email' => $email,
		'user_url' => $url,
		'role' => $form_user_role
	);
	$createUser = wp_insert_user( $newUserData );

$i = $points = 0;
$count = 0;
// Loop through each form field and build the body of the message
foreach ( $fields as $field ) :
	// Handle attachments
	if ( $field->field_type == 'file-upload' ) :
		$value = ( isset( $_FILES[ 'dfm-' . $field->field_id ] ) ) ? $_FILES[ 'dfm-' . $field->field_id ] : '';

		if ( is_array( $value) && $value['size'] > 0 ) :
			// 25MB is the max size allowed
			$size = apply_filters( 'dfm_max_file_size', $settings_max_upload );
			$max_attach_size = $size * 1048576;

			// Display error if file size has been exceeded
			if ( $value['size'] > $max_attach_size )
				wp_die( sprintf( __( "File size exceeds %dMB. Please decrease the file size and try again.", 'dynamic-form-maker' ), $size ), '', array( 'back_link' => true ) );

			// Options array for the wp_handle_upload function. 'test_form' => false
			$upload_overrides = array( 'test_form' => false );

			// We need to include the file that runs the wp_handle_upload function
			require_once( ABSPATH . 'wp-admin/includes/file.php' );

			// Handle the upload using WP's wp_handle_upload function. Takes the posted file and an options array
			$uploaded_file = wp_handle_upload( $value, $upload_overrides );

			// If the wp_handle_upload call returned a local path for the image
			if ( isset( $uploaded_file['file'] ) ) :
				// Retrieve the file type from the file name. Returns an array with extension and mime type
				$wp_filetype = wp_check_filetype( basename( $uploaded_file['file'] ), null );

				// Return the current upload directory location
				$wp_upload_dir = wp_upload_dir();

				$media_upload = array(
					'guid' 				=> $wp_upload_dir['url'] . '/' . basename( $uploaded_file['file'] ),
					'post_mime_type' 	=> $wp_filetype['type'],
					'post_title' 		=> preg_replace( '/\.[^.]+$/', '', basename( $uploaded_file['file'] ) ),
					'post_content' 		=> '',
					'post_status' 		=> 'inherit'
				);

				// Insert attachment into Media Library and get attachment ID
				$attach_id = wp_insert_attachment( $media_upload, $uploaded_file['file'] );

				// Include the file that runs wp_generate_attachment_metadata()
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				require_once( ABSPATH . 'wp-admin/includes/media.php' );

				// Setup attachment metadata
				$attach_data = wp_generate_attachment_metadata( $attach_id, $uploaded_file['file'] );

				// Update the attachment metadata
				wp_update_attachment_metadata( $attach_id, $attach_data );

				$attachments[ 'dfm-' . $field->field_id ] = $uploaded_file['file'];

				$data[] = array(
					'id' 		=> $field->field_id,
					'slug' 		=> $field->field_key,
					'name' 		=> $field->field_name,
					'type' 		=> $field->field_type,
					'options' 	=> $field->field_options,
					'parent_id' => $field->field_parent,
					'value' 	=> $uploaded_file['url']
				);

				
			endif;
		else :
			$value = ( isset( $_POST[ 'dfm-' . $field->field_id ] ) ) ? $_POST[ 'dfm-' . $field->field_id ] : '';
			
		endif;

	// Everything else
	else :
		$value = ( isset( $_POST[ 'dfm-' . $field->field_id ] ) ) ? $_POST[ 'dfm-' . $field->field_id ] : '';

		// If time field, build proper output
		if ( is_array( $value ) && $field->field_type == 'time' )
			$value = $this->build_array_form_item( $value, $field->field_type );
		// If address field, build proper output
		elseif ( is_array( $value ) && $field->field_type == 'address' )
			$value = $this->build_array_form_item( $value, $field->field_type );
		// If multiple values, build the list
		elseif ( is_array( $value ) )
			$value = $this->build_array_form_item( $value, $field->field_type );
		elseif ( 'radio' == $field->field_type )
			$value = wp_specialchars_decode( stripslashes( esc_html( $value ) ), ENT_QUOTES );
		// Lastly, handle single values
		else
			$value = html_entity_decode( stripslashes( esc_html( $value ) ), ENT_QUOTES, 'UTF-8' );

		//Sanitize input
		$value = $this->sanitize_input( $value, $field->field_type );
		// Validate input
		//$this->validate_input( $value, $field->field_name, $field->field_type, $field->field_required );
		
		$removed_field_types = array( 'verification', 'secret', 'submit' );
		
		if(!in_array( $field->field_type , array( 'password','username','re-password','first-name','last-name','verification', 'secret', 'submit' ) )){
		add_user_meta( $createUser, $field->field_key, esc_html( $value ), true );
		}

	endif;
 $count++;	
endforeach;



