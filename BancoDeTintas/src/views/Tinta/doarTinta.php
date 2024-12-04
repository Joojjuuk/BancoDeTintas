<?php
require_once('../../../config/database.php');
require_once('../../../src/models/tinta.php');
require_once('../../../src/models/doacao.php');
require_once('../../../src/models/ponto_coleta.php');
require('../../../src/controllers/auth.php');

verificarSessao();
$isLoggedIn = isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado'] === true;
$fk_usuario_id_usuario = $_SESSION['id_usuario'] ?? null;

if (!$fk_usuario_id_usuario) {
    die("Usuário não está logado.");
}

$stmt = $pdo->query("SELECT * FROM ponto_coleta");
$pontos_coleta = $stmt->fetchAll(PDO::FETCH_ASSOC);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imageData = null;
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $imageData = file_get_contents($_FILES['imagem']['tmp_name']);
    }

    $diaDoacao = $_POST['diaDoacao'];
    $dt_validade = $_POST['dt_validade'];

    $doacao_date = new DateTime($diaDoacao);
    $today = new DateTime();
    $day_of_week = $doacao_date->format('N');

    if ($doacao_date <= $today || !in_array($day_of_week, [1, 2, 3])) {
        $message = "Data de doação inválida. Escolha uma segunda, terça ou quarta-feira futura.";
        $messageType = 'error';
    } else {
        $validade_date = new DateTime($dt_validade);
        $min_validade = clone $doacao_date;
        $min_validade->modify('+30 days');

        if ($validade_date < $min_validade) {
            $message = "A data de validade deve ser pelo menos 30 dias após a data de doação.";
            $messageType = 'error';
        } else {
            $tinta = new Tinta(
                $_POST['cor'],
                $_POST['quantidade'],
                $_POST['aplicacao'],
                $_POST['marca'],
                $imageData,
                $_POST['tamanho'],
                $_POST['acabamento'],
                $dt_validade,
                $_POST['ponto_coleta']
            );

            $doacao = new Doacao(
                $_POST['horario'],
                $diaDoacao,
                null, 
                $fk_usuario_id_usuario
            );

            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("INSERT INTO tintas (cor_tinta, quantidade, aplicacao, marca, imagem, embalagem, acabamento, dt_validade, fk_ponto_coleta_cod_ponto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $tinta->getCorTinta(),
                    $tinta->getQuantidade(),
                    $tinta->getAplicacao(),
                    $tinta->getMarca(),
                    $tinta->getImagem(),
                    $tinta->getEmbalagem(),
                    $tinta->getAcabamento(),
                    $tinta->getDtValidade(),
                    $tinta->getFkPontoColetaCodPonto()
                ]);

                $lastInsertId = $pdo->lastInsertId();

                $stmt = $pdo->prepare("INSERT INTO doacao_doar (horario_disp, dias_disp, fk_tintas_cod_tinta, fk_usuario_id_usuario) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $doacao->getHorarioDisp(),
                    $doacao->getDiasDisp(),
                    $lastInsertId,
                    $doacao->getFkUsuarioIdUsuario()
                ]);

                $pdo->commit();
                $message = "Doação cadastrada com sucesso!";
                $messageType = 'success';
            } catch(PDOException $e) {
                $pdo->rollBack();
                $message = "Erro ao cadastrar doação: " . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Doação - PHPaintHub</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <style>
    :root {
        --primary-bg: #0d1b2a;
        --secondary-bg: #1b263b;
        --text-color: #e0e1dd;
        --accent-color: #84a98c;
        --button-color: #52796f;
        --button-hover: #354f52;
        --error-color: #ff6b6b;
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
        padding-bottom: 2rem;
    }

    .container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .form-section {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        padding: 2rem;
        backdrop-filter: blur(5px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .form-title {
        text-align: center;
        margin-bottom: 2rem;
        color: var(--accent-color);
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: bold;
    }

    input, select, textarea {
        width: 100%;
        padding: 0.8rem;
        border-radius: 4px;
        border: 1px solid var(--text-color);
        background: rgba(255, 255, 255, 0.1);
        color: var(--text-color);
        margin-top: 0.25rem;
    }

    input:focus, select:focus, textarea:focus {
        outline: none;
        border-color: var(--accent-color);
        box-shadow: 0 0 5px var(--accent-color);
    }

    button {
        background: var(--button-color);
        color: var(--text-color);
        padding: 1rem 2rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
        font-size: 1.1rem;
        margin-top: 1rem;
    }

    button:hover {
        background: var(--button-hover);
        box-shadow: 0 0 15px var(--accent-color);
    }

    #map {
        height: 400px;
        width: 100%;
        margin-bottom: 1rem;
    }

    .login-btn {
        border: 1px solid var(--text-color);
        padding: 0.5rem 1rem;
        border-radius: 4px;
        transition: all 0.3s ease;
    }

    .login-btn:hover {
        background: var(--text-color);
        color: var(--primary-bg);
    }

    header {
        background-color: #212123;
        padding: 10px 20px;
        margin-bottom: 80px;
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

    .dropdown {
        position: relative;
        display: inline-block;
    }

    .nav img {
        width: 110px;
        height: auto;
        margin-right: 5px;
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
    
    .message {
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 4px;
        font-size: 1.2rem;
        text-align: center;
    }

    .message.error {
        background-color: var(--error-color);
        color: var(--primary-bg);
    }

    .message.success {
        background-color: var(--accent-color);
        color: var(--primary-bg);
    }

    .calendar {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 4px;
        padding: 0.8rem;
        margin-top: 0.25rem;
    }

    .calendar input[type="date"] {
        width: 100%;
        padding: 0.5rem;
        border-radius: 4px;
        border: 1px solid var(--text-color);
        background: transparent;
        color: var(--text-color);
    }
    </style>
</head>
<body>
<header>
    <nav class="nav">
        <div><img src="../../imgs/logo_tintas.png" alt="Logo"></div>
        <div class="flex items-center space-x-6">
            <a href="../home.php">Home</a>
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
        </div>
    </nav>
</header>
<div class="container">
    <div class="form-section">
        <h1 class="form-title">Cadastro de Doação de Tinta</h1>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <form id="paintDonationForm" action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="cor">Cor da tinta</label>
                <input type="text" id="cor" name="cor" required>
            </div>

            <div class="form-group">
                <label for="quantidade">Quantidade de tinta</label>
                <input type="text" id="quantidade" name="quantidade" required>
            </div>

            <div class="form-group">
                <label for="aplicacao">Indicação de aplicação da Tinta</label>
                <textarea id="aplicacao" name="aplicacao" rows="3" required></textarea>
            </div>

            <div class="form-group">
                <label for="marca">Marca da tinta</label>
                <select id="marca" name="marca" required>
                    <option value="">Selecione a marca</option>
                    <option value="premium">Premium</option>
                    <option value="standard">Standard</option>
                    <option value="Econômica">Econômica</option>
                </select>
            </div>

            <div class="form-group">
                <label for="imagem">Escolha uma imagem:</label>
                <input type="file" name="imagem" id="imagem" required>
            </div>

            <div class="form-group">
                <label for="tamanho">Tamanho da embalagem</label>
                <select id="tamanho" name="tamanho" required>
                    <option value="">Selecione o tamanho</option>
                    <option value="¼ de galão (900 ml)">¼ de galão (900 ml)</option>
                    <option value="Galão 3,6 Litros">Galão 3,6 Litros</option>
                    <option value="Lata 18 litros">Lata 18 litros</option>
                </select>
            </div>

            <div class="form-group">
                <label for="acabamento">Acabamento da tinta</label>
                <select id="acabamento" name="acabamento" required>
                    <option value="">Selecione o acabamento</option>
                    <option value="Fosco">Fosco</option>
                    <option value="Acetinado">Acetinado</option>
                    <option value="Brilhante">Brilhante</option>
                </select>
            </div>

            <div class="form-group">
                <label for="dt_validade">Data de validade da tinta</label>
                <input type="date" id="dt_validade" name="dt_validade" required>
            </div>

            <div class="form-group">
                <label for="ponto_coleta">Ponto de coleta</label>
                <select id="ponto_coleta" name="ponto_coleta" required>
                    <option value="">Selecione o ponto de coleta</option>
                    <?php foreach ($pontos_coleta as $ponto): ?>
                        <option value="<?php echo $ponto['cod_ponto']; ?>" data-lat="<?php echo $ponto['latitude']; ?>" data-lng="<?php echo $ponto['longitude']; ?>">
                            <?php echo $ponto['endereco'] . ', ' . $ponto['cidade']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div id="map"></div>
            </div>

            <div class="form-group">
                <label for="diaDoacao">Dia disponível para doação (Segunda, Terça ou Quarta)</label>
                <div class="calendar">
                    <input type="date" id="diaDoacao" name="diaDoacao" required>
                </div>
            </div>

            <div class="form-group">
                <label for="horario">Horário disponível</label>
                <select id="horario" name="horario" required>
                    <option value="">Selecione o horário</option>
                    <option value="Das 8:00 às 11:00">Das 8:00 às 11:00</option>
                    <option value="Das 13:00 às 17:00">Das 13:00 às 17:00</option>
                </select>
            </div>

            <button type="submit" name="submit">Cadastrar Doação</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var map = L.map('map').setView([-23.1857, -46.8978], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    var pontosColeta = <?php echo json_encode($pontos_coleta); ?>;
    var markers = [];

    pontosColeta.forEach(function(ponto) {
        var marker = L.marker([ponto.latitude, ponto.longitude])
            .addTo(map)
            .bindPopup(ponto.endereco + ', ' + ponto.cidade);

        markers.push(marker);
    });

    document.getElementById('ponto_coleta').addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];
        var lat = selectedOption.getAttribute('data-lat');
        var lng = selectedOption.getAttribute('data-lng');
        if (lat && lng) {
            map.setView([lat, lng], 15);
            markers.forEach(function(marker) {
                if (marker.getLatLng().lat == lat && marker.getLatLng().lng == lng) {
                    marker.openPopup();
                }
            });
        }
    });

    const diaDoacaoInput = document.getElementById('diaDoacao');
    const dtValidadeInput = document.getElementById('dt_validade');

    diaDoacaoInput.addEventListener('change', function() {
        const selectedDate = new Date(this.value);
        const dayOfWeek = selectedDate.getDay();

        if (dayOfWeek < 0 || dayOfWeek > 3) {
            alert('Por favor, selecione apenas Segunda, Terça ou Quarta-feira.');
            this.value = '';
        } else {
            const minValidadeDate = new Date(selectedDate);
            minValidadeDate.setDate(minValidadeDate.getDate() + 30);
            dtValidadeInput.min = minValidadeDate.toISOString().split('T')[0];
        }
    });

    dtValidadeInput.addEventListener('change', function() {
        const selectedDoacaoDate = new Date(diaDoacaoInput.value);
        const selectedValidadeDate = new Date(this.value);
        const minValidadeDate = new Date(selectedDoacaoDate);
        minValidadeDate.setDate(minValidadeDate.getDate() + 30);

        if (selectedValidadeDate < minValidadeDate) {
            alert('A data de validade deve ser pelo menos 30 dias após a data de doação.');
            this.value = '';
        }
    });
});
</script>
</body>
</html>