<?php
/*
Plugin Name: Dynamic Form Maker
Description: Dynamic Form Maker has a easy interface. It has jQuery validation,entry tracking system and basic logical verification system.
Author: Jakir Hossain
Author URI: http://jakir.net
Version: 1.0.0
*/

// Version number to output as meta tag
define( 'VFB_VERSION', '1.0.0' );

/*
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; version 2 of the License.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Instantiate new class
$dynamic_form_maker = new Dynamic_form_maker_Builder();

// Dynamic Form Maker class
class Dynamic_form_maker_Builder{

	/**
	 * The DB version. Used for SQL install and upgrades.
	 *
	 * Should only be changed when needing to change SQL
	 * structure or custom capabilities.
	 *
	 * @since 1.0
	 * @var string
	 * @access protected
	 */
	protected $dfm_db_version = '1.0';

	/**
	 * Flag used to add scripts to front-end only once
	 *
	 * @since 1.0
	 * @var string
	 * @access protected
	 */
	protected $add_scripts = false;

	/**
	 * An array of countries to be used throughout plugin
	 *
	 * @since 1.0
	 * @var array
	 * @access public
	 */
	public $countries = array( "", "Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua and Barbuda", "Argentina", "Armenia", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Central African Republic", "Chad", "Chile", "China", "Colombi", "Comoros", "Congo (Brazzaville)", "Congo", "Costa Rica", "Cote d\'Ivoire", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor (Timor Timur)", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Fiji", "Finland", "France", "Gabon", "Gambia, The", "Georgia", "Germany", "Ghana", "Greece", "Grenada", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Honduras", "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, North", "Korea, South", "Kuwait", "Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Macedonia", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania", "Mauritius", "Mexico", "Micronesia", "Moldova", "Monaco", "Mongolia", "Montenegro", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepa", "Netherlands", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal", "Qatar", "Romania", "Russia", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "Spain", "Sri Lanka", "Sudan", "Suriname", "Swaziland", "Sweden", "Switzerland", "Syria", "Taiwan", "Tajikistan", "Tanzania", "Thailand", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States of America", "Uruguay", "Uzbekistan", "Vanuatu", "Vatican City", "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe" );

	/**
	 * Admin page menu hooks
	 *
	 * @since 2.7.2
	 * @var array
	 * @access private
	 */
	private $_admin_pages = array();

	/**
	 * Flag used to display post_max_vars error when saving
	 *
	 * @since 2.7.6
	 * @var string
	 * @access protected
	 */
	protected $post_max_vars = false;

	/**
	 * Constructor. Register core filters and actions.
	 *
	 * @access public
	 */
	public function __construct(){
		global $wpdb;

		// Setup global database table names
		$this->field_table_name 	= $wpdb->prefix . 'dynamic_form_maker_fields';
		$this->form_table_name 		= $wpdb->prefix . 'dynamic_form_maker_forms';
		$this->entries_table_name 	= $wpdb->prefix . 'dynamic_form_maker_entries';

		// Add suffix to load dev files
		$this->load_dev_files = ( defined( 'VFB_SCRIPT_DEBUG' ) && VFB_SCRIPT_DEBUG ) ? '' : '.min';

		// Saving functions
		add_action( 'admin_init', array( &$this, 'save_add_new_form' ) );
		add_action( 'admin_init', array( &$this, 'save_update_form' ) );
		add_action( 'admin_init', array( &$this, 'save_trash_delete_form' ) );
		add_action( 'admin_init', array( &$this, 'save_copy_form' ) );
		add_action( 'admin_init', array( &$this, 'save_settings' ) );

		// Build options and settings pages.
		add_action( 'admin_menu', array( &$this, 'add_admin' ) );
		add_action( 'admin_menu', array( &$this, 'additional_plugin_setup' ) );

		// Register AJAX functions
		$actions = array(
			// Form Builder
			'sort_field',
			'create_field',
			'delete_field',
			'form_settings',

			// Media button
			'media_button',
		);

		// Add all AJAX functions
		foreach( $actions as $name ) {
			add_action( "wp_ajax_dynamic_form_maker_$name", array( &$this, "ajax_$name" ) );
		}

		// Adds additional media button to insert form shortcode
		add_action( 'media_buttons', array( &$this, 'add_media_button' ), 999 );

		// Adds a Dashboard widget
		add_action( 'wp_dashboard_setup', array( &$this, 'add_dashboard_widget' ) );

		// Adds a Settings link to the Plugins page
		add_filter( 'plugin_action_links', array( &$this, 'plugin_action_links' ), 10, 2 );

		// Check the db version and run SQL install, if needed
		add_action( 'plugins_loaded', array( &$this, 'update_db_check' ) );

		// Display update messages
		add_action( 'admin_notices', array( &$this, 'admin_notices' ) );

		// Load i18n
		add_action( 'plugins_loaded', array( &$this, 'languages' ) );

		// Print meta keyword
		add_action( 'wp_head', array( &$this, 'add_meta_keyword' ) );

		add_shortcode( 'dfm', array( &$this, 'dynamic_form_code' ) );
		add_action( 'init', array( &$this, 'email' ), 10 );
		add_action( 'init', array( &$this, 'confirmation' ), 12 );

		// Add CSS to the front-end
		add_action( 'wp_enqueue_scripts', array( &$this, 'dfm_css' ) );
	}

	/**
	 * Allow for additional plugin code to be run during admin_init
	 * which is not available during the plugin __construct()
	 *
	 * @since 2.7
	 */
	public function additional_plugin_setup() {

		$page_main = $this->_admin_pages[ 'dfm' ];

		if ( !get_option( 'dfm_dashboard_widget_options' ) ) {
			$widget_options['dfm_dashboard_recent_entries'] = array(
				'items' => 5,
			);
			update_option( 'dfm_dashboard_widget_options', $widget_options );
		}

	}

	/**
	 * Output plugin version number to help with troubleshooting
	 *
	 * @since 2.7.5
	 */
	public function add_meta_keyword() {
		// Get global settings
		$dfm_settings 	= get_option( 'dfm-settings' );

		// Settings - Disable meta tag version
		$settings_meta	= isset( $dfm_settings['show-version'] ) ? '' : '<!-- <meta name="dfm" version="'. VFB_VERSION . '" /> -->' . "\n";

		echo apply_filters( 'dfm_show_version', $settings_meta );
	}

	/**
	 * Load localization file
	 *
	 * @since 2.7
	 */
	public function languages() {
		load_plugin_textdomain( 'dynamic-form-maker', false , 'dynamic-form-maker/languages' );
	}

	/**
	 * Adds extra include files
	 *
	 * @since 1.2
	 */
	public function includes(){
		global $dfm_entries_list, $dfm_entries_detail;

		// Load the Form Form Entries List class
		require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/class-entries-list.php' );
		$dfm_entries_list = new DynamicFormMaker_Form_Entries_List();

		// Load the Form Form Entries Details class
		require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/class-entries-detail.php' );
		$dfm_entries_detail = new DynamicFormMaker_Form_Entries_Detail();
	}

	public function include_forms_list() {
		global $dfm_forms_list;

		// Load the Forms List class
		require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/class-forms-list.php' );
		$dfm_forms_list = new DynamicFormMaker_Forms_List();
	}

	/**
	 * Add Settings link to Plugins page
	 *
	 * @since 1.8
	 * @return $links array Links to add to plugin name
	 */
	public function plugin_action_links( $links, $file ) {
		if ( $file == plugin_basename( __FILE__ ) )
			$links[] = '<a href="admin.php?page=dynamic-form-maker">' . __( 'Settings' , 'dynamic-form-maker') . '</a>';

		return $links;
	}

	/**
	 * Adds the media button image
	 *
	 * @since 2.3
	 */
	public function add_media_button(){
    	if ( current_user_can( 'manage_options' ) ) :
?>
			<a href="<?php echo add_query_arg( array( 'action' => 'dynamic_form_maker_media_button', 'width' => '450' ), admin_url( 'admin-ajax.php' ) ); ?>" class="button add_media thickbox" title="Add Dynamic Form Maker form">
				<img width="18" height="18" src="<?php echo plugins_url( 'dynamic-form-maker/images/dfm_icon.png' ); ?>" alt="<?php _e( 'Add Dynamic Form Maker form', 'dynamic-form-maker' ); ?>" style="vertical-align: middle; margin-left: -8px; margin-top: -2px;" /> <?php _e( 'Add Form', 'dynamic-form-maker' ); ?>
			</a>
<?php
		endif;
	}

	/**
	 * Adds the dashboard widget
	 *
	 * @since 2.7
	 */
	public function add_dashboard_widget() {
		wp_add_dashboard_widget( 'dfm-dashboard', __( 'Recent Dynamic Form Maker Form Entries', 'dynamic-form-maker' ), array( &$this, 'dashboard_widget' ), array( &$this, 'dashboard_widget_control' ) );
	}

	/**
	 * Displays the dashboard widget content
	 *
	 * @since 2.7
	 */
	public function dashboard_widget() {
		global $wpdb;

		// Get the date/time format that is saved in the options table
		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		$widgets = get_option( 'dfm_dashboard_widget_options' );
		$total_items = isset( $widgets['dfm_dashboard_recent_entries'] ) && isset( $widgets['dfm_dashboard_recent_entries']['items'] ) ? absint( $widgets['dfm_dashboard_recent_entries']['items'] ) : 5;

		$forms = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->form_table_name}" );

		if ( !$forms ) :
			echo sprintf(
				'<p>%1$s <a href="%2$s">%3$s</a></p>',
				__( 'You currently do not have any forms.', 'dynamic-form-maker' ),
				esc_url( admin_url( 'admin.php?page=dfm-add-new' ) ),
				__( 'Get started!', 'dynamic-form-maker' )
			);

			return;
		endif;

		$entries = $wpdb->get_results( $wpdb->prepare( "SELECT forms.form_title, entries.entries_id, entries.form_id, entries.sender_name, entries.sender_email, entries.date_submitted FROM $this->form_table_name AS forms INNER JOIN $this->entries_table_name AS entries ON entries.form_id = forms.form_id ORDER BY entries.date_submitted DESC LIMIT %d", $total_items ) );

		if ( !$entries ) :
			echo sprintf( '<p>%1$s</p>', __( 'You currently do not have any entries.', 'dynamic-form-maker' ) );
		else :

			$content = '';

			foreach ( $entries as $entry ) :

				$content .= sprintf(
					'<li><a href="%1$s">%4$s</a> via <a href="%2$s">%5$s</a> <span class="rss-date">%6$s</span><cite>%3$s</cite></li>',
					esc_url( add_query_arg( array( 'action' => 'view', 'entry' => absint( $entry->entries_id ) ), admin_url( 'admin.php?page=dfm-entries' ) ) ),
					esc_url( add_query_arg( 'form-filter', absint( $entry->form_id ), admin_url( 'admin.php?page=dfm-entries' ) ) ),
					esc_html( $entry->sender_name ),
					esc_html( $entry->sender_email ),
					esc_html( $entry->form_title ),
					date( "$date_format $time_format", strtotime( $entry->date_submitted ) )
				);

			endforeach;

			echo "<div class='rss-widget'><ul>$content</ul></div>";

		endif;
	}

	/**
	 * Displays the dashboard widget form control
	 *
	 * @since 2.7
	 */
	public function dashboard_widget_control() {
		if ( !$widget_options = get_option( 'dfm_dashboard_widget_options' ) )
			$widget_options = array();

		if ( !isset( $widget_options['dfm_dashboard_recent_entries'] ) )
			$widget_options['dfm_dashboard_recent_entries'] = array();

		if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST['dfm-widget-recent-entries'] ) ) {
			$number = absint( $_POST['dfm-widget-recent-entries']['items'] );
			$widget_options['dfm_dashboard_recent_entries']['items'] = $number;
			update_option( 'dfm_dashboard_widget_options', $widget_options );
		}

		$number = isset( $widget_options['dfm_dashboard_recent_entries']['items'] ) ? (int) $widget_options['dfm_dashboard_recent_entries']['items'] : '';

		echo sprintf(
			'<p>
			<label for="comments-number">%1$s</label>
			<input id="comments-number" name="dfm-widget-recent-entries[items]" type="text" value="%2$d" size="3" />
			</p>',
			__( 'Number of entries to show:', 'dynamic-form-maker' ),
			$number
		);
	}

	/**
	 * Register contextual help. This is for the Help tab dropdown
	 *
	 * @since 1.0
	 */
	public function help(){
		$screen = get_current_screen();

		$screen->add_help_tab( array(
			'id' => 'dfm-help-tab-getting-started',
			'title' => 'Getting Started',
			'content' => '<ul>
						<li>Click on the + tab, give your form a name and click Create Form.</li>
						<li>Select form fields from the box on the left and click a field to add it to your form.</li>
						<li>Edit the information for each form field by clicking on the down arrow.</li>
						<li>Drag and drop the elements to put them in order.</li>
						<li>Click Save Form to save your changes.</li>
					</ul>'
		) );

		$screen->add_help_tab( array(
			'id' => 'dfm-help-tab-item-config',
			'title' => 'Form Item Configuration',
			'content' => "<ul>
						<li><em>Name</em> will change the display name of your form input.</li>
						<li><em>Description</em> will be displayed below the associated input.</li>
						<li><em>Validation</em> allows you to select from several of jQuery's Form Validation methods for text inputs. For more about the types of validation, read the <em>Validation</em> section below.</li>
						<li><em>Required</em> is either Yes or No. Selecting 'Yes' will make the associated input a required field and the form will not submit until the user fills this field out correctly.</li>
						<li><em>Options</em> will only be active for Radio and Checkboxes.  This field contols how many options are available for the associated input.</li>
						<li><em>Size</em> controls the width of Text, Textarea, Select, and Date Picker input fields.  The default is set to Medium but if you need a longer text input, select Large.</li>
						<li><em>CSS Classes</em> allow you to add custom CSS to a field.  This option allows you to fine tune the look of the form.</li>
					</ul>"
		) );

		$screen->add_help_tab( array(
			'id' => 'dfm-help-tab-validation',
			'title' => 'Validation',
			'content' => "<p>Dynamic Form Maker uses the <a href='http://docs.jquery.com/Plugins/Validation/Validator'>jQuery Form Validation plugin</a> to perform clientside form validation.</p>
					<ul>

						<li><em>Email</em>: makes the element require a valid email.</li>
						<li><em>URL</em>: makes the element require a valid url.</li>
						<li><em>Date</em>: makes the element require a date. <a href='http://docs.jquery.com/Plugins/Validation/Methods/date'>Refer to documentation for various accepted formats</a>.
						<li><em>Number</em>: makes the element require a decimal number.</li>
						<li><em>Digits</em>: makes the element require digits only.</li>
						<li><em>Phone</em>: makes the element require a US or International phone number. Most formats are accepted.</li>
						<li><em>Time</em>: choose either 12- or 24-hour time format (NOTE: only available with the Time field).</li>
					</ul>"
		) );

		$screen->add_help_tab( array(
			'id' => 'dfm-help-tab-confirmation',
			'title' => 'Confirmation',
			'content' => "<p>Each form allows you to customize the confirmation by selecing either a Text Message, a WordPress Page, or to Redirect to a URL.</p>
					<ul>
						<li><em>Text</em> allows you to enter a custom formatted message that will be displayed on the page after your form is submitted. HTML is allowed here.</li>
						<li><em>Page</em> displays a dropdown of all WordPress Pages you have created. Select one to redirect the user to that page after your form is submitted.</li>
						<li><em>Redirect</em> will only accept URLs and can be used to send the user to a different site completely, if you choose.</li>
					</ul>"
		) );

		$screen->add_help_tab( array(
			'id' => 'dfm-help-tab-notification',
			'title' => 'Notification',
			'content' => "<p>Send a customized notification email to the user when the form has been successfully submitted.</p>
					<ul>
						<li><em>Sender Name</em>: the name that will be displayed on the email.</li>
						<li><em>Sender Email</em>: the email that will be used as the Reply To email.</li>
						<li><em>Send To</em>: the email where the notification will be sent. This must be a required text field with email validation.</li>
						<li><em>Subject</em>: the subject of the email.</li>
						<li><em>Message</em>: additional text that can be displayed in the body of the email. HTML tags are allowed.</li>
						<li><em>Include a Copy of the User's Entry</em>: appends a copy of the user's submitted entry to the notification email.</li>
					</ul>"
		) );

		$screen->add_help_tab( array(
			'id' => 'dfm-help-tab-tips',
			'title' => 'Tips',
			'content' => "<ul>
						<li>Fieldsets, a way to group form fields, are an essential piece of this plugin's HTML. As such, at least one fieldset is required and must be first in the order. Subsequent fieldsets may be placed wherever you would like to start your next grouping of fields.</li>
						<li>Security verification is automatically included on very form. It's a simple logic question and should keep out most, if not all, spam bots.</li>
						<li>There is a hidden spam field, known as a honey pot, that should also help deter potential abusers of your form.</li>
						<li>Nesting is allowed underneath fieldsets and sections.  Sections can be nested underneath fieldsets.  Nesting is not required, however, it does make reorganizing easier.</li>
					</ul>"
		) );
	}

	/**
	 * Adds the Screen Options tab to the Form Entries screen
	 *
	 * @since 1.0
	 */
	public function screen_options(){
		$screen = get_current_screen();

		$page_main		= $this->_admin_pages[ 'dfm' ];
		$page_entries 	= $this->_admin_pages[ 'dfm-entries' ];

		switch( $screen->id ) {
			case $page_entries :

				add_screen_option( 'per_page', array(
					'label'		=> __( 'Form Entries per page', 'dynamic-form-maker' ),
					'default'	=> 20,
					'option'	=> 'dfm_entries_per_page'
				) );

				break;

			case $page_main :

				if ( isset( $_REQUEST['form'] ) ) :
					add_screen_option( 'layout_columns', array(
						'max'		=> 2,
						'default'	=> 2
					) );
				else :
					add_screen_option( 'per_page', array(
						'label'		=> __( 'Forms per page', 'dynamic-form-maker' ),
						'default'	=> 20,
						'option'	=> 'dfm_forms_per_page'
					) );
				endif;

				break;
		}
	}

	/**
	 * Saves the Screen Options
	 *
	 * @since 1.0
	 */
	public function save_screen_options( $status, $option, $value ){

		if ( $option == 'dfm_entries_per_page' )
			return $value;
		elseif ( $option == 'dfm_forms_per_page' )
			return $value;
	}

	/**
	 * Add meta boxes to form builder screen
	 *
	 * @since 1.8
	 */
	public function add_meta_boxes() {
		global $current_screen;

		$page_main = $this->_admin_pages[ 'dfm' ];

		if ( $current_screen->id == $page_main && isset( $_REQUEST['form'] ) ) {
			add_meta_box( 'dfm_form_items_meta_box', __( 'Dynamic Form Items', 'dynamic-form-maker' ), array( &$this, 'meta_box_form_items' ), $page_main, 'side', 'high' );
			add_meta_box( 'dfm_form_media_button_tip', __( 'Dynamic Display Forms', 'dynamic-form-maker' ), array( &$this, 'meta_box_display_forms' ), $page_main, 'side', 'low' );
		}
	}
	/**
	 * Output for Form Items meta box
	 *
	 * @since 1.8
	 */
	public function meta_box_form_items() {
	?>
		<div class="taxonomydiv">
			<p><strong><?php _e( 'Click' , 'dynamic-form-maker'); ?></strong> <?php _e( 'to Add a Form Field' , 'dynamic-form-maker'); ?> <img id="add-to-form" alt="" src="<?php echo admin_url( '/images/wpspin_light.gif' ); ?>" class="waiting spinner" /></p>
			<ul class="posttype-tabs add-menu-item-tabs" id="dfm-field-tabs">
				<li class="tabs"><a href="#standard-fields" class="nav-tab-link dfm-field-types"><?php _e( 'Standard' , 'dynamic-form-maker'); ?></a></li>
			</ul>
			<div id="standard-fields" class="tabs-panel tabs-panel-active">
				<ul class="dfm-fields-col-1">
					<li><a href="#" class="dfm-draggable-form-items" id="form-element-fieldset">Fieldset</a></li>
					<li><a href="#" class="dfm-draggable-form-items" id="form-element-text"><i class="fa fa-font"></i>Text</a></li>
					<li><a href="#" class="dfm-draggable-form-items" id="form-element-checkbox"><i class="fa fa-check-square-o"></i>Checkbox</a></li>
					<li><a href="#" class="dfm-draggable-form-items" id="form-element-phone"><i class="fa fa-phone"></i>Phone</a></li>
					<li><a href="#" class="dfm-draggable-form-items" id="form-element-address"><i class="fa fa-location-arrow"></i>Address</a></li>						
					<li><a href="#" class="dfm-draggable-form-items" id="form-element-select"><i class="fa fa-list"></i>Select</a></li>
					<li><a href="#" class="dfm-draggable-form-items" id="form-element-digits"><i class="fa fa-sort-numeric-asc"></i>Number</a></li>					
					<li><a href="#" class="dfm-draggable-form-items" id="form-element-datepicker"><i class="fa fa-calendar"></i>Date</a></li>
					<li><a href="#" class="dfm-draggable-form-items" id="form-element-file"><i class="fa fa-cloud-upload"></i>File Upload</a></li>
				</ul>
				<ul class="dfm-fields-col-2">
					<li><a href="#" class="dfm-draggable-form-items" id="form-element-section">Section</a></li>
					<li><a href="#" class="dfm-draggable-form-items" id="form-element-textarea"><i class="fa fa-file-text-o"></i>Textarea</a></li>
					<li><a href="#" class="dfm-draggable-form-items" id="form-element-radio"><i class="fa fa-dot-circle-o"></i>Radio</a></li>
					<li><a href="#" class="dfm-draggable-form-items" id="form-element-email"><i class="fa fa-envelope-o"></i>Email</a></li>
					<li><a href="#" class="dfm-draggable-form-items" id="form-element-url"><i class="fa fa-link"></i>URL</a></li>										
					<li><a href="#" class="dfm-draggable-form-items" id="form-element-currency"><i class="fa fa-usd"></i>Currency</a></li>
					<li><a href="#" class="dfm-draggable-form-items" id="form-element-time"><i class="fa fa-clock-o"></i>Time</a></li>

					<li><a href="#" class="dfm-draggable-form-items" id="form-element-html"><i class="fa fa-file-code-o"></i>HTML</a></li>

					<li><a href="#" class="dfm-draggable-form-items" id="form-element-instructions"><i class="fa fa-comment-o"></i>Instructions</a></li>
				</ul>
				<div class="clear"></div>
			</div> <!-- #standard-fields -->
		</div> <!-- .taxonomydiv -->
		<div class="clear"></div>
	<?php
	}

	/**
	 * Output for the Dynamic Display Forms meta box
	 *
	 * @since 1.8
	 */
	public function meta_box_display_forms() {
	?>
		<p><?php _e( 'Add created forms to your Posts or Pages and any post type by locating the <strong>Add Form</strong> button in the area above your post/page/post type editor.', 'dynamic-form-maker' ); ?></p>
    	<p><?php _e( 'You may also manually add the shortcode into a post/page/post type.', 'dynamic-form-maker' ); ?></p>
    	<p>
    		<?php _e( 'Shortcode', 'dynamic-form-maker' ); ?>
    		<input value="[dfm id='<?php echo (int) $_REQUEST['form']; ?>']" readonly="readonly" />
    	</p>
	<?php
	}

	/**
	 * Check database version and run SQL install, if needed
	 *
	 * @since 2.1
	 */
	public function update_db_check() {
		// Add a database version to help with upgrades and run SQL install
		if ( !get_option( 'dfm_db_version' ) ) {
			update_option( 'dfm_db_version', $this->dfm_db_version );
			$this->install_db();
		}

		// If database version doesn't match, update and run SQL install
		if ( version_compare( get_option( 'dfm_db_version' ), $this->dfm_db_version, '<' ) ) {
			update_option( 'dfm_db_version', $this->dfm_db_version );
			$this->install_db();
		}
	}

	/**
	 * Install database tables
	 *
	 * @since 1.0
	 */
	static function install_db() {
		global $wpdb;

		$field_table_name     = $wpdb->prefix . 'dynamic_form_maker_fields';
		$form_table_name      = $wpdb->prefix . 'dynamic_form_maker_forms';
		$entries_table_name   = $wpdb->prefix . 'dynamic_form_maker_entries';

		// Explicitly set the character set and collation when creating the tables
		$charset = ( defined( 'DB_CHARSET' && '' !== DB_CHARSET ) ) ? DB_CHARSET : 'utf8';
		$collate = ( defined( 'DB_COLLATE' && '' !== DB_COLLATE ) ) ? DB_COLLATE : 'utf8_general_ci';

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$field_sql = "CREATE TABLE $field_table_name (
				field_id BIGINT(20) NOT NULL AUTO_INCREMENT,
				form_id BIGINT(20) NOT NULL,
				field_key VARCHAR(255) NOT NULL,
				field_type VARCHAR(25) NOT NULL,
				field_options TEXT,
				field_description TEXT,
				field_name TEXT NOT NULL,
				field_sequence BIGINT(20) DEFAULT '0',
				field_parent BIGINT(20) DEFAULT '0',
				field_validation VARCHAR(25),
				field_required VARCHAR(25),
				field_size VARCHAR(25) DEFAULT 'medium',
				field_css VARCHAR(255),
				field_layout VARCHAR(255),
				field_default TEXT,
				PRIMARY KEY  (field_id)
			) DEFAULT CHARACTER SET $charset COLLATE $collate;";

		$form_sql = "CREATE TABLE $form_table_name (
				form_id BIGINT(20) NOT NULL AUTO_INCREMENT,
				form_key TINYTEXT NOT NULL,
				form_title TEXT NOT NULL,
				form_email_subject TEXT,
				form_email_to TEXT,
				form_email_from VARCHAR(255),
				form_email_from_name VARCHAR(255),
				form_email_from_override VARCHAR(255),
				form_email_from_name_override VARCHAR(255),
				form_success_type VARCHAR(25) DEFAULT 'text',
				form_success_message TEXT,
				form_notification_setting VARCHAR(25),
				form_notification_email_name VARCHAR(255),
				form_notification_email_from VARCHAR(255),
				form_notification_email VARCHAR(25),
				form_notification_subject VARCHAR(255),
				form_notification_message TEXT,
				form_notification_entry VARCHAR(25),
				form_label_alignment VARCHAR(25),
				PRIMARY KEY  (form_id)
			) DEFAULT CHARACTER SET $charset COLLATE $collate;";

		$entries_sql = "CREATE TABLE $entries_table_name (
				entries_id BIGINT(20) NOT NULL AUTO_INCREMENT,
				form_id BIGINT(20) NOT NULL,
				data LONGTEXT NOT NULL,
				subject TEXT,
				sender_name VARCHAR(255),
				sender_email VARCHAR(255),
				emails_to TEXT,
				date_submitted DATETIME,
				ip_address VARCHAR(25),
				entry_approved VARCHAR(20) DEFAULT '1',
				PRIMARY KEY  (entries_id)
			) DEFAULT CHARACTER SET $charset COLLATE $collate;";

		// Create or Update database tables
		dbDelta( $field_sql );
		dbDelta( $form_sql );
		dbDelta( $entries_sql );
	}

	/**
	 * Queue plugin scripts for sorting form fields
	 *
	 * @since 1.0
	 */
	public function admin_scripts() {
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'postbox' );
		wp_enqueue_script( 'jquery-com-ui-js', plugins_url( '/js/jquery-ui.js', __FILE__ ), array( 'jquery' ), '1.11.4', true );
		wp_enqueue_script( 'jquery-form-validation', plugins_url( '/js/jquery.validate.min.js', __FILE__ ), array( 'jquery' ), '1.9.0', true );
		wp_enqueue_script( 'dfm-admin', plugins_url( "/js/dfm-admin$this->load_dev_files.js", __FILE__ ) , array( 'jquery', 'jquery-form-validation' ), '20140412', true );
		wp_enqueue_script( 'nested-sortable', plugins_url( "/js/jquery.ui.nestedSortable$this->load_dev_files.js", __FILE__ ) , array( 'jquery', 'jquery-ui-sortable' ), '1.3.5', true );
		wp_enqueue_style( 'dfm-jqueryui-css', plugins_url( "/css/jqueryUi/jquery-ui-1.10.3.min.css", __FILE__ ), array(), '20150415' );
		wp_enqueue_style( 'dynamic-form-maker-style', plugins_url( "/css/dynamic-form-maker-admin$this->load_dev_files.css", __FILE__ ), array(), '20150415' );
		
		wp_enqueue_style( 'dfm-font-awesome-css', plugins_url( "/css/font-awesome/css/font-awesome.min.css", __FILE__ ), array(), '20150415' );		

		wp_localize_script( 'dfm-admin', 'DfmAdminPages', array( 'dfm_pages' => $this->_admin_pages ) );
	}

	/**
	 * Queue form validation scripts
	 *
	 * Scripts loaded in form-output.php, when field is present:
	 *	jQuery UI date picker
	 *	CKEditor
	 *
	 * @since 1.0
	 */
	public function scripts() {
		// Make sure scripts are only added once via shortcode
		$this->add_scripts = true;

		wp_register_script( 'jquery-form-validation', plugins_url( '/js/jquery.validate.min.js', __FILE__ ), array( 'jquery' ), '1.9.0', true );
		wp_register_script( 'dynamic-form-maker-validation', plugins_url( "/js/dfm-validation$this->load_dev_files.js", __FILE__ ) , array( 'jquery', 'jquery-form-validation' ), '20140412', true );
		wp_register_script( 'dynamic-form-maker-metadata', plugins_url( '/js/jquery.metadata.js', __FILE__ ) , array( 'jquery', 'jquery-form-validation' ), '2.0', true );
		wp_register_script( 'dfm-ckeditor', plugins_url( '/js/ckeditor/ckeditor.js', __FILE__ ), array( 'jquery' ), '4.1', true );

		wp_enqueue_script( 'jquery-form-validation' );
		wp_enqueue_script( 'dynamic-form-maker-validation' );
		wp_enqueue_script( 'dynamic-form-maker-metadata' );

		$locale = get_locale();
        $translations = array(
        	'cs_CS',	// Czech
        	'de_DE',	// German
        	'el_GR',	// Greek
        	'en_US',	// English (US)
        	'en_AU',	// English (AU)
        	'en_GB',	// English (GB)
        	'es_ES',	// Spanish
        	'fr_FR',	// French
        	'he_IL', 	// Hebrew
        	'hu_HU',	// Hungarian
        	'id_ID',	// Indonseian
        	'it_IT',	// Italian
        	'ja_JP',	// Japanese
        	'ko_KR',	// Korean
        	'nl_NL',	// Dutch
        	'pl_PL',	// Polish
        	'pt_BR',	// Portuguese (Brazilian)
        	'pt_PT',	// Portuguese (European)
        	'ro_RO',	// Romanian
        	'ru_RU',	// Russian
        	'sv_SE',	// Swedish
        	'tr_TR', 	// Turkish
        	'zh_CN',	// Chinese
        	'zh_TW',	// Chinese (Taiwan)
        );

		// Load localized vaidation and datepicker text, if translation files exist
        if ( in_array( $locale, $translations ) ) {
            wp_register_script( 'dfm-validation-i18n', plugins_url( "/js/i18n/validate/messages-$locale.js", __FILE__ ), array( 'jquery-form-validation' ), '1.9.0', true );
            wp_register_script( 'dfm-datepicker-i18n', plugins_url( "/js/i18n/datepicker/datepicker-$locale.js", __FILE__ ), array( 'jquery-ui-datepicker' ), '1.0', true );

            wp_enqueue_script( 'dfm-validation-i18n' );
        }
        // Otherwise, load English translations
        else {
	        wp_register_script( 'dfm-validation-i18n', plugins_url( "/js/i18n/validate/messages-en_US.js", __FILE__ ), array( 'jquery-form-validation' ), '1.9.0', true );
            wp_register_script( 'dfm-datepicker-i18n', plugins_url( "/js/i18n/datepicker/datepicker-en_US.js", __FILE__ ), array( 'jquery-ui-datepicker' ), '1.0', true );

            wp_enqueue_script( 'dfm-validation-i18n' );
        }
	}

	/**
	 * Add form CSS to wp_head
	 *
	 * @since 1.0
	 */
	public function dfm_css() {

		$dfm_settings = get_option( 'dfm-settings' );

		wp_register_style( 'dfm-jqueryui-css', apply_filters( 'dfm-date-picker-css', plugins_url( '/css/jqueryUi/jquery-ui-1.10.3.min.css', __FILE__ ) ), array(), '20150415' );
		wp_register_style( 'dynamic-form-maker-css', apply_filters( 'dynamic-form-maker-css', plugins_url( "/css/dynamic-form-maker$this->load_dev_files.css", __FILE__ ) ), array(), '20150415' );		

		// Settings - Always load CSS
		if ( isset( $dfm_settings['always-load-css'] ) ) {
			wp_enqueue_style( 'dynamic-form-maker-css' );
			wp_enqueue_style( 'dfm-jqueryui-css' );	

			return;
		}

		// Settings - Disable CSS
		if ( isset( $dfm_settings['disable-css'] ) )
			return;

		// Get active widgets
		$widget = is_active_widget( false, false, 'dfm_widget' );

		// If no widget is found, test for shortcode
		if ( empty( $widget ) ) {
			// If WordPress 3.6, use internal function. Otherwise, my own
			if ( function_exists( 'has_shortcode' ) ) {
				global $post;

				// If no post exists, exit
				if ( !$post )
					return;

				if ( !has_shortcode( $post->post_content, 'dfm' ) )
					return;
			} elseif ( !$this->has_shortcode( 'dfm' ) ) {
				return;
			}
		}
		
		
		wp_enqueue_style( 'dynamic-form-maker-css' );
		wp_enqueue_style( 'dfm-jqueryui-css' );
	}

	/**
	 * Save new forms on the DFM Pro > Add New page
	 *
	 * @access public
	 * @since 2.8.1
	 * @return void
	 */
	public function save_add_new_form() {
		global $wpdb;

		if ( !isset( $_REQUEST['action'] ) || !isset( $_GET['page'] ) )
			return;

		if ( 'dfm-add-new' !== $_GET['page'] )
			return;

		if ( 'create_form' !== $_REQUEST['action'] )
			return;

		check_admin_referer( 'create_form' );

		$form_key 		= sanitize_title( $_REQUEST['form_title'] );
		$form_title 	= esc_html( $_REQUEST['form_title'] );
		$form_from_name = esc_html( $_REQUEST['form_email_from_name'] );
		$form_subject 	= esc_html( $_REQUEST['form_email_subject'] );
		$form_from 		= esc_html( $_REQUEST['form_email_from'] );
		$form_to 		= serialize( $_REQUEST['form_email_to'] );

		$newdata = array(
			'form_key' 				=> $form_key,
			'form_title' 			=> $form_title,
			'form_email_from_name'	=> $form_from_name,
			'form_email_subject'	=> $form_subject,
			'form_email_from'		=> $form_from,
			'form_email_to'			=> $form_to,
			'form_success_message'	=> '<p id="form_success">Your form was successfully submitted. Thank you for contacting us.</p>'
		);

		// Create the form
		$wpdb->insert( $this->form_table_name, $newdata );

		// Get form ID to add our first field
		$new_form_selected = $wpdb->insert_id;

		// Setup the initial fieldset
		$initial_fieldset = array(
			'form_id' 			=> $wpdb->insert_id,
			'field_key' 		=> 'fieldset',
			'field_type' 		=> 'fieldset',
			'field_name' 		=> 'Fieldset',
			'field_sequence' 	=> 0
		);

		// Add the first fieldset to get things started
		$wpdb->insert( $this->field_table_name, $initial_fieldset );

		$verification_fieldset = array(
			'form_id' 			=> $new_form_selected,
			'field_key' 		=> 'verification',
			'field_type' 		=> 'verification',
			'field_name' 		=> 'Verification',
			'field_description' => '(This is for preventing spam)',
			'field_sequence' 	=> 1
		);

		// Insert the submit field
		$wpdb->insert( $this->field_table_name, $verification_fieldset );

		$verify_fieldset_parent_id = $wpdb->insert_id;

		$secret = array(
			'form_id' 			=> $new_form_selected,
			'field_key' 		=> 'secret',
			'field_type' 		=> 'secret',
			'field_name' 		=> 'Please enter any two digits',
			'field_description'	=> 'Example: 12',
			'field_size' 		=> 'medium',
			'field_required' 	=> 'yes',
			'field_parent' 		=> $verify_fieldset_parent_id,
			'field_sequence' 	=> 2
		);

		// Insert the submit field
		$wpdb->insert( $this->field_table_name, $secret );

		// Make the submit last in the sequence
		$submit = array(
			'form_id' 			=> $new_form_selected,
			'field_key' 		=> 'submit',
			'field_type' 		=> 'submit',
			'field_name' 		=> 'Submit',
			'field_parent' 		=> $verify_fieldset_parent_id,
			'field_sequence' 	=> 3
		);

		// Insert the submit field
		$wpdb->insert( $this->field_table_name, $submit );

		// Redirect to keep the URL clean (use AJAX in the future?)
		wp_redirect( 'admin.php?page=dynamic-form-maker&action=edit&form=' . $new_form_selected );
		exit();
	}

	/**
	 * Save the form
	 *
	 * @access public
	 * @since 2.8.1
	 * @return void
	 */
	public function save_update_form() {
		global $wpdb;

		if ( !isset( $_REQUEST['action'] ) || !isset( $_GET['page'] ) )
			return;

		if ( 'dynamic-form-maker' !== $_GET['page'] )
			return;

		if ( 'update_form' !== $_REQUEST['action'] )
			return;

		check_admin_referer( 'dfm_update_form' );

		$form_id 						= absint( $_REQUEST['form_id'] );
		$form_key 						= sanitize_title( $_REQUEST['form_title'], $form_id );
		$form_title 					= $_REQUEST['form_title'];
		$form_subject 					= $_REQUEST['form_email_subject'];
		$form_to 						= serialize( array_map( 'sanitize_email', $_REQUEST['form_email_to'] ) );
		$form_from 						= sanitize_email( $_REQUEST['form_email_from'] );
		$form_from_name 				= $_REQUEST['form_email_from_name'];
		$form_from_override 			= isset( $_REQUEST['form_email_from_override'] ) ? $_REQUEST['form_email_from_override'] : '';
		$form_from_name_override 		= isset( $_REQUEST['form_email_from_name_override'] ) ? $_REQUEST['form_email_from_name_override'] : '';
		$form_success_type 				= $_REQUEST['form_success_type'];
		$form_notification_setting 		= isset( $_REQUEST['form_notification_setting'] ) ? $_REQUEST['form_notification_setting'] : '';
		$form_notification_email_name 	= isset( $_REQUEST['form_notification_email_name'] ) ? $_REQUEST['form_notification_email_name'] : '';
		$form_notification_email_from 	= isset( $_REQUEST['form_notification_email_from'] ) ? sanitize_email( $_REQUEST['form_notification_email_from'] ) : '';
		$form_notification_email 		= isset( $_REQUEST['form_notification_email'] ) ? $_REQUEST['form_notification_email'] : '';
		$form_notification_subject 		= isset( $_REQUEST['form_notification_subject'] ) ? $_REQUEST['form_notification_subject'] : '';
		$form_notification_message 		= isset( $_REQUEST['form_notification_message'] ) ? wp_richedit_pre( $_REQUEST['form_notification_message'] ) : '';
		$form_notification_entry 		= isset( $_REQUEST['form_notification_entry'] ) ? $_REQUEST['form_notification_entry'] : '';
		$form_label_alignment 			= $_REQUEST['form_label_alignment'];

		// Add confirmation based on which type was selected
		switch ( $form_success_type ) {
			case 'text' :
				$form_success_message = wp_richedit_pre( $_REQUEST['form_success_message_text'] );
			break;
			case 'page' :
				$form_success_message = $_REQUEST['form_success_message_page'];
			break;
			case 'redirect' :
				$form_success_message = $_REQUEST['form_success_message_redirect'];
			break;
		}

		$newdata = array(
			'form_key' 						=> $form_key,
			'form_title' 					=> $form_title,
			'form_email_subject' 			=> $form_subject,
			'form_email_to' 				=> $form_to,
			'form_email_from' 				=> $form_from,
			'form_email_from_name' 			=> $form_from_name,
			'form_email_from_override' 		=> $form_from_override,
			'form_email_from_name_override' => $form_from_name_override,
			'form_success_type' 			=> $form_success_type,
			'form_success_message' 			=> $form_success_message,
			'form_notification_setting' 	=> $form_notification_setting,
			'form_notification_email_name' 	=> $form_notification_email_name,
			'form_notification_email_from' 	=> $form_notification_email_from,
			'form_notification_email' 		=> $form_notification_email,
			'form_notification_subject' 	=> $form_notification_subject,
			'form_notification_message' 	=> $form_notification_message,
			'form_notification_entry' 		=> $form_notification_entry,
			'form_label_alignment' 			=> $form_label_alignment
		);

		$where = array( 'form_id' => $form_id );

		// Update form details
		$wpdb->update( $this->form_table_name, $newdata, $where );

		$field_ids = array();

		// Get max post vars, if available. Otherwise set to 1000
		$max_post_vars = ( ini_get( 'max_input_vars' ) ) ? intval( ini_get( 'max_input_vars' ) ) : 1000;

		// Set a message to be displayed if we've reached a limit
		if ( count( $_POST, COUNT_RECURSIVE ) > $max_post_vars )
			$this->post_max_vars = true;

		foreach ( $_REQUEST['field_id'] as $fields ) :
				$field_ids[] = $fields;
		endforeach;

		// Initialize field sequence
		$field_sequence = 0;

		// Loop through each field and update
		foreach ( $field_ids as $id ) :
			$id = absint( $id );

			$field_name 		= ( isset( $_REQUEST['field_name-' . $id] ) ) ? trim( $_REQUEST['field_name-' . $id] ) : '';
			$field_key 			= sanitize_key( sanitize_title( $field_name, $id ) );
			$field_desc 		= ( isset( $_REQUEST['field_description-' . $id] ) ) ? trim( $_REQUEST['field_description-' . $id] ) : '';
			$field_options 		= ( isset( $_REQUEST['field_options-' . $id] ) ) ? serialize( array_map( 'trim', $_REQUEST['field_options-' . $id] ) ) : '';
			$field_validation 	= ( isset( $_REQUEST['field_validation-' . $id] ) ) ? $_REQUEST['field_validation-' . $id] : '';
			$field_required 	= ( isset( $_REQUEST['field_required-' . $id] ) ) ? $_REQUEST['field_required-' . $id] : '';
			$field_size 		= ( isset( $_REQUEST['field_size-' . $id] ) ) ? $_REQUEST['field_size-' . $id] : '';
			$field_css 			= ( isset( $_REQUEST['field_css-' . $id] ) ) ? $_REQUEST['field_css-' . $id] : '';
			$field_layout 		= ( isset( $_REQUEST['field_layout-' . $id] ) ) ? $_REQUEST['field_layout-' . $id] : '';
			$field_default 		= ( isset( $_REQUEST['field_default-' . $id] ) ) ? trim( $_REQUEST['field_default-' . $id] ) : '';

			$field_data = array(
				'field_key' 		=> $field_key,
				'field_name' 		=> $field_name,
				'field_description' => $field_desc,
				'field_options'		=> $field_options,
				'field_validation' 	=> $field_validation,
				'field_required' 	=> $field_required,
				'field_size' 		=> $field_size,
				'field_css' 		=> $field_css,
				'field_layout' 		=> $field_layout,
				'field_sequence' 	=> $field_sequence,
				'field_default' 	=> $field_default
			);

			$where = array(
				'form_id' 	=> $form_id,
				'field_id' 	=> $id
			);

			// Update all fields
			$wpdb->update( $this->field_table_name, $field_data, $where );

			$field_sequence++;
		endforeach;
	}

	/**
	 * Handle trashing and deleting forms
	 *
	 * This is a placeholder function since all processing is handled in includes/class-forms-list.php
	 *
	 * @access public
	 * @since 2.8.1
	 * @return void
	 */
	public function save_trash_delete_form() {
		global $wpdb;

		if ( !isset( $_REQUEST['action'] ) || !isset( $_GET['page'] ) )
			return;

		if ( 'dynamic-form-maker' !== $_GET['page'] )
			return;

		if ( 'delete_form' !== $_REQUEST['action'] )
			return;

		$id = absint( $_REQUEST['form'] );

		check_admin_referer( 'delete-form-' . $id );

		// Delete form and all fields
		$wpdb->query( $wpdb->prepare( "DELETE FROM $this->form_table_name WHERE form_id = %d", $id ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM $this->field_table_name WHERE form_id = %d", $id ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM $this->entries_table_name WHERE form_id = %d", $id ) );

		// Redirect to keep the URL clean (use AJAX in the future?)
		wp_redirect( add_query_arg( 'action', 'deleted', 'admin.php?page=dynamic-form-maker' ) );
		exit();
	}

	/**
	 * Handle form duplication
	 *
	 * @access public
	 * @since 2.8.1
	 * @return void
	 */
	public function save_copy_form() {
		global $wpdb;

		if ( !isset( $_REQUEST['action'] ) || !isset( $_GET['page'] ) )
			return;

		if ( 'dynamic-form-maker' !== $_GET['page'] )
			return;

		if ( 'copy_form' !== $_REQUEST['action'] )
			return;

		$id = absint( $_REQUEST['form'] );

		check_admin_referer( 'copy-form-' . $id );

		// Get all fields and data for the request form
		$fields    = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->field_table_name WHERE form_id = %d", $id ) );
		$forms     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->form_table_name WHERE form_id = %d", $id ) );
		$override  = $wpdb->get_var( $wpdb->prepare( "SELECT form_email_from_override, form_email_from_name_override, form_notification_email FROM $this->form_table_name WHERE form_id = %d", $id ) );
		$from_name = $wpdb->get_var( null, 1 );
		$notify    = $wpdb->get_var( null, 2 );

		// Copy this form and force the initial title to denote a copy
		foreach ( $forms as $form ) {
			$data = array(
				'form_key'						=> sanitize_title( $form->form_key . ' copy' ),
				'form_title' 					=> $form->form_title . ' Copy',
				'form_email_subject' 			=> $form->form_email_subject,
				'form_email_to' 				=> $form->form_email_to,
				'form_email_from' 				=> $form->form_email_from,
				'form_email_from_name' 			=> $form->form_email_from_name,
				'form_email_from_override' 		=> $form->form_email_from_override,
				'form_email_from_name_override' => $form->form_email_from_name_override,
				'form_success_type' 			=> $form->form_success_type,
				'form_success_message' 			=> $form->form_success_message,
				'form_notification_setting' 	=> $form->form_notification_setting,
				'form_notification_email_name' 	=> $form->form_notification_email_name,
				'form_notification_email_from' 	=> $form->form_notification_email_from,
				'form_notification_email' 		=> $form->form_notification_email,
				'form_notification_subject' 	=> $form->form_notification_subject,
				'form_notification_message' 	=> $form->form_notification_message,
				'form_notification_entry' 		=> $form->form_notification_entry,
				'form_label_alignment' 			=> $form->form_label_alignment
			);

			$wpdb->insert( $this->form_table_name, $data );
		}

		// Get form ID to add our first field
		$new_form_selected = $wpdb->insert_id;

		// Copy each field and data
		foreach ( $fields as $field ) {
			$data = array(
				'form_id' 			=> $new_form_selected,
				'field_key' 		=> $field->field_key,
				'field_type' 		=> $field->field_type,
				'field_name' 		=> $field->field_name,
				'field_description' => $field->field_description,
				'field_options' 	=> $field->field_options,
				'field_sequence' 	=> $field->field_sequence,
				'field_validation' 	=> $field->field_validation,
				'field_required' 	=> $field->field_required,
				'field_size' 		=> $field->field_size,
				'field_css' 		=> $field->field_css,
				'field_layout' 		=> $field->field_layout,
				'field_parent' 		=> $field->field_parent
			);

			$wpdb->insert( $this->field_table_name, $data );

			// If a parent field, save the old ID and the new ID to update new parent ID
			if ( in_array( $field->field_type, array( 'fieldset', 'section', 'verification' ) ) )
				$parents[ $field->field_id ] = $wpdb->insert_id;

			if ( $override == $field->field_id )
				$wpdb->update( $this->form_table_name, array( 'form_email_from_override' => $wpdb->insert_id ), array( 'form_id' => $new_form_selected ) );

			if ( $from_name == $field->field_id )
				$wpdb->update( $this->form_table_name, array( 'form_email_from_name_override' => $wpdb->insert_id ), array( 'form_id' => $new_form_selected ) );

			if ( $notify == $field->field_id )
				$wpdb->update( $this->form_table_name, array( 'form_notification_email' => $wpdb->insert_id ), array( 'form_id' => $new_form_selected ) );
		}

		// Loop through our parents and update them to their new IDs
		foreach ( $parents as $k => $v ) {
			$wpdb->update( $this->field_table_name, array( 'field_parent' => $v ), array( 'form_id' => $new_form_selected, 'field_parent' => $k ) );
		}
	}

	/**
	 * Save options on the DFM Pro > Settings page
	 *
	 * @access public
	 * @since 2.8.1
	 * @return void
	 */
	public function save_settings() {

		if ( !isset( $_REQUEST['action'] ) || !isset( $_GET['page'] ) )
			return;

		if ( 'dfm-settings' !== $_GET['page'] )
			return;

		if ( 'dfm_settings' !== $_REQUEST['action'] )
			return;

		check_admin_referer( 'dfm-update-settings' );

		$data = array();

		foreach ( $_POST['dfm-settings'] as $key => $val ) {
			$data[ $key ] = esc_html( $val );
		}

		update_option( 'dfm-settings', $data );
	}

	/**
	 * The jQuery field sorting callback
	 *
	 * @since 1.0
	 */
	public function ajax_sort_field() {
		global $wpdb;

		$data = array();

		foreach ( $_REQUEST['order'] as $k ) :
			if ( 'root' !== $k['item_id'] && !empty( $k['item_id'] ) ) :
				$data[] = array(
					'field_id' 	=> $k['item_id'],
					'parent' 	=> $k['parent_id']
				);
			endif;
		endforeach;

		foreach ( $data as $k => $v ) :
			// Update each field with it's new sequence and parent ID
			$wpdb->update( $this->field_table_name, array(
				'field_sequence'	=> $k,
				'field_parent'  	=> $v['parent'] ),
				array( 'field_id' => $v['field_id'] ),
				'%d'
			);
		endforeach;

		die(1);
	}

	/**
	 * The jQuery create field callback
	 *
	 * @since 1.9
	 */
	public function ajax_create_field() {
		global $wpdb;

		$data = array();
		$field_options = $field_validation = '';

		foreach ( $_REQUEST['data'] as $k ) {
			$data[ $k['name'] ] = $k['value'];
		}

		check_ajax_referer( 'create-field-' . $data['form_id'], 'nonce' );

		$form_id 	= absint( $data['form_id'] );
		$field_key 	= sanitize_title( $_REQUEST['field_type'] );
		$field_name = esc_html( $_REQUEST['field_type'] );
		$field_type = strtolower( sanitize_title( $_REQUEST['field_type'] ) );

		// Set defaults for validation
		switch ( $field_type ) {
			case 'select' :
			case 'radio' :
			case 'checkbox' :
				$field_options = serialize( array( 'Option 1', 'Option 2', 'Option 3' ) );
				break;

			case 'email' :
			case 'url' :
			case 'phone' :
				$field_validation = $field_type;
				break;

			case 'currency' :
				$field_validation = 'number';
				break;

			case 'number' :
				$field_validation = 'digits';
				break;

			case 'time' :
				$field_validation = 'time-12';
				break;

			case 'file-upload' :
				$field_options = serialize( array( 'png|jpe?g|gif' ) );
				break;
		}


		// Get the last row's sequence that isn't a Verification
		$sequence_last_row = $wpdb->get_var( $wpdb->prepare( "SELECT field_sequence FROM $this->field_table_name WHERE form_id = %d AND field_type = 'verification' ORDER BY field_sequence DESC LIMIT 1", $form_id ) );

		// If it's not the first for this form, add 1
		$field_sequence = ( !empty( $sequence_last_row ) ) ? $sequence_last_row : 0;

		$newdata = array(
			'form_id' 			=> $form_id,
			'field_key' 		=> $field_key,
			'field_name' 		=> $field_name,
			'field_type' 		=> $field_type,
			'field_options' 	=> $field_options,
			'field_sequence' 	=> $field_sequence,
			'field_validation' 	=> $field_validation
		);

		// Create the field
		$wpdb->insert( $this->field_table_name, $newdata );
		$insert_id = $wpdb->insert_id;

		// VIP fields
		$vip_fields = array( 'verification', 'secret', 'submit' );

		// Move the VIPs
		foreach ( $vip_fields as $update ) {
			$field_sequence++;
			$where = array(
				'form_id' 		=> absint( $data['form_id'] ),
				'field_type' 	=> $update
			);
			$wpdb->update( $this->field_table_name, array( 'field_sequence' => $field_sequence ), $where );

		}

		echo $this->field_output( $data['form_id'], $insert_id );

		die(1);
	}

	/**
	 * The jQuery delete field callback
	 *
	 * @since 1.9
	 */
	public function ajax_delete_field() {
		global $wpdb;

		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'dynamic_form_maker_delete_field' ) {
			$form_id = absint( $_REQUEST['form'] );
			$field_id = absint( $_REQUEST['field'] );

			check_ajax_referer( 'delete-field-' . $form_id, 'nonce' );

			if ( isset( $_REQUEST['child_ids'] ) ) {
				foreach ( $_REQUEST['child_ids'] as $children ) {
					$parent = absint( $_REQUEST['parent_id'] );

					// Update each child item with the new parent ID
					$wpdb->update( $this->field_table_name, array( 'field_parent' => $parent ), array( 'field_id' => $children ) );
				}
			}

			// Delete the field
			$wpdb->query( $wpdb->prepare( "DELETE FROM $this->field_table_name WHERE field_id = %d", $field_id ) );
		}

		die(1);
	}

	/**
	 * The jQuery form settings callback
	 *
	 * @since 2.2
	 */
	public function ajax_form_settings() {
		global $current_user;
		get_currentuserinfo();

		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'dynamic_form_maker_form_settings' ) {
			$form_id 	= absint( $_REQUEST['form'] );
			$status 	= isset( $_REQUEST['status'] ) ? $_REQUEST['status'] : 'opened';
			$accordion 	= isset( $_REQUEST['accordion'] ) ? $_REQUEST['accordion'] : 'general-settings';
			$user_id 	= $current_user->ID;

			$form_settings = get_user_meta( $user_id, 'dfm-form-settings', true );

			$array = array(
				'form_setting_tab' 	=> $status,
				'setting_accordion' => $accordion
			);

			// Set defaults if meta key doesn't exist
			if ( !$form_settings || $form_settings == '' ) {
				$meta_value[ $form_id ] = $array;

				update_user_meta( $user_id, 'dfm-form-settings', $meta_value );
			}
			else {
				$form_settings[ $form_id ] = $array;

				update_user_meta( $user_id, 'dfm-form-settings', $form_settings );
			}
		}

		die(1);
	}

	/**
	 * Display the additional media button
	 *
	 * Used for inserting the form shortcode with desired form ID
	 *
	 * @since 2.3
	 */
	public function ajax_media_button(){
		global $wpdb;

		// Sanitize the sql orderby
		$order = sanitize_sql_orderby( 'form_id ASC' );

		// Build our forms as an object
		$forms = $wpdb->get_results( "SELECT form_id, form_title FROM $this->form_table_name ORDER BY $order" );
	?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
	    $( '#add_dfm_form' ).submit(function(e){
	        e.preventDefault();

	        window.send_to_editor( '[dfm id=' + $( '#dfm_forms' ).val() + ']' );

	        window.tb_remove();
	    });
	});
</script>
<div id="dfm_form">
	<form id="add_dfm_form" class="media-upload-form type-form validate">
		<h3 class="media-title">Insert Dynamic Form Maker Form</h3>
		<p>Select a form below to insert into any Post or Page.</p>
		<select id="dfm_forms" name="dfm_forms">
			<?php foreach( $forms as $form ) : ?>
				<option value="<?php echo $form->form_id; ?>"><?php echo $form->form_title; ?></option>
			<?php endforeach; ?>
		</select>
		<p><input type="submit" class="button-primary" value="Insert Form" /></p>
	</form>
</div>
	<?php
		die(1);
	}

	/**
	 * All Forms output in admin
	 *
	 * @since 2.5
	 */
	public function all_forms() {
		global $wpdb, $dfm_forms_list;

		$order = sanitize_sql_orderby( 'form_title ASC' );

		$where = apply_filters( 'dfm_pre_get_forms', '' );
		$forms = $wpdb->get_results( "SELECT form_id, form_title FROM $this->form_table_name WHERE 1=1 $where ORDER BY $order" );

		if ( !$forms ) :
			echo '<div class="dfm-form-alpha-list"><h3 id="dfm-no-forms">You currently do not have any forms.  <a href="' . esc_url( admin_url( 'admin.php?page=dfm-add-new' ) ) . '">Click here to get started</a>.</h3></div>';
			return;
		endif;

		echo '<form id="forms-filter" method="post" action="">';
		$dfm_forms_list->views();
		$dfm_forms_list->prepare_items();

    	$dfm_forms_list->search_box( 'search', 'search_id' );
    	$dfm_forms_list->display();

		echo '</form>';
?>

	<?php
	}

	/**
	 * Build field output in admin
	 *
	 * @since 1.9
	 */
	public function field_output( $form_nav_selected_id, $field_id = NULL ) {
		require( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/admin-field-options.php' );
	}

	/**
	 * Display admin notices
	 *
	 * @since 1.0
	 */
	public function admin_notices(){
		if ( !isset( $_REQUEST['action'] ) || !isset( $_GET['page'] ) )
			return;

		if ( !in_array( $_GET['page'], array( 'dynamic-form-maker', 'dfm-add-new', 'dfm-entries', 'dfm-email-design', 'dfm-reports', 'dfm-import', 'dfm-export', 'dfm-settings' ) ) )
			return;

		switch( $_REQUEST['action'] ) {
			case 'create_form' :
				echo '<div id="message" class="updated"><p>' . __( 'Form created.' , 'dynamic-form-maker' ) . '</p></div>';
				break;

			case 'update_form' :
				echo '<div id="message" class="updated"><p>' . __( 'Form updated.' , 'dynamic-form-maker' ) . '</p></div>';

				if ( $this->post_max_vars ) :
					// Get max post vars, if available. Otherwise set to 1000
					$max_post_vars = ( ini_get( 'max_input_vars' ) ) ? intval( ini_get( 'max_input_vars' ) ) : 1000;

					echo '<div id="message" class="error"><p>' . sprintf( __( 'Error saving form. The maximum amount of data allowed by your server has been reached. Please update <a href="%s" target="_blank">max_input_vars</a> in your php.ini file to allow more data to be saved. Current limit is <strong>%d</strong>', 'dynamic-form-maker' ), 'http://www.php.net/manual/en/info.configuration.php#ini.max-input-vars', $max_post_vars ) . '</p></div>';
				endif;
				break;

			case 'deleted' :
				echo '<div id="message" class="updated"><p>' . __( 'Item permanently deleted.' , 'dynamic-form-maker') . '</p></div>';
				break;

			case 'copy_form' :
				echo '<div id="message" class="updated"><p>' . __( 'Item successfully duplicated.' , 'dynamic-form-maker') . '</p></div>';
				break;

			case 'dfm_settings' :
				echo sprintf( '<div id="message" class="updated"><p>%s</p></div>', __( 'Settings saved.' , 'dynamic-form-maker' ) );
				break;
		}
	}

	/**
	 * Add options page to Settings menu
	 *
	 *
	 * @since 1.0
	 * @uses add_options_page() Creates a menu item under the Settings menu.
	 */
	public function add_admin() {
		$current_pages = array();

		$current_pages[ 'dfm' ] = add_menu_page( __( 'Dynamic Form Maker', 'dynamic-form-maker' ), __( 'Dynamic Form Maker', 'dynamic-form-maker' ), 'manage_options', 'dynamic-form-maker', array( &$this, 'admin' ), plugins_url( 'dynamic-form-maker/images/dfm_icon.png' ) );

		add_submenu_page( 'dynamic-form-maker', __( 'Dynamic Form Maker', 'dynamic-form-maker' ), __( 'All Forms', 'dynamic-form-maker' ), 'manage_options', 'dynamic-form-maker', array( &$this, 'admin' ) );
		$current_pages[ 'dfm-add-new' ] = add_submenu_page( 'dynamic-form-maker', __( 'Add New Form', 'dynamic-form-maker' ), __( 'Add New Form', 'dynamic-form-maker' ), 'manage_options', 'dfm-add-new', array( &$this, 'admin_add_new' ) );
		$current_pages[ 'dfm-settings' ] = add_submenu_page( 'dynamic-form-maker', __( 'Settings', 'dynamic-form-maker' ), __( 'Settings', 'dynamic-form-maker' ), 'manage_options', 'dfm-settings', array( &$this, 'admin_settings' ) );
		$current_pages[ 'dfm-entries' ] = add_submenu_page( 'dynamic-form-maker', __( 'Form Entries', 'dynamic-form-maker' ), __( 'Form Entries', 'dynamic-form-maker' ), 'manage_options', 'dfm-entries', array( &$this, 'admin_entries' ) );
		$current_pages[ 'dfm-export' ] = add_submenu_page( 'dynamic-form-maker', __( 'Export', 'dynamic-form-maker' ), __( 'Export', 'dynamic-form-maker' ), 'manage_options', 'dfm-export', array( &$this, 'admin_form_export' ) );
		

		// All plugin page load hooks
		foreach ( $current_pages as $key => $page ) :
			// Load the jQuery and CSS we need if we're on our plugin page
			add_action( "load-$page", array( &$this, 'admin_scripts' ) );

			// Load the Help tab on all pages
			add_action( "load-$page", array( &$this, 'help' ) );
		endforeach;

		// Save pages array for filter/action use throughout plugin
		$this->_admin_pages = $current_pages;

		// Adds a Screen Options tab to the Form Entries screen
		add_action( 'load-' . $current_pages['dfm'], array( &$this, 'screen_options' ) );
		add_action( 'load-' . $current_pages['dfm-entries'], array( &$this, 'screen_options' ) );

		// Add meta boxes to the form builder admin page
		add_action( 'load-' . $current_pages['dfm'], array( &$this, 'add_meta_boxes' ) );

		// Include Form Entries and Import files
		add_action( 'load-' . $current_pages['dfm-entries'], array( &$this, 'includes' ) );

		add_action( 'load-' . $current_pages['dfm'], array( &$this, 'include_forms_list' ) );
	}

	/**
	 * Display Add New Form page
	 *
	 *
	 * @since 2.7.2
	 */
	public function admin_add_new() {
?>
	<div class="wrap">
		<h2><?php _e( 'Add New Form', 'dynamic-form-maker' ); ?></h2>
<?php
		include_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/admin-new-form.php' );
?>
	</div>
<?php
	}

	/**
	 * Display Form Entries
	 *
	 *
	 * @since 2.7.2
	 */
	public function admin_entries() {
		global $dfm_entries_list, $dfm_entries_detail;
?>
	<div class="wrap">
		<h2>
			<?php _e( 'Form Entries', 'dynamic-form-maker' ); ?>
<?php
			// If searched, output the query
			if ( isset( $_REQUEST['s'] ) && !empty( $_REQUEST['s'] ) )
				echo '<span class="subtitle">' . sprintf( __( 'Search results for "%s"' , 'dynamic-form-maker' ), $_REQUEST['s'] );
?>
		</h2>
<?php
		if ( isset( $_REQUEST['action'] ) && in_array( $_REQUEST['action'], array( 'view', 'edit', 'update_entry' ) ) ) :
			$dfm_entries_detail->entries_detail();
		else :
			$dfm_entries_list->views();
			$dfm_entries_list->prepare_items();
?>
    	<form id="entries-filter" method="post" action="">
<?php
        	$dfm_entries_list->search_box( 'search', 'search_id' );
        	$dfm_entries_list->display();
?>
        </form>
	<?php endif; ?>
	</div>
<?php
	}

	/**
	 * Display Export
	 *
	 *
	 * @since 2.7.2
	 */
	public function admin_form_export() {
		global $export_entries;
?>
	<div class="wrap">
		<h2><?php _e( 'Export', 'dynamic-form-maker' ); ?></h2>
<?php
		$export_entries->display_export();
?>
	</div>
<?php
	}

	/**
	 * admin_settings function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_settings() {

		$dfm_settings = get_option( 'dfm-settings' );
?>
	<div class="wrap">
		<h2><?php _e( 'Settings', 'dynamic-form-maker' ); ?></h2>
		<form id="dfm-settings" method="post">
			<input name="action" type="hidden" value="dfm_settings" />
			<?php wp_nonce_field( 'dfm-update-settings' ); ?>
			<h3><?php _e( 'Global Settings', 'dynamic-form-maker' ); ?></h3>
			<p><?php _e( 'Settings will affect all forms on your site.', 'dynamic-form-maker' ); ?></p>
			<table class="form-table">				

				<tr valign="top">
					<th scope="row"><label for="dfm-settings-sender-mail-header"><?php _e( 'Sender Mail Header', 'dynamic-form-maker' ); ?></label></th>
					<td>
						<?php
						// Use the admin_email as the From email
						$from_email = get_site_option( 'admin_email' );

						// Get the site domain and get rid of www.
						$sitename = strtolower( $_SERVER['SERVER_NAME'] );
						if ( substr( $sitename, 0, 4 ) == 'www.' )
							$sitename = substr( $sitename, 4 );

						// Get the domain from the admin_email
						list( $user, $domain ) = explode( '@', $from_email );

						// If site domain and admin_email domain match, use admin_email, otherwise a same domain email must be created
						$from_email = ( $sitename == $domain ) ? $from_email : "wordpress@$sitename";

						$dfm_settings['sender-mail-header'] = isset( $dfm_settings['sender-mail-header'] ) ? $dfm_settings['sender-mail-header'] : $from_email;
						?>
						<input type="text" name="dfm-settings[sender-mail-header]" id="dfm-settings-sender-mail-header" value="<?php echo $dfm_settings['sender-mail-header']; ?>" class="regular-text" />
						<p class="description"><?php _e( 'Server configurations require an existing email on the domain be used when sending emails.', 'dynamic-form-maker' ); ?></p>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><?php _e( 'Form Output', 'dynamic-form-maker-pro' ); ?></th>
					<td>
						<fieldset>
						<?php
							$disable = array(
								'address-labels'      => __( 'Place Address above fields', 'dynamic-form-maker' ),	// dfm_address_labels_placement
								'show-version'        => __( 'Disable meta tag version', 'dynamic-form-maker' ),	// dfm_show_version
							);

							foreach ( $disable as $key => $title ) :

								$dfm_settings[ $key ] = isset( $dfm_settings[ $key ] ) ? $dfm_settings[ $key ] : '';
						?>
							<label for="dfm-settings-<?php echo $key; ?>">
								<input type="checkbox" name="dfm-settings[<?php echo $key; ?>]" id="dfm-settings-<?php echo $key; ?>" value="1" <?php checked( $dfm_settings[ $key ], 1 ); ?> /> <?php echo $title; ?>
							</label>
							<br>
						<?php endforeach; ?>
						</fieldset>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="dfm-settings-spam-points"><?php _e( 'Spam word sensitivity', 'dynamic-form-maker' ); ?></label></th>
					<td>
						<?php $dfm_settings['spam-points'] = isset( $dfm_settings['spam-points'] ) ? $dfm_settings['spam-points'] : '4'; ?>
						<input type="number" min="1" name="dfm-settings[spam-points]" id="dfm-settings-spam-points" value="<?php echo $dfm_settings['spam-points']; ?>" class="small-text" />
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="dfm-settings-max-upload-size"><?php _e( 'Max Upload Size', 'dynamic-form-maker' ); ?></label></th>
					<td>
						<?php $dfm_settings['max-upload-size'] = isset( $dfm_settings['max-upload-size'] ) ? $dfm_settings['max-upload-size'] : '25'; ?>
						<input type="number" name="dfm-settings[max-upload-size]" id="dfm-settings-max-upload-size" value="<?php echo $dfm_settings['max-upload-size']; ?>" class="small-text" /> MB
					</td>
				</tr>

				
				<tr valign="top">
					<th scope="row"><?php _e( 'CSS', 'dynamic-form-maker-pro' ); ?></th>
					<td>
						<fieldset>
						<?php
							$disable = array(
								'always-load-css'     => __( 'Always load CSS', 'dynamic-form-maker' ),
								'disable-css'         => __( 'Disable CSS', 'dynamic-form-maker' ),	// dynamic-form-maker-css
							);

							foreach ( $disable as $key => $title ) :

								$dfm_settings[ $key ] = isset( $dfm_settings[ $key ] ) ? $dfm_settings[ $key ] : '';
						?>
							<label for="dfm-settings-<?php echo $key; ?>">
								<input type="checkbox" name="dfm-settings[<?php echo $key; ?>]" id="dfm-settings-<?php echo $key; ?>" value="1" <?php checked( $dfm_settings[ $key ], 1 ); ?> /> <?php echo $title; ?>
							</label>
							<br>
						<?php endforeach; ?>
						</fieldset>
					</td>
				</tr>
			</table>

			<?php submit_button( __( 'Save', 'dynamic-form-maker' ), 'primary', 'submit', false ); ?>
		</form>
	</div>
<?php
	}

	/**
	 * Builds the options settings page
	 *
	 * @since 1.0
	 */
	public function admin() {
		global $wpdb, $current_user;

		get_currentuserinfo();

		// Save current user ID
		$user_id = $current_user->ID;

		// Set variables depending on which tab is selected
		$form_nav_selected_id = ( isset( $_REQUEST['form'] ) ) ? $_REQUEST['form'] : '0';
	?>
	<div class="wrap">
		<h2>
			<?php _e( 'Dynamic Form Maker', 'dynamic-form-maker' ); ?>
<?php
			// Add New link
			echo sprintf( ' <a href="%1$s" class="add-new-h2">%2$s</a>', esc_url( admin_url( 'admin.php?page=dfm-add-new' ) ), esc_html( __( 'Add New', 'dynamic-form-maker' ) ) );

			// If searched, output the query
			if ( isset( $_REQUEST['s'] ) && !empty( $_REQUEST['s'] ) )
				echo '<span class="subtitle">' . sprintf( __( 'Search results for "%s"' , 'dynamic-form-maker' ), $_REQUEST['s'] );
?>
		</h2>
		<?php if ( empty( $form_nav_selected_id ) ) : ?>
		<div id="dfm-form-list">
			<div id="dfm-sidebar">
				<?php //include_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/dfm-sidebar.php' ); ?>
			</div> <!-- #dfm-sidebar -->
			<div id="dfm-main" class="dfm-order-type-list">
				<?php $this->all_forms(); ?>
			</div> <!-- #dfm-main -->
		</div> <!-- #dfm-form-list -->

		<?php
		elseif ( !empty( $form_nav_selected_id ) && $form_nav_selected_id !== '0' ) :
			include_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/admin-form-creator.php' );
		endif;
		?>
	</div>
	<?php
	}

	/**
	 * Handle confirmation when form is submitted
	 *
	 * @since 1.3
	 */
	function confirmation(){
		global $wpdb;

		$form_id = ( isset( $_REQUEST['form_id'] ) ) ? (int) esc_html( $_REQUEST['form_id'] ) : '';

		if ( !isset( $_REQUEST['dfm-submit'] ) )
			return;

		// Get forms
		$order = sanitize_sql_orderby( 'form_id DESC' );
		$forms 	= $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->form_table_name WHERE form_id = %d ORDER BY $order", $form_id ) );

		foreach ( $forms as $form ) :
			// If text, return output and format the HTML for display
			if ( 'text' == $form->form_success_type )
				return stripslashes( html_entity_decode( wp_kses_stripslashes( $form->form_success_message ) ) );
			// If page, redirect to the permalink
			elseif ( 'page' == $form->form_success_type ) {
				$page = get_permalink( $form->form_success_message );
				wp_redirect( $page );
				exit();
			}
			// If redirect, redirect to the URL
			elseif ( 'redirect' == $form->form_success_type ) {
				wp_redirect( esc_url( $form->form_success_message ) );
				exit();
			}
		endforeach;
	}

	/**
	 * Output form via shortcode
	 *
	 * @since 1.0
	 */
	public function dynamic_form_code( $atts, $output = '' ) {

		require( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/form-output.php' );

		return $output;
	}

	/**
	 * Handle emailing the content
	 *
	 * @since 1.0
	 * @uses wp_mail() E-mails a message
	 */
	public function email() {
		require( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/dfm-email.php' );
	}

	/**
	 * Validate the input
	 *
	 * @since 2.2
	 */
	public function validate_input( $data, $name, $type, $required ) {

		if ( 'yes' == $required && strlen( $data ) == 0 )
			wp_die( "<h1>$name</h1><br>" . __( 'This field is required and cannot be empty.', 'dynamic-form-maker' ), $name, array( 'back_link' => true ) );

		if ( strlen( $data ) > 0 ) :
			switch( $type ) :

				case 'email' :
					if ( !is_email( $data ) )
						wp_die( "<h1>$name</h1><br>" . __( 'Not a valid email address', 'dynamic-form-maker' ), '', array( 'back_link' => true ) );
					break;

				case 'number' :
				case 'currency' :
					if ( !is_numeric( $data ) )
						wp_die( "<h1>$name</h1><br>" . __( 'Not a valid number', 'dynamic-form-maker' ), '', array( 'back_link' => true ) );
					break;

				case 'phone' :
					if ( strlen( $data ) > 9 && preg_match( '/^((\+)?[1-9]{1,2})?([-\s\.])?((\(\d{1,4}\))|\d{1,4})(([-\s\.])?[0-9]{1,12}){1,2}$/', $data ) )
						return true;
					else
						wp_die( "<h1>$name</h1><br>" . __( 'Not a valid phone number. Most US/Canada and International formats accepted.', 'dynamic-form-maker' ), '', array( 'back_link' => true ) );
					break;

				case 'url' :
					if ( !preg_match( '|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $data ) )
						wp_die( "<h1>$name</h1><br>" . __( 'Not a valid URL.', 'dynamic-form-maker' ), '', array( 'back_link' => true ) );
						break;

				default :
					return true;
					break;

			endswitch;
		endif;
	}

	/**
	 * Sanitize the input
	 *
	 * @since 2.5
	 */
	public function sanitize_input( $data, $type ) {
		if ( strlen( $data ) > 0 ) :
			switch( $type ) :
				case 'text' :
					return sanitize_text_field( $data );
					break;

				case 'textarea' :
					return wp_strip_all_tags( $data );
					break;

				case 'email' :
					return sanitize_email( $data );
					break;

				case 'html' :
					return wp_kses_data( force_balance_tags( $data ) );
					break;

				case 'min' :
				case 'max' :
				case 'digits' :
					return preg_replace( '/\D/i', '', $data );
					break;

				case 'address' :
					$allowed_html = array( 'br' => array() );
					return wp_kses( $data, $allowed_html );
					break;

				default :
					return wp_kses_data( $data );
					break;
			endswitch;
		endif;
	}

	/**
	 * Make sure the User Agent string is not a SPAM bot
	 *
	 * @since 1.3
	 */
	public function isBot() {
		$bots = apply_filters( 'dfm_blocked_spam_bots', array(
			'<', '>', '&lt;', '%0A', '%0D', '%27', '%3C', '%3E', '%00', 'href',
			'binlar', 'casper', 'cmsworldmap', 'comodo', 'diavol',
			'dotbot', 'feedfinder', 'flicky', 'ia_archiver', 'jakarta',
			'kmccrew', 'nutch', 'planetwork', 'purebot', 'pycurl',
			'skygrid', 'sucker', 'turnit', 'vikspider', 'zmeu',
			)
		);

		$isBot = false;

		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? wp_kses_data( $_SERVER['HTTP_USER_AGENT'] ) : '';

		do_action( 'dfm_isBot', $user_agent, $bots );

		foreach ( $bots as $bot ) {
			if ( stripos( $user_agent, $bot ) !== false )
				$isBot = true;
		}

		return $isBot;
	}

	public function build_array_form_item( $value, $type = '' ) {

		$output = '';

		// Basic check for type when not set
		if ( empty( $type ) ) :
			if ( is_array( $value ) && array_key_exists( 'address', $value ) )
				$type = 'address';
			elseif ( is_array( $value ) && array_key_exists( 'hour', $value ) && array_key_exists( 'min', $value ) )
				$type = 'time';
			elseif ( is_array( $value ) )
				$type = 'checkbox';
			else
				$type = 'default';
		endif;

		// Build array'd form item output
		switch( $type ) :

			case 'time' :
				$output = ( array_key_exists( 'ampm', $value ) ) ? substr_replace( implode( ':', $value ), ' ', 5, 1 ) : implode( ':', $value );
			break;

			case 'address' :

				if ( !empty( $value['address'] ) )
					$output .= $value['address'];

				if ( !empty( $value['address-2'] ) ) {
					if ( !empty( $output ) )
						$output .= '<br>';
					$output .= $value['address-2'];
				}

				if ( !empty( $value['city'] ) ) {
					if ( !empty( $output ) )
						$output .= '<br>';
					$output .= $value['city'];
				}
				if ( !empty( $value['state'] ) ) {
					if ( !empty( $output ) && empty( $value['city'] ) )
						$output .= '<br>';
					elseif ( !empty( $output ) && !empty( $value['city'] ) )
						$output .= ', ';
					$output .= $value['state'];
				}
				if ( !empty( $value['zip'] ) ) {
					if ( !empty( $output ) && ( empty( $value['city'] ) && empty( $value['state'] ) ) )
						$output .= '<br>';
					elseif ( !empty( $output ) && ( !empty( $value['city'] ) || !empty( $value['state'] ) ) )
						$output .= ' ';
					$output .= $value['zip'];
				}
				if ( !empty( $value['country'] ) ) {
					if ( !empty( $output ) )
						$output .= '<br>';
					$output .= $value['country'];
				}

			break;

			case 'checkbox' :

				$output = esc_html( implode( ', ', $value ) );

			break;

			default :

				$output = wp_specialchars_decode( stripslashes( esc_html( $value ) ), ENT_QUOTES );

			break;

		endswitch;

		return $output;
	}

	/**
	 * Check whether the content contains the specified shortcode
	 *
	 * @access public
	 * @param string $shortcode (default: '')
	 * @return void
	 */
	function has_shortcode($shortcode = '') {

		$post_to_check = get_post(get_the_ID());

		// false because we have to search through the post content first
		$found = false;

		// if no short code was provided, return false
		if (!$shortcode) {
			return $found;
		}
		// check the post content for the short code
		if ( stripos($post_to_check->post_content, '[' . $shortcode) !== false ) {
			// we have found the short code
			$found = true;
		}

		// return our final results
		return $found;
	}
}

// The DFM widget
require( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/class-widget.php' );

// Special case to load Export class so AJAX is registered
require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/class-export.php' );
if ( !isset( $export_entries ) )
	$export_entries = new DinamicFormMaker_Export();
