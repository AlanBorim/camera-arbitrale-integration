<?php
if (! defined('ABSPATH')) {
    exit;
}

add_action('rest_api_init', function () {
    register_rest_route('camera/v1', '/inscrever/', array(
        'methods' => 'POST',
        'callback' => 'cai_inscrever_usuario_no_curso',
        'permission_callback' => 'cai_validar_token'
    ));
});

function cai_validar_token($request)
{
    $headers = $request->get_headers();
    $token_recebido = isset($headers['token'][0]) ? $headers['token'][0] : '';

    // Defina sua chave secreta (também configure no site externo)
    $options = get_option('cai_config_options');
    $token_secreto = $options['token'] ?? '';


    return hash_equals($token_secreto, $token_recebido);
}

function cai_inscrever_usuario_no_curso(WP_REST_Request $request)
{

    if (!function_exists('learn_press_enroll_student')) {
        error_log('[CAI Plugin] Função learn_press_enroll_student não encontrada. Verifique se o LearnPress está ativo.');
    }
    
    $data = $request->get_json_params();

    $nome      = sanitize_text_field($data['nome'] ?? '');
    $email     = sanitize_email($data['email'] ?? '');
    $curso_id  = intval($data['curso_id'] ?? 0);
    $whatsapp  = sanitize_text_field($data['whatsapp'] ?? ''); // opcional

    if (empty($nome) || empty($email) || empty($curso_id)) {
        return new WP_REST_Response(['status' => 'erro', 'mensagem' => 'Dados incompletos.'], 400);
    }

    // Criação ou recuperação de usuário
    $user_id = cai_criar_ou_recuperar_usuario($nome, $email);

    if (is_wp_error($user_id)) {
        cai_log('Erro ao criar usuário: ' . $user_id->get_error_message());
        return new WP_REST_Response(['status' => 'erro', 'mensagem' => 'Erro ao criar usuário.'], 500);
    }

    // Matrícula no curso
    if (function_exists('learn_press_enroll_student')) {
        learn_press_enroll_student($user_id, $curso_id);
    } else {
        return new WP_REST_Response(['status' => 'erro', 'mensagem' => 'LearnPress não encontrado.'], 500);
    }

    // Envio de email com dados de login/curso
    cai_enviar_email_acesso($nome, $email, $curso_id);

    // (Opcional) Envio de WhatsApp via API externa
    if (! empty($whatsapp)) {
        cai_enviar_whatsapp($nome, $whatsapp, $curso_id);
    }

    cai_log("Usuário {$email} inscrito no curso {$curso_id}.");

    return new WP_REST_Response(['status' => 'ok', 'mensagem' => 'Usuário inscrito com sucesso.', 'user_id' => $user_id], 200);
}
