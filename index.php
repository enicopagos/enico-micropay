<?php // Silence is golden


/**
 * This script will be deprecated in next versions
 * 
 * This is only created to guarantee the v1.1 and v1.2 versions and must be removed in following versions
 */
require_once('../../../wp-load.php');

if ($_REQUEST['eni_action'] === 'link') {
    global $wpdb;

    $token = $_REQUEST['eni_token'];
    $redirect_to = site_url('enico-activation-link');

    $sql = $wpdb->prepare(
        "SELECT token, post_id, email, status, fecha_creacion 
        FROM {$wpdb->prefix}eni_requests 
        WHERE token = %s AND status = %s", 
        array($token, 'A')
    );
    $rows = $wpdb->get_results($sql);
    if ($rows) {

        // Redirect to new activation link
        $redirect_to .= '?token=' . urlencode($rows[0]->token) . '&pid=' . $rows[0]->post_id . '&uemail=' . $rows[0]->email;
       
    }
    // https://enico.info/wp-content/plugins/enico-micropay/?eni_token=$2y$10$aotZ0qARjYFYIObpE0286OIJjkpUPuJgSAkl1rJJnXXTVW065zBIK&eni_action=link
    wp_redirect($redirect_to);
    exit;
}