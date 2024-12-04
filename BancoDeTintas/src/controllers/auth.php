<?php

function login($email, $senha) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    try {
        $pdo = new PDO("mysql:host=localhost;dbname=Banco de Tintas", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && $senha === $usuario['senha']) {
            $_SESSION['usuario_logado'] = true;
            $_SESSION['email'] = $email;
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            
            if (isset($_SESSION['url_anterior']) && $_SESSION['url_anterior'] !== '/BancoDeTintas/src/views/enter/user/userlogin.php') {
                $urlAnterior = $_SESSION['url_anterior'];
                unset($_SESSION['url_anterior']);
                header("Location: $urlAnterior");
            } else {
                header("Location: /BancoDeTintas/src/views/home.php");
            }
            exit;
        } else {
            echo "<script>alert('Login ou senha incorretos');</script>";
        }
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}
function loginAdm($email_inst, $Senha) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=Banco de Tintas", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT * FROM adm WHERE email_inst = :email_inst");
        $stmt->bindParam(':email_inst', $email_inst);
        $stmt->execute();

        $adm = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($adm && $Senha === $adm['senha']) {
            $_SESSION['adm_logado'] = true;
            $_SESSION['email_inst'] = $email_inst;
            if (isset($_SESSION['url_anterior']) && $_SESSION['url_anterior'] !== '/BancoDeTintas/src/views/enter/adm/admlogin.php') {
                $urlAnterior = $_SESSION['url_anterior'];
                unset($_SESSION['url_anterior']);
                header("Location: $urlAnterior");
            } else {
                header("Location: /BancoDeTintas/src/views/adm/catalogo.php");
            }
            exit;
        } else {
            echo "<script>alert('Login ou senha incorretos');</script>";
        }
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}
function verificarSessao() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['usuario_logado'])) {
        if (!isset($_SESSION['url_anterior']) || $_SESSION['url_anterior'] !== '/BancoDeTintas/src/views/userlogin.php') {
            $_SESSION['url_anterior'] = $_SERVER['REQUEST_URI'];
        }
        header("Location: /BancoDeTintas/src/views/enter/user/userlogin.php");
        exit;
    }
}
function logoutUser() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_unset(); 
    session_destroy();
    header("Location: /BancoDeTintas/src/views/home.php");
    exit;
}

function verificarAdm() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['adm_logado'])) {
        if (!isset($_SESSION['url_anterior']) || $_SESSION['url_anterior'] !== '/BancoDeTintas/src/views/admlogin.php') {
            $_SESSION['url_anterior'] = $_SERVER['REQUEST_URI'];
        }
        header("Location: /BancoDeTintas/src/views/enter/adm/admlogin.php");
        exit;
    }
}


