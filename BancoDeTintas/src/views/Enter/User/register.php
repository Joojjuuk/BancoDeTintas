<?php
require('../../../../config/database.php');
require_once('../../../../src/models/Usuario.php');
require_once('../../../../src/models/PessoaFisica.php');
require_once('../../../../src/models/Entidade.php');

try {
    $pdo = new PDO("mysql:host=localhost;dbname=Banco de Tintas", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Conexão falhou: " . $e->getMessage());
}

$message = '';
$messageType = '';

function formatCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
}

function formatCNPJ($cnpj) {
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
    return substr($cnpj, 0, 2) . '.' . substr($cnpj, 2, 3) . '.' . substr($cnpj, 5, 3) . '/' . substr($cnpj, 8, 4) . '-' . substr($cnpj, 12, 2);
}

function checkDuplicate($pdo, $table, $field, $value) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE $field = :value");
    $stmt->execute([':value' => $value]);
    return $stmt->fetchColumn() > 0;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cep = $_POST['cep'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $endereco = $_POST['logradouro'] . ', ' . $_POST['numero'] . ' - ' . $_POST['bairro'];
    $cidade = $_POST['cidade'];
    $celular = $_POST['telefone'];

    if (checkDuplicate($pdo, 'usuario', 'email', $email)) {
        $message = "Email já cadastrado.";
        $messageType = "error";
    } else {
        try {
            $pdo->beginTransaction();
            $usuario = new Usuario($pdo, $cep, $email, $senha, $endereco, $cidade);
            $userId = $usuario->create();

            if ($userId) {
                if (isset($_POST['submitFisica'])) {
                    $nome = $_POST['nome'];
                    $cpf = formatCPF($_POST['cpf']);
                    $dataNascimento = $_POST['dataNascimento'];
                    $sexo = $_POST['sexo'];

                    if (checkDuplicate($pdo, 'pessoa_fisica', 'CPF', $cpf)) {
                        throw new Exception("CPF já cadastrado.");
                    }

                    $pessoaFisica = new PessoaFisica($pdo, $nome, $cpf, $dataNascimento, $userId);
                    if ($pessoaFisica->create()) {
                        $message = "Cadastro de Pessoa Física realizado com sucesso!";
                        $messageType = "success";
                    } else {
                        throw new Exception("Erro ao criar Pessoa Física");
                    }
                } elseif (isset($_POST['submitJuridica'])) {
                    $razaoSocial = $_POST['razao-social'];
                    $cnpj = formatCNPJ($_POST['cnpj']);
                    if (checkDuplicate($pdo, 'entidade', 'CNPJ', $cnpj)) {
                        throw new Exception("CNPJ já cadastrado.");
                    }

                    $entidade = new Entidade($pdo, $razaoSocial, $cnpj, $userId);
                    if ($entidade->create()) {
                        $message = "Cadastro de Entidade realizado com sucesso!";
                        $messageType = "success";
                    } else {
                        throw new Exception("Erro ao criar Entidade");
                    }
                }

                $pdo->commit();
            } else {
                throw new Exception("Erro ao criar Usuário");
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Erro: " . $e->getMessage();
            $messageType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="src/styles/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>PHPaintHub - Cadastro</title>
    <style>
        body {
            background: linear-gradient(#0d1b2a, #1b263b);
            color: #e0e1dd;
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
            color: #0d1b2a;
            display: block;
            text-decoration: none;
            transition: background 0.3s, color 0.3s;
        }

        .hover-content a:hover {
            background: #A8BDFF;
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
            background: #0d1b2a;
            color: #fff;
            transform: scale(1.05);
        }
        .slightly-lighter-red {
            background: rgba(0, 0, 0, 0.6);
            border-radius: 10px;
            box-shadow: 0 15px 25px rgba(13, 27, 42, 0.5);
        }
        .slightly-lighter-red input[type="text"],
        .slightly-lighter-red input[type="date"],
        .slightly-lighter-red input[type="email"],
        .slightly-lighter-red input[type="tel"],
        .slightly-lighter-red input[type="password"] {
            color: #e0e1dd;
            border: none;
            border-bottom: 1px solid #e0e1dd;
            background: transparent;
            outline: none;
        }
        .slightly-lighter-red label {
            font-size: 16px;
            color: #e0e1dd;
            transition: 0.5s;
        }
        .slightly-lighter-red input:focus ~ label,
        .slightly-lighter-red input:valid ~ label {
            color: #84a98c;
        }

        input[type="date"] {
            color: #ffffff;
            background-color: #1b263b;
            border: 1px solid #e0e1dd;
            border-radius: 5px;
            padding: 8px;
        }

        input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
        }

        button[type="submit"] {
            background: #52796f;
            color: #e0e1dd;
            border: none;
            cursor: pointer;
            transition: background 0.5s;
            overflow: hidden;
        }
        button[type="submit"]:hover {
            background: #354f52;
            box-shadow: 0 0 5px #84a98c, 0 0 25px #84a98c, 0 0 50px #84a98c, 0 0 100px #84a98c;
            border-radius: 5px;
        }
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
        <!-- Logo -->
        <div><img src="../../../imgs/logo_tintas.png" alt="Logo"></div>

        <!-- Navegação -->
        <div class="flex items-center space-x-6">
            <a href="../../home.php">Home</a>
            <a href="userLogin.php" class="border border-white rounded px-4 py-2 hover:bg-white hover:text-black transition">
                Login
            </a>
        </div>
    </nav>
</header>
    <main>
        <div class="border-2 border-black rounded mx-7 text-xl text-center py-5 mt-14 mb-10 slightly-lighter-red">
            <h1 class="text-3xl font-bold mb-4">Cadastro</h1>
            <?php if (!empty($message)): ?>
                <div class="<?php echo $messageType === 'error' ? 'bg-red-500' : 'bg-green-500'; ?> text-white p-2 mb-4 rounded">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
 
            <div class="mb-4">
                <button id="btnFisica" class="border border-black rounded py-2 px-3 hover:bg-black hover:text-white mr-2" onclick="mostrarFormulario('fisica')">Pessoa Física</button>
                <button id="btnJuridica" class="border border-black rounded py-2 px-3 hover:bg-black hover:text-white" onclick="mostrarFormulario('juridica')">Entidade</button>
            </div>

            <!-- Formulário Pessoa Física -->
            <form id="form-fisica" method="POST" action="register.php" class="text-left px-10">
                <label class="block mb-1" for="nome">Nome completo:</label>
                <input type="text" id="nome" name="nome" class="block w-full mb-4 p-2 border border-black rounded" required>

                <label class="block mb-1" for="cpf">CPF:</label>
                <input type="text" id="cpf" name="cpf" class="block w-full mb-4 p-2 border border-black rounded" required>

                <label class="block mb-1" for="dataNascimento">Data de nascimento:</label>
                <input type="date" id="dataNascimento" name="dataNascimento" class="block w-full mb-4 p-2 border border-black rounded" required>

                <span class="block mb-1">Sexo:</span>
                <div class="mb-4 border rounded p-2">
                    <label class="inline-flex items-center mr-4">
                        <input type="radio" name="sexo" value="M" class="form-radio text-black" checked>
                        <span class="ml-2">Masculino</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="sexo" value="F" class="form-radio text-black">
                        <span class="ml-2">Feminino</span>
                    </label>
                </div>

                <span class="block mb-1">Endereço:</span>
                <div class="border px-4 rounded mb-4">
                    <div class="flex gap-2">
                        <label class="block mb-1" for="cep">CEP:
                            <input type="text" id="cep" name="cep" class="block w-full mb-4 p-2 border-2 border-black rounded" required>
                        </label>

                        <label class="block mb-1" for="cidade">Cidade:
                            <input type="text" id="cidade" name="cidade" class="block w-full mb-4 p-2 border-2 border-black rounded" required>
                        </label>
                    </div>
                    <label class="block mb-1" for="logradouro">Logradouro:</label>
                    <input type="text" id="logradouro" name="logradouro" class="block w-full mb-4 p-2 border-2 border-black rounded" required>
                    <div class="flex gap-2">
                        <label class="block mb-1" for="numero">Número:
                            <input type="text" id="numero" name="numero" class="block w-full mb-4 p-2 border-2 border-black rounded" required>
                        </label>

                        <label class="block mb-1" for="complemento">Complemento:
                            <input type="text" id="complemento" name="complemento" class="block w-max mb-4 p-2 border-2 border-black rounded">
                        </label>
                    </div>
                    <label class="block mb-1" for="bairro">Bairro:</label>
                    <input type="text" id="bairro" name="bairro" class="block w-full mb-4 p-2 border-2 border-black rounded" required>
                </div>

                <label class="block mb-1" for="email">Email:</label>
                <input type="email" id="email" name="email" class="block w-full mb-4 p-2 border border-black rounded" required>

                <label class="block mb-1" for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" class="block w-full mb-4 p-2 border border-black rounded" required>

                <label class="block mb-1" for="telefone">Telefone para contato:</label>
                <input type="tel" id="telefone" name="telefone" class="block w-full mb-4 p-2 border border-black rounded" required>

                <button class="border border-black rounded py-2 px-3 hover:bg-black hover:text-white mt-4 w-full max-w-md mx-auto block" type="submit" name="submitFisica">Cadastrar Pessoa Física</button>
            </form>

            <!-- Formulário Pessoa Jurídica -->
            <form id="form-juridica" method="POST" action="register.php" class="hidden text-left px-10">
                <label class="block mb-1" for="razao-social">Razão Social:</label>
                <input type="text" id="razao-social" name="razao-social" class="block w-full mb-4 p-2 border border-black rounded" required>

                <label class="block mb-1" for="cnpj">CNPJ:</label>
                <input type="text" id="cnpj" name="cnpj" class="block w-full mb-4 p-2 border border-black rounded" required>

                <span class="block mb-1">Endereço:</span>
                <div class="border px-4 rounded mb-4">
                    <div class="flex gap-2">
                        <label class="block mb-1" for="cep-juridica">CEP:
                            <input type="text" id="cep-juridica" name="cep" class="block w-full mb-4 p-2 border-2 border-black rounded" required>
                        </label>

                        <label class="block mb-1" for="cidade-juridica">Cidade:
                            <input type="text" id="cidade-juridica" name="cidade" class="block w-full mb-4 p-2 border-2 border-black rounded" required>
                        </label>
                    </div>
                    <label class="block mb-1" for="logradouro-juridica">Logradouro:</label>
                    <input type="text" id="logradouro-juridica" name="logradouro" class="block w-full mb-4 p-2 border-2 border-black rounded" required>
                    <div class="flex gap-2">
                        <label class="block mb-1" for="numero-juridica">Número:
                            <input type="text" id="numero-juridica" name="numero" class="block w-full mb-4 p-2 border-2 border-black rounded" required>
                        </label>

                        <label class="block mb-1" for="complemento-juridica">Complemento:
                            <input type="text" id="complemento-juridica" name="complemento" class="block w-max mb-4 p-2 border-2 border-black rounded">
                        </label>
                    </div>
                    <label class="block mb-1" for="bairro-juridica">Bairro:</label>
                    <input type="text" id="bairro-juridica" name="bairro" class="block w-full mb-4 p-2 border-2 border-black rounded" required>
                </div>

                <label class="block mb-1" for="email-juridica">Email:</label>
                <input type="email" id="email-juridica" name="email" class="block w-full mb-4 p-2 border border-black rounded" required>

                <label class="block mb-1" for="senha-juridica">Senha:</label>
                <input type="password" id="senha-juridica" name="senha" class="block w-full mb-4 p-2 border border-black rounded" required>

                <label class="block mb-1" for="telefone-juridica">Telefone para contato:</label>
                <input type="tel" id="telefone-juridica" name="telefone" class="block w-full mb-4 p-2 border border-black rounded" required>

                <button class="border border-black rounded py-2 px-3 hover:bg-black hover:text-white mt-4 w-full max-w-md mx-auto block" type="submit" name="submitJuridica">Cadastrar Entidade</button>
            </form>

            <span class="mt-4 block"> Já possui cadastro? <a class="text-blue-500 hover:underline" href="login">Entre aqui</a> </span>
        </div>
    </main>

    <script>
        function mostrarFormulario(tipo) {
            const formFisica = document.getElementById('form-fisica');
            const formJuridica = document.getElementById('form-juridica');
            const btnFisica = document.getElementById('btnFisica');
            const btnJuridica = document.getElementById('btnJuridica');

            if (tipo === 'fisica') {
                formFisica.classList.remove('hidden');
                formJuridica.classList.add('hidden');
                btnFisica.classList.add('bg-black', 'text-white');
                btnJuridica.classList.remove('bg-black', 'text-white');
            } else {
                formFisica.classList.add('hidden');
                formJuridica.classList.remove('hidden');
                btnFisica.classList.remove('bg-black', 'text-white');
                btnJuridica.classList.add('bg-black', 'text-white');
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            mostrarFormulario('fisica');
        });
    </script>
</body>
</html>