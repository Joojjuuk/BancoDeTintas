<?php
require('../../../config/database.php');
require('../../../src/models/Tinta.php');
require('../../../src/models/Doacao.php');
require('../../../src/models/Autorizar.php');
require('../../../src/controllers/auth.php');

verificarAdm();
$isLoggedIn = isset($_SESSION['adm_logado']) && $_SESSION['adm_logado'] === true;

function fetchDonations($pdo) {
    $sql = "SELECT t.*, d.horario_disp, d.dias_disp, d.fk_usuario_id_usuario, d.fk_tintas_cod_tinta
            FROM tintas t 
            JOIN doacao_doar d ON t.cod_tinta = d.fk_tintas_cod_tinta 
            LEFT JOIN autorizar a ON d.dias_disp = a.fk_doacao_doar_dias_disp
                AND d.fk_usuario_id_usuario = a.fk_doacao_doar_id_usuario
                AND d.fk_tintas_cod_tinta = a.fk_doacao_doar_cod_tinta
            WHERE a.fk_doacao_doar_cod_tinta IS NULL";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function blobToBase64($blob) {
    if (empty($blob)) {
        return '';
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_buffer($finfo, $blob);
    finfo_close($finfo);
    
    $base64 = base64_encode($blob);
    
    return "data:$mimeType;base64,$base64";
}

$response = ['success' => false, 'message' => '', 'error' => ''];

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cod_tinta = $_POST['id'] ?? '';
    $action = $_POST['action'] ?? '';
    
    try {
        $pdo->beginTransaction();

        if ($action === 'editar') {
            $sql = "UPDATE tintas SET 
                        cor_tinta = :cor_tinta,
                        quantidade = :quantidade,
                        aplicacao = :aplicacao,
                        marca = :marca,
                        embalagem = :embalagem,
                        acabamento = :acabamento,
                        dt_validade = :dt_validade
                    WHERE cod_tinta = :cod_tinta";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':cor_tinta' => $_POST['cor_tinta'],
                ':quantidade' => $_POST['quantidade'],
                ':aplicacao' => $_POST['aplicacao'],
                ':marca' => $_POST['marca'],
                ':embalagem' => $_POST['embalagem'],
                ':acabamento' => $_POST['acabamento'],
                ':dt_validade' => $_POST['dt_validade'],
                ':cod_tinta' => $cod_tinta
            ]);

            $sql = "UPDATE doacao_doar SET 
                        horario_disp = :horario_disp,
                        dias_disp = :dias_disp
                    WHERE fk_tintas_cod_tinta = :cod_tinta";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':horario_disp' => $_POST['horario_disp'],
                ':dias_disp' => $_POST['dias_disp'],
                ':cod_tinta' => $cod_tinta
            ]);

            $response['success'] = true;
            $response['message'] = 'Informações atualizadas com sucesso';
        } elseif ($action === 'aprovado' || $action === 'rejeitado') {
            $sql = "SELECT dias_disp, fk_usuario_id_usuario, fk_tintas_cod_tinta 
                   FROM doacao_doar 
                   WHERE fk_tintas_cod_tinta = :cod_tinta";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':cod_tinta' => $cod_tinta]);
            $doacao = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($doacao) {
                $admin_email = 'admin@teste.com';

                $autorizar = new Autorizar(
                    $doacao['dias_disp'],
                    $doacao['fk_usuario_id_usuario'],
                    $doacao['fk_tintas_cod_tinta'],
                    $admin_email,
                    $action
                );

                $sql = "INSERT INTO autorizar (
                            fk_doacao_doar_dias_disp, 
                            fk_doacao_doar_id_usuario, 
                            fk_doacao_doar_cod_tinta, 
                            fk_adm_email_inst, 
                            status
                        ) VALUES (
                            :dias_disp, 
                            :id_usuario, 
                            :cod_tinta, 
                            :admin_email, 
                            :status
                        )";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':dias_disp' => $autorizar->getFk_doacao_doar_dias_disp(),
                    ':id_usuario' => $autorizar->getFk_doacao_doar_id_usuario(),
                    ':cod_tinta' => $autorizar->getFk_doacao_doar_cod_tinta(),
                    ':admin_email' => $autorizar->getFk_adm_email_inst(),
                    ':status' => $autorizar->getStatus()
                ]);

                $response['success'] = true;
                $response['message'] = 'Doação ' . ($action === 'aprovado' ? 'aprovada' : 'rejeitada') . ' com sucesso';
            }
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $response['error'] = $e->getMessage();
    }

    echo json_encode($response);
    exit;
}

$paintDonations = fetchDonations($pdo);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aprovação de Tintas - Admin</title>
    <style>
        :root {
            --primary-bg: #0d1b2a;
            --secondary-bg: #1b263b;
            --text-color: #e0e1dd;
            --accent-color: #84a98c;
            --button-color: #52796f;
            --button-hover: #354f52;
            --success-color: #4caf50;
            --warning-color: #ff9800;
            --danger-color: #f44336;
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
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .approval-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(5px);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .paint-info {
            display: grid;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .paint-info-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 4px;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            flex: 1;
        }

        .btn-approve {
            background: var(--success-color);
            color: white;
        }

        .btn-reject {
            background: var(--danger-color);
            color: white;
        }

        .btn-edit {
            background: var(--warning-color);
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .paint-image {
            max-width: 100%;
            height: auto;
            margin-bottom: 1rem;
            border-radius: 4px;
        }

        .confirmation-modal, .edit-modal {
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
            padding: 20px;
            border: 1px solid var(--accent-color);
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
        }

        .close {
            color: var(--text-color);
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: var(--accent-color);
            text-decoration: none;
            cursor: pointer;
        }

        .edit-form label {
            display: block;
            margin-top: 10px;
        }

        .edit-form input, .edit-form select, .edit-form textarea {
            width: 100%;
            padding: 5px;
            margin-top: 5px;
        }

        .admin-header {
            background: rgba(0, 0, 0, 0.2);
            padding: 2rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .admin-title {
            font-size: 1.5rem;
            color: var(--accent-color);
            align-items: center;
        }

        .admin-nav {
            display: flex;
            gap: 1rem;
        }

        .admin-nav a {
            color: var(--text-color);
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .admin-nav a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            text-align: center;
        }

        .message.success {
            background-color: var(--success-color);
            color: white;
        }

        .message.error {
            background-color: var(--danger-color);
            color: white;
        }

        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            .admin-nav {
                flex-direction: column;
                width: 100%;
            }
            .admin-nav a {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1 class="admin-title">Painel Administrativo - Aprovação de Tintas</h1>
        <nav class="admin-nav">
            <a href="catalogo.php">Catálogo</a>
            <a href="aprovarReceb.php">Aprovar Recebimentos</a>
            <?php if ($isLoggedIn): ?>
            <a href="/BancoDeTintas/src/controllers/logout.php" class="user-status">
                <i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['email_inst'] ?? 'adm'); ?>
            </a>
        <?php else: ?>
            <a href="enter/user/userlogin.php" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
        <?php endif; ?>
        </nav>
    </header>

    <div class="container">
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($paintDonations)): ?>
            <p>Não há doações pendentes no momento.</p>
        <?php else: ?>
            <?php foreach ($paintDonations as $donation): ?>
                <div class="approval-card" data-id="<?= $donation['cod_tinta'] ?>">
                    <div class="card-header">
                        <h2>Doação #<?= $donation['cod_tinta'] ?></h2>
                    </div>
                    
                    <?php
                    if (!empty($donation['imagem'])) {
                        $imageData = blobToBase64($donation['imagem']);
                        echo "<img src='{$imageData}' alt='Imagem da tinta' class='paint-image'>";
                    }
                    ?>

                    <div class="paint-info">
                        <div class="paint-info-item">
                            <span>Cor:</span>
                            <span><?= htmlspecialchars($donation['cor_tinta']) ?></span>
                        </div>
                        <div class="paint-info-item">
                            <span>Quantidade:</span>
                            <span><?= htmlspecialchars($donation['quantidade']) ?></span>
                        </div>
                        <div class="paint-info-item">
                            <span>Aplicação:</span>
                            <span><?= htmlspecialchars($donation['aplicacao']) ?></span>
                        </div>
                        <div class="paint-info-item">
                            <span>Marca:</span>
                            <span><?= htmlspecialchars($donation['marca']) ?></span>
                        </div>
                        <div class="paint-info-item">
                            <span>Embalagem:</span>
                            <span><?= htmlspecialchars($donation['embalagem']) ?></span>
                        </div>
                        <div class="paint-info-item">
                            <span>Acabamento:</span>
                            <span><?= htmlspecialchars($donation['acabamento']) ?></span>
                        </div>
                        <div class="paint-info-item">
                            <span>Validade:</span>
                            <span><?= date('d/m/Y', strtotime($donation['dt_validade'])) ?></span>
                        </div>
                        <div class="paint-info-item">
                            <span>Horário Disponível:</span>
                            <span><?= htmlspecialchars($donation['horario_disp']) ?></span>
                        </div>
                        <div class="paint-info-item">
                            <span>Dias Disponíveis:</span>
                            <span><?= htmlspecialchars($donation['dias_disp']) ?></span>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button class="btn btn-edit" onclick="showEditForm(<?= $donation['cod_tinta'] ?>)">
                            Editar
                        </button>
                        <button class="btn btn-approve" onclick="showConfirmation(<?= $donation['cod_tinta'] ?>, 'aprovado')">
                            Aprovar
                        </button>
                        <button class="btn btn-reject" onclick="showConfirmation(<?= $donation['cod_tinta'] ?>, 'rejeitado')">
                            Rejeitar
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div id="confirmationModal" class="confirmation-modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <p id="confirmationMessage"></p>
            <button id="confirmYes" class="btn btn-approve">Sim</button>
            <button id="confirmNo" class="btn btn-reject">Não</button>
        </div>
    </div>

    <div id="editModal" class="edit-modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Editar Informações da Tinta</h2>
            <form id="editForm" class="edit-form">
                <input type="hidden" id="edit_cod_tinta" name="id">
                <label for="edit_cor_tinta">Cor:</label>
                <input type="text" id="edit_cor_tinta" name="cor_tinta" required>
                
                <label for="edit_quantidade">Quantidade:</label>
                <input type="text" id="edit_quantidade" name="quantidade" required>
                
                <label for="edit_aplicacao">Aplicação:</label>
                <textarea id="edit_aplicacao" name="aplicacao" required></textarea>
                
                <label for="edit_marca">Marca:</label>
                <input type="text" id="edit_marca" name="marca" required>
                
                <label for="edit_embalagem">Embalagem:</label>
                <input type="text" id="edit_embalagem" name="embalagem" required>
                
                <label for="edit_acabamento">Acabamento:</label>
                <input type="text" id="edit_acabamento" name="acabamento" required>
                
                <label for="edit_dt_validade">Data de Validade:</label>
                <input type="date" id="edit_dt_validade" name="dt_validade" required>
                
                <label for="edit_horario_disp">Horário Disponível:</label>
                <input type="text" id="edit_horario_disp" name="horario_disp" required>
                
                <label for="edit_dias_disp">Dias Disponíveis:</label>
                <input type="text" id="edit_dias_disp" name="dias_disp" required>
                
                <button type="submit" class="btn btn-approve">Salvar Alterações</button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById("confirmationModal");
        const editModal = document.getElementById("editModal");
        const closeButtons = document.getElementsByClassName("close");
        const confirmMessage = document.getElementById("confirmationMessage");
        const confirmYesBtn = document.getElementById("confirmYes");
        const confirmNoBtn = document.getElementById("confirmNo");
        const editForm = document.getElementById("editForm");

        let currentId, currentAction;

        function showConfirmation(id, action) {
            currentId = id;
            currentAction = action;
            confirmMessage.textContent = `Tem certeza que deseja ${action === 'aprovado' ? 'aprovar' : 'rejeitar'} esta doação?`;
            modal.style.display = "block";
        }

        function showEditForm(id) {
            currentId = id;
            const card = document.querySelector(`[data-id="${id}"]`);
            const fields = card.querySelectorAll('.paint-info-item');
            
            document.getElementById('edit_cod_tinta').value = id;
            document.getElementById('edit_cor_tinta').value = fields[0].querySelector('span:last-child').textContent;
            document.getElementById('edit_quantidade').value = fields[1].querySelector('span:last-child').textContent;
            document.getElementById('edit_aplicacao').value = fields[2].querySelector('span:last-child').textContent;
            document.getElementById('edit_marca').value = fields[3].querySelector('span:last-child').textContent;
            document.getElementById('edit_embalagem').value = fields[4].querySelector('span:last-child').textContent;
            document.getElementById('edit_acabamento').value = fields[5].querySelector('span:last-child').textContent;
            document.getElementById('edit_dt_validade').value = fields[6].querySelector('span:last-child').textContent.split('/').reverse().join('-');
            document.getElementById('edit_horario_disp').value = fields[7].querySelector('span:last-child').textContent;
            document.getElementById('edit_dias_disp').value = fields[8].querySelector('span:last-child').textContent;
            
            editModal.style.display = "block";
        }

        for (let closeButton of closeButtons) {
            closeButton.onclick = function() {
                modal.style.display = "none";
                editModal.style.display = "none";
            }
        }

        confirmYesBtn.onclick = function() {
            updateStatus(currentId, currentAction);
            modal.style.display = "none";
        }

        confirmNoBtn.onclick = function() {
            modal.style.display = "none";
        }

        editForm.onsubmit = function(e) {
            e.preventDefault();
            updateTintaInfo(new FormData(editForm));
            editModal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
            if (event.target == editModal) {
                editModal.style.display = "none";
            }
        }

        async function updateStatus(id, action) {
            try {
                const response = await fetch('aprovarTinta.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${id}&action=${action}`
                });

                const data = await response.json();
        
                if (data.success) {
                    const card = document.querySelector(`[data-id="${id}"]`);
                    card.remove();
                    showMessage(data.message, 'success');
                } else {
                    throw new Error(data.error || 'Erro desconhecido');
                }
            } catch (error) {
                console.error('Erro:', error);
                showMessage('Erro ao processar a solicitação: ' + error.message, 'error');
            }
        }

        async function updateTintaInfo(formData) {
            try {
                formData.append('action', 'editar');
                const response = await fetch('aprovarTinta.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
        
                if (data.success) {
                    showMessage(data.message, 'success');
                    location.reload();
                } else {
                    throw new Error(data.error || 'Erro desconhecido');
                }
            } catch (error) {
                console.error('Erro:', error);
                showMessage('Erro ao processar a solicitação: ' + error.message, 'error');
            }
        }

        function showMessage(message, type) {
            const messageDiv = document.createElement('div');
            messageDiv.textContent = message;
            messageDiv.className = `message ${type}`;
            document.querySelector('.container').insertBefore(messageDiv, document.querySelector('.container').firstChild);
            setTimeout(() => messageDiv.remove(), 5000);
        }
    </script>
</body>
</html>