<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado'] === true;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHPaintHub - Doe Tinta, Pinte o Futuro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-bg: #0d1b2a;
            --secondary-bg: #1b263b;
            --text-color: #e0e1dd;
            --accent-color: #84a98c;
            --button-color: #52796f;
            --button-hover: #354f52;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: linear-gradient(var(--primary-bg), var(--secondary-bg));
            color: var(--text-color);
            min-height: 100vh;
        }

        .hero {
            background-image: url('../imgs/Fundo-Tela.webp');
            background-size: cover;
            background-position: center;
            height: 600px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            position: relative;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .hero-content {
            position: relative;
            max-width: 600px;
            z-index: 1;
        }

        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .hero p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .btn {
            background-color: var(--button-color);
            color: var(--text-color);
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
            margin-right: 20px;
            display: inline-block;
            text-decoration: none;
        }

        .btn:hover {
            background-color: var(--button-hover);
        }

        .section {
            padding: 4rem 2rem;
            text-align: center;
        }

        .section h2 {
            font-size: 2rem;
            margin-bottom: 2rem;
            color: var(--accent-color);
        }

        .steps, .rule-list {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .step, .rule {
            max-width: 250px;
            background: rgba(255, 255, 255, 0.1);
            padding: 2rem;
            border-radius: 8px;
            backdrop-filter: blur(5px);
            transition: transform 0.3s ease;
        }

        .step:hover, .rule:hover {
            transform: translateY(-5px);
        }

        .step i, .rule i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--accent-color);
        }

        .step h3, .rule h3 {
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .ods-section {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 4rem 2rem;
            gap: 4rem;
            flex-wrap: wrap;
        }

        .ods-content {
            max-width: 500px;
            text-align: left;
        }

        .ods-content h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--accent-color);
        }

        .ods-content p {
            margin-bottom: 2rem;
        }

        footer {
            background-color: rgba(0, 0, 0, 0.2);
            color: var(--text-color);
            text-align: center;
            padding: 2rem;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {

            .hero h1 {
                font-size: 2rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .ods-section {
                flex-direction: column;
                text-align: center;
            }

            .ods-content {
                order: 2;
            }
        }
        .user-info {
            text-align: right;
            margin-bottom: 1rem;
            color: var(--text-color);
            font-size: 1.2rem;
        }

        .user-info span {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: var(--button-bg);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            color: var(--text-color);
        }
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

 .user-status {
                margin-right: 1rem;
     }

 .login-btn {
   background-color: var(--button-bg);
   color: var(--text-color);
   padding: 0.5rem 1rem;
   border-radius: 5px;
   transition: background-color 0.3s ease;
  }

 .login-btn:hover {
  background-color: var(--button-hover);
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

    </style>
</head>
<body>
<header>
    <nav class="nav">
        <!-- Logo -->
        <div><img src="../imgs/logo_tintas.png" alt="Logo"></div>

        <!-- Navegação -->
        <div class="flex items-center space-x-6">
            <a href="home.php">Home</a>
            <!-- Dropdown para "Catálogo" -->
            <div class="dropdown">
                <a href="#" class="hover:text-gray-400">Catálogo</a>
                <div class="dropdown-menu">
                    <a href="../views/Tinta/catalog.php">Retirar Tinta</a>
                    <a href="../views/tinta/doarTinta.php">Doar Tinta</a>
                </div>
            </div>
            <?php if ($isLoggedIn): ?>
                <a href="../views/Enter/User/logout.php" class="user-status">
                    <i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['email'] ?? 'Usuário'); ?>
                </a>
            <?php else: ?>
                <a href="enter/user/userlogin.php" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            <?php endif; ?>
    </nav>
</header>

    <section class="hero">
        <div class="hero-content">
            <h1>Doe Tinta, Pinte o Futuro</h1>
            <p>Faça parte desta iniciativa que transforma vidas. Sua doação de tinta pode colorir sonhos e renovar esperanças em comunidades carentes.</p>
            <a class="btn" href="tinta/doartinta.php">Quero Doar Tinta</a>
            <a class="btn" href="tinta/catalog.php">Quero Retirar Tinta</a>
        </div>
    </section>

    <section class="section">
        <h2>Como Funciona</h2>
        <div class="steps">
            <div class="step">
                <i class="fas fa-user-plus"></i>
                <h3>Cadastre-se ou Faça Login</h3>
                <p>Crie sua conta em nosso site ou faça login se já é um usuário cadastrado.</p>
            </div>
            <div class="step">
                <i class="fas fa-calendar-alt"></i>
                <h3>Agende sua Doação ou Retirada</h3>
                <p>Escolha uma data e horário convenientes para você fazer sua doação ou retirada de tinta.</p>
            </div>
            <div class="step">
                <i class="fas fa-exchange-alt"></i>
                <h3>Entregue ou Retire a Tinta</h3>
                <p>No dia agendado, entregue sua tinta ou retire a tinta doada no local especificado.</p>
            </div>
        </div>
    </section>

    <section class="ods-section">
        <div class="ods-content">
            <h2>ODS 11 - Cidades e Comunidades Sustentáveis</h2>
            <p>Nosso projeto está alinhado com o ODS 11 da ONU, que busca promover cidades e assentamentos humanos inclusivos, seguros, resilientes e sustentáveis. A doação de tinta para projetos comunitários é uma forma de contribuir para revitalizar espaços e promover o bem-estar das comunidades.</p>
            <button class="btn">SAIBA +</button>
        </div>
        <img src="../imgs/11.png" alt="ODS 11" width="300px" height="300px">
    </section>

    <section class="section">
        <h2>Regras</h2>
        <div class="rule-list">
            <div class="rule">
                <i class="fas fa-paint-roller"></i>
                <h3>Tipos de Tinta Disponíveis</h3>
            </div>
            <div class="rule">
                <i class="fas fa-file-invoice-dollar"></i>
                <h3>Comprovação de Renda</h3>
            </div>
            <div class="rule">
                <i class="fas fa-tachometer-alt"></i>
                <h3>Limite de Retirada</h3>
            </div>
            <div class="rule">
                <i class="fas fa-calendar-check"></i>
                <h3>Agendamento Prévio</h3>
            </div>
        </div>
    </section>

    <footer>
        <p>© 2024 PHPaintHub | Política de Privacidade | Termos de Uso</p>
        <p>Nossa História | FAQ | Contato</p>
    </footer>

</body>
</html>