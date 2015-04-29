<?php
$order = sanitize_sql_orderby( 'form_id DESC' );
$form = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->form_table_name WHERE form_id = %d ORDER BY $order", $form_nav_selected_id ) );

if ( !$form || $form->form_id !== $form_nav_selected_id )
	wp_die( 'You must select a form' );

$form_id 					= $form->form_id;
$form_title 				= stripslashes( $form->form_title );
$form_subject 				= stripslashes( $form->form_email_subject );
$form_email_from_name 		= stripslashes( $form->form_email_from_name );
$form_email_from 			= stripslashes( $form->form_email_from);
$form_email_from_override 	= stripslashes( $form->form_email_from_override);
$form_email_from_name_override = stripslashes( $form->form_email_from_name_override);
$form_email_to = ( is_array( unserialize( $form->form_email_to ) ) ) ? unserialize( $form->form_email_to ) : explode( ',', unserialize( $form->form_email_to ) );
$form_success_type 			= stripslashes( $form->form_success_type );
$form_success_message 		= stripslashes( $form->form_success_message );
$form_notification_setting 	= stripslashes( $form->form_notification_setting );
$form_notification_email_name = stripslashes( $form->form_notification_email_name );
$form_notification_email_from = stripslashes( $form->form_notification_email_from );
$form_notification_email 	= stripslashes( $form->form_notification_email );
$form_notification_subject 	= stripslashes( $form->form_notification_subject );
$form_notification_message 	= stripslashes( $form->form_notification_message );
$form_notification_entry 	= stripslashes( $form->form_notification_entry );

$form_label_alignment 		= stripslashes( $form->form_label_alignment );

// Only show required text fields for the sender name override
$senders = $wpdb->get_results( $wpdb->prepare( "SELECT field_id, field_name FROM $this->field_table_name WHERE form_id = %d AND field_type IN( 'text', 'name' ) AND field_validation = '' AND field_required = 'yes'", $form_nav_selected_id ) );

// Only show required email fields for the email override
$emails = $wpdb->get_results( $wpdb->prepare( "SELECT field_id, field_name FROM $this->field_table_name WHERE (form_id = %d AND field_type='text' AND field_validation = 'email' AND field_required = 'yes') OR (form_id = %d AND field_type='email' AND field_validation = 'email' AND field_required = 'yes')", $form_nav_selected_id, $form_nav_selected_id ) );

$screen = get_current_screen();
$class = 'columns-' . get_current_screen()->get_columns();

$page_main = $this->_admin_pages[ 'dfm' ];
?>
<div id="dfm-form-builder-frame" class="metabox-holder <?php echo $class; ?>">
	<div id="dfm-postbox-container-1" class='dfm-postbox-container'>
    	<form id="form-items" class="nav-menu-meta" method="post" action="">
			<input name="action" type="hidden" value="create_field" />
			<input name="form_id" type="hidden" value="<?php echo $form_nav_selected_id; ?>" />
			<?php
			wp_nonce_field( 'create-field-' . $form_nav_selected_id );
			do_meta_boxes( $page_main, 'side', null );
			?>
		</form>
	</div> <!-- .dfm-postbox-container -->

    <div id="dfm-postbox-container-2" class='dfm-postbox-container'>
	    <div id="dfm-form-builder-main">
	        <div id="dfm-form-builder-management">
	            <div class="form-edit">
<form method="post" id="dynamic-form-maker-update" action="">
	<input name="action" type="hidden" value="update_form" />
	<input name="form_id" type="hidden" value="<?php echo $form_nav_selected_id; ?>" />
    <?php wp_nonce_field( 'dfm_update_form' ); ?>
	<div id="form-editor-header">
    	<div id="submitpost" class="submitbox">
        	<div class="dfm-major-publishing-actions">
        		<label for="form-name" class="menu-name-label howto open-label">
                    <span class="sender-labels"><?php _e( 'Form Name' , 'dynamic-form-maker'); ?></span>
                    <input type="text" value="<?php echo ( isset( $form_title ) ) ? $form_title : ''; ?>" placeholder="<?php _e( 'Enter form name here' , 'dynamic-form-maker'); ?>" class="menu-name regular-text menu-item-textbox required" id="form-name" name="form_title" />
                </label>
                <br class="clear" />

                <?php
					// Get the Form Setting drop down and accordion settings, if any
					$user_form_settings = get_user_meta( $user_id, 'dfm-form-settings' );

					// Setup defaults for the Form Setting tab and accordion
					$settings_tab = 'closed';
					$settings_accordion = 'general-settings';

					// Loop through the user_meta array
					foreach( $user_form_settings as $set ) :
						// If form settings exist for this form, use them instead of the defaults
						if ( isset( $set[ $form_id ] ) ) :
							$settings_tab 		= $set[ $form_id ]['form_setting_tab'];
							$settings_accordion = $set[ $form_id ]['setting_accordion'];
						endif;
					endforeach;

					// If tab is opened, set current class
					$opened_tab = ( $settings_tab == 'opened' ) ? 'current' : '';
				?>


                <div class="dfm-button-group">
					<a href="#form-settings" id="form-settings-button" class="dfm-button dfm-settings <?php echo $opened_tab; ?>">
						<?php _e( 'Settings' , 'dynamic-form-maker'); ?>
						<i class="fa fa-cogs"></i>
					</a>
                    <a href="<?php echo esc_url( wp_nonce_url( admin_url('admin.php?page=dynamic-form-maker&amp;action=copy_form&amp;form=' . $form_nav_selected_id ), 'copy-form-' . $form_nav_selected_id ) ); ?>" class="dfm-button dfm-duplicate">
                    	<?php _e( 'Duplicate' , 'dynamic-form-maker'); ?>
                    	<!--<span class="dfm-interface-icon dfm-interface-duplicate fa fa-files-o"></span>-->
						<i class="fa fa-files-o"></i>
                    </a>
                    <a href="<?php echo esc_url( wp_nonce_url( admin_url('admin.php?page=dynamic-form-maker&amp;action=delete_form&amp;form=' . $form_nav_selected_id ), 'delete-form-' . $form_nav_selected_id ) ); ?>" class="dfm-button dfm-delete dfm-last menu-delete">
                    	<?php _e( 'Delete' , 'dynamic-form-maker'); ?>
                    	<i class="fa fa-trash-o"></i>
                    </a>

                    <?php submit_button( __( 'Save', 'dynamic-form-maker' ), 'primary', 'save_form', false ); ?>
                </div>

                    <div id="form-settings" class="<?php echo $opened_tab; ?>">
                        <!-- General settings section -->
                            <a href="#general-settings" class="settings-links<?php echo ( $settings_accordion == 'general-settings' ) ? ' on' : ''; ?>"><?php _e( 'General', 'dynamic-form-maker' ); ?><span class="dfm-large-arrow"></span></a>
                        <div id="general-settings" class="form-details<?php echo ( $settings_accordion == 'general-settings' ) ? ' on' : ''; ?>">
                            <!-- Label Alignment -->
                            <p class="description description-wide">
                            <label for="form-label-alignment">
                                <?php _e( 'Label Alignment' , 'dynamic-form-maker'); ?>
                                <span class="dfm-tooltip" title="<?php esc_attr_e( 'About Label Alignment', 'dynamic-form-maker' ); ?>" rel="<?php esc_attr_e( 'Set the field labels for this form to be aligned either on top, to the left, or to the right.  By default, all labels are aligned on top of the inputs.' ); ?>">(?)</span>
            					<br />
                             </label>
                                <select name="form_label_alignment" id="form-label-alignment" class="widefat">
                                    <option value="" <?php selected( $form_label_alignment, '' ); ?>><?php _e( 'Top Aligned' , 'dynamic-form-maker'); ?></option>
                                    <option value="left-label" <?php selected( $form_label_alignment, 'left-label' ); ?>><?php _e( 'Left Aligned' , 'dynamic-form-maker'); ?></option>
                                    <option value="right-label" <?php selected( $form_label_alignment, 'right-label' ); ?>><?php _e( 'Right Aligned' , 'dynamic-form-maker'); ?></option>
                                </select>
                            </p>
                            <br class="clear" />
                        </div> <!-- #general-settings -->

						<?php
							global $wpdb;
							$form_table_name = $wpdb->prefix . "dynamic_form_maker_forms";				
							$form_table = $wpdb->get_results( "SELECT * FROM $form_table_name WHERE form_id = ".$_REQUEST['form'] );
							$form_type = $form_table[0]->form_type;
							if($form_type === 'user_form'):
						?>
						
						<!-- User Role section -->
                        <a href="#user-roles" class="settings-links<?php echo ( $settings_accordion == 'user-roles' ) ? ' on' : ''; ?>"><?php _e( 'User Roles', 'dynamic-form-maker' ); ?><span class="dfm-large-arrow"></span></a>
                        <div id="user-roles" class="form-details<?php echo ( $settings_accordion == 'user-roles' ) ? ' on' : ''; ?>">

                            <p><em><?php _e( 'Select user role' , 'dynamic-form-maker'); ?></em></p>

                            <!-- User Role -->
                            <p class="description description-wide">
                            <label for="form-user-role">
                                <?php _e( 'User Role' , 'dynamic-form-maker'); ?>
                                <span class="dfm-tooltip" title="<?php esc_attr_e( 'About User role', 'dynamic-form-maker' ); ?>" rel="<?php esc_attr_e( 'Select new user role to update.', 'dynamic-form-maker' ); ?>">(?)</span>
            					<br />
                                <select name="user_role" id="form-user-role" >
								  <?php foreach (get_editable_roles() as $role_name => $role_info) {
								if( $role_name!= 'administrator') { ;?> 
								  <option value="<?php echo $role_name; ?>"><?php echo  $role_info['name']; ?></option>
									<?php } } ?>
								</select> 
                            </label>
                            </p>
                            <br class="clear" />
                        </div>
						
						<?php else : ?>

                        <!-- Email section -->
                        <a href="#email-details" class="settings-links<?php echo ( $settings_accordion == 'email-details' ) ? ' on' : ''; ?>"><?php _e( 'Email', 'dynamic-form-maker' ); ?><span class="dfm-large-arrow"></span></a>
                        <div id="email-details" class="form-details<?php echo ( $settings_accordion == 'email-details' ) ? ' on' : ''; ?>">

                            <p><em><?php _e( 'The forms you build here will send information to one or more email addresses when submitted by a user on your site.  Use the fields below to customize the details of that email.' , 'dynamic-form-maker'); ?></em></p>

                            <!-- E-mail Subject -->
                            <p class="description description-wide">
                            <label for="form-email-subject">
                                <?php _e( 'E-mail Subject' , 'dynamic-form-maker'); ?>
                                <span class="dfm-tooltip" title="<?php esc_attr_e( 'About E-mail Subject', 'dynamic-form-maker' ); ?>" rel="<?php esc_attr_e( 'This option sets the subject of the email that is sent to the emails you have set in the E-mail(s) To field.', 'dynamic-form-maker' ); ?>">(?)</span>
            					<br />
                                <input type="text" value="<?php echo stripslashes( $form_subject ); ?>" class="widefat" id="form-email-subject" name="form_email_subject" />
                            </label>
                            </p>
                            <br class="clear" />

                            <!-- Sender Name -->
                            <p class="description description-thin">
                            <label for="form-email-sender-name">
                                <?php _e( 'Your name' , 'dynamic-form-maker'); ?>
                                <span class="dfm-tooltip" title="<?php esc_attr_e( 'About Your name', 'dynamic-form-maker' ); ?>" rel="<?php esc_attr_e( 'This option sets the From display name of the email that is sent to the emails you have set in the E-mail(s) To field.', 'dynamic-form-maker' ); ?>">(?)</span>
            					<br />
                                <input type="text" value="<?php echo $form_email_from_name; ?>" class="widefat" id="form-email-sender-name" name="form_email_from_name"<?php echo ( $form_email_from_name_override != '' ) ? ' readonly="readonly"' : ''; ?> />
                            </label>
                            </p>
                            <p class="description description-thin">
                            	<label for="form_email_from_name_override">
                                	<?php _e( "User's Name (optional)" , 'dynamic-form-maker'); ?>
                                    <span class="dfm-tooltip" title="<?php esc_attr_e( "About User's Name", 'dynamic-form-maker' ); ?>" rel="<?php esc_attr_e( 'Select a required text field from your form to use as the From display name in the email.', 'dynamic-form-maker' ); ?>">(?)</span>
            						<br />
                                <?php if ( empty( $senders ) ) : ?>
                                <span><?php _e( 'No required text fields detected', 'dynamic-form-maker' ); ?></span>
                                <?php else : ?>
                                <select name="form_email_from_name_override" id="form_email_from_name_override" class="widefat">
                                    <option value="" <?php selected( $form_email_from_name_override, '' ); ?>></option>
                                    <?php
                                    foreach( $senders as $sender ) {
                                        echo sprintf( '<option value="%1$d"%2$s>%3$s</option>',
	                                        $sender->field_id,
	                                        selected( $form_email_from_name_override, $sender->field_id, 0 ),
	                                        stripslashes( $sender->field_name )
                                        );
                                    }
                                    ?>
                                </select>
                                <?php endif; ?>
                                </label>
                            </p>
                            <br class="clear" />

                            <!-- Sender E-mail -->
                            <p class="description description-thin">
                            <label for="form-email-sender">
                                <?php _e( 'Reply to email' , 'dynamic-form-maker'); ?>
                                <span class="dfm-tooltip" title="<?php esc_attr_e( 'About Reply-To Email', 'dynamic-form-maker' ); ?>" rel="<?php esc_attr_e( 'Manually set the email address that users will reply to.', 'dynamic-form-maker' ); ?>">(?)</span>
            					<br />
                                <input type="text" value="<?php echo $form_email_from; ?>" class="widefat" id="form-email-sender" name="form_email_from"<?php echo ( $form_email_from_override != '' ) ? ' readonly="readonly"' : ''; ?> />
                            </label>
                            </p>
                            <p class="description description-thin">
                                <label for="form_email_from_override">
                                	<?php _e( "User's E-mail (optional)" , 'dynamic-form-maker'); ?>
                                    <span class="dfm-tooltip" title="<?php esc_attr_e( "About User's Email", 'dynamic-form-maker' ); ?>" rel="<?php esc_attr_e( 'Select a required email field from your form to use as the Reply-To email.', 'dynamic-form-maker' ); ?>">(?)</span>
            						<br />
                                <?php if ( empty( $emails ) ) : ?>
                                <span><?php _e( 'No required email fields detected', 'dynamic-form-maker' ); ?></span>
                                <?php else : ?>
                                <select name="form_email_from_override" id="form_email_from_override" class="widefat">
                                    <option value="" <?php selected( $form_email_from_override, '' ); ?>></option>
                                    <?php
                                    foreach( $emails as $email ) {
                                        echo sprintf( '<option value="%1$d"%2$s>%3$s</option>',
                                        	$email->field_id,
                                        	selected( $form_email_from_override, $email->field_id, 0 ),
                                        	stripslashes( $email->field_name )
                                        );
                                    }
                                    ?>
                                </select>
                                <?php endif; ?>
                                </label>
                            </p>
                            <br class="clear" />

                            <!-- E-mail(s) To -->
                            <?php
                                // Basic count to keep track of multiple options
                                $count = 1;

                                // Loop through the options
                                foreach ( $form_email_to as $email_to ) :
                            ?>
                            <div id="clone-email-<?php echo $count; ?>" class="option">
                                <p class="description description-wide">
                                    <label for="form-email-to-<?php echo "$count"; ?>" class="clonedOption">
                                    <?php _e( 'E-mail(s) To' , 'dynamic-form-maker'); ?>
                                    <span class="dfm-tooltip" title="<?php esc_attr_e( 'About E-mail(s) To', 'dynamic-form-maker' ); ?>" rel="<?php esc_attr_e( 'This option sets single or multiple emails to send the submitted form data to. At least one email is required.', 'dynamic-form-maker' ); ?>">(?)</span>
            					<br />
                                        <input type="text" value="<?php echo stripslashes( $email_to ); ?>" name="form_email_to[]" class="widefat" id="form-email-to-<?php echo "$count"; ?>" />
                                    </label>

                                    <a href="#" class="addEmail dfm-interface-icon dfm-interface-plus" title="<?php esc_attr_e( 'Add an Email', 'visua-form-builder' ); ?>">
                                    	<?php _e( 'Add', 'dynamic-form-maker' ); ?>
                                    </a>
                                    <a href="#" class="deleteEmail dfm-interface-icon dfm-interface-minus" title="<?php esc_attr_e( 'Delete Email', 'dynamic-form-maker' ); ?>">
                                    	<?php _e( 'Delete', 'dynamic-form-maker' ); ?>
                                    </a>

                                </p>
                                <br class="clear" />
                            </div>
                            <?php
                                    $count++;
                                endforeach;
                            ?>
                            <div class="clear"></div>
                        </div>
					<?php endif; ?>
                        <!-- Confirmation section -->
                        <a href="#confirmation" class="settings-links<?php echo ( $settings_accordion == 'confirmation' ) ? ' on' : ''; ?>"><?php _e( 'Confirmation', 'dynamic-form-maker' ); ?><span class="dfm-large-arrow"></span></a>
                        <div id="confirmation-message" class="form-details<?php echo ( $settings_accordion == 'confirmation' ) ? ' on' : ''; ?>">
                            <p><em><?php _e( "After someone submits a form, you can control what is displayed. By default, it's a message but you can send them to another WordPress Page or a custom URL." , 'dynamic-form-maker'); ?></em></p>
                            <label for="form-success-type-text" class="menu-name-label open-label">
                                <input type="radio" value="text" id="form-success-type-text" class="form-success-type" name="form_success_type" <?php checked( $form_success_type, 'text' ); ?> />
                                <span><?php _e( 'Text' , 'dynamic-form-maker'); ?></span>
                            </label>
                            <label for="form-success-type-page" class="menu-name-label open-label">
                                <input type="radio" value="page" id="form-success-type-page" class="form-success-type" name="form_success_type" <?php checked( $form_success_type, 'page' ); ?>/>
                                <span><?php _e( 'Page' , 'dynamic-form-maker'); ?></span>
                            </label>
                            <label for="form-success-type-redirect" class="menu-name-label open-label">
                                <input type="radio" value="redirect" id="form-success-type-redirect" class="form-success-type" name="form_success_type" <?php checked( $form_success_type, 'redirect' ); ?>/>
                                <span><?php _e( 'Redirect' , 'dynamic-form-maker'); ?></span>
                            </label>
                            <br class="clear" />
                            <p class="description description-wide">
                            <?php
                            $default_text = '';

                            /* If there's no text message, make sure there is something displayed by setting a default */
                            if ( $form_success_message === '' )
                                $default_text = sprintf( '<p id="form_success">%s</p>', __( 'Your form was successfully submitted. Thank you for contacting us.' , 'dynamic-form-maker') );
                            ?>
                            <textarea id="form-success-message-text" class="form-success-message<?php echo ( 'text' == $form_success_type ) ? ' active' : ''; ?>" name="form_success_message_text"><?php echo $default_text; ?><?php echo ( 'text' == $form_success_type ) ? $form_success_message : ''; ?></textarea>

                            <?php
                            /* Display all Pages */
                            wp_dropdown_pages( array(
                                'name' => 'form_success_message_page',
                                'id' => 'form-success-message-page',
                                'class' => 'widefat',
                                'show_option_none' => __( 'Select a Page' , 'dynamic-form-maker'),
                                'selected' => $form_success_message
                            ));
                            ?>
                            <input type="text" value="<?php echo ( 'redirect' == $form_success_type ) ? $form_success_message : ''; ?>" id="form-success-message-redirect" class="form-success-message regular-text<?php echo ( 'redirect' == $form_success_type ) ? ' active' : ''; ?>" name="form_success_message_redirect" placeholder="http://" />
                            </p>
                        <br class="clear" />

                        </div>

						<?php
							global $wpdb;
							$form_table_name = $wpdb->prefix . "dynamic_form_maker_forms";				
							$form_table = $wpdb->get_results( "SELECT * FROM $form_table_name WHERE form_id = ".$_REQUEST['form'] );
							$form_type = $form_table[0]->form_type;
							if($form_type != 'user_form'):
						?>
						
                        <!-- Notification section -->
                        <a href="#notification" class="settings-links<?php echo ( $settings_accordion == 'notification' ) ? ' on' : ''; ?>"><?php _e( 'Notification', 'dynamic-form-maker' ); ?><span class="dfm-large-arrow"></span></a>
                        <div id="notification" class="form-details<?php echo ( $settings_accordion == 'notification' ) ? ' on' : ''; ?>">
                            <p><em><?php _e( "When a user submits their entry, you can send a customizable notification email." , 'dynamic-form-maker'); ?></em></p>
                            <label for="form-notification-setting">
                                <input type="checkbox" value="1" id="form-notification-setting" class="form-notification" name="form_notification_setting" <?php checked( $form_notification_setting, '1' ); ?> style="margin-top:-1px;margin-left:0;"/>
                                <?php _e( 'Send Confirmation Email to User' , 'dynamic-form-maker'); ?>
                            </label>
                            <br class="clear" />
                            <div id="notification-email">
                                <p class="description description-wide">
                                <label for="form-notification-email-name">
                                    <?php _e( 'Sender Name or Company' , 'dynamic-form-maker'); ?>
                                    <span class="dfm-tooltip" title="<?php esc_attr_e( 'About Sender Name or Company', 'dynamic-form-maker' ); ?>" rel="<?php esc_attr_e( 'Enter the name you would like to use for the email notification.', 'dynamic-form-maker' ); ?>">(?)</span>
            						<br />
                                    <input type="text" value="<?php echo $form_notification_email_name; ?>" class="widefat" id="form-notification-email-name" name="form_notification_email_name" />
                                </label>
                                </p>
                                <br class="clear" />
                                <p class="description description-wide">
                                <label for="form-notification-email-from">
                                    <?php _e( 'Reply to email' , 'dynamic-form-maker'); ?>
                                    <span class="dfm-tooltip" title="<?php esc_attr_e( 'About Reply-To Email', 'dynamic-form-maker' ); ?>" rel="<?php esc_attr_e( 'Manually set the email address that users will reply to.', 'dynamic-form-maker' ); ?>">(?)</span>
            						<br />
                                    <input type="text" value="<?php echo $form_notification_email_from; ?>" class="widefat" id="form-notification-email-from" name="form_notification_email_from" />
                                </label>
                                </p>
                                <br class="clear" />
                                <p class="description description-wide">
                                    <label for="form-notification-email">
                                        <?php _e( 'E-mail To' , 'dynamic-form-maker'); ?>
                                        <span class="dfm-tooltip" title="<?php esc_attr_e( 'About E-mail To', 'dynamic-form-maker' ); ?>" rel="<?php esc_attr_e( 'Select a required email field from your form to send the notification email to.', 'dynamic-form-maker' ); ?>">(?)</span>
            							<br />
                                        <?php if ( empty( $emails ) ) : ?>
                                        <span><?php _e( 'No required email fields detected', 'dynamic-form-maker' ); ?></span>
                                        <?php else : ?>
                                        <select name="form_notification_email" id="form-notification-email" class="widefat">
                                            <option value="" <?php selected( $form_notification_email, '' ); ?>></option>
                                            <?php
                                            foreach( $emails as $email ) {
                                                echo sprintf( '<option value="%1$d"%2$s>%3$s</option>',
                                                	$email->field_id,
                                                	selected( $form_notification_email, $email->field_id, 0 ),
                                                	$email->field_name
                                                );
                                            }
                                            ?>
                                        </select>
                                        <?php endif; ?>
                                    </label>
                                </p>
                                <br class="clear" />
                                <p class="description description-wide">
                                <label for="form-notification-subject">
                                   <?php _e( 'E-mail Subject' , 'dynamic-form-maker'); ?>
                                   <span class="dfm-tooltip" title="<?php esc_attr_e( 'About E-mail Subject', 'dynamic-form-maker' ); ?>" rel="<?php esc_attr_e( 'This option sets the subject of the email that is sent to the emails you have set in the E-mail To field.', 'dynamic-form-maker' ); ?>">(?)</span>
            						<br />
                                    <input type="text" value="<?php echo $form_notification_subject; ?>" class="widefat" id="form-notification-subject" name="form_notification_subject" />
                                </label>
                                </p>
                                <br class="clear" />
                                <p class="description description-wide">
                                <label for="form-notification-message"><?php _e( 'Message' , 'dynamic-form-maker'); ?></label>
                                <span class="dfm-tooltip" title="<?php esc_attr_e( 'About Message', 'dynamic-form-maker' ); ?>" rel="<?php esc_attr_e( 'Insert a message to the user. This will be inserted into the beginning of the email body.', 'dynamic-form-maker' ); ?>">(?)</span>
            					<br />
                                <textarea id="form-notification-message" class="form-notification-message widefat" name="form_notification_message"><?php echo $form_notification_message; ?></textarea>
                                </p>
                                <br class="clear" />
                                <label for="form-notification-entry">
                                <input type="checkbox" value="1" id="form-notification-entry" class="form-notification" name="form_notification_entry" <?php checked( $form_notification_entry, '1' ); ?> style="margin-top:-1px;margin-left:0;"/>
                                <?php _e( "Include a Copy of the User's Entry" , 'dynamic-form-maker'); ?>
                            </label>
                            <br class="clear" />
                        </div>
                    </div>
					<?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div id="post-body">
        <div id="post-body-content">
        <div id="dfm-fieldset-first-warning" class="error"><?php printf( '<p><strong>%1$s </strong><br>%2$s</p>', __( 'Warning &mdash; Missing Fieldset', 'dynamic-form-maker' ), __( 'Your form may not function or display correctly. Please be sure to add or move a Fieldset to the beginning of your form.' , 'dynamic-form-maker') ); ?></div>
        <!-- !Field Items output -->
		<ul id="dfm-menu-to-edit" class="menu ui-sortable droppable">
		<?php echo $this->field_output( $form_nav_selected_id ); ?>
		</ul>
        </div>
        <br class="clear" />
     </div>
     <br class="clear" />
    <div id="form-editor-footer">
    	<div class="dfm-major-publishing-actions">
            <div class="publishing-action">
            	<?php submit_button( __( 'Save Form', 'dynamic-form-maker' ), 'primary', 'save_form', false ); ?>
            </div> <!-- .publishing-action -->
        </div> <!-- .dfm-major-publishing-actions -->
    </div> <!-- #form-editor-footer -->
</form>
	            </div> <!-- .form-edit -->
	        </div> <!-- #dfm-form-builder-management -->
	    </div> <!-- dfm-form-builder-main -->
    </div> <!-- .dfm-postbox-container -->
</div> <!-- #dfm-form-builder-frame -->
<?php
wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
