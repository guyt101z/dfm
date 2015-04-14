<?php
global $wpdb;

$field_where = ( isset( $field_id ) && !is_null( $field_id ) ) ? "AND field_id = $field_id" : '';
// Display all fields for the selected form
$fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->field_table_name WHERE form_id = %d $field_where ORDER BY field_sequence ASC", $form_nav_selected_id ) );

$depth = 1;
$parent = $last = 0;
ob_start();

// Loop through each field and display
foreach ( $fields as &$field ) :
	// If we are at the root level
	if ( !$field->field_parent && $depth > 1 ) {
		// If we've been down a level, close out the list
		while ( $depth > 1 ) {
			echo '</li></ul>';
			$depth--;
		}

		// Close out the root item
		echo '</li>';
	}
	// first item of <ul>, so move down a level
	elseif ( $field->field_parent && $field->field_parent == $last ) {
		echo '<ul class="parent">';
		$depth++;
	}
	// Close up a <ul> and move up a level
	elseif ( $field->field_parent && $field->field_parent != $parent ) {
		echo '</li></ul></li>';
		$depth--;
	}
	// Same level so close list item
	elseif ( $field->field_parent && $field->field_parent == $parent )
		echo '</li>';

	// Store item ID and parent ID to test for nesting
	$last = $field->field_id;
	$parent = $field->field_parent;
?>
<li id="form_item_<?php echo $field->field_id; ?>" class="form-item<?php echo ( in_array( $field->field_type, array( 'submit', 'secret', 'verification' ) ) ) ? ' ui-state-disabled' : ''; ?><?php echo ( !in_array( $field->field_type, array( 'fieldset', 'section', 'verification' ) ) ) ? ' mjs-nestedSortable-no-nesting' : ''; ?>">
<dl class="menu-item-bar dfm-menu-item-inactive">
	<dt class="dfm-menu-item-handle dfm-menu-item-type-<?php echo esc_attr( $field->field_type ); ?>">
		<span class="item-title"><?php echo stripslashes( esc_attr( $field->field_name ) ); ?><?php echo ( $field->field_required == 'yes' ) ? ' <span class="is-field-required">*</span>' : ''; ?></span>
        <span class="item-controls">
			<span class="item-type"><?php echo strtoupper( str_replace( '-', ' ', $field->field_type ) ); ?></span>
			<a href="#" title="<?php _e( 'Edit Field Item' , 'dynamic-form-maker'); ?>" id="edit-<?php echo $field->field_id; ?>" class="item-edit"><?php _e( 'Edit Field Item' , 'dynamic-form-maker'); ?></a>
		</span>
	</dt>
</dl>

<div id="form-item-settings-<?php echo $field->field_id; ?>" class="menu-item-settings field-type-<?php echo $field->field_type; ?>" style="display: none;">
<?php if ( in_array( $field->field_type, array( 'fieldset', 'section', 'verification' ) ) ) : ?>

	<p class="description description-wide">
		<label for="edit-form-item-name-<?php echo $field->field_id; ?>"><?php echo ( in_array( $field->field_type, array( 'fieldset', 'verification' ) ) ) ? 'Legend' : 'Name'; ?>
        <span class="dfm-tooltip" rel="<?php esc_attr_e( 'For Fieldsets, a Legend is simply the name of that group. Use general terms that describe the fields included in this Fieldset.', 'dynamic-form-maker' ); ?>" title="<?php esc_attr_e( 'About Legend', 'dynamic-form-maker' ); ?>">(?)</span>
            <br />
			<input type="text" value="<?php echo stripslashes( esc_attr( $field->field_name ) ); ?>" name="field_name-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-name-<?php echo $field->field_id; ?>" maxlength="255" />
		</label>
	</p>
    <p class="description description-wide">
        <label for="edit-form-item-css-<?php echo $field->field_id; ?>">
            <?php _e( 'CSS Classes' , 'dynamic-form-maker'); ?>
            <span class="dfm-tooltip" rel="<?php esc_attr_e( 'For each field, you can insert your own CSS class names which can be used in your own stylesheets.', 'dynamic-form-maker' ); ?>" title="<?php esc_attr_e( 'About CSS Classes', 'dynamic-form-maker' ); ?>">(?)</span>
            <br />
            <input type="text" value="<?php echo stripslashes( esc_attr( $field->field_css ) ); ?>" name="field_css-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-css-<?php echo $field->field_id; ?>" />
        </label>
    </p>

<?php elseif( $field->field_type == 'instructions' ) : ?>
	<!-- Instructions -->
	<p class="description description-wide">
		<label for="edit-form-item-name-<?php echo $field->field_id; ?>">
				<?php _e( 'Name' , 'dynamic-form-maker'); ?>
                <span class="dfm-tooltip" title="<?php esc_attr_e( 'About Name', 'dynamic-form-maker' ); ?>" rel="<?php esc_attr_e( "A field's name is the most visible and direct way to describe what that field is for.", 'dynamic-form-maker' ); ?>">(?)</span>
                <br />
				<input type="text" value="<?php echo stripslashes( esc_attr( $field->field_name ) ); ?>" name="field_name-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-name-<?php echo $field->field_id; ?>" maxlength="255" />
		</label>
	</p>
	<!-- Description -->
	<p class="description description-wide">
		<label for="edit-form-item-description-<?php echo $field->field_id; ?>">
        	<?php _e( 'Description (HTML tags allowed)', 'dynamic-form-maker' ); ?>
        	<span class="dfm-tooltip" title="<?php esc_attr_e( 'About Instructions Description', 'dynamic-form-maker' ); ?>" rel="<?php esc_attr_e( 'The Instructions field allows for long form explanations, typically seen at the beginning of Fieldsets or Sections. HTML tags are allowed.', 'dynamic-form-maker' ); ?>">(?)</span>
            <br />
			<textarea name="field_description-<?php echo $field->field_id; ?>" class="widefat edit-menu-item-description" cols="20" rows="3" id="edit-form-item-description-<?php echo $field->field_id; ?>" /><?php echo stripslashes( $field->field_description ); ?></textarea>
		</label>
	</p>
	<!-- CSS Classes -->
<p class="description description-thin">
    <label for="edit-form-item-css-<?php echo $field->field_id; ?>">
        <?php _e( 'CSS Classes' , 'dynamic-form-maker-pro'); ?>
        <span class="dfm-tooltip" rel="<?php esc_attr_e( 'For each field, you can insert your own CSS class names which can be used in your own stylesheets.', 'dynamic-form-maker-pro' ); ?>" title="<?php esc_attr_e( 'About CSS Classes', 'dynamic-form-maker-pro' ); ?>">(?)</span>
        <br />
        <input type="text" value="<?php echo stripslashes( esc_attr( $field->field_css ) ); ?>" name="field_css-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-css-<?php echo $field->field_id; ?>" />
    </label>
</p>

<!-- Field Layout -->
<p class="description description-thin">
	<label for="edit-form-item-layout">
		<?php _e( 'Field Layout' , 'dynamic-form-maker-pro'); ?>
        <span class="dfm-tooltip" title="<?php esc_attr_e( 'About Field Layout', 'dynamic-form-maker-pro' ); ?>" rel="<?php esc_attr_e( 'Used to create advanced layouts. Align fields side by side in various configurations.', 'dynamic-form-maker-pro' ); ?>">(?)</span>
    <br />
		<select name="field_layout-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-layout-<?php echo $field->field_id; ?>">

			<option value="" <?php selected( $field->field_layout, '' ); ?>><?php _e( 'Default' , 'dynamic-form-maker-pro'); ?></option>
            <optgroup label="------------">
            <option value="left-half" <?php selected( $field->field_layout, 'left-half' ); ?>><?php _e( 'Left Half' , 'dynamic-form-maker-pro'); ?></option>
            <option value="right-half" <?php selected( $field->field_layout, 'right-half' ); ?>><?php _e( 'Right Half' , 'dynamic-form-maker-pro'); ?></option>
            </optgroup>
            <optgroup label="------------">
			<option value="left-third" <?php selected( $field->field_layout, 'left-third' ); ?>><?php _e( 'Left Third' , 'dynamic-form-maker-pro'); ?></option>
            <option value="middle-third" <?php selected( $field->field_layout, 'middle-third' ); ?>><?php _e( 'Middle Third' , 'dynamic-form-maker-pro'); ?></option>
            <option value="right-third" <?php selected( $field->field_layout, 'right-third' ); ?>><?php _e( 'Right Third' , 'dynamic-form-maker-pro'); ?></option>
            </optgroup>
            <optgroup label="------------">
            <option value="left-two-thirds" <?php selected( $field->field_layout, 'left-two-thirds' ); ?>><?php _e( 'Left Two Thirds' , 'dynamic-form-maker-pro'); ?></option>
            <option value="right-two-thirds" <?php selected( $field->field_layout, 'right-two-thirds' ); ?>><?php _e( 'Right Two Thirds' , 'dynamic-form-maker-pro'); ?></option>
            </optgroup>
            <?php apply_filters( 'dfm_admin_field_layout', $field->field_layout ); ?>
		</select>
	</label>
</p>

<?php else: ?>

	<!-- Name -->
	<p class="description description-wide">
		<label for="edit-form-item-name-<?php echo $field->field_id; ?>">
			<?php _e( 'Name' , 'dynamic-form-maker'); ?>
            <span class="dfm-tooltip" title="<?php esc_attr_e( 'About Name', 'dynamic-form-maker' ); ?>" rel="<?php esc_attr_e( "A field's name is the most visible and direct way to describe what that field is for.", 'dynamic-form-maker' ); ?>">(?)</span>
            <br />
			<input type="text" value="<?php echo stripslashes( esc_attr( $field->field_name ) ); ?>" name="field_name-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-name-<?php echo $field->field_id; ?>" maxlength="255" />
		</label>
	</p>
	<?php if ( $field->field_type == 'submit' ) : ?>
		<!-- CSS Classes -->
        <p class="description description-wide">
            <label for="edit-form-item-css-<?php echo $field->field_id; ?>">
                <?php _e( 'CSS Classes' , 'dynamic-form-maker'); ?>
                <span class="dfm-tooltip" rel="<?php esc_attr_e( 'For each field, you can insert your own CSS class names which can be used in your own stylesheets.', 'dynamic-form-maker' ); ?>" title="<?php esc_attr_e( 'About CSS Classes', 'dynamic-form-maker' ); ?>">(?)</span>
                <br />
                <input type="text" value="<?php echo stripslashes( esc_attr( $field->field_css ) ); ?>" name="field_css-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-css-<?php echo $field->field_id; ?>" />
            </label>
        </p>
	<?php elseif ( $field->field_type !== 'submit' ) : ?>
		<!-- Description -->
		<p class="description description-wide">
			<label for="edit-form-item-description-<?php echo $field->field_id; ?>">
				<?php _e( 'Description' , 'dynamic-form-maker'); ?>
                 <span class="dfm-tooltip" title="<?php esc_attr_e( 'About Description', 'dynamic-form-maker' ); ?>" rel="<?php esc_attr_e( 'A description is an optional piece of text that further explains the meaning of this field. Descriptions are displayed below the field. HTML tags are allowed.', 'dynamic-form-maker' ); ?>">(?)</span>
                <br />
				<textarea name="field_description-<?php echo $field->field_id; ?>" class="widefat edit-menu-item-description" cols="20" rows="3" id="edit-form-item-description-<?php echo $field->field_id; ?>" /><?php echo stripslashes( $field->field_description ); ?></textarea>
			</label>
		</p>

		<?php
			// Display the Options input only for radio, checkbox, and select fields
			if ( in_array( $field->field_type, array( 'radio', 'checkbox', 'select' ) ) ) : ?>
			<!-- Options -->
			<p class="description description-wide">
				<?php _e( 'Options' , 'dynamic-form-maker'); ?>
                <span class="dfm-tooltip" title="<?php esc_attr_e( 'About Options', 'dynamic-form-maker' ); ?>" rel="<?php esc_attr_e( 'This property allows you to set predefined options to be selected by the user.  Use the plus and minus buttons to add and delete options.  At least one option must exist.', 'dynamic-form-maker' ); ?>">(?)</span>
                <br />
			<?php
				// If the options field isn't empty, unserialize and build array
				if ( !empty( $field->field_options ) ) {
					if ( is_serialized( $field->field_options ) )
						$opts_vals = ( is_array( unserialize( $field->field_options ) ) ) ? unserialize( $field->field_options ) : explode( ',', unserialize( $field->field_options ) );
				}
				// Otherwise, present some default options
				else
					$opts_vals = array( 'Option 1', 'Option 2', 'Option 3' );

				// Basic count to keep track of multiple options
				$count = 1;
?>
			<div class="dfm-cloned-options">
			<?php foreach ( $opts_vals as $options ) : ?>
			<div id="clone-<?php echo $field->field_id . '-' . $count; ?>" class="option">
				<label for="edit-form-item-options-<?php echo $field->field_id . "-$count"; ?>" class="clonedOption">
					<input type="radio" value="<?php echo esc_attr( $count ); ?>" name="field_default-<?php echo $field->field_id; ?>" <?php checked( $field->field_default, $count ); ?> />
					<input type="text" value="<?php echo stripslashes( esc_attr( $options ) ); ?>" name="field_options-<?php echo $field->field_id; ?>[]" class="widefat" id="edit-form-item-options-<?php echo $field->field_id . "-$count"; ?>" />
				</label>

				<a href="#" class="deleteOption dfm-interface-icon dfm-interface-minus" title="Delete Option">
					<?php _e( 'Delete', 'dynamic-form-maker' ); ?>
				</a>
				<span class="dfm-interface-icon dfm-interface-sort" title="<?php esc_attr_e( 'Drag and Drop to Sort Options', 'dynamic-form-maker-pro' ); ?>"></span>
			</div>
			<?php
					$count++;
				endforeach;
			?>

			</div> <!-- .dfm-cloned-options -->
			<div class="clear"></div>
			<div class="dfm-add-options-group">
				<a href="#" class="dfm-button dfm-add-option" title="Add Option">
					<?php _e( 'Add Option', 'dynamic-form-maker' ); ?>
					<span class="dfm-interface-icon dfm-interface-plus"></span>
				</a>
			</div>
			</p>
		<?php
			// Unset the options for any following radio, checkboxes, or selects
			unset( $opts_vals );
			endif;
		?>

		<?php if ( in_array( $field->field_type, array( 'file-upload' ) ) ) : ?>
        	<!-- File Upload Accepts -->
			<p class="description description-wide">
                <?php
				$opts_vals = array( '' );

				// If the options field isn't empty, unserialize and build array
				if ( !empty( $field->field_options ) ) {
					if ( is_serialized( $field->field_options ) )
						$opts_vals = ( is_array( unserialize( $field->field_options ) ) ) ? unserialize( $field->field_options ) : unserialize( $field->field_options );
				}

				// Loop through the options
				foreach ( $opts_vals as $options ) {
			?>
				<label for="edit-form-item-options-<?php echo $field->field_id; ?>">
					<?php _e( 'Accepted File Extensions' , 'dynamic-form-maker'); ?>
                    <span class="dfm-tooltip" title="<?php esc_attr_e( 'About Accepted File Extensions', 'dynamic-form-maker' ); ?>" rel="<?php esc_attr_e( 'Control the types of files allowed.  Enter extensions without periods and separate multiples using the pipe character ( | ).', 'dynamic-form-maker' ); ?>">(?)</span>
            		<br />
                    <input type="text" value="<?php echo stripslashes( esc_attr( $options ) ); ?>" name="field_options-<?php echo $field->field_id; ?>[]" class="widefat" id="edit-form-item-options-<?php echo $field->field_id; ?>" />
				</label>
            </p>
        <?php
				}
			// Unset the options for any following radio, checkboxes, or selects
			unset( $opts_vals );
			endif;
		?>

		<?php if ( in_array( $field->field_type, array( 'date' ) ) ) : ?>
	    	<!-- Date Format -->
			<p class="description description-wide">
				<?php
					$opts_vals = maybe_unserialize( $field->field_options );
					$dateFormat = ( isset( $opts_vals['dateFormat'] ) ) ? $opts_vals['dateFormat'] : 'mm/dd/yy';
				?>
				<label for="edit-form-item-date-dateFormat-<?php echo $field->field_id; ?>">
					<?php _e( 'Date Format', 'dynamic-form-maker' ); ?>
					<span class="dfm-tooltip" title="<?php esc_attr_e( 'About Date Format', 'dynamic-form-maker' ); ?>" rel="<?php esc_attr_e( 'Set the date format for each date picker.', 'dynamic-form-maker' ); ?>">(?)</span>
					<br />
					<input type="text" value="<?php echo esc_attr( $dateFormat ); ?>" name="field_options-<?php echo $field->field_id; ?>[dateFormat]" class="widefat" id="edit-form-item-date-dateFormat-<?php echo $field->field_id; ?>" />
				</label>
	        </p>
		<?php
			// Unset the options for any following radio, checkboxes, or selects
			unset( $opts_vals );
			endif;
		?>
		<!-- Validation -->
		<p class="description description-thin">
			<label for="edit-form-item-validation">
				<?php _e( 'Validation' , 'dynamic-form-maker'); ?>
                <span class="dfm-tooltip" title="<?php esc_attr_e( 'About Validation', 'dynamic-form-maker' ); ?>" rel="<?php esc_attr_e( 'Ensures user-entered data is formatted properly. For more information on Validation, refer to the Help tab at the top of this page.', 'dynamic-form-maker' ); ?>">(?)</span>
                <br />

			   <?php if ( in_array( $field->field_type , array( 'text', 'time', 'number' ) ) ) : ?>
				   <select name="field_validation-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-validation-<?php echo $field->field_id; ?>">
				   		<?php if ( $field->field_type == 'time' ) : ?>
						<option value="time-12" <?php selected( $field->field_validation, 'time-12' ); ?>><?php _e( '12 Hour Format' , 'dynamic-form-maker'); ?></option>
						<option value="time-24" <?php selected( $field->field_validation, 'time-24' ); ?>><?php _e( '24 Hour Format' , 'dynamic-form-maker'); ?></option>
						<?php elseif ( in_array( $field->field_type, array( 'number' ) ) ) : ?>
                        <option value="number" <?php selected( $field->field_validation, 'number' ); ?>><?php _e( 'Number' , 'dynamic-form-maker'); ?></option>
						<option value="digits" <?php selected( $field->field_validation, 'digits' ); ?>><?php _e( 'Digits' , 'dynamic-form-maker'); ?></option>
						<?php else : ?>
						<option value="" <?php selected( $field->field_validation, '' ); ?>><?php _e( 'None' , 'dynamic-form-maker'); ?></option>
						<option value="email" <?php selected( $field->field_validation, 'email' ); ?>><?php _e( 'Email' , 'dynamic-form-maker'); ?></option>
						<option value="url" <?php selected( $field->field_validation, 'url' ); ?>><?php _e( 'URL' , 'dynamic-form-maker'); ?></option>
						<option value="date" <?php selected( $field->field_validation, 'date' ); ?>><?php _e( 'Date' , 'dynamic-form-maker'); ?></option>
						<option value="number" <?php selected( $field->field_validation, 'number' ); ?>><?php _e( 'Number' , 'dynamic-form-maker'); ?></option>
						<option value="digits" <?php selected( $field->field_validation, 'digits' ); ?>><?php _e( 'Digits' , 'dynamic-form-maker'); ?></option>
						<option value="phone" <?php selected( $field->field_validation, 'phone' ); ?>><?php _e( 'Phone' , 'dynamic-form-maker'); ?></option>
						<?php endif; ?>
				   </select>
			   <?php else :
				   $field_validation = '';

				   switch ( $field->field_type ) {
					   case 'email' :
						case 'url' :
						case 'phone' :
							$field_validation = $field->field_type;
						break;

						case 'currency' :
							$field_validation = 'number';
						break;

						case 'number' :
							$field_validation = 'digits';
						break;
				   }

			   ?>
			   <input type="text" class="widefat" name="field_validation-<?php echo $field->field_id; ?>" value="<?php echo $field_validation; ?>" readonly="readonly" />
			   <?php endif; ?>

			</label>
		</p>

		<!-- Required -->
		<p class="field-link-target description description-thin">
			<label for="edit-form-item-required">
				<?php _e( 'Required' , 'dynamic-form-maker'); ?>
                <span class="dfm-tooltip" title="<?php esc_attr_e( 'About Required', 'dynamic-form-maker' ); ?>" rel="<?php esc_attr_e( 'Requires the field to be completed before the form is submitted. By default, all fields are set to No.', 'dynamic-form-maker' ); ?>">(?)</span>
                <br />
				<select name="field_required-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-required-<?php echo $field->field_id; ?>">
					<option value="no" <?php selected( $field->field_required, 'no' ); ?>><?php _e( 'No' , 'dynamic-form-maker'); ?></option>
					<option value="yes" <?php selected( $field->field_required, 'yes' ); ?>><?php _e( 'Yes' , 'dynamic-form-maker'); ?></option>
				</select>
			</label>
		</p>

		<?php if ( !in_array( $field->field_type, array( 'radio', 'checkbox', 'time' ) ) ) : ?>
			<!-- Size -->
			<p class="description description-thin">
				<label for="edit-form-item-size">
					<?php _e( 'Size' , 'dynamic-form-maker'); ?>
                    <span class="dfm-tooltip" title="<?php esc_attr_e( 'About Size', 'dynamic-form-maker' ); ?>" rel="<?php esc_attr_e( 'Control the size of the field.  By default, all fields are set to Medium.', 'dynamic-form-maker' ); ?>">(?)</span>
                    <br />
					<select name="field_size-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-size-<?php echo $field->field_id; ?>">
                    	<option value="small" <?php selected( $field->field_size, 'small' ); ?>><?php _e( 'Small' , 'dynamic-form-maker'); ?></option>
						<option value="medium" <?php selected( $field->field_size, 'medium' ); ?>><?php _e( 'Medium' , 'dynamic-form-maker'); ?></option>
						<option value="large" <?php selected( $field->field_size, 'large' ); ?>><?php _e( 'Large' , 'dynamic-form-maker'); ?></option>
					</select>
				</label>
			</p>

		<?php elseif ( in_array( $field->field_type, array( 'radio', 'checkbox', 'time' ) ) ) : ?>
			<!-- Options Layout -->
			<p class="description description-thin">
				<label for="edit-form-item-size">
					<?php _e( 'Options Layout' , 'dynamic-form-maker'); ?>
                    <span class="dfm-tooltip" title="<?php esc_attr_e( 'About Options Layout', 'dynamic-form-maker' ); ?>" rel="<?php esc_attr_e( 'Control the layout of radio buttons or checkboxes.  By default, options are arranged in One Column.', 'dynamic-form-maker' ); ?>">(?)</span>
                    <br />
					<select name="field_size-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-size-<?php echo $field->field_id; ?>"<?php echo ( $field->field_type == 'time' ) ? ' disabled="disabled"' : ''; ?>>
						<option value="" <?php selected( $field->field_size, '' ); ?>><?php _e( 'One Column' , 'dynamic-form-maker'); ?></option>
                        <option value="two-column" <?php selected( $field->field_size, 'two-column' ); ?>><?php _e( 'Two Columns' , 'dynamic-form-maker'); ?></option>
						<option value="three-column" <?php selected( $field->field_size, 'three-column' ); ?>><?php _e( 'Three Columns' , 'dynamic-form-maker'); ?></option>
                        <option value="auto-column" <?php selected( $field->field_size, 'auto-column' ); ?>><?php _e( 'Auto Width' , 'dynamic-form-maker'); ?></option>
					</select>
				</label>
			</p>

		<?php endif; ?>
			<!-- Field Layout -->
			<p class="description description-thin">
				<label for="edit-form-item-layout">
					<?php _e( 'Field Layout' , 'dynamic-form-maker'); ?>
                    <span class="dfm-tooltip" title="<?php esc_attr_e( 'About Field Layout', 'dynamic-form-maker' ); ?>" rel="<?php esc_attr_e( 'Used to create advanced layouts. Align fields side by side in various configurations.', 'dynamic-form-maker' ); ?>">(?)</span>
                    <br />
					<select name="field_layout-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-layout-<?php echo $field->field_id; ?>">

						<option value="" <?php selected( $field->field_layout, '' ); ?>><?php _e( 'Default' , 'dynamic-form-maker'); ?></option>
                        <optgroup label="------------">
                        <option value="left-half" <?php selected( $field->field_layout, 'left-half' ); ?>><?php _e( 'Left Half' , 'dynamic-form-maker'); ?></option>
                        <option value="right-half" <?php selected( $field->field_layout, 'right-half' ); ?>><?php _e( 'Right Half' , 'dynamic-form-maker'); ?></option>
                        </optgroup>
                        <optgroup label="------------">
						<option value="left-third" <?php selected( $field->field_layout, 'left-third' ); ?>><?php _e( 'Left Third' , 'dynamic-form-maker'); ?></option>
                        <option value="middle-third" <?php selected( $field->field_layout, 'middle-third' ); ?>><?php _e( 'Middle Third' , 'dynamic-form-maker'); ?></option>
                        <option value="right-third" <?php selected( $field->field_layout, 'right-third' ); ?>><?php _e( 'Right Third' , 'dynamic-form-maker'); ?></option>
                        </optgroup>
                        <optgroup label="------------">
                        <option value="left-two-thirds" <?php selected( $field->field_layout, 'left-two-thirds' ); ?>><?php _e( 'Left Two Thirds' , 'dynamic-form-maker'); ?></option>
                        <option value="right-two-thirds" <?php selected( $field->field_layout, 'right-two-thirds' ); ?>><?php _e( 'Right Two Thirds' , 'dynamic-form-maker'); ?></option>
                        </optgroup>
					</select>
				</label>
			</p>
		<?php if ( !in_array( $field->field_type, array( 'radio', 'select', 'checkbox', 'time', 'address' ) ) ) : ?>
		<!-- Default Value -->
		<p class="description description-wide">
            <label for="edit-form-item-default-<?php echo $field->field_id; ?>">
                <?php _e( 'Default Value' , 'dynamic-form-maker'); ?>
                <span class="dfm-tooltip" title="<?php esc_attr_e( 'About Default Value', 'dynamic-form-maker' ); ?>" rel="<?php esc_attr_e( 'Set a default value that will be inserted automatically.', 'dynamic-form-maker' ); ?>">(?)</span>
            	<br />
                <input type="text" value="<?php echo stripslashes( esc_attr( $field->field_default ) ); ?>" name="field_default-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-default-<?php echo $field->field_id; ?>" maxlength="255" />
            </label>
		</p>
		<?php elseif( in_array( $field->field_type, array( 'address' ) ) ) : ?>
		<!-- Default Country -->
		<p class="description description-wide">
            <label for="edit-form-item-default-<?php echo $field->field_id; ?>">
                <?php _e( 'Default Country' , 'dynamic-form-maker'); ?>
                <span class="dfm-tooltip" title="<?php esc_attr_e( 'About Default Country', 'dynamic-form-maker' ); ?>" rel="<?php esc_attr_e( 'Select the country you would like to be displayed by default.', 'dynamic-form-maker' ); ?>">(?)</span>
            	<br />
                <select name="field_default-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-default-<?php echo $field->field_id; ?>">
                <?php
                foreach ( $this->countries as $country ) {
					echo '<option value="' . $country . '" ' . selected( $field->field_default, $country, 0 ) . '>' . $country . '</option>';
				}
				?>
				</select>
            </label>
		</p>
		<?php endif; ?>
		<!-- CSS Classes -->
		<p class="description description-wide">
            <label for="edit-form-item-css-<?php echo $field->field_id; ?>">
                <?php _e( 'CSS Classes' , 'dynamic-form-maker'); ?>
                <span class="dfm-tooltip" title="<?php esc_attr_e( 'About CSS Classes', 'dynamic-form-maker' ); ?>" rel="<?php esc_attr_e( 'For each field, you can insert your own CSS class names which can be used in your own stylesheets.', 'dynamic-form-maker' ); ?>">(?)</span>
                <br />
                <input type="text" value="<?php echo stripslashes( esc_attr( $field->field_css ) ); ?>" name="field_css-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-css-<?php echo $field->field_id; ?>" maxlength="255" />
            </label>
		</p>

	<?php endif; ?>
<?php endif; ?>

<?php if ( !in_array( $field->field_type, array( 'verification', 'secret', 'submit' ) ) ) : ?>
		<!-- Delete link -->
		<a href="<?php echo esc_url( wp_nonce_url( admin_url('admin.php?page=dynamic-form-maker&amp;action=delete_field&amp;form=' . $form_nav_selected_id . '&amp;field=' . $field->field_id ), 'delete-field-' . $form_nav_selected_id ) ); ?>" class="dfm-button dfm-delete item-delete submitdelete deletion">
			<?php _e( 'Delete' , 'dynamic-form-maker'); ?>
			<span class="dfm-interface-icon dfm-interface-trash"></span>
		</a>
<?php endif; ?>

<input type="hidden" name="field_id[<?php echo $field->field_id; ?>]" value="<?php echo $field->field_id; ?>" />
</div>
<?php
endforeach;

// This assures all of the <ul> and <li> are closed
if ( $depth > 1 ) {
	while( $depth > 1 ) {
		echo '</li>
			</ul>';
		$depth--;
	}
}

// Close out last item
echo '</li>';
echo ob_get_clean();
