<?php
require('../../../../src/controllers/auth.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    login($email, $senha);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>PHPaintHub - Cadastro</title>
    <style>
    /* Estilos Gerais */
    html, body {
        height: 100%;
        margin: 0;
        overflow-x: hidden; /* Impede a rolagem horizontal */
    }

    body {
        background: linear-gradient(#4163fc, #212123);
        color: #e0e1dd;
        font-family: "Poppins", sans-serif;
        display: flex;
        flex-direction: column;
        min-height: 100%; /* Garante que o fundo cubra toda a altura da página */
    }

    /* Main Form */
    main {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-grow: 1; /* Faz o conteúdo principal ocupar o restante da tela */
        background: rgba(0, 0, 0, 0.6);
    }

    .form-container {
        background: rgba(0, 0, 0, 0.7);
        padding: 40px;
        border-radius: 10px;
        box-shadow: 0 15px 25px rgba(13, 27, 42, 0.5);
        width: 100%;
        max-width: 400px;
    }

    .input-container {
        position: relative;
        margin-bottom: 30px;
    }

    input[type="text"],
    input[type="date"],
    input[type="email"],
    input[type="tel"],
    input[type="password"] {
        color: #e0e1dd;
        border: none;
        border-bottom: 1px solid #e0e1dd;
        background: transparent;
        outline: none;
        width: 100%;
        padding: 10px 0;
        font-size: 16px;
    }

    label {
        font-size: 16px;
        color: #e0e1dd;
        transition: 0.5s;
        position: absolute;
        pointer-events: none;
        left: 0;
        top: 10px;
    }

    input:focus + label,
    input:valid + label {
        color: blue;
        top: -10px;
        left: 0;
        font-size: 12px;
    }

    button[type="submit"] {
        background: #4163fc;
        color: #e0e1dd;
        border: none;
        cursor: pointer;
        transition: background 0.5s;
        padding: 10px 20px;
        font-size: 16px;
        text-transform: uppercase;
    }

    button[type="submit"]:hover {
        background: #00177e;
        box-shadow: 0 0 5px #00177e, 0 0 25px #00177e, 0 0 50px #00177e, 0 0 100px #00177e;
        border-radius: 5px;
    }

    .link {
        font-size: 15px;
        color: white;
        text-decoration: none;
        transition: background 0.3s, color 0.3s;
    }

    .link:hover {
        background: #00177e;
        color: #fff;
        border-radius: 5px;
        padding: 5px;
    }

    /* Ajustes de imagem */
    .nav img {
        width: 150px; /* Ajuste o tamanho da imagem */
        height: auto;
        margin-right: 5px; /* Adiciona 5px de espaço à direita da imagem */
    }

    /* Media Query para telas pequenas */
    @media (max-width: 580px) {
        .nav {
            flex-direction: row; /* Mantém a imagem e os links na mesma linha */
            justify-content: space-between; /* Espaço entre os elementos */
            align-items: center; /* Alinha verticalmente no centro */
        }

        .nav a {
            padding: 6px 10px; /* Diminui o espaçamento entre os links */
        }

        .dropdown {
            margin-top: 0; /* Remove o espaço adicional do dropdown */
        }
    }

    /* Header e Navegação */
    header {
            background-color: #212123;
            padding: 10px 20px;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;

        }

        .nav a {
            color: #e0e1dd;
            text-decoration: none;
            padding: 8px 15px;
            transition: background-color 0.3s, color 0.3s;
            border-radius: 5px;
            font-size: 20px;
        }

        .nav a:hover {
            background-color: #00177e;
            color: #fff;
        }

        /* Dropdown */
        .dropdown {
            position: relative;
            display: inline-block;
        }
                /* Ajustes de imagem */
        .nav img {
            width: 110px; /* Ajuste o tamanho da imagem */
            height: auto;
            margin-right: 5px; /* Adiciona 5px de espaço à direita da imagem */
        }   

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: rgba(0, 0, 0, 0.9);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            border-radius: 5px;
            min-width: 150px;
            z-index: 10;
        }

        .dropdown-menu a {
            display: block;
            padding: 10px;
            color: #e0e1dd;
            text-decoration: none;
            transition: background-color 0.3s, color 0.3s;
        }

        .dropdown-menu a:hover {
            background-color: #4163fc;
            color: #fff;
        }

        .dropdown:hover .dropdown-menu {
            display: block;
        }

</style>

</head>
<body>

<header>
    <nav class="nav">
        <div><img src="../../../imgs/logo_tintas.png" alt="Logo"></div>
        <div class="flex items-center space-x-6">
            <a href="../../home.php">Home</a>
            <a href="../User/register.php" class="border border-white rounded px-4 py-2 hover:bg-white hover:text-black transition"> Cadastrar </a>
        </div>
    </nav>
</header>

<main>
    <div class="form-container">
        <h1 class="text-3xl font-bold mb-4">Login</h1>
        <form id="loginForm" method="POST">
            <div class="input-container">
                <input type="email" id="email" name="email" required>
                <label for="email">Email:</label>
            </div>
            <div class="input-container">
                <input type="password" id="senha" name="senha" required>
                <label for="senha">Senha:</label>
            </div>
            <button type="submit" class="submit-btn">Entrar</button>
            </br>
            <span>Esqueceu a senha? <a class="link" href="login">Clique aqui</a></span>
        </form>
    </div>       
</main>

</body>
</html>