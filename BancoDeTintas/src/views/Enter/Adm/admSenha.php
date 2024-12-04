<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="src/styles/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>PHPaintHub - Cadastro</title>
    <style>
        html, body {
            height: 100%; 
            margin: 0; 
        }

        body {
            background: linear-gradient(#026863, #212123); 
            color: #e0e1dd;
            font-family: "Poppins", sans-serif; 
            display: flex; 
            flex-direction: column; 
            min-height: 100vh; 
        }

        header {
            flex: 0 0 auto;
            background-color: rgba(0, 0, 0, 0);
            position: relative; 
            z-index: 1; 
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 10px;
        }

        .nav-links {
            display: flex;
            gap: 10px;
        }

        .nav-links a {
            color: #e0e1dd;
            font-size: 20px;
            transition: color 0.3s, transform 0.3s;
        }

        .nav-links a:hover {
            color: #84a98c;
        }

        .hover-container {
            position: relative;
            display: inline-block;
        }

        .hover-content {
            display: none;
            position: absolute;
            background-color: rgba(0, 0, 0, 0.9);
            border-radius: 5px;
            text-align: left;
            z-index: 1;
        }

        .hover-container:hover .hover-content {
            display: block;
        }

        .hover-content a {
            color: #e0e1dd;
            display: block;
            text-decoration: none;
            transition: background 0.3s, color 0.3s;
        }

        .hover-content a:hover {
            background: #84a98c;
            color: #000;
            border-radius: 3px;
        }

        .login-button {
            background: transparent;
            color: #e0e1dd;
            border: 2px solid rgba(0,0,0,0);
            border-radius: 5px;
            padding: 10px 15px;
            text-transform: uppercase;
            transition: background 0.3s, color 0.3s, transform 0.3s;
        }

        .login-button:hover {
            background: #354f52;
            color: #fff;
            transform: scale(1.05);
            box-shadow: 0 0 5px #84a98c, 0 0 25px #84a98c, 0 0 50px #84a98c;
        }

        main {
            flex: 1; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            padding: 20px; 
            overflow: auto; 
            min-height: calc(100vh - 100px); 
        }

        .form-container {
            background: rgba(0, 0, 0, 0.6);
            border-radius: 10px;
            box-shadow: 0 15px 25px rgba(13, 27, 42, 0.5);
            padding: 40px; 
            max-width: 400px; 
            width: 100%; 
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
            color: #84a98c;
            top: -10px; 
            left: 0;
            font-size: 12px; 
        }

        button[type="submit"] {
            background: #026863;
            color: #e0e1dd;
            border: none;
            cursor: pointer;
            transition: background 0.5s;
            overflow: hidden;
            padding: 10px 20px; 
            font-size: 16px; 
            text-transform: uppercase; 
        }

        button[type="submit"]:hover {
            background: #354f52;
            color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 5px #84a98c, 0 0 25px #84a98c, 0 0 50px #84a98c, 0 0 100px #84a98c;
        }

        .link {
            color: white;
            text-decoration: none;
            transition: background 0.3s, color 0.3s; 
        }

        .link:hover {
        background: #354f52;
        color: #fff;
        border-radius: 5px;
        box-shadow: 0 0 5px #84a98c, 0 0 25px #84a98c, 0 0 50px #84a98c, 0 0 100px #84a98c;
            padding: 5px; 
        }
    </style>
</head>
<body>

<header>
    <nav>
        <div class="nav-links">
            <a href="home">Home</a>
            
            <div class="hover-container">
                <a>Catálogo</a>
                <div class="hover-content">
                    <a href="retire">Retire tinta</a>
                    <a href="doe">Doe tinta</a>
                </div>
            </div>
            
            <div class="hover-container">
                <a class="login-button">Entre / <br> Cadastre-se</a>
                <div class="hover-content">
                    <a href="adm">Adm</a>
                    <a href="usuario">Usuário</a>
                </div>
            </div>
        </div>

        <div class="flex text-lg gap-2 md:gap-3 items-center">
            <a class="text-sm md:text-lg">Nome</a>
            <a class="border border-transparent rounded py-1 px-2 md:py-2 md:px-3 hover:bg-[#354f52] hover:text-white">Sair</a>
        </div>
    </nav>
</header>

<main>
    <div class="form-container">
        <h1 class="text-3xl font-bold mb-4">Altere sua senha:</h1>
        <form id="registrationForm" method="POST">
            <div class="input-container">
                <input type="password" id="senha" name="senha" required>
                <label for="senha">Nova Senha:</label> 
            </div>
            <button type="submit" class="submit-btn">Mudar Senha</button>
        </form>
    </div>       
</main>

</body>
</html>
