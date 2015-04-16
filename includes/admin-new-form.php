<div id="tabs">
  <ul>
    <li><a href="#tabs-1"><?php _e( 'Contact form' , 'dynamic-form-maker'); ?></a></li>
    <li><a href="#tabs-2"><?php _e( 'User Form' , 'dynamic-form-maker'); ?></a></li>    
  </ul>
  <div id="tabs-1">
    <p>
	<form method="post" id="dynamic-form-maker-new-form" action="">
	<input name="action" type="hidden" value="create_form" />
    <?php
    	wp_nonce_field( 'create_form' );

    	if ( !current_user_can( 'manage_options' ) )
    		wp_die( __( 'You do not have sufficient permissions to create a new form.', 'dynamic-form-maker' ) );
    ?>
	<h3><?php _e( 'Create a form' , 'dynamic-form-maker'); ?></h3>
		<table class="form-table">
		<tbody>
			<!-- Form Name -->
			<tr valign="top">
				<th scope="row"><label for="form-name"><?php _e( 'Name the form' , 'dynamic-form-maker'); ?><span class="is-field-required">*</span></label></th>
				<td>
					<input type="text" autofocus="autofocus" class="regular-text required" id="form-name" name="form_title" />
					<p class="description"><?php _e( 'Required this field. This is used for admin site.' , 'dynamic-form-maker'); ?></p>
				</td>
			</tr>
			<!-- Sender Name -->
			<tr valign="top">
				<th scope="row"><label for="form-email-sender-name"><?php _e( 'Your Name or Company' , 'dynamic-form-maker'); ?><span class="is-field-required">*</span></label></th>
				<td>
					<input type="text" value="" placeholder="" class="regular-text required" id="form-email-sender-name" name="form_email_from_name" />
					<p class="description"><?php _e( 'Required this field. This option sets the "From" email display name that is sent.' , 'dynamic-form-maker'); ?></p>
				</td>
			</tr>
			<!-- Reply-to Email -->
			<tr valign="top">
				<th scope="row"><label for="form-email-from"><?php _e( 'Reply-To E-mail' , 'dynamic-form-maker'); ?><span class="is-field-required">*</span></label></th>
				<td>
					<input type="text" value="" placeholder="" class="regular-text required" id="form-email-from" name="form_email_from" />
					<p class="description"><?php _e( 'Required this field. Replies to your email will go from here.' , 'dynamic-form-maker'); ?></p>
					<p class="description"><?php _e( 'Tip: for best results, use an email that exists on your domain.' , 'dynamic-form-maker'); ?></p>
				</td>
			</tr>
			<!-- Email Subject -->
			<tr valign="top">
				<th scope="row"><label for="form-email-subject"><?php _e( 'E-mail Subject' , 'dynamic-form-maker'); ?></label></th>
				<td>
					<input type="text" value="" placeholder="" class="regular-text" id="form-email-subject" name="form_email_subject" />
					<p class="description"><?php _e( 'This sets the subject of the email that is sent.' , 'dynamic-form-maker'); ?></p>
				</td>
			</tr>
			<!-- E-mail To -->
			<tr valign="top">
				<th scope="row"><label for="form-email-to"><?php _e( 'E-mail To' , 'dynamic-form-maker'); ?></label></th>
				<td>
					<input type="text" value="" placeholder="" class="regular-text" id="form-email-to" name="form_email_to[]" />
					<p class="description"><?php _e( 'Send the submitted data to. You can add more after creating the form.' , 'dynamic-form-maker'); ?></p>
				</td>
			</tr>

		</tbody>
	</table>
	<?php submit_button( __( 'Create Form', 'dynamic-form-maker' ) ); ?>
</form>
	</p>
  </div>
  <div id="tabs-2">
    <p>Second.</p>
  </div>  
</div>
	
	