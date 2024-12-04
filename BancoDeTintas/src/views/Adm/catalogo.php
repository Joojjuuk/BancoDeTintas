<?php
require_once('../../../config/database.php');
require_once('../../../src/models/Tinta.php');
require_once('../../../src/models/Doacao.php');
require_once('../../../src/models/Autorizar.php');
require('../../../src/controllers/auth.php');


verificarAdm();
$isLoggedIn = isset($_SESSION['adm_logado']) && $_SESSION['adm_logado'] === true;

function fetchAllPaints($pdo) {
    $sql = "SELECT t.*, a.status, d.fk_usuario_id_usuario, d.dias_disp as dt_retirada, d.horario_disp
            FROM tintas t 
            JOIN doacao_doar d ON t.cod_tinta = d.fk_tintas_cod_tinta 
            JOIN autorizar a ON d.dias_disp = a.fk_doacao_doar_dias_disp
                AND d.fk_usuario_id_usuario = a.fk_doacao_doar_id_usuario
                AND d.fk_tintas_cod_tinta = a.fk_doacao_doar_cod_tinta
            WHERE a.status = 'aprovado'";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
            case 'edit':
                $cod_tinta = isset($_POST['cod_tinta']) ? $_POST['cod_tinta'] : null;
                $tinta = new Tinta(
                    $_POST['cor_tinta'],
                    $_POST['quantidade'],
                    $_POST['aplicacao'],
                    $_POST['marca'],
                    null,
                    $_POST['embalagem'],
                    $_POST['acabamento'],
                    $_POST['dt_validade'],
                    $_POST['fk_ponto_coleta_cod_ponto']
                );

                try {
                    $pdo->beginTransaction();

                    if ($_POST['action'] === 'add') {
                        $sql = "INSERT INTO tintas (cor_tinta, quantidade, aplicacao, marca, embalagem, acabamento, dt_validade, fk_ponto_coleta_cod_ponto) 
                                VALUES (:cor_tinta, :quantidade, :aplicacao, :marca, :embalagem, :acabamento, :dt_validade, :fk_ponto_coleta_cod_ponto)";
                    } else {
                        $sql = "UPDATE tintas SET 
                                cor_tinta = :cor_tinta, quantidade = :quantidade, aplicacao = :aplicacao, 
                                marca = :marca, embalagem = :embalagem, acabamento = :acabamento, 
                                dt_validade = :dt_validade, fk_ponto_coleta_cod_ponto = :fk_ponto_coleta_cod_ponto 
                                WHERE cod_tinta = :cod_tinta";
                    }

                    $stmt = $pdo->prepare($sql);
                    $params = [
                        ':cor_tinta' => $tinta->getCorTinta(),
                        ':quantidade' => $tinta->getQuantidade(),
                        ':aplicacao' => $tinta->getAplicacao(),
                        ':marca' => $tinta->getMarca(),
                        ':embalagem' => $tinta->getEmbalagem(),
                        ':acabamento' => $tinta->getAcabamento(),
                        ':dt_validade' => $tinta->getDtValidade(),
                        ':fk_ponto_coleta_cod_ponto' => $tinta->getFkPontoColetaCodPonto()
                    ];

                    if ($_POST['action'] === 'edit') {
                        $params[':cod_tinta'] = $cod_tinta;
                    }

                    $stmt->execute($params);

                    if ($_POST['action'] === 'add') {
                        $cod_tinta = $pdo->lastInsertId();

                        $doacao = new Doacao("adição adm", "Adição Adm", $cod_tinta, 1);
                        $sql = "INSERT INTO doacao_doar (horario_disp, dias_disp, fk_tintas_cod_tinta, fk_usuario_id_usuario) 
                                VALUES (:horario_disp, :dias_disp, :fk_tintas_cod_tinta, :fk_usuario_id_usuario)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([
                            ':horario_disp' => $doacao->getHorarioDisp(),
                            ':dias_disp' => $doacao->getDiasDisp(),
                            ':fk_tintas_cod_tinta' => $doacao->getFkTintasCodTinta(),
                            ':fk_usuario_id_usuario' => $doacao->getFkUsuarioIdUsuario()
                        ]);

                        $autorizar = new Autorizar($doacao->getDiasDisp(), $doacao->getFkUsuarioIdUsuario(), $doacao->getFkTintasCodTinta(), $_SESSION['email_inst'], 'aprovado');
                        $sql = "INSERT INTO autorizar (fk_doacao_doar_dias_disp, fk_doacao_doar_id_usuario, fk_doacao_doar_cod_tinta, fk_adm_email_inst, status) 
                                VALUES (:fk_doacao_doar_dias_disp, :fk_doacao_doar_id_usuario, :fk_doacao_doar_cod_tinta, :fk_adm_email_inst, :status)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([
                            ':fk_doacao_doar_dias_disp' => $autorizar->getFk_doacao_doar_dias_disp(),
                            ':fk_doacao_doar_id_usuario' => $autorizar->getFk_doacao_doar_id_usuario(),
                            ':fk_doacao_doar_cod_tinta' => $autorizar->getFk_doacao_doar_cod_tinta(),
                            ':fk_adm_email_inst' => $autorizar->getFk_adm_email_inst(),
                            ':status' => $autorizar->getStatus()
                        ]);
                    }
                    
                    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
                        $imgData = file_get_contents($_FILES['imagem']['tmp_name']);
                        $stmt = $pdo->prepare("UPDATE tintas SET imagem = :imagem WHERE cod_tinta = :cod_tinta");
                        $stmt->bindParam(':imagem', $imgData, PDO::PARAM_LOB);
                        $stmt->bindParam(':cod_tinta', $cod_tinta);
                        $stmt->execute();
                    }

                    $pdo->commit();
                    $message = ($_POST['action'] === 'add') ? "Tinta adicionada com sucesso!" : "Tinta atualizada com sucesso!";
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $message = "Erro ao " . ($_POST['action'] === 'add' ? "adicionar" : "atualizar") . " a tinta: " . $e->getMessage();
                }
                break;

            case 'update_status':
                $cod_tinta = $_POST['cod_tinta'];
                $new_status = $_POST['new_status'];
                $fk_usuario_id_usuario = $_POST['fk_usuario_id_usuario'];
                $dt_retirada = $_POST['dt_retirada'];
                $admin_email = $_SESSION['email_inst'] ?? 'admin@example.com';

                try {
                    $pdo->beginTransaction();

                    $autorizar = new Autorizar($dt_retirada, $fk_usuario_id_usuario, $cod_tinta, $admin_email, $new_status);

                    $sql = "UPDATE autorizar SET 
                            status = :status,
                            fk_adm_email_inst = :admin_email
                            WHERE fk_doacao_doar_dias_disp = :dt_retirada
                            AND fk_doacao_doar_id_usuario = :fk_usuario_id_usuario
                            AND fk_doacao_doar_cod_tinta = :cod_tinta";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':status' => $autorizar->getStatus(),
                        ':admin_email' => $autorizar->getFk_adm_email_inst(),
                        ':dt_retirada' => $autorizar->getFk_doacao_doar_dias_disp(),
                        ':fk_usuario_id_usuario' => $autorizar->getFk_doacao_doar_id_usuario(),
                        ':cod_tinta' => $autorizar->getFk_doacao_doar_cod_tinta()
                    ]);

                    $pdo->commit();
                    $message = "Status atualizado com sucesso para " . $new_status;
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $message = "Erro ao atualizar o status: " . $e->getMessage();
                }
                break;
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
    <title>Catálogo de Tintas - Admin - PHPaintHub</title>
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
            margin: 0px auto;
        }
        .texto{
            margin: 10px 0 40px 0;
        }
        .paint-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
        }

        .paint-card {
            background: var(--card-bg);
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .paint-card:hover {
            transform: translateY(-5px);
        }

        .paint-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .paint-info {
            padding: 1rem;
        }

        .paint-info h2 {
            margin-bottom: 0.5rem;
            color: var(--accent-color);
        }

        .paint-info p {
            margin-bottom: 0.25rem;
        }
        
        .button-espc{
            margin-bottom: 40px;
        }

        .button {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: var(--button-bg);
            color: var(--text-color);
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
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
            padding: 20px;
            border: 1px solid var(--accent-color);
            width: 80%;
            max-width: 600px;
        }

        .close {
            color: var(--text-color);
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.5rem;
            border-radius: 4px;
            border: 1px solid var(--accent-color);
            background-color: var(--primary-bg);
            color: var(--text-color);
        }

        .message {
            background-color: var(--accent-color);
            color: var(--primary-bg);
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }

        .status-buttons {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .status-button {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }

        .status-doado {
            background-color: #4CAF50;
        }

        .status-vencido {
            background-color: #f44336;
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
        <h1 class="admin-title">Painel Administrativo - Catálogo</h1>
        <nav class="admin-nav">
            <a href="aprovartinta.php">Aprovar Tintas</a>
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
        <h1 class="texto">Catálogo de Tintas - Admin</h1>
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        <button class="button button-espc" onclick="showAddModal()">Adicionar Nova Tinta</button>
        <div class="paint-grid">
            <?php foreach ($paints as $paint): ?>
                <div class="paint-card" data-id="<?php echo $paint['cod_tinta']; ?>">
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
                        <p><strong>Status:</strong> <?php echo htmlspecialchars($paint['status']); ?></p>
                        <button class="button" onclick="showEditModal(<?php echo $paint['cod_tinta']; ?>)">Editar</button>
                        <div class="status-buttons">
                            <button class="button status-button status-doado" onclick="updateStatus(<?php echo $paint['cod_tinta']; ?>, '<?php echo $paint['fk_usuario_id_usuario']; ?>', '<?php echo $paint['dt_retirada']; ?>', 'doado')">Doado</button>
                            <button class="button status-button status-vencido" onclick="updateStatus(<?php echo $paint['cod_tinta']; ?>, '<?php echo $paint['fk_usuario_id_usuario']; ?>', '<?php echo $paint['dt_retirada']; ?>', 'vencido')">Vencido</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="paintModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle">Adicionar/Editar Tinta</h2>
            <form id="paintForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="action" name="action" value="add">
                <input type="hidden" id="cod_tinta" name="cod_tinta">
                <div class="form-group">
                    <label for="cor_tinta">Cor:</label>
                    <input type="text" id="cor_tinta" name="cor_tinta" required>
                </div>
                <div class="form-group">
                    <label for="quantidade">Quantidade:</label>
                    <input type="text" id="quantidade" name="quantidade" required>
                </div>
                <div class="form-group">
                    <label for="aplicacao">Aplicação:</label>
                    <textarea id="aplicacao" name="aplicacao" required></textarea>
                </div>
                <div class="form-group">
                    <label for="marca">Marca:</label>
                    <input type="text" id="marca" name="marca" required>
                </div>
                <div class="form-group">
                    <label for="imagem">Imagem:</label>
                    <input type="file" id="imagem" name="imagem">
                </div>
                <div class="form-group">
                    <label for="embalagem">Embalagem:</label>
                    <input type="text" id="embalagem" name="embalagem" required>
                </div>
                <div class="form-group">
                    <label for="acabamento">Acabamento:</label>
                    <input type="text" id="acabamento" name="acabamento" required>
                </div>
                <div class="form-group">
                    <label for="dt_validade">Data de Validade:</label>
                    <input type="date" id="dt_validade" name="dt_validade" required>
                </div>
                <div class="form-group">
                    <label for="fk_ponto_coleta_cod_ponto">Ponto de Coleta:</label>
                    <select id="fk_ponto_coleta_cod_ponto" name="fk_ponto_coleta_cod_ponto" required>
                        <option value="1">Ponto de Coleta 1</option>
                        <option value="2">Ponto de Coleta 2</option>
                        <option value="3">Ponto de Coleta 3</option>
                    </select>
                </div>
                <button type="submit" class="button">Salvar</button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('paintModal');
        const closeBtn = document.getElementsByClassName('close')[0];
        const form = document.getElementById('paintForm');

        function showAddModal() {
            document.getElementById('modalTitle').textContent = 'Adicionar Nova Tinta';
            document.getElementById('action').value = 'add';
            document.getElementById('cod_tinta').value = '';
            form.reset();
            modal.style.display = 'block';
        }

        function showEditModal(id) {
            document.getElementById('modalTitle').textContent = 'Editar Tinta';
            document.getElementById('action').value = 'edit';
            document.getElementById('cod_tinta').value = id;
            const paintCard = document.querySelector(`.paint-card[data-id="${id}"]`);
            document.getElementById('cor_tinta').value = paintCard.querySelector('h2').textContent;
            document.getElementById('marca').value = paintCard.querySelector('p:nth-child(2)').textContent.split(': ')[1];
            document.getElementById('quantidade').value = paintCard.querySelector('p:nth-child(3)').textContent.split(': ')[1];
            document.getElementById('embalagem').value = paintCard.querySelector('p:nth-child(4)').textContent.split(': ')[1];
            document.getElementById('acabamento').value = paintCard.querySelector('p:nth-child(5)').textContent.split(': ')[1];
            document.getElementById('dt_validade').value = paintCard.querySelector('p:nth-child(6)').textContent.split(': ')[1].split('/').reverse().join('-');
            
            modal.style.display = 'block';
        }

        closeBtn.onclick = function() {
            modal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        function updateStatus(cod_tinta, fk_usuario_id_usuario, dt_retirada, new_status) {
            if (confirm(`Tem certeza que deseja marcar esta tinta como ${new_status}?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="cod_tinta" value="${cod_tinta}">
                    <input type="hidden" name="fk_usuario_id_usuario" value="${fk_usuario_id_usuario}">
                    <input type="hidden" name="dt_retirada" value="${dt_retirada}">
                    <input type="hidden" name="new_status" value="${new_status}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>