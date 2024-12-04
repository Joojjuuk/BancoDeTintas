<?php

$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "banco de tintas";

try {
    
    $pdo = new PDO("mysql:host=$servidor;dbname=$banco", $usuario, $senha);
    
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch(PDOException $e) {
    echo "Falha na conexão: " . $e->getMessage();
}
?>