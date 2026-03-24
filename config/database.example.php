<?php
// =============================================
// CONFIGURAÇÃO DO BANCO DE DADOS
// =============================================
// Copie este arquivo como "database.php" e preencha com seus dados
//
// LOCAL (XAMPP):
//   $host     = 'localhost';
//   $dbname   = 'sistema_eventos';
//   $username = 'root';
//   $password = '';
//
// HOSTINGER:
//   $host     = 'localhost'; (geralmente localhost na Hostinger)
//   $dbname   = 'u123456789_sistema_eventos';
//   $username = 'u123456789_admin';
//   $password = 'SUA_SENHA_AQUI';
// =============================================

$host = 'localhost';
$dbname = 'NOME_DO_BANCO';
$username = 'USUARIO';
$password = 'SENHA';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die(json_encode([
        'success' => false,
        'message' => 'Erro de conexão com o banco de dados',
        'error' => $e->getMessage()
    ]));
}
