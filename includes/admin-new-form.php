
<div id="tabs">
  <ul>
    <li><a href="#tabs-1"><?php _e( 'User form' , 'dynamic-form-maker'); ?></a></li>
    <li><a href="#tabs-2"><?php _e( 'Email Form' , 'dynamic-form-maker'); ?></a></li>  	
  </ul>
  <div id="tabs-1">
    <p>
		<form method="post" id="dynamic-form-maker-user-form" action="">
	<input name="action" type="hidden" value="create_user" />
    <?php
    	wp_nonce_field( 'create_user' );

    	if ( !current_user_can( 'manage_options' ) )
    		wp_die( __( 'You do not have sufficient permissions to create a new user.', 'dynamic-form-maker' ) );
    ?>
	<h3><?php _e( 'Create a user' , 'dynamic-form-maker'); ?></h3>
		<table class="form-table">
		<tbody>
			<!-- Form Name -->
			<tr valign="top">
				<th scope="row"><label for="form-name"><?php _e( 'Form name' , 'dynamic-form-maker'); ?><span class="is-field-required">*</span></label></th>
				<td>
					<input type="text" autofocus="autofocus" class="regular-text required" id="form-name" name="form_title" />
					<p class="description"><?php _e( 'This field is required.' , 'dynamic-form-maker'); ?></p>
				</td>
			</tr>
			<!-- User Role -->
			<tr valign="top">
				<th scope="row"><label for="user-role"><?php _e( 'User Role' , 'dynamic-form-maker'); ?><span class="is-field-required">*</span></label></th>
				<td>
					<select name="user_role" id="user-role" >
					  <?php foreach (get_editable_roles() as $role_name => $role_info) {
					if( $role_name!= 'administrator') { ;?> 
					  <option value="<?php echo $role_name; ?>"><?php echo  $role_info['name']; ?></option>
						<?php } } ?>
					</select> 
					<p class="description"><?php _e( 'This field is required. Select form user role. Registration will be completed with this role.' , 'dynamic-form-maker'); ?></p>
				</td>
			</tr>

		</tbody>
	</table>
	<?php submit_button( __( 'Create User', 'dynamic-form-maker' ) ); ?>
	</form>
	</p>
  </div>
  <div id="tabs-2">
  <p>
  
	<form method="post" id="dynamic-form-maker-new-form" action="">
	<input name="action" type="hidden" value="create_form" />
    <?php
    	wp_nonce_field( 'create_form' );

    	if ( !current_user_can( 'manage_options' ) )
    		wp_die( __( 'You do not have sufficient permissions to create a new form.', 'dynamic-form-maker' ) );
    ?>
	<h3><?php _e( 'Create form' , 'dynamic-form-maker'); ?></h3>
		<table class="form-table">
		<tbody>
			<!-- Form Name -->
			<tr valign="top">
				<th scope="row"><label for="form-name"><?php _e( 'Form name' , 'dynamic-form-maker'); ?><span class="is-field-required">*</span></label></th>
				<td>
					<input type="text" autofocus="autofocus" class="regular-text required" id="form-name" name="form_title" />
					<p class="description"><?php _e( 'This field is required.' , 'dynamic-form-maker'); ?></p>
				</td>
			</tr>
			<!-- Sender Name -->
			<tr valign="top">
				<th scope="row"><label for="form-email-sender-name"><?php _e( 'Your name' , 'dynamic-form-maker'); ?><span class="is-field-required">*</span></label></th>
				<td>
					<input type="text" value="" placeholder="" class="regular-text required" id="form-email-sender-name" name="form_email_from_name" />
					<p class="description"><?php _e( 'This field is required. This field is used for email display name.' , 'dynamic-form-maker'); ?></p>
				</td>
			</tr>
			<!-- Email Subject -->
			<tr valign="top">
				<th scope="row"><label for="form-email-subject"><?php _e( 'Email Subject' , 'dynamic-form-maker'); ?></label></th>
				<td>
					<input type="text" value="" placeholder="" class="regular-text" id="form-email-subject" name="form_email_subject" />
					<p class="description"><?php _e( 'Subject of the email.' , 'dynamic-form-maker'); ?></p>
				</td>
			</tr>
			<!-- Reply-to Email -->
			<tr valign="top">
				<th scope="row"><label for="form-email-from"><?php _e( 'Reply to email' , 'dynamic-form-maker'); ?><span class="is-field-required">*</span></label></th>
				<td>
					<input type="text" value="" placeholder="" class="regular-text required" id="form-email-from" name="form_email_from" />
					<p class="description"><?php _e( 'This field is required. Reply to email/Form email.' , 'dynamic-form-maker'); ?></p>					
				</td>
			</tr>
			
			<!-- E-mail To -->
			<tr valign="top">
				<th scope="row"><label for="form-email-to"><?php _e( 'Email To' , 'dynamic-form-maker'); ?><span class="is-field-required">*</span></label></th>
				<td>
					<input type="text" value="" placeholder="" class="regular-text" id="form-email-to" name="form_email_to[]" />
					<p class="description"><?php _e( 'Email to. Add more after creating the form.' , 'dynamic-form-maker'); ?></p>
				</td>
			</tr>

		</tbody>
	</table>
	<?php submit_button( __( 'Create Form', 'dynamic-form-maker' ) ); ?>
</form>  
    
	</p>
  </div> 
   
  
</div>
	
	