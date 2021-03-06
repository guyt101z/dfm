<?php
/**
 * Class that builds our Form Records detail page
 *
 * @since 1.4
 */
class DynamicFormMaker_Form_Records_Detail{
	
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
	
	public function __construct(){
		global $wpdb;

		// Setup global database table names
		$this->field_table_name 	= $wpdb->prefix . 'dynamic_form_maker_fields';
		$this->form_table_name 		= $wpdb->prefix . 'dynamic_form_maker_forms';
		$this->records_table_name 	= $wpdb->prefix . 'dynamic_form_maker_records';

		add_action( 'admin_init', array( &$this, 'records_detail' ) );
	}

	public function records_detail(){
		global $wpdb;

		$entry_id = absint( $_REQUEST['entry'] );

		$records = $wpdb->get_results( $wpdb->prepare( "SELECT forms.form_title, records.* FROM $this->form_table_name AS forms INNER JOIN $this->records_table_name AS records ON records.form_id = forms.form_id WHERE records.records_id  = %d", $entry_id ) );

		echo '<p>' . sprintf( '<a href="?page=%s" class="view-entry">&laquo; Back to Form Records</a>', $_REQUEST['page'] ) . '</p>';

		// Get the date/time format that is saved in the options table
		$date_format = get_option('date_format');
		$time_format = get_option('time_format');

		// Loop trough the records and setup the data to be displayed for each row
		foreach ( $records as $entry ) {
			$data = unserialize( $entry->data );
?>
			<form id="entry-edit" method="post" action="">
			<h3><span><?php echo stripslashes( $entry->form_title ); ?> : <?php echo __( 'Entry' , 'dynamic-form-maker'); ?> # <?php echo $entry->records_id; ?></span></h3>
            <div id="dfm-poststuff" class="metabox-holder has-right-sidebar">
				<div id="side-info-column" class="inner-sidebar">
					<div id="side-sortables">
						<div id="submitdiv" class="postbox">
							<h3><span><?php echo __( 'Details' , 'dynamic-form-maker'); ?></span></h3>
							<div class="inside">
							<div id="submitbox" class="submitbox">
								<div id="minor-publishing">
									<div id="misc-publishing-actions">
										<div class="misc-pub-section">
											<span><strong><?php echo  __( 'Form Title' , 'dynamic-form-maker'); ?>: </strong><?php echo stripslashes( $entry->form_title ); ?></span>
										</div>
										<div class="misc-pub-section">
											<span><strong><?php echo  __( 'Date Submitted' , 'dynamic-form-maker'); ?>: </strong><?php echo date( "$date_format $time_format", strtotime( $entry->date_submitted ) ); ?></span>
										</div>
										<div class="misc-pub-section">
											<span><strong><?php echo __( 'IP Address' , 'dynamic-form-maker'); ?>: </strong><?php echo $entry->ip_address; ?></span>
										</div>
										<div class="misc-pub-section">
											<span><strong><?php echo __( 'Email Subject' , 'dynamic-form-maker'); ?>: </strong><?php echo stripslashes( $entry->subject ); ?></span>
										</div>
										<div class="misc-pub-section">
											<span><strong><?php echo __( 'Sender Name' , 'dynamic-form-maker'); ?>: </strong><?php echo stripslashes( $entry->sender_name ); ?></span>
										</div>
										<div class="misc-pub-section">
											<span><strong><?php echo __( 'Sender Email' , 'dynamic-form-maker'); ?>: </strong><a href="mailto:<?php echo stripslashes( $entry->sender_email ); ?>"><?php echo stripslashes( $entry->sender_email ); ?></a></span>
										</div>
										<div class="misc-pub-section">
											<span><strong><?php echo __( 'Emailed To' , 'dynamic-form-maker'); ?>: </strong><?php echo preg_replace('/\b([A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4})\b/i', '<a href="mailto:$1">$1</a>', implode( ',', unserialize( stripslashes( $entry->emails_to ) ) ) ); ?></span>
										</div>
										<div class="clear"></div>
									</div> <!--#misc-publishing-actions -->
								</div> <!-- #minor-publishing -->

								<div id="major-publishing-actions">
									<div id="delete-action">
										<?php echo sprintf( '<a class="submitdelete deletion entry-delete" href="?page=%2$s&action=%3$s&entry=%4$d">%1$s</a>', __( 'Move to Trash', 'dynamic-form-maker' ), $_REQUEST['page'], 'trash', $entry_id ); ?>
									</div>
									<div id="publishing-action">
										<?php submit_button( __( 'Print', 'dynamic-form-maker' ), 'secondary', 'submit', false, array( 'onclick' => 'window.print();return false;' ) ); ?>
									</div>
									<div class="clear"></div>
								</div> <!-- #major-publishing-actions -->
							</div> <!-- #submitbox -->
							</div> <!-- .inside -->
						</div> <!-- #submitdiv -->
					</div> <!-- #side-sortables -->
				</div> <!-- #side-info-column -->
            <!--</div>  #poststuff -->
			<div id="dfm-records-body-content">
        <?php
			$count = 0;
			$open_fieldset = $open_section = false;

			foreach ( $data as $k => $v ) :
				if ( !is_array( $v ) ) :
					if ( $count == 0 ) {
						echo '<div class="postbox">
							<h3><span>' . $entry->form_title . ' : ' . __( 'Entry' , 'dynamic-form-maker') .' #' . $entry->records_id . '</span></h3>
							<div class="inside">';
					}

					echo '<h4>' . ucwords( $k ) . '</h4>';
					echo $v;
					$count++;
				else :
					// Cast each array as an object
					$obj = (object) $v;

					if ( $obj->type == 'fieldset' ) :
						// Close each fieldset
						if ( $open_fieldset == true )
							echo '</table>';

						echo '<h3>' . stripslashes( $obj->name ) . '</h3><table class="form-table">';

						$open_fieldset = true;
					endif;


					switch ( $obj->type ) :
						case 'fieldset' :
						case 'section' :
						case 'submit' :
						case 'page-break' :
						case 'verification' :
						case 'secret' :
							break;

						case 'file-upload' :
							?>
							<tr valign="top">
								<th scope="row"><label for="field[<?php echo $obj->id; ?>]"><?php echo stripslashes( $obj->name ); ?></label></th>
								<td style="background:#eee;border:1px solid #ddd"><a href="<?php esc_attr_e( $obj->value ); ?>" target="_blank"><?php echo stripslashes( esc_html( $obj->value ) ); ?></a></td>
							</tr>
	                    	<?php
							break;

						case 'textarea' :
						case 'html' :
							?>
							<tr valign="top">
								<th scope="row"><label for="field[<?php echo $obj->id; ?>]"><?php echo stripslashes( $obj->name ); ?></label></th>
								<td style="background:#eee;border:1px solid #ddd"><?php echo wpautop( stripslashes( wp_specialchars_decode( esc_html( $obj->value ) ) ) ); ?></td>
							</tr>
	                    	<?php
							break;

						default :
							?>
							<tr valign="top">
								<th scope="row"><label for="field[<?php echo $obj->id; ?>]"><?php echo stripslashes( $obj->name ); ?></label></th>
								<td style="background:#eee;border:1px solid #ddd"><?php echo stripslashes( wp_specialchars_decode( esc_html( $obj->value ) ) ); ?></td>
							</tr>
                        	<?php
							break;

					endswitch;
				endif;
			endforeach;

			if ( $count > 0 )
				echo '</div></div>';

		}
		echo '</table></div>';
		echo '<br class="clear"></div>';


		echo '</form>';
	}
}
