<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://enico.info
 * @since      1.5.0
 *
 * @package    Enico_Micropay
 * @subpackage Enico_Micropay/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Enico_Micropay
 * @subpackage Enico_Micropay/public
 * @author     Enico <info@enico.info>
 */
class Enico_Micropay_Public {

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.5.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->activation_link_slug = 'enico-activation-link';
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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
		global $post;
		$is_payment_active = get_post_meta($post->ID, '_enico_activate_payment', true);
		if ($is_payment_active || $post->post_name === $this->activation_link_slug) {
			wp_enqueue_style( 
				$this->plugin_name, 
				plugin_dir_url( __DIR__ ) . 'dist/enico-micropay.css', 
				array(), 
				time(), 
				'all' 
			);
		}
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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
		global $post;
		$is_payment_active = get_post_meta($post->ID, '_enico_activate_payment', true);
		if ($is_payment_active || $post->post_name === $this->activation_link_slug) {
			wp_enqueue_script( 
				$this->plugin_name . '-public', 
				plugin_dir_url( __DIR__ ) . 'dist/enico-micropay.js', 
				array( 'jquery' ), 
				time(), 
				true 
			);
			wp_localize_script( 
				$this->plugin_name . '-public', 
				'enico_vars', array(
					'pluginName' => $this->plugin_name,
					'restURL' => rest_url() . $this->plugin_name . '/v1',
					'restNonce' => wp_create_nonce( 'wp_rest' )
				)
			);
		}

	}

	/**
	 * Filters the <title> HTML tag
	 * Users who are redirected to the "enico-activation-link" page will see the "post to read" title.
	 * This allow users to bookmark this activation link with the post title.
	 *
	 * @param string $title
	 */
	public function document_title($title, $sep = false) {
		global $post;

		// Get the post ID
		$pid = $_REQUEST['pid'];
		
		if ($post->post_name === $this->activation_link_slug) {
			
			// Get the "post to read" from $pid
			// Filters the page title
			$post2read = get_post($pid);
			$title = (empty($post2read)) ? 'El post no existe.' : $post2read->post_title ;
			$title .= " | " . get_bloginfo('name');
		}
		
		return $title;
	}

	/**
	 * Filter the post content
	 * If payment is active for this post, will limit the content and show the "pay" form
	 *
	 * @author Victor H. Morales <vmorales@mkdev.ar>
	 *
	 * @param string $content
	 */
	public function the_content($content) {
		global $post;
		
		/** Get the post meta values */
		$is_payment_active = get_post_meta($post->ID, '_enico_activate_payment', true);
		$price = get_post_meta($post->ID,'_enico_custom_price', true);
		$val_default = get_post_meta($post->ID,'_enico_default_price', true);
		$min_price = get_post_meta($post->ID,'_enico_min_price', true);

		// Check if token exists on post buyers list
		$buyers_list = get_post_meta($post->ID, '_enico_buyers_list', true);
		$search_token = false;
		if (is_array($buyers_list)) {
			// Get COOKIE token
			$token = '';
			$readerPost = json_decode(stripslashes($_COOKIE['enico-reader-post-' . $post->ID]), true);
			//print_r($readerPost);
			if (is_array($readerPost)) {
				$token = $readerPost['token'];
			}
			$search_token = array_search($token, array_column($buyers_list, 'token'));
		}
		/** Check if this post has the enico payment activated */
		if ($is_payment_active && !is_numeric($search_token)) {

			/** count words until MORE tag; default 1000 words */
			$more_tag = strpos($content, '<span id="more-') ?: 1000;
			/** cut the content till $more_tag words */
			$limited_content = substr($content, 0, $more_tag);

			/** original content total words */
			$total_words = strlen($content);
			/** limited content total words  */
			$public_words = strlen($limited_content);
			/** calculate percentage left to read */
			$percent = 100-floor(($public_words*100)/$total_words);

			$min_price = get_post_meta($post->ID, '_enico_min_price', true);
			$price = (get_post_meta($post->ID, '_enico_custom_price', true)) ?: get_option('enico_default_price');

			ob_start();
			?>
			<div class="enico-gradient"></div>
			<div class="enico-bg-white">
				<div id="enicoPaymentBox" class="enico-container enico-w-full enico-px-5 md:enico-px-0 enico-pb-10 md:enico-py-10 enico-mx-auto enico-text-black">
					<div class="md:enico-flex">
						<div class="md:enico-flex-0 md:enico-mr-10 enico-text-center enico-mb-5 md:enico-mb-0">
							<img src="<?php echo plugins_url('img/logo-enico.png', dirname(__FILE__)); ?>" width="100" class="enico-w-12">
						</div>
						<div class="md:enico-flex-1">
							<form id="enicoPaymentForm" method="post" action="#">
								<input type="hidden" id="enicoPostID" name="enicoPostID" value="<?php echo $post->ID; ?>">
								
								<?php echo get_option('enico_payperview_text'); ?>

								<div id="enicoMessage" class="enico-message enico-hidden"></div>

								<div class="enico-flex enico-flex-wrap enico-items-center enico-mb-4">
									<div class="enico-flex-none enico-mr-4">
										<?php echo get_option('enico_price_text'); ?> $
									</div>
									<div class="enico-flex-1">
										<input type="number" id="enicoPostPrice" step="1.00" min="<?php echo $price; ?>" value='<?php echo $price; ?>' class="enico-w-full enico-m-0" name="enico_price"<?php if (!$min_price )  { echo " readonly"; } ?> required="required">
									</div>
								</div>

								<div class="enico-flex enico-flex-wrap enico-items-center enico-mb-4">
									<div class="enico-flex-1 enico-mr-4">
										<input type="email" id="enicoUserEmail" class="enico-w-full enico-m-0" name="enicoUserEmail" required="required" placeholder="Ingrese su correo electr&oacute;nico">
									</div>
									<div class="enico-flex-0">
										<input type="submit" id="enicoPayBtn" class="enico-button enico-m-0" value="Comprar">
									</div>
								</div>
								
								<p class="enico-font-bold">Queda por leer el <?php echo $percent; ?>% del art&iacute;culo.</p>
							</form>
						</div>
					</div>
				</div>
			</div>
			<?php
			$html = ob_get_contents();
			ob_end_clean();

			return $limited_content . $html;
		}

		return $content;
	}

	/**
	 * Set "enico-reader-post-$pid" COOKIE
	 * This COOKIE is checked on "the_content" filter to show the full post content or the "payment box" 
	 *
	 * @author Victor H. Morales <vmorales@mkdev.ar>
	 */
	public function process_link_validation() {
		global $post;
		
		if ($post->post_name === $this->activation_link_slug) {
			$token = $_REQUEST['token'];
			$post_id = $_REQUEST['pid'];
			$uemail = $_REQUEST['uemail'];
			
			// Not enough information
			if (empty($token) || !is_numeric($post_id) || empty($uemail)) {
				return;
			}

			// Post doesn't exists
			$post2read = get_post($post_id);
			if (empty($post2read)) {
				return;
			}

			// Check if token exists on post buyers list
			$buyers_list = get_post_meta($post_id, '_enico_buyers_list', true);
			$search_token = array_search($token, array_column($buyers_list, 'token'));
			
			// If token doesn't exists
			if (!is_numeric($search_token))
			{
				return;
			}

			// Set cookie if not already set
			if (!isset($_COOKIE['enico-reader-post-' . $post_id])) {
				$setPost2Reader = array(
					'token' => $token,
					'uemail' => $uemail
				);
				
				setcookie('enico-reader-post-' . $post_id, json_encode($setPost2Reader), current_time( 'timestamp' ) + (180 * DAY_IN_SECONDS), COOKIEPATH);
			}

			// Reridect if Safari
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
			if (strpos($user_agent, 'Safari') && !strpos($user_agent, 'Chrome')) {
				$redirect_to = get_the_permalink($post_id);
				wp_redirect($redirect_to);
				exit;
			}
		}
	}

	public function register_shortcodes() {
		add_shortcode( 'enico-activation-link', [$this, 'enico_activation_link_content'] );
	}

	public function enico_activation_link_content($atts) {

		$token = $_REQUEST['token'];
		$post_id = $_REQUEST['pid'];
		$uemail = $_REQUEST['uemail'];

		// Not enough information
		if (empty($token) || !is_numeric($post_id) || empty($uemail)) {
			ob_start();
			?>
			<div class="enico-message enico-error">
				Falta informaci&oacute;n. Verifique que el enlace de activaci&oacute;n sea el correcto.
			</div>
			<p><a href="<?php echo home_url(); ?>" class="enico-button">Volver a la p&aacute;gina principal</a></p>
			<?php
			return ob_get_clean();
		}

		// Post doesn't exists
		$post2read = get_post($post_id);
		if (empty($post2read)) {
			ob_start();
			?>
			<div class="enico-message enico-error">
				El art&iacute;culo que intenta validar no existe. Verifique que el enlace de activaci&oacute;n sea el correcto.
			</div>
			<p><a href="<?php echo home_url(); ?>" class="enico-button">Volver a la p&aacute;gina principal</a></p>
			<?php
			return ob_get_clean();
		}

		// Check if token exists on post buyers list
		$buyers_list = get_post_meta($post_id, '_enico_buyers_list', true);
		$search_token = array_search($token, array_column($buyers_list, 'token'));
		
		// If token doesn't exists
		if (!is_numeric($search_token)) {
			ob_start();
			?>
			<div class="enico-message enico-error">
				La clave token proporcionada no es v&aacute;lida. Verifique que el enlace de activaci&oacute;n sea el correcto.
			</div>
			<p><a href="<?php echo home_url(); ?>" class="enico-button">Volver a la p&aacute;gina principal</a></p>
			<?php
			return ob_get_clean();
		}

		ob_start();
		?>
		<p>
			<strong>Guarda esta p&aacute;gina en tus favoritos y vuelve a acceder cuando quieras 👇</strong><br>
			Haz click o presiona en la estrella en la barra de direcciones de tu navegador
		</p>
		<p>
			<strong>Encuentra el enlace siempre en tu mail:</strong><br>
			Tambi&eacute;n te lo enviamos por email a la direcci&oacute;n que ingresaste previamente. Si no lo recibiste, verifica tu carpeta de spam.
		</p>
		<p>&iexcl;Ahora s&iacute;!</p>
		<p><a href="<?php echo get_the_permalink($post_id); ?>" class="enico-button">Continuar al art&iacute;culo</a></p>
		<?php
		return ob_get_clean();
	}
}
