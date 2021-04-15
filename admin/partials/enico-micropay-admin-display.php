<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://enico.info
 * @since      1.5.0
 *
 * @package    Enico_Micropay
 * @subpackage Enico_Micropay/admin/partials
 */
?>

<div id="<?php echo $this->plugin_name; ?>" class="wrap">
<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

<form method="post" action="options.php">
<?php settings_fields( $this->plugin_name ); ?>

<h2>Opciones Mercado Pago</h2>
<table class="form-table">
    <tbody>
        <tr valign="top" id="price">
            <th scope="row">Pa&iacute;s</th>
            <td>
                <select name="enico_mp_country">
                    <option>Elegir</option>
                    <option value="ARS"<?php echo selected(get_option('enico_mp_country'), 'ARS'); ?>>Argentina</option>
                    <option value="BRL"<?php echo selected(get_option('enico_mp_country'), 'BRL'); ?>>Brasil</option>
                    <option value="CLP"<?php echo selected(get_option('enico_mp_country'), 'CLP'); ?>>Chile</option>
                    <option value="COP"<?php echo selected(get_option('enico_mp_country'), 'COP'); ?>>Colombia</option>
                    <option value="MXN"<?php echo selected(get_option('enico_mp_country'), 'MXN'); ?>>Mexico</option>
                    <option value="PEN"<?php echo selected(get_option('enico_mp_country'), 'PEN'); ?>>Perú</option>
                    <option value="UYU"<?php echo selected(get_option('enico_mp_country'), 'UYU'); ?>>Uruguay</option>
                </select>
                <p class="description">Selecciona el pa&iacute;s en el que est&aacute; creada tu cuenta de Mercado Pago.</p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">Token de MercadoPago </th>
            <td>
                <input type="text" name="enico_mp_token" value="<?php echo get_option('enico_mp_token'); ?>">
                <p class="description">Este Token te permitir&aacute; generar el link de pago. Debes gestionarlo en la p&aacute;gina de MercadoPago. <a href='https://www.mercadopago.com.ar/developers/es/guides/faqs/credentials/' target='_blank'>TUTORIAL</a>.</p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">Sandbox MercadoPago </th>
            <td>
                <label><input type="checkbox" name="enico_mp_sandbox"<?php checked(get_option('enico_mp_sandbox'), 'on', true); ?>> Mercadopago en modo de desarrollo</label>
            </td>
        </tr>
    </tbody>
</table>

<h2>Generales</h2>
<table class="form-table">
    <tbody>
        <tr valign="top">
            <th scope="row">Invitaci&oacute;n al lector</th>
            <td>
                <?php wp_editor(get_option('enico_payperview_text'), "enico_payperview_text", array('textarea_rows' => 10)); ?>
                <p class="description">Escribe un texto para llamar a tus lectores a comprar el art&iacute;culo.</p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">Texto del Precio</th>
            <td>
                <input type="text" name="enico_price_text" value="<?php echo get_option('enico_price_text'); ?>" />
                <p class="description">Escribe el texto que se leer&aacute; junto al precio.</p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">Precio predefinido</th>
            <td>
                <input type="number" step="0.01" name="enico_default_price" value="<?php echo get_option('enico_default_price'); ?>" />
                <p class="description">Este valor se mostrar&aacute; por defecto. Si lo deseas, podr&aacute; modificarlo en cada una de las entradas.  </p>
            </td>
        </tr>
    </tbody>
</table>

<h2>Opciones Email</h2>
<table class="form-table">
    <tbody>
        <tr valign="top">
            <th scope="row">Asunto del correo electr&oacute;nico</th>
            <td>
                <input type="text" name="enico_email_subject" value="<?php echo get_option('enico_email_subject'); ?>" />
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">Texto del correo electr&oacute;nico</th>
            <td>
                <?php wp_editor(get_option('enico_email_body'), "enico_email_body"); ?>
                <ul>
                    <li><b>%%SITE_NAME%%</b>: Nombre de tu sitio web</li>
                    <li><b>%%TITLE%%</b>: T&iacute;tulo del art&iacute;culo</li>
                    <li><b>%%LINK%%</b>: Enlace &uacute;nico para recuperar el art&iacute;culo</li>
                </ul>
            </td>
        </tr>
    </tbody>
</table>


<?php submit_button(); ?>
</form>

</div>