# === Camera Arbitrale Integration ===
Contributors: Alan Borim
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4+
Version: 1.0.0

Plugin para integrar vendas do site camera-arbitrale.it com o LearnPress, criando usuários e matriculando automaticamente em cursos.

# == Configuração ==
1. Defina sua chave secreta em includes/endpoints.php (variável $token_secreto).
2. Configure o endpoint no site externo.

# == Endpoint ==
POST /wp-json/camera/v1/inscrever/

Headers:
token: SUA_CHAVE_SECRETA

Body JSON:
{
  "nome": "Nome do Aluno",
  "email": "email@exemplo.com",
  "curso_id": 123,
  "whatsapp": "5511999999999" // opcional
}
