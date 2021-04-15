<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Enico_Micropay
 * @subpackage Enico_Micropay/admin
 * @author     Enico <info@enico.info>
 */
class Enico_Micropay_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.5.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.5.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The activation link slug
	 *
	 * @var string
	 */
	private $activation_link_slug;
	
	/**
	 * The MercadoPago token
	 *
	 * @var string
	 */
	private $mp_token;

	/**
	 * The MercadoPago API URL
	 *
	 * @var string
	 */
	private $mp_url;

	/**
	 * If MercadoPago is in Sandbox
	 *
	 * @var string
	 */
	private $mp_sandbox;

	/**
	 * Mercado Pago country currency code
	 *
	 * @var string
	 */
	private $mp_country;

	/**
	 * Rest URL
	 *
	 * @var string
	 */
	private $rest_url;

	private $mail_headers;
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.5.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		// Plugin properties
		$this->plugin_name = $plugin_name;
		$this->version = $version;

		// General properties
		$this->activation_link_slug = 'enico-activation-link';

		// MercadoPago properties
		$this->mp_token = get_option('enico_mp_token');
		$this->mp_url = "https://api.mercadopago.com/";
		$this->mp_sandbox = get_option('enico_mp_sandbox');
		$this->mp_country = get_option('enico_mp_country');

		// Email
		$this->mail_headers[] = 'Content-Type: text/html; charset=UTF-8';

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.5.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Enico_Micropay_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Enico_Micropay_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.5.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Enico_Micropay_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Enico_Micropay_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( 
			$this->plugin_name . '-admin', 
			plugin_dir_url( __DIR__ ) . 'dist/enico-micropay.js', 
			array( 'wp-plugins', 'wp-edit-post', 'wp-element' ), 
			$this->version, 
			true 
		);
		wp_localize_script( 
			$this->plugin_name . '-admin', 
			'enico_vars', array(
				'currentScreen' => get_current_screen(),
				'pluginName' => $this->plugin_name,
				'restURL' => rest_url() . $this->plugin_name . '/v1',
				'restNonce' => wp_create_nonce( 'wp_rest' )
			)
		);
	}

	/**
	 * Register REST API Routes
	 * 
	 * @since	1.5.0
	 */
	public function register_rest_routes(){
		$namespace = $this->plugin_name . '/v1';
		
		register_rest_route( $namespace, '/checkout', array(
			'methods' => WP_REST_Server::CREATABLE,
			'callback' => [$this,'post_checkout'],
			'permission_callback' => '__return_true'
		));

		register_rest_route( $namespace, '/validate_payment', array(
			'methods' => WP_REST_Server::READABLE,
			'callback' => [$this,'get_validate_payment'],
			'permission_callback' => '__return_true'
		));

		// Routes only for v1.1 and v1.2
		register_rest_route( $namespace, '/migrate', array(
			'methods' => WP_REST_Server::CREATABLE,
			'callback' => [$this,'post_migrate'],
			'permission_callback' => '__return_true'
		));
	}

	/**
	 * Callback: POST/checkout
	 * 
	 * Create the MercadoPago checkout preference.
	 *
	 * @author Victor H. Morales <vmorales@mkdev.ar>
	 *
	 * @param WP_REST_Request $request
	 */
	public function post_checkout(WP_REST_Request $request) {
	
		$post_id = $request['postID'];
		$post_price = $request['postPrice'];
		$user_email = $request['userEmail'];
		$url = $this->mp_url . "checkout/preferences?access_token=" . $this->mp_token;
		$this->rest_url = rest_url() . $this->plugin_name . '/v1';

		/**
		 * Detect errors
		 * 
		 */
		// If posts doesn't exists
		$post = get_post($post_id);
		if (!isset($post))
		{
			return new WP_Error( 'not_found', 'No se encontr&oacute; el art&iacute;lo.', array( 'status' => 404 ) );
		}

		// Price is empty
		if (empty($post_price)) {
			return new WP_Error( 'empty_price', 'El precio no est&aacute; definido.', array( 'status' => 402 ) );
		}

		// If minimun is activated and the selected price is minor thand the product price
		$active_min_price = get_post_meta($post_id, '_enico_min_price', true);
		if ($active_min_price == 'on') {
			$post_min_price = get_post_meta($post_id, '_enico_custom_price', true);
			if ($post_price < $post_min_price) {
				return new WP_Error( 'price_is_minor', 'El valor debe ser mayor al m&iacute;nimo establecido (' . $this->mp_country . ' ' . $post_min_price . ').', array( 'status' => 400 ) );
			}
		}

		// Validate user email
		if (!is_email($user_email)) {
			return new WP_Error( 'invalid_email', 'El email no es válido', array( 'status' => 400 ) );
		}

		// Check if email already bought this post
		/*
		$buyers_list = get_post_meta($post_id, '_enico_buyers_list', true);
		$search_email = array_search($user_email, array_column($buyers_list, 'buyer_email'));
		if (is_numeric($search_email))
		{
			return new WP_Error( 'invalid_email', 'Ya has comprado este art&iacute;culo. Revisa tu casilla de correo para seguir el enlace de activaci&oacute;n', array( 'status' => 400 ) );
		}
		*/
		
		/**
		 * Create MP preference
		 * 
		 */

		// Item
		$preference['items'][] = array(
			'title' => 'Artículo: ' . $post->post_title,
			'description' => $post->post_title,
			'quantity' => 1,
			'currency_id' => $this->mp_country,
			'unit_price' => (float)$post_price
		);

		// Payer email
		$preference['payer'] = array(
			'email' => $user_email
		);

		// Exclude payment types
		$preference['payment_methods']['excluded_payment_types'] = array(
			array('id' => 'ticket'),
			array('id' => 'bank_transfer'),
			array('id' => 'atm')
		);

		// Redirect after payment
		$preference['back_urls'] = array(
			'success' => $this->rest_url . '/validate_payment?pid=' . $post_id . '&uemail=' . $user_email
		);

		// On approved, auto redirect
		$preference['auto_return'] = "approved";
		
		// Post $options to $url
		$options = [
			'body' => json_encode($preference),
			'headers' => array(
				'accept' => 'application/json',
				'content-type' => 'application/json'
			)
		]; 
		$resp = wp_remote_post($url, $options);

		// Return message on error
		// else, redirect to MercadoPago
		if (is_wp_error($resp)) {
			$error_message = $resp->get_error_message();
			return new WP_Error( 'unexpected_error', 'Sucedi&oacute; un error inesperado.', array( 'status' => 400 ) );
			//return rest_ensure_response(esc_url(get_the_permalink() . '?status=error&message=' . esc_url($error_message)));
		} else {
			$body = json_decode($resp['body']);
			// Sandbox URL if ON; Real MP init point if false
			$mp_url_point = ($this->mp_sandbox == 'on') ? $body->sandbox_init_point : $body->init_point ;
			
			// Return URL to redirect via JS
			return rest_ensure_response($mp_url_point);
		}
		
	}

	/**
	 * Callback: GET/validate_payment
	 *
	 * Redirected to this URL when MercadoPago payment is approved. This API endpoint validates the payment, creates a TOKEN and add it to the post.
	 * 
	 * @author Victor H. Morales <vmorales@mkdev.ar>
	 *
	 * @param WP_REST_Request $request
	 */
	public function get_validate_payment(WP_REST_Request $request) {
		
		/** 
		 * This is the information that MercadoPago returns when redirecting on success:
		 * collection_id=1232366746&
		 * collection_status=approved&
		 * payment_id=1232366746&
		 * status=approved&
		 * external_reference=null&
		 * payment_type=account_money&
		 * merchant_order_id=2146977426&
		 * preference_id=48851064-66605e41-7fd8-46cb-9168-602d5cb27cfc&
		 * site_id=MLA&
		 * processing_mode=aggregator&
		 * merchant_account_id=null
		*/
		
		$pid = $request['pid'];
		$uemail = $request['uemail'];
		$price = (get_post_meta($pid, '_enico_custom_price', true)) ?: get_option('enico_default_price');

		$redirect_to = home_url($this->activation_link_slug);
		$collection_id = $request['collection_id'];
		$collection_status = $request['collection_status'];
		$payment_id = $request['payment_id'];
		$status = $request['status'];
		$external_reference = $request['external_reference'];
		$payment_type = $request['payment_type'];
		$merchant_order_id = $request['merchant_order_id'];
		$preference_id = $request['preference_id'];
		$site_id = $request['site_id'];
		$processing_mode = $request['processing_mode'];
		$merchant_account_id = $request['merchant_account_id'];
		$this->rest_url = rest_url() . $this->plugin_name . '/v1';
		
		/**
		 * Check for errors
		 * 
		 */
		// If status is not approved
		if ($status !== 'approved') {
			wp_redirect( $redirect_to . '?status=error&msgid=not_approved');
			exit;
		}
		
		// If posts doesn't exists
		
		$post = get_post($pid);
		if (!isset($post))
		{
			wp_redirect( $redirect_to . '?status=error&msgid=not_found');
			exit;
		}
		
		/**
		 * Process information
		 * 
		 */
		// Create token
		$token = wp_hash(time());

		// Add token, email and price to post meta data
		$buyers_list = get_post_meta($pid, '_enico_buyers_list', true);
		if ( !is_array($buyers_list) || empty($buyers_list) ) {
			$buyers_list = array();
		}
		$buyers_list[] = array('token' => $token, 'buyer_email' => $uemail, 'price' => $price);
		 update_post_meta($pid, '_enico_buyers_list', $buyers_list);

		// Create a validation link to email it and redirect to it
		$validation_link = $redirect_to . '?token=' . $token . '&pid=' . $pid . '&uemail=' . $uemail;
		
		// Replace email subject variables		
		$str_email_subject = array(
			'%%TITLE%%' => get_the_title($pid)
		);
		$email_subject = strtr(get_option('enico_email_subject'), $str_email_subject);

		// Replace email body variables
		$str_email_body = array(
			'%%SITE_NAME%%' => get_bloginfo('name'),
			'%%TITLE%%' => get_the_title($pid),
			'%%LINK%%' => $validation_link
		);
		$email_body = strtr(get_option('enico_email_body'), $str_email_body);

		// Send email with validation link
		wp_mail($uemail, $email_subject, $email_body, $this->mail_headers);
		
		// Redirect to validation link
		wp_redirect( $validation_link );
		exit;
	}

	public function post_migrate(WP_REST_Request $request) {
		global $wpdb;

		$error = array();
		/**
		 * Migrate plugin settings
		 * 
		 * Get the old SQL and WordPress options
		 */
		if ($request['action'] === 'settings') {
			// Activation link page settings
			$activation_link_slug = 'enico-activation-link';
			$post_details = array(
				'post_title'    => '¡El artículo es tuyo!',
				'post_content'  => '[enico-activation-link]',
				'post_status'   => 'publish',
				'post_author'   => 1,
				'post_type' => 'page',
				'post_name' => $activation_link_slug
			);
			// Create page if it doesn't exists
			$page = get_page_by_path($activation_link_slug );
			if (empty($page))
			{
				$post_id = wp_insert_post( $post_details );
			}
			
			// Get v1.1 and v1.2 database settings and update v1.5 wordpress options
			$sql = "SELECT id, front_text, post_id_terms, eni_content, eni_subject_mail, eni_content_mail FROM ".$wpdb->prefix."eni_configs WHERE id=1";
			$rows = $wpdb->get_results($sql);
			$total_rows = count($rows);

			if ($total_rows > 0) {
				if (!empty($rows[0]->front_text))
				update_option('enico_payperview_text', $rows[0]->front_text);
			
				if (!empty($rows[0]->eni_content))
					update_option('enico_price_text', $rows[0]->eni_content);
				
				if (!empty($rows[0]->eni_subject_mail))
					update_option('enico_email_subject', $rows[0]->eni_subject_mail);
				
				if (!empty($rows[0]->eni_content_mail))
					update_option('enico_email_body', $rows[0]->eni_content_mail);
			}
			
			// Get 1.1 and v1.2 wordpress options and update the v1.5 options
			$old_value_token = get_option('enipay_mp_key');
			$old_value_price = get_option('enipay_value');
			$old_value_moneda = get_option('enipay_mp_moneda');
			$old_value_mp_prod = get_option('enipay_mp_prod');
			
			if (!empty($old_value_price))
				update_option('enico_default_price', $old_value_price);
			
			if (!empty($old_value_token))
				update_option('enico_mp_token', $old_value_token);
			
			//if (get_option('enipay_mp_url') == "")
				//update_option('enipay_mp_url', 'wwww.mercadopago.com.ar');
			
			if (!empty($old_value_moneda))
				update_option('enico_mp_country', $old_value_moneda);

			// Return
			$return = "Configuraciones importadas. Actualice la pagina para ver el resultado.";

		}

		/**
		 * MIGRATE TOKENS
		 * 
		 * Get all tokens from database
		 */
		if ($request['action'] === 'tokens') {
			
			// Get v1.1 and v1.2 posts tokens
			$sql = "SELECT * FROM ".$wpdb->prefix."eni_requests WHERE status='A'";
			$rows = $wpdb->get_results($sql);
			$total_rows = count($rows);
			if ($total_rows > 0) {
				foreach ($rows as $row) {
					// Add token, email and price to post meta data (since 1.5)
					$buyers_list = get_post_meta($row->post_id, '_enico_buyers_list', true);
					if ( !is_array($buyers_list) || empty($buyers_list) ) {
						$buyers_list = array();
					}
					$buyers_list[] = array('token' => $row->token, 'buyer_email' => $row->email, 'price' => $price);
					update_post_meta($row->post_id, '_enico_buyers_list', $buyers_list);
				}
				$return = "Se importaron {$total_rows} tokens";
			} else {
				$return = "No se encontraron tokens para importar.";
			}
		}

		/**
		 * Migrate post meta
		 */
		if ($request['action'] === 'posts') {
			$sql = $wpdb->prepare(
				"SELECT * 
				FROM {$wpdb->prefix}eni_postpay");
			$rows = $wpdb->get_results($sql);
			$total_rows = count($rows);
			if ($total_rows > 0) {
				foreach ($rows as $row) {
				
					update_post_meta($row->post_id, '_enico_activate_payment', 'on');
	
					// If minimum price was set in previous version
					if ($row->min_price > 0) {
						// Activate the new version checkbox
						update_post_meta($row->post_id, '_enico_min_price', 'on');
						// Set the price
						update_post_meta($row->post_id, '_enico_custom_price', $row->min_price);
					} else {
	
						// Set default value if greater than 0; else set the value
						if ($row->val_default > 0)
							update_post_meta($row->post_id, '_enico_custom_price', $row->val_default);
						else
							update_post_meta($row->post_id, '_enico_custom_price', $row->value);
	
					}
				}
				$return = "Se migraron {$total_rows} configuraciones de articulos.";
			} else {
				$return = "No se encontraron configuraciones de articulos para migrar.";
			}
			
		}

		return rest_ensure_response($return);
	}

	/**
	 * Add menu item
	 * 
	 * @author Victor H. Morales <vmorales@mkdev.ar>
	 */
	public function custom_menu_page() {
		add_options_page(
			__( 'Énico Micropagos', $this->plugin_name ),
			__( 'Énico Micropagos', $this->plugin_name ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_options_page' )
		);
	}
	/**
	 * Render the options page for plugin
	 *
	 * @since  1.5.0
	 */
	public function display_options_page() {
		include_once 'partials/'.$this->plugin_name.'-admin-display.php';
	}
	
	/**
	 * Register all related settings of this plugin
	 *
	 * @since  1.5.0
	 */
	public function register_settings() {
		/** MercadoPago related settings */
		register_setting( $this->plugin_name, 'enico_mp_country' );
		register_setting( $this->plugin_name, 'enico_mp_token' );
		register_setting( $this->plugin_name, 'enico_mp_sandbox' );

		/** Payment box template related settings */
		register_setting( $this->plugin_name, 'enico_payperview_text' );
		register_setting( $this->plugin_name, 'enico_price_text' );
		register_setting( $this->plugin_name, 'enico_default_price' );

		/** Email related settings */
		register_setting( $this->plugin_name, 'enico_email_subject' );
		register_setting( $this->plugin_name, 'enico_email_body' );
	}

	/**
	 * Register the post custom meta box
	 *
	 * @author Victor H. Morales <vmorales@mkdev.ar>
	 */
	public function register_meta_boxes() {
		add_meta_box( 
			'enico-micropay', 
			__( 'Énico Micropagos', 'textdomain' ), 
			[$this, 'render_meta_box'], 
			'post', 
			'side',
			'high'
		);
	}

	/**
	 * Render the post custom meta box
	 *
	 * @author Victor H. Morales <vmorales@mkdev.ar>
	 *
	 * @param obj $post - The post object
	 */
	public function render_meta_box($post) {
		wp_nonce_field( 'enico_micropay_nonce_action', 'enico_micropay_nonce' );

		$enico_activate_payment = get_post_meta($post->ID, '_enico_activate_payment', true);
		$enico_default_price = get_post_meta($post->ID, '_enico_default_price', true);
		$enico_min_price = get_post_meta($post->ID, '_enico_min_price', true);
		$enico_custom_price = (get_post_meta($post->ID, '_enico_custom_price', true)) ?: get_option('enico_default_price');

		include_once 'partials/post-metabox.php';
	}

	/**
	 * Save the posts custom meta box fields
	 *
	 * @author Victor H. Morales <vmorales@mkdev.ar>
	 *
	 * @param number $post_id - the Post ID
	 */
	public function save_meta_box($post_id) {
		// Add nonce for security and authentication.
		$nonce_name   = isset( $_POST['enico_micropay_nonce'] ) ? $_POST['enico_micropay_nonce'] : '';
		$nonce_action = 'enico_micropay_nonce_action';

		// Check if nonce is valid.
		if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
			return;
		}

		// Check if user has permissions to save data.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check if not an autosave.
		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Check if not a revision.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Santize information and save meta fields
		$activate_payment = sanitize_text_field($_REQUEST['enico_activate_payment']);
		if ($activate_payment == 'on' || $activate_payment == 'off')
			update_post_meta($post_id, '_enico_activate_payment', $activate_payment);

		$default_price = sanitize_text_field($_REQUEST['enico_default_price']);
		if (is_numeric($default_price) || !$default_price)
			update_post_meta($post_id, '_enico_default_price', $default_price);
		
		$min_price = sanitize_text_field($_REQUEST['enico_min_price']);
		if ($min_price == 'on' || $min_price == 'off')
			update_post_meta($post_id, '_enico_min_price', $min_price);
		
		$custom_price = sanitize_text_field($_REQUEST['enico_custom_price']);
		if (is_numeric($custom_price) || !$custom_price)
			update_post_meta($post_id, '_enico_custom_price', $custom_price);
	}

	/**
	 * Avoid subscribers to access admin dashboard
	 *
	 * @author Victor H. Morales <vmorales@mkdev.ar>
	 */
	public function redirect_subscribers() {
		$current_user   = wp_get_current_user();
        $role_name      = $current_user->roles[0];
        if ( $role_name === 'subscriber' ) {
			wp_redirect( site_url() );
			exit;
        }
	}

	/**
	 * Remove the wordpress admin bar to subscribers
	 *
	 * @author Victor H. Morales <vmorales@mkdev.ar>
	 *
	 * @param string $content
	 */
	public function hide_admin_bar_subscribers($content) {
		$current_user   = wp_get_current_user();
		$role_name      = $current_user->roles[0];
		return ( $role_name === 'subscriber' ) ? false : $content ;
	}

	/**
	 * Add links to the plugin action in the plugin list
	 *
	 * @author Victor H. Morales <vmorales@mkdev.ar>
	 *
	 * @param string $links
	 */
	function plugin_action_links( $links ) {
		// Build and escape the URL.
		$url = esc_url( add_query_arg(
			'page',
			$this->plugin_name,
			get_admin_url() . 'admin.php'
		) );
		// Create the link.
		$settings_link = "<a href='$url'>" . __( 'Settings' ) . '</a>';
		// Adds the link to the end of the array.
		array_unshift(
			$links,
			$settings_link
		);
		return $links;
	}
	
}
