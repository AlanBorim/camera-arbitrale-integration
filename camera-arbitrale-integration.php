<?php
/*
Plugin Name: Camera Arbitrale Integration
Description: Sincroniza usuários da base externa da Camera Arbitrale com o WordPress/LearnPress.
Version: 1.2
Author: Você
*/

if (!defined('ABSPATH')) exit;

add_action('admin_menu', 'cai_admin_menu');
add_action('admin_enqueue_scripts', 'cai_admin_scripts');
add_action('wp_ajax_cai_sync_users', 'cai_sincronizar_usuarios');
add_action('admin_init', 'cai_register_settings');

function cai_admin_menu()
{
    add_menu_page(
        'Camera Arbitrale Integration',
        'Camera Sync',
        'manage_options',
        'camera-sync',
        'cai_admin_page',
        'dashicons-update',
        30
    );
}

function cai_register_settings()
{
    register_setting('cai_config_options', 'cai_config_options');
}

function cai_admin_scripts($hook)
{
    if ($hook === 'toplevel_page_camera-sync') {
        wp_enqueue_script('cai-admin', plugin_dir_url(__FILE__) . 'js/admin.js', array('jquery'), null, true);
        wp_localize_script('cai-admin', 'cai_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('cai_sync_nonce')
        ));
    }
}

function cai_admin_page()
{
    $last_id = get_option('cai_ultimo_id_sincronizado', 0);
    $last_time = get_option('cai_ultima_sincronizacao', 'Nunca');
    $options = get_option('cai_config_options');
    $token = isset($options['token']) ? $options['token'] : '';

    echo '<div class="wrap">';
    echo '<h1>Sincronização de Usuários</h1>';
    echo '<p><strong>Último ID sincronizado:</strong> ' . esc_html($last_id) . '</p>';
    echo '<p><strong>Última sincronização:</strong> ' . esc_html($last_time) . '</p>';
    echo '<form method="post" action="options.php">';
    settings_fields('cai_config_options');
    do_settings_sections('cai_config_options');
    echo '<table class="form-table">';
    echo '<tr><th scope="row">Token de Autenticação</th><td><input type="text" name="cai_config_options[token]" value="' . esc_attr($token) . '" class="regular-text" /></td></tr>';
    echo '</table>';
    submit_button('Salvar Configurações');
    echo '</form>';
    echo '<hr />';
    echo '<button id="cai-sync-btn" class="button button-primary">Sincronizar Agora</button>';
    echo '<div id="cai-sync-status"></div>';
    echo '</div>';
}

function cai_sincronizar_usuarios()
{
    check_ajax_referer('cai_sync_nonce', 'nonce');

    $last_id = get_option('cai_ultimo_id_sincronizado', 0);
    $options = get_option('cai_config_options');
    $token = isset($options['token']) ? $options['token'] : '';

    if (empty($token)) {
        wp_send_json_error('Token não configurado.');
    }

    // Atualizar URL para o endereço correto
    $url = 'https://www.camera-arbitrale.it/it/export_users.php?last_id=' . $last_id;

    $args = array(
        'headers' => array(
            'X-Auth-Token' => $token
        )
    );

    $response = wp_remote_get($url, $args);

    if (is_wp_error($response)) {
        wp_send_json_error('Erro ao buscar dados: ' . $response->get_error_message());
    }

    $body = wp_remote_retrieve_body($response);
    $usuarios = json_decode($body, true);

    if (!is_array($usuarios)) {
        wp_send_json_error('Formato de dados inválido.');
    }

    $novo_ultimo_id = $last_id;
    $importados = 0;

    foreach ($usuarios as $user) {
        if (!isset($user['CI_Email'], $user['CI_Nome'], $user['CI_Id'])) {
            continue; // ignora registros malformados
        }

        $email = sanitize_email($user['CI_Email']);
        $nome  = sanitize_text_field($user['CI_Nome'] . ' ' . $user['CI_Cognome']);
        $id    = intval($user['CI_Id']);

        if (!$email || !$id) continue;

        if (email_exists($email)) {
            $user_obj = get_user_by('email', $email);
            $user_id = $user_obj->ID;
            wp_update_user(array('ID' => $user_id, 'display_name' => $nome));
        } else {
            $senha_temporaria = wp_generate_password();
            $user_id = wp_create_user($email, $senha_temporaria, $email);
            wp_update_user(array('ID' => $user_id, 'display_name' => $nome));
            update_user_meta($user_id, 'first_login', 1);
            wp_mail($email, 'Benvenuto sulla piattaforma', "Ciao $nome,\n\nIl tuo accesso è stato creato. Login: $email | Password: $senha_temporaria\nAccedi alla piattaforma e modifica la tua password al primo accesso.");
        }

        if ($id > $novo_ultimo_id) {
            $novo_ultimo_id = $id;
        }

        $importados++;
    }

    if ($novo_ultimo_id > $last_id) {
        update_option('cai_ultimo_id_sincronizado', $novo_ultimo_id);
        update_option('cai_ultima_sincronizacao', current_time('mysql'));
        wp_send_json_success("Usuários sincronizados com sucesso. Total importados: $importados. Último ID: $novo_ultimo_id");
    } else {
        wp_send_json_success("Nenhum novo usuário importado. Último ID continua: $last_id");
    }
}


add_action('wp_login', function ($user_login, $user) {
    if (get_user_meta($user->ID, 'first_login', true)) {
        delete_user_meta($user->ID, 'first_login');
        wp_redirect(site_url('/minha-conta/edit-account/'));
        exit;
    }
}, 10, 2);
