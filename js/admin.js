jQuery(document).ready(function ($) {
    $('#cai-sync-btn').click(function () {
        const statusBox = $('#cai-sync-status');
        statusBox.html('<p>🔄 Iniciando sincronização de usuários...</p>');

        // Etapa 1: Preparando requisição
        statusBox.append('<p>📦 Preparando dados da requisição AJAX...</p>');

        $.post(cai_ajax.ajax_url, {
            action: 'cai_sync_users',
            nonce: cai_ajax.nonce
        })
        .done(function (response) {
            // Etapa 2: Requisição AJAX bem-sucedida
            statusBox.append('<p>✅ Requisição enviada com sucesso.</p>');

            if (response.success) {
                // Etapa 3: Dados recebidos e processados com sucesso
                statusBox.append('<p>📥 Dados recebidos com sucesso.</p>');
                statusBox.append('<p style="color: green;">' + response.data + '</p>');

                // Opcional: Atualizar página após alguns segundos
                setTimeout(() => {
                    location.reload();
                }, 3000);
            } else {
                // Etapa 3: Erro no processamento dos dados
                statusBox.append('<p>⚠️ Erro durante a sincronização.</p>');
                statusBox.append('<p style="color: red;">' + response.data + '</p>');
            }
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            // Etapa 2: Erro na requisição AJAX
            statusBox.append('<p>❌ Falha na requisição AJAX.</p>');
            statusBox.append('<p style="color: red;">Erro: ' + textStatus + ' - ' + errorThrown + '</p>');
        });
    });
});
