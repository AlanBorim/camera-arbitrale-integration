<?php
if (! defined('ABSPATH')) {
    exit;
}

function cai_criar_ou_recuperar_usuario($nome, $email)
{
    if (email_exists($email)) {
        $user = get_user_by('email', $email);
        return $user->ID;
    }

    $senha = wp_generate_password(12, true);
    $user_id = wp_create_user($email, $senha, $email);

    if (is_wp_error($user_id)) {
        return $user_id;
    }

    wp_update_user([
        'ID'           => $user_id,
        'display_name' => $nome,
        'first_name'   => $nome,
    ]);

    // Guarda senha gerada para envio
    update_user_meta($user_id, 'cai_senha_gerada', $senha);

    return $user_id;
}

function cai_enviar_email_acesso($nome, $email, $curso_id)
{
    $senha = get_user_meta(email_exists($email), 'cai_senha_gerada', true);
    $curso = get_post($curso_id);

    $assunto = "Acesso ao curso {$curso->post_title}";
    $mensagem = "Olá, {$nome}!\n\nSeu acesso foi criado para o curso: {$curso->post_title}.\n\nLogin: {$email}\nSenha: {$senha}\n\nAcesse: " . get_permalink($curso_id) . "\n\nBom aprendizado!";
    wp_mail($email, $assunto, $mensagem);
}

function cai_enviar_whatsapp($nome, $whatsapp, $curso_id)
{
    $curso = get_post($curso_id);
    $mensagem = "Olá, {$nome}! Você foi matriculado no curso {$curso->post_title}. Confira seu e-mail para mais detalhes de acesso.";

    $options = get_option('cai_config_options');
    $api_url = $options['whatsapp_url'] ?? '';

    if (empty($api_url) || ($options['whatsapp_enable'] ?? '') !== 'on') {
        return;
    }

    $body = [
        'numero'   => $whatsapp,
        'mensagem' => $mensagem
    ];
    wp_remote_post($api_url, [
        'body'    => json_encode($body),
        'headers' => ['Content-Type' => 'application/json']
    ]);
}
