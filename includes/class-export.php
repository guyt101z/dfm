<?php
/**
 * Class that builds our Form Records table
 *
 * @since 1.0
 */
class DinamicFormMaker_Export {

	/**
	 * field_table_name
	 *
	 * @var mixed
	 * @access public
	 */
	public $field_table_name;

	/**
	 * form_table_name
	 *
	 * @var mixed
	 * @access public
	 */
	public $form_table_name;

	/**
	 * records_table_name
	 *
	 * @var mixed
	 * @access public
	 */
	public $records_table_name;

	/**
	 * delimiter
	 *
	 * @var mixed
	 * @access public
	 */
	public $delimiter;

	/**
	 * default_cols
	 *
	 * @var mixed
	 * @access public
	 */
	public $default_cols;
	
	public function __construct(){
		global $wpdb;

		// CSV delimiter
		$this->delimiter = apply_filters( 'dfm_csv_delimiter', ',' );

		// Setup our default columns
		$this->default_cols = array(
			'records_id' 		=> __( 'Form Records ID' , 'dynamic-form-maker'),
			'date_submitted' 	=> __( 'Date Submitted' , 'dynamic-form-maker'),
			'ip_address' 		=> __( 'IP Address' , 'dynamic-form-maker'),
			'subject' 			=> __( 'Subject' , 'dynamic-form-maker'),
			'sender_name' 		=> __( 'Sender Name' , 'dynamic-form-maker'),
			'sender_email' 		=> __( 'Sender Email' , 'dynamic-form-maker'),
			'emails_to' 		=> __( 'Emailed To' , 'dynamic-form-maker'),
		);

		// Setup global database table names
		$this->field_table_name 	= $wpdb->prefix . 'dynamic_form_maker_fields';
		$this->form_table_name 		= $wpdb->prefix . 'dynamic_form_maker_forms';
		$this->records_table_name 	= $wpdb->prefix . 'dynamic_form_maker_records';

		// AJAX for loading new entry checkboxes
		add_action( 'wp_ajax_dynamic_form_maker_export_load_options', array( &$this, 'ajax_load_options' ) );

		// AJAX for getting records count
		add_action( 'wp_ajax_dynamic_form_maker_export_records_count', array( &$this, 'ajax_records_count' ) );

		$this->process_export_action();
	}

	/**
	 * Display the export form
	 *
	 * @since 1.0
	 *
	 */
	public function display_export(){
		global $wpdb;

		// Query to get all forms
		$order = sanitize_sql_orderby( 'form_id ASC' );
		$where = apply_filters( 'dfm_pre_get_forms_export', '' );
		$forms = $wpdb->get_results( "SELECT form_id, form_key, form_title FROM $this->form_table_name WHERE 1=1 $where ORDER BY $order" );

		if ( !$forms ) :
			echo sprintf(
				'<div class="dfm-form-alpha-list"><h3 id="dfm-no-forms">You currently do not have any forms.  Click on the <a href="%1$s">New Form</a> button to get started.</h3></div>',
				esc_url( admin_url( 'admin.php?page=dfm-add-new' ) )
			);

			return;
		endif;

		$records_count = $this->count_records( $forms[0]->form_id );

		// Return nothing if no records found
		if ( !$records_count ) :
			$no_records = __( 'No records to pull field names from.', 'dynamic-form-maker' );
		else :

			$limit = $records_count > 1000 ? 1000 : $records_count;

			// Safe to get records now
			$records = $wpdb->get_results( $wpdb->prepare( "SELECT data FROM $this->records_table_name WHERE form_id = %d AND entry_approved = 1 LIMIT %d", $forms[0]->form_id, $limit ), ARRAY_A );

			// Get columns
			$columns = $this->get_cols( $records );

			// Get JSON data
			$data = json_decode( $columns, true );
		endif;

		?>
        <form method="post" id="dfm-export">       	

        	<ul id="records-filters" class="dfm-export-filters">
				
				<li>
					<p><?php _e( 'Backup and save some or all of your Dynamic Form Maker data.', 'dynamic-form-maker' ); ?></p>
					<p><?php //_e( 'Once you have saved the file, you will be able to import Dynamic Form Maker Pro data from this site into another site.', 'dynamic-form-maker' ); ?></p>
        		<li>
        			<label class="dfm-export-label" for="dfm-content"><?php _e( 'Choose to export', 'dynamic-form-maker' ); ?>:</label>
        			<select name="dfm-content"> 
						<!--<option value="all" disabled="disabled"><?php //_e( 'All data - Pro only', 'dynamic-form-maker' ); ?></option>
						<option value="forms" disabled="disabled"><?php //_e( 'Forms - Pro only', 'dynamic-form-maker' ); ?></option>-->        				
        				<option value="records"  selected="selected"><?php _e( 'Form Records', 'dynamic-form-maker' ); ?></option>												
        			</select>
        		</li>
			
        		<li><p class="description"><?php _e( 'This will export records in either a .txt.', 'dynamic-form-maker' ); ?></p></li>
        		<!-- Format -->
        		<li>
        			<label class="dfm-export-label" for="format"><?php _e( 'Format', 'dynamic-form-maker' ); ?>:</label>
        			<select name="format">        				
        				<option value="txt"  selected="selected"><?php _e( 'Tab Delimited (.txt)', 'dynamic-form-maker' ); ?></option>
						<!--<option value="csv" disabled="disabled"><?php //_e( 'Comma Separated (.csv) - Pro only', 'dynamic-form-maker' ); ?></option>
        				<option value="xls" disabled="disabled"><?php //_e( 'Excel (.xls) - Pro only', 'dynamic-form-maker' ); ?></option>-->						
        			</select>
        		</li>
        		<!-- Forms -->
        		<li>
		        	<label class="dfm-export-label" for="form_id"><?php _e( 'Form', 'dynamic-form-maker' ); ?>:</label>
		            <select id="dfm-export-records-forms" name="records_form_id">
<?php
						foreach ( $forms as $form ) :
							echo sprintf(
								'<option value="%1$d" id="%2$s">%1$d - %3$s</option>',
								$form->form_id,
								$form->form_key,
								stripslashes( $form->form_title )
							);
						endforeach;
?>
					</select>
        		</li>
        		<!-- Date Range -->
        		<li>
        			<label class="dfm-export-label"><?php _e( 'Date Range', 'dynamic-form-maker' ); ?>:</label>
        			<select name="records_start_date">
        				<option value="0">Start Date</option>
        				<?php $this->months_dropdown(); ?>
        			</select>
        			<select name="records_end_date">
        				<option value="0">End Date</option>
        				<?php $this->months_dropdown(); ?>
        			</select>
        		</li>
        		<!-- Pages to Export -->
				<?php $num_pages = ceil( $records_count / 1000 ); ?>
				<li id="dfm-export-records-pages" style="display:<?php echo ( $records_count > 1000 ) ? 'list-item' : 'none'; ?>">
					<label class="dfm-export-label"><?php _e( 'Page to Export', 'dynamic-form-maker' ); ?>:</label>
					<select id="dfm-export-records-rows" name="records_page">
<?php
					for ( $i = 1; $i <= $num_pages; $i++ ) {
						echo sprintf( '<option value="%1$d">%1$s</option>', $i );
					}
?>
					</select>
					<p class="description"><?php _e( 'A large number of records have been detected for this form. Only 1000 records can be exported at a time.', 'dynamic-form-maker' ); ?></p>
				</li>
				<!-- Fields -->
        		<li>
        			<label class="dfm-export-label"><?php _e( 'Fields', 'dynamic-form-maker' ); ?>:</label>

        			<p>
        				<a id="dfm-export-select-all" href="#"><?php _e( 'Select All', 'dynamic-form-maker' ); ?></a>
        				<a id="dfm-export-unselect-all" href="#"><?php _e( 'Unselect All', 'dynamic-form-maker' ); ?></a>
        			</p>

        			<div id="dfm-export-records-fields">
	        		<?php
						if ( isset( $no_records ) )
							echo $no_records;
						else
							echo $this->build_options( $data );
					 ?>
        			</div>
        		</li>
        	</ul>

         <?php submit_button( __( 'Download Export File', 'dynamic-form-maker' ) ); ?>
        </form>
<?php
	}


	/**
	 * Build the records export array
	 *
	 * @since 1.0
	 *
	 * @param array $args Filters defining what should be included in the export
	 */
	public function export_records( $args = array() ) {
		global $wpdb;

		// Set inital fields as a string
		$initial_fields = implode( ',', $this->default_cols );

		$defaults = array(
			'content' 		=> 'records',
			'format' 		=> 'txt',
			'form_id' 		=> 0,
			'start_date' 	=> false,
			'end_date' 		=> false,
			'page'			=> 0,
			'fields'		=> $initial_fields,
		);

		$args = wp_parse_args( $args, $defaults );

		$where = '';

		$limit = '0,1000';

		if ( 'records' == $args['content'] ) {
			if ( 0 !== $args['form_id'] )
				$where .= $wpdb->prepare( " AND form_id = %d", $args['form_id'] );

			if ( $args['start_date'] )
				$where .= $wpdb->prepare( " AND date_submitted >= %s", date( 'Y-m-d', strtotime( $args['start_date'] ) ) );

			if ( $args['end_date'] )
				$where .= $wpdb->prepare( " AND date_submitted < %s", date( 'Y-m-d', strtotime( '+1 month', strtotime( $args['end_date'] ) ) ) );

			if ( $args['page'] > 1 )
				$limit = ( $args['page'] - 1 ) * 1000 . ',1000';
		}

		$form_id = ( 0 !== $args['form_id'] ) ? $args['form_id'] : null;

		$records = $wpdb->get_results( "SELECT * FROM $this->records_table_name WHERE entry_approved = 1 $where ORDER BY records_id ASC LIMIT $limit" );
		$form_key = $wpdb->get_var( $wpdb->prepare( "SELECT form_key, form_title FROM $this->form_table_name WHERE form_id = %d", $args['form_id'] ) );
		$form_title = $wpdb->get_var( null, 1 );

		$sitename = sanitize_key( get_bloginfo( 'name' ) );
		if ( ! empty($sitename) ) $sitename .= '.';
		$filename = $sitename . 'dfm.' . "$form_key." . date( 'Y-m-d' ) . ".{$args['format']}";

		$content_type = 'text/csv';

		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( "Content-Type: $content_type; charset=" . get_option( 'blog_charset' ), true );
		header( 'Expires: 0' );
		header( 'Pragma: public' );

		// Get columns
		$columns = $this->get_cols( $records );

		// Get JSON data
		$data = json_decode( $columns, true );

		// Build array of fields to display
		$fields = !is_array( $args['fields'] ) ? array_map( 'trim', explode( ',', $args['fields'] ) ) : $args['fields'];

		// Strip slashes from header values
		$fields = array_map( 'stripslashes', $fields );

		// Build CSV
		$this->csv( $data, $fields );
	}

	/**
	 * Build the records as JSON
	 *
	 * @since 1.0
	 *
	 * @param array $records The resulting database query for records
	 */
	public function get_cols( $records ) {

		// Initialize row index at 0
		$row = 0;
		$output = array();

		// Loop through all records
		foreach ( $records as $entry ) :

			foreach ( $entry as $key => $value ) :

				switch ( $key ) {
					case 'records_id':
					case 'date_submitted':
					case 'ip_address':
					case 'subject':
					case 'sender_name':
					case 'sender_email':
						$output[ $row ][ stripslashes( $this->default_cols[ $key ] ) ] = $value;
					break;

					case 'emails_to':
						$output[ $row ][ stripslashes( $this->default_cols[ $key ] ) ] = implode( ',', maybe_unserialize( $value ) );
					break;

					case 'data':
						// Unserialize value only if it was serialized
						$fields = maybe_unserialize( $value );

						// Make sure there are no errors with unserializing before proceeding
						if ( is_array( $fields ) ) {
							// Loop through our submitted data
							foreach ( $fields as $field_key => $field_value ) :
								// Cast each array as an object
								$obj = (object) $field_value;

								// Decode the values so HTML tags can be stripped
								$val = wp_specialchars_decode( $obj->value, ENT_QUOTES );

								switch ( $obj->type ) {
									case 'fieldset' :
									case 'section' :
									case 'instructions' :
									case 'page-break' :
									case 'verification' :
									case 'secret' :
									case 'submit' :
										break;

									case 'address' :

										$val = str_replace( array( '<p>', '</p>', '<br>' ), array( '', "\n", "\n" ), $val );

										$output[ $row ][ stripslashes( $obj->name ) . "{{{$obj->id}}}" ] =  $val;

										break;

									case 'html' :

										$output[ $row ][ stripslashes( $obj->name ) . "{{{$obj->id}}}" ] =  $val;

										break;

									default :

										$val = wp_strip_all_tags( $val );
										$output[ $row ][ stripslashes( $obj->name ) . "{{{$obj->id}}}" ] =  $val;

										break;
								} //end $obj switch
							endforeach; // end $fields loop
						}
					break;
				} //end $key switch
			endforeach; // end $entry loop
			$row++;
		endforeach; //end $records loop

		return json_encode( $output );
	}

	public function count_records( $form_id ) {
		global $wpdb;

		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $this->records_table_name WHERE form_id = %d", $form_id ) );

		if ( !$count )
			return 0;

		return $count;
	}

	public function get_form_IDs( $form_id = null ) {
		global $wpdb;

		$where = '';

		if ( $form_id )
			$where .= $wpdb->prepare( " AND form_id = %d", $form_id );

		$form_ids = $wpdb->get_col( "SELECT DISTINCT form_id FROM $this->form_table_name WHERE 1=1 $where" );

		if ( !$form_ids )
			return;

		return $form_ids;
	}

	public function get_field_IDs( $form_id = null ) {
		global $wpdb;

		$where = '';

		if ( $form_id )
			$where .= $wpdb->prepare( " AND form_id = %d", $form_id );

		$field_ids = $wpdb->get_col( "SELECT DISTINCT field_id FROM $this->field_table_name WHERE 1=1 $where" );

		if ( !$field_ids )
			return;

		return $field_ids;
	}

	public function get_entry_IDs( $form_id = null ) {
		global $wpdb;

		$where = '';

		if ( $form_id ) :
			$where .= $wpdb->prepare( " AND form_id = %d", $form_id );

			$count = $this->count_records( $form_id );
			$where .= " LIMIT $count";
		endif;



		$entry_ids = $wpdb->get_col( "SELECT DISTINCT records_id FROM $this->records_table_name WHERE entry_approved = 1 $where" );

		if ( !$entry_ids )
			return;

		return $entry_ids;
	}

	public function get_form_design_IDs( $form_id = null ) {
		global $wpdb;

		$where = '';

		if ( $form_id )
			$where .= $wpdb->prepare( " AND form_id = %d", $form_id );

		$design_ids = $wpdb->get_col( "SELECT DISTINCT design_id FROM {$this->design_table_name} WHERE 1=1 $where" );

		if ( !$design_ids )
			return;

		return $design_ids;
	}

	public function get_payments_IDs( $form_id = null ) {
		global $wpdb;

		$where = '';

		if ( $form_id )
			$where .= $wpdb->prepare( " AND form_id = %d", $form_id );

		$payments_ids = $wpdb->get_col( "SELECT DISTINCT payment_id FROM {$this->payment_table_name} WHERE 1=1 $where" );

		if ( !$payments_ids )
			return;

		return $payments_ids;
	}

	/**
	 * Return the records data formatted for txt
	 *
	 * @since 1.0
	 *
	 * @param array $data The multidimensional array of records data
	 * @param array $fields The selected fields to export
	 */
	public function csv( $data, $fields ) {
		// Open file with PHP wrapper
		$fh = @fopen( 'php://output', 'w' );

		$rows = $fields_clean = $fields_header = array();

		// Decode special characters
		foreach ( $fields as $field ) :
			// Strip unique ID for a clean header
			$search = preg_replace( '/{{(\d+)}}/', '', $field );
			$fields_header[] = wp_specialchars_decode( $search, ENT_QUOTES );

			// Field with unique ID to use as matching data
			$fields_clean[] = wp_specialchars_decode( $field, ENT_QUOTES );
		endforeach;

		// Build headers
		fputcsv( $fh, $fields_header, $this->delimiter );

		// Build table rows and cells
		foreach ( $data as $row ) :

			foreach ( $fields_clean as $label ) {
				$label = wp_specialchars_decode( $label );
				$rows[ $label ] =  ( isset( $row[ $label ] ) && in_array( $label, $fields_clean ) ) ? $row[ $label ] : '';
			}

			fputcsv( $fh, $rows, $this->delimiter );

		endforeach;

		// Close the file
		fclose( $fh );

		exit();
	}

	/**
	 * Build the checkboxes when changing forms
	 *
	 * @since 1.0
	 *
	 * @return string Either no records or the entry headers
	 */
	public function ajax_load_options() {
		global $wpdb, $export_records;

		if ( !isset( $_REQUEST['action'] ) )
			return;

		if ( $_REQUEST['action'] !== 'dynamic_form_maker_export_load_options' )
			return;

		$form_id = absint( $_REQUEST['id'] );

		// Safe to get records now
		$entry_ids = $this->get_entry_IDs( $form_id );

		// Return nothing if no records found
		if ( !$entry_ids ) {
			echo __( 'No records to pull field names from.', 'dynamic-form-maker-pro' );
			wp_die();
		}

		$offset = '';
		$limit = 1000;

		if ( isset( $_REQUEST['count'] ) )
			$limit = ( $_REQUEST['count'] < 1000 ) ? absint( $_REQUEST['count'] ) : 1000;
		elseif ( isset( $_REQUEST['offset'] ) ) {
			// Reset offset/page to a zero index
			$offset = absint( $_REQUEST['offset'] ) - 1;

			// Calculate the offset
			$offset_num = $offset * 1000;

			// If page is 2 or greater, set the offset (page 2 is equal to offset 1 because of zero index)
			$offset = $offset >= 1 ? "OFFSET $offset_num" : '';
		}

		$records = $wpdb->get_results( "SELECT data FROM {$this->records_table_name} WHERE form_id = $form_id AND entry_approved = 1 LIMIT $limit $offset", ARRAY_A );

		// Get columns
		$columns = $export_records->get_cols( $records );

		// Get JSON data
		$data = json_decode( $columns, true );

		echo $this->build_options( $data );

		wp_die();
	}

	public function ajax_records_count() {
		global $wpdb, $export_records;

		if ( !isset( $_REQUEST['action'] ) )
			return;

		if ( $_REQUEST['action'] !== 'dynamic_form_maker_export_records_count' )
			return;

		$form_id = absint( $_REQUEST['id'] );

		echo $export_records->count_records( $form_id );

		wp_die();
	}

	public function build_options( $data ) {

		$output = '';

		$array = array();
		foreach ( $data as $row ) :
			$array = array_merge( $row, $array );
		endforeach;

		$array = array_keys( $array );
		$array = array_values( array_merge( $this->default_cols, $array ) );
		$array = array_map( 'stripslashes', $array );

		foreach ( $array as $k => $v ) :
			$selected = ( in_array( $v, $this->default_cols ) ) ? ' checked="checked"' : '';

			// Strip unique ID for a clean list
			$search = preg_replace( '/{{(\d+)}}/', '', $v );

			$output .= sprintf( '<label for="dfm-display-records-val-%1$d"><input name="records_columns[]" class="dfm-display-records-vals" id="dfm-display-records-val-%1$d" type="checkbox" value="%4$s" %3$s> %2$s</label><br>', $k, $search, $selected, esc_attr( $v ) );
		endforeach;

		return $output;
	}

	/**
	 * Return the selected export type
	 *
	 * @since 1.7
	 *
	 * @return string|bool The type of export
	 */
	public function export_action() {
		if ( isset( $_REQUEST['dfm-content'] ) )
			return $_REQUEST['dfm-content'];

		return false;
	}

	/**
	 * Determine which export process to run
	 *
	 * @since 1.7
	 *
	 */
	public function process_export_action() {

		$args = array();

		if ( !isset( $_REQUEST['dfm-content'] ) || 'records' == $_REQUEST['dfm-content'] ) {
			$args['content'] = 'records';

			$args['format'] = @$_REQUEST['format'];

			if ( isset( $_REQUEST['records_form_id'] ) )
				$args['form_id'] = (int) $_REQUEST['records_form_id'];

			if ( isset( $_REQUEST['records_start_date'] ) || isset( $_REQUEST['records_end_date'] ) ) {
				$args['start_date'] = $_REQUEST['records_start_date'];
				$args['end_date'] = $_REQUEST['records_end_date'];
			}

			if ( isset( $_REQUEST['records_columns'] ) )
				$args['fields'] = array_map( 'esc_html',  $_REQUEST['records_columns'] );

			if ( isset( $_REQUEST['records_page'] ) )
				$args['page'] = absint( $_REQUEST['records_page'] );
		}

		switch( $this->export_action() ) {
			case 'records' :
				$this->export_records( $args );
				die(1);
			break;
		}
	}

	/**
	 * Display Year/Month filter
	 *
	 * @since 1.7
	 */
	public function months_dropdown() {
		global $wpdb, $wp_locale;

		$where = apply_filters( 'dfm_pre_get_records', '' );

	    $months = $wpdb->get_results( "
			SELECT DISTINCT YEAR( forms.date_submitted ) AS year, MONTH( forms.date_submitted ) AS month
			FROM $this->records_table_name AS forms
			WHERE 1=1 $where
			ORDER BY forms.date_submitted DESC
		" );

		$month_count = count( $months );

		if ( !$month_count || ( 1 == $month_count && 0 == $months[0]->month ) )
			return;

		$m = isset( $_REQUEST['m'] ) ? (int) $_REQUEST['m'] : 0;

		foreach ( $months as $arc_row ) :
			if ( 0 == $arc_row->year )
				continue;

			$month = zeroise( $arc_row->month, 2 );
			$year = $arc_row->year;

			printf( "<option value='%s'>%s</option>\n",
				esc_attr( $arc_row->year . '-' . $month ),
				sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $month ), $year )
			);
		endforeach;

	}
}
