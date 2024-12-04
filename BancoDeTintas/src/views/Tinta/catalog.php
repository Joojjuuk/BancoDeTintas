<?php
require_once('../../../config/database.php');
require_once('../../../src/models/Tinta.php');
require_once('../../../src/models/Usuario.php');
require_once('../../../src/models/Autorizar.php');
require('../../../src/controllers/auth.php');


verificarSessao();
$isLoggedIn = isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado'] === true;
$fk_usuario_id_usuario = $_SESSION['id_usuario'];

function fetchAllPaints($pdo) {
    $sql = "SELECT t.* 
            FROM tintas t 
            JOIN doacao_doar d ON t.cod_tinta = d.fk_tintas_cod_tinta 
            JOIN autorizar a ON d.dias_disp = a.fk_doacao_doar_dias_disp
                AND d.fk_usuario_id_usuario = a.fk_doacao_doar_id_usuario
                AND d.fk_tintas_cod_tinta = a.fk_doacao_doar_cod_tinta
            WHERE a.status = 'aprovado'";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function isValidDate($date) {
    $today = new DateTime();
    $selectedDate = new DateTime($date);
    $dayOfWeek = $selectedDate->format('N');

    return $selectedDate >= $today && in_array($dayOfWeek, [1, 2, 3]); // 1 = Monday, 2 = Tuesday, 3 = Wednesday
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request') {
    $dt_retirada = $_POST['dt_retirada'];
    $finalidade = $_POST['finalidade'];
    $fk_usuario_id_usuario = $fk_usuario_id_usuario = $_SESSION['id_usuario'];
    $fk_tintas_cod_tinta = $_POST['cod_tinta'];

    // Validate the date
    if (!isValidDate($dt_retirada)) {
        $message = "Data inválida. Por favor, selecione uma segunda, terça ou quarta-feira futura.";
        $messageType = "error";
    } else {
        $sql = "INSERT INTO pedido_pedir (dt_retirada, finalidade, fk_usuario_id_usuario, fk_tintas_cod_tinta) 
                VALUES (:dt_retirada, :finalidade, :fk_usuario_id_usuario, :fk_tintas_cod_tinta)";
        $stmt = $pdo->prepare($sql);
        
        try {
            if ($stmt->execute([
                ':dt_retirada' => $dt_retirada,
                ':finalidade' => $finalidade,
                ':fk_usuario_id_usuario' => $fk_usuario_id_usuario,
                ':fk_tintas_cod_tinta' => $fk_tintas_cod_tinta
            ])) {
                $message = "Pedido realizado com sucesso!";
                $messageType = "success";
            } else {
                $message = "Erro ao realizar o pedido.";
                $messageType = "error";
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                $message = "Você já realizou um pedido para essa tinta, peça outra ou espere novas tintas ficarem em estoque!";
            } else {
                $message = "Erro ao realizar o pedido: " . $e->getMessage();
            }
            $messageType = "error";
        }
    }
}

$paints = fetchAllPaints($pdo);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Tintas - PHPaintHub</title>
    <style>
        :root {
            --primary-bg: #0d1b2a;
            --secondary-bg: #1b263b;
            --text-color: #e0e1dd;
            --accent-color: #84a98c;
            --card-bg: rgba(255, 255, 255, 0.1);
            --button-bg: #52796f;
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

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--accent-color);
            font-size: 2.5rem;
        }

        .paint-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }

        .paint-card {
            background: var(--card-bg);
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s ease;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .paint-card:hover {
            transform: translateY(-5px);
        }

        .paint-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        .paint-info {
            padding: 1.5rem;
        }

        .paint-info h2 {
            margin-bottom: 1rem;
            color: var(--accent-color);
            font-size: 1.8rem;
        }

        .paint-info p {
            margin-bottom: 0.75rem;
            font-size: 1.2rem;
        }

        .button {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background-color: var(--button-bg);
            color: var(--text-color);
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
            font-size: 1.2rem;
            border: none;
            cursor: pointer;
        }

        .button:hover {
            background-color: var(--button-hover);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: var(--secondary-bg);
            margin: 15% auto;
            padding: 2rem;
            border: 1px solid var(--accent-color);
            width: 90%;
            max-width: 600px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .close {
            color: var(--text-color);
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: var(--accent-color);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border-radius: 4px;
            border: 1px solid var(--accent-color);
            background-color: var(--primary-bg);
            color: var(--text-color);
            font-size: 1.1rem;
        }

        .message {
            background-color: var(--accent-color);
            color: var(--primary-bg);
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            font-size: 1.2rem;
            text-align: center;
        }

        .message.success {
            background-color: #4CAF50;
            color: white;
        }

        .message.error {
            background-color: #F44336;
            color: white;
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
            margin-bottom: 40px;
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
        <div><img src="../../imgs/logo_tintas.png" alt="Logo"></div>

        <!-- Navegação -->
        <div class="flex items-center space-x-6">
            <a href="../home.php">Home</a>
            <!-- Dropdown para "Catálogo" -->
            <div class="dropdown">
                <a href="#" class="hover:text-gray-400">Catálogo</a>
                <div class="dropdown-menu">
                    <a href="../tinta/catalog.php">Retirar Tinta</a>
                    <a href="../tinta/doarTinta.php">Doar Tinta</a>
                </div>
            </div>
            <?php if ($isLoggedIn): ?>
                <a href="../Enter/User/logout.php" class="user-status">
                    <i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['email'] ?? 'Usuário'); ?>
                </a>
            <?php else: ?>
                <a href="enter/user/userlogin.php" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            <?php endif; ?>
    </nav>
</header>

    <div class="container">
        <h1>Catálogo de Tintas</h1>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if (empty($paints)): ?>
            <div class="message info">Estamos sem tintas disponíveis por enquanto.</div>
        <?php else: ?>
            <div class="paint-grid">
                <?php foreach ($paints as $paint): ?>
                    <div class="paint-card" onclick="showRequestModal(<?php echo $paint['cod_tinta']; ?>)">
                        <?php if (!empty($paint['imagem'])): ?>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($paint['imagem']); ?>" alt="<?php echo htmlspecialchars($paint['cor_tinta']); ?>" class="paint-image">
                        <?php else: ?>
                            <div class="paint-image" style="background-color: <?php echo htmlspecialchars($paint['cor_tinta']); ?>"></div>
                        <?php endif; ?>
                        <div class="paint-info">
                            <h2><?php echo htmlspecialchars($paint['cor_tinta']); ?></h2>
                            <p><strong>Marca:</strong> <?php echo htmlspecialchars($paint['marca']); ?></p>
                            <p><strong>Quantidade:</strong> <?php echo htmlspecialchars($paint['quantidade']); ?></p>
                            <p><strong>Embalagem:</strong> <?php echo htmlspecialchars($paint['embalagem']); ?></p>
                            <p><strong>Acabamento:</strong> <?php echo htmlspecialchars($paint['acabamento']); ?></p>
                            <p><strong>Validade:</strong> <?php echo date('d/m/Y', strtotime($paint['dt_validade'])); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div id="requestModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Solicitar Tinta</h2>
            <form id="requestForm" method="POST">
                <input type="hidden" name="action" value="request">
                <input type="hidden" id="cod_tinta" name="cod_tinta">
                <div class="form-group">
                    <label for="dt_retirada">Data de Retirada:</label>
                    <input type="date" id="dt_retirada" name="dt_retirada" required>
                </div>
                <div class="form-group">
                    <label for="finalidade">Finalidade:</label>
                    <textarea id="finalidade" name="finalidade" required></textarea>
                </div>
                <button type="submit" class="button">Solicitar</button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('requestModal');
        const closeBtn = document.getElementsByClassName('close')[0];
        const form = document.getElementById('requestForm');

        function showRequestModal(codTinta) {
            if (codTinta) {
                document.getElementById('cod_tinta').value = codTinta;
                modal.style.display = 'block';
            } else {
                console.error('Invalid paint code:', codTinta);
            }
        }

        closeBtn.onclick = function() {
            modal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>