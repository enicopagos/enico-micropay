<?php

/**
 * Fired during plugin activation
 *
 * @link       https://enico.info
 * @since      1.5.0
 *
 * @package    Enico_Micropay
 * @subpackage Enico_Micropay/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.5.0
 * @package    Enico_Micropay
 * @subpackage Enico_Micropay/includes
 * @author     Enico <info@enico.info>
 */
class Enico_Micropay_Activator {
	
	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.5.0
	 */
	public static function activate() {
		/*
		if (get_option('enipay_value') == "")
			update_option('enipay_value', '99.0');

		if (get_option('enipay_mp_key') == "")
			update_option('enipay_mp_key', 'XXXX');
		
		if (get_option('enipay_mp_url') == "")
			update_option('enipay_mp_url', 'wwww.mercadopago.com.ar');
		
		if (get_option('enipay_mp_moneda') == "")
			update_option('enipay_mp_moneda', 'ARS');
		
		if (get_option('enipay_version') == "")
			update_option('enipay_version', '2.0.0');
		
		if (get_option('enipay_mp_prod') == "")
			update_option('enipay_mp_prod', '0');

		if (get_option('enipay_payperview_text') == "")
			update_option('enipay_payperview_text', 'Este contenido es pago. Por favor, colabora con el medio y con el autor.');
		
		if (get_option('enipay_readmore_text') == "")
			update_option('enipay_readmore_text', 'Leelo por ');
		
		if (get_option('enipay_email_subject') == "")
			update_option('enipay_email_subject', '%%TITLE%%');
		
		if (get_option('enipay_email_body') == "")
			update_option('enipay_email_body', '¡Hola!\r\n\r\nCompraste el artículo %%TITLE%% en %%SITE_NAME%%. Para leerlo cuando quieras, en cualquiera de tus dispositivos, ingresa aquí %%LINK%%\r\n \r\nQue disfrutes tu contenido de calidad. Saludos,\r\n\r\n%%SITE_NAME%%\r\n');
		*/
		
		if (get_option('enico_default_price') == "")
			update_option('enico_default_price', '99.0');

		if (get_option('enico_mp_token') == "")
			update_option('enico_mp_token', 'XXXX');
		
		if (get_option('enipay_mp_url') == "")
			update_option('enipay_mp_url', 'wwww.mercadopago.com.ar');
		
		if (get_option('enico_mp_country') == "")
			update_option('enico_mp_country', 'ARS');

		if (get_option('enico_payperview_text') == "")
			update_option('enico_payperview_text', 'Este contenido es pago. Por favor, colabora con el medio y con el autor.');
		
		if (get_option('enico_price_text') == "")
			update_option('enico_price_text', 'Leelo por ');
		
		if (get_option('enico_email_subject') == "")
			update_option('enico_email_subject', '%%TITLE%%');
		
		if (get_option('enico_email_body') == "")
			update_option('enico_email_body', '¡Hola!\r\n\r\nCompraste el artículo %%TITLE%% en %%SITE_NAME%%. Para leerlo cuando quieras, en cualquiera de tus dispositivos, ingresa aquí %%LINK%%\r\n \r\nQue disfrutes tu contenido de calidad. Saludos,\r\n\r\n%%SITE_NAME%%\r\n');

		// Create activation link page
		$activation_link_slug = 'enico-activation-link';

		$post_details = array(
			'post_title'    => 'Enlace de activación',
			'post_content'  => '[enico-activation-link]',
			'post_status'   => 'publish',
			'post_author'   => 1,
			'post_type' => 'page',
			'post_name' => $activation_link_slug
		);
		$page = get_page_by_path($activation_link_slug );
		if (empty($page))
			wp_insert_post( $post_details );
	}

}
