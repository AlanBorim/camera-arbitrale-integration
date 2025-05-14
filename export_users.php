<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexão com a base de dados local
$host = 'localhost';
$user = 'camearbi';
$pass = 'TgH0w#pU2vSab4';
$dbname = 'camearbi';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die('Erro na conexão: ' . $conn->connect_error);
}
$conn->set_charset("utf8");

// 🔐 Validação de token via Header
$headers = getallheaders();
$token_recebido = $headers['X-Auth-Token'] ?? '';
$token_correto = 'bfe472b418bda9a4f0342e8f9a56d176e28ca34e17ef71232967477fda2b5e6d';

if ($token_recebido !== $token_correto) {
    http_response_code(403);
    echo json_encode(['erro' => 'Token non valido']);
    exit;
}

// Parâmetros de controle
$last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;
$limit   = isset($_GET['limit']) ? intval($_GET['limit']) : 100;

// Consulta
$sql = "SELECT * FROM CA_Corsi_Utenti WHERE CI_Id > $last_id ORDER BY CI_Id ASC LIMIT $limit";

$result = $conn->query($sql);

$usuarios = [];
while ($row = $result->fetch_assoc()) {
    $usuarios[] = $row;
}

header('Content-Type: application/json');
echo json_encode($usuarios, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
