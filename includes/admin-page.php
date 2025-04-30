<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', 'cai_add_admin_menu');
add_action('admin_init', 'cai_settings_init');

function cai_add_admin_menu() {
    add_menu_page(
        'Integração Cursos',
        'Integração Cursos',
        'manage_options',
        'cai_config',
        'cai_config_page',
        'dashicons-forms',
        100
    );
}

function cai_settings_init() {
    register_setting('cai_config_group', 'cai_config_options');

    add_settings_section(
        'cai_config_section',
        'Configurações da Integração',
        null,
        'cai_config'
    );

    add_settings_field(
        'token',
        'Token Secreto',
        'cai_field_token_render',
        'cai_config',
        'cai_config_section'
    );

    add_settings_field(
        'whatsapp_url',
        'URL da API de WhatsApp',
        'cai_field_whatsapp_url_render',
        'cai_config',
        'cai_config_section'
    );

    add_settings_field(
        'whatsapp_enable',
        'Ativar envio de WhatsApp',
        'cai_field_whatsapp_enable_render',
        'cai_config',
        'cai_config_section'
    );
}

function cai_field_token_render() {
    $options = get_option('cai_config_options');
    ?>
    <input type='text' name='cai_config_options[token]' value='<?php echo esc_attr($options['token'] ?? ''); ?>' style="width: 400px;">
    <?php
}

function cai_field_whatsapp_url_render() {
    $options = get_option('cai_config_options');
    ?>
    <input type='text' name='cai_config_options[whatsapp_url]' value='<?php echo esc_attr($options['whatsapp_url'] ?? ''); ?>' style="width: 400px;">
    <?php
}

function cai_field_whatsapp_enable_render() {
    $options = get_option('cai_config_options');
    ?>
    <input type='checkbox' name='cai_config_options[whatsapp_enable]' <?php checked($options['whatsapp_enable'] ?? '', 'on'); ?>>
    <?php
}

function cai_config_page() {
    ?>
    <div class="wrap">
        <h1>Integração com Sistema Externo</h1>
        <form action='options.php' method='post'>
            <?php
            settings_fields('cai_config_group');
            do_settings_sections('cai_config');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
