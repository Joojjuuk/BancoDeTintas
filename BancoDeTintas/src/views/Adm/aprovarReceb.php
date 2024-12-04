<?php
require("../../../config/database.php");
require("../../../src/models/PessoaFisica.php");
require("../../../src/models/Entidade.php");
require("../../../src/models/Aprovar.php");
require("../../../src/controllers/auth.php");

verificarAdm();
$isLoggedIn = isset($_SESSION['adm_logado']) && $_SESSION['adm_logado'] === true;

function fetchPendingReceipts($pdo) {
    $sql = "SELECT p.*, t.cor_tinta, t.quantidade, u.id_usuario
            FROM pedido_pedir p
            JOIN tintas t ON p.fk_tintas_cod_tinta = t.cod_tinta
            JOIN usuario u ON p.fk_usuario_id_usuario = u.id_usuario
            LEFT JOIN aprovar a ON p.dt_retirada = a.fk_pedido_pedir_dt_retira
                AND p.fk_usuario_id_usuario = a.fk_pedido_pedir_id_usuario
                AND p.fk_tintas_cod_tinta = a.fk_pedido_pedir_cod_tinta
            WHERE a.fk_pedido_pedir_cod_tinta IS NULL OR a.status = 'pendente'";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_POST['id_usuario'] ?? '';
    $cod_tinta = $_POST['cod_tinta'] ?? '';
    $action = $_POST['action'] ?? '';
    $observacao = $_POST['observacao'] ?? '';
    
    try {
        $pdo->beginTransaction();

        if ($action === 'aprovado' || $action === 'rejeitado') {
            $sql = "SELECT dt_retirada, fk_usuario_id_usuario, fk_tintas_cod_tinta 
                   FROM pedido_pedir 
                   WHERE fk_usuario_id_usuario = :id_usuario AND fk_tintas_cod_tinta = :cod_tinta";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id_usuario' => $id_usuario, ':cod_tinta' => $cod_tinta]);
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($pedido) {
                $admin_email = $_SESSION['admin_email'] ?? 'admin@teste.com';

                $aprovar = new Aprovar(
                    $admin_email,
                    $pedido['dt_retirada'],
                    $pedido['fk_usuario_id_usuario'],
                    $pedido['fk_tintas_cod_tinta'],
                    $action
                );

                $sql = "INSERT INTO aprovar (
                            fk_adm_email_inst,
                            fk_pedido_pedir_dt_retira, 
                            fk_pedido_pedir_id_usuario, 
                            fk_pedido_pedir_cod_tinta, 
                            status
                        ) VALUES (
                            :admin_email,
                            :dt_retira, 
                            :id_usuario, 
                            :cod_tinta, 
                            :status
                        ) ON DUPLICATE KEY UPDATE
                            status = :status,
                            fk_adm_email_inst = :admin_email";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':admin_email' => $aprovar->getFk_adm_email_inst(),
                    ':dt_retira' => $aprovar->getFk_pedido_pedir_dt_retira(),
                    ':id_usuario' => $aprovar->getFk_pedido_pedir_id_usuario(),
                    ':cod_tinta' => $aprovar->getFk_pedido_pedir_cod_tinta(),
                    ':status' => $aprovar->getStatus()
                ]);
            }
            echo json_encode(['success' => true, 'message' => 'Recebimento ' . ($action === 'aprovado' ? 'aprovado' : 'rejeitado') . ' com sucesso']);
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

$pendingReceipts = fetchPendingReceipts($pdo);
foreach ($pendingReceipts as &$receipt) {
    $userId = $receipt['fk_usuario_id_usuario'];
    $pessoaFisica = new PessoaFisica($pdo, '', '', '', $userId);
    $entidade = new Entidade($pdo, '', '', $userId);

    if ($pessoaFisica->read($userId)) {
        $receipt['nome'] = $pessoaFisica->getNomeCompleto();
        $receipt['tipo'] = 'Pessoa Física';
    } elseif ($entidade->read($userId)) {
        $receipt['nome'] = $entidade->getRazaoSocial();
        $receipt['tipo'] = 'Entidade';
    } else {
        $receipt['nome'] = 'Nome não encontrado';
        $receipt['tipo'] = 'Desconhecido';
    }
}
unset($receipt);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aprovação de Recebimentos - Admin</title>
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
        .receipt-card {
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
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.875rem;
        }
        .status-pendente {
            background: var(--warning-color);
            color: black;
        }
        .status-aprovado {
            background: var(--success-color);
            color: white;
        }
        .status-rejeitado {
            background: var(--danger-color);
            color: white;
        }
        .receipt-info {
            display: grid;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        .receipt-info-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 4px;
        }
        .observation-field {
            width: 100%;
            padding: 0.5rem;
            margin: 1rem 0;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--text-color);
            border-radius: 4px;
            color: var(--text-color);
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
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
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
        <h1 class="admin-title">Painel Administrativo - Aprovação de Recebimentos</h1>
        <nav class="admin-nav">
            <a href="aprovartinta.php">Aprovar Tintas</a>
            <a href="catalogo.php">Catálogo</a>
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
        <?php foreach ($pendingReceipts as $receipt): ?>
            <div class="receipt-card" data-id="<?= htmlspecialchars($receipt['id_usuario']) ?>" data-cod-tinta="<?= htmlspecialchars($receipt['fk_tintas_cod_tinta']) ?>">
                <div class="card-header">
                    <h2>Recebimento #<?= htmlspecialchars($receipt['id_usuario']) ?> - Tinta #<?= htmlspecialchars($receipt['fk_tintas_cod_tinta']) ?></h2>
                    <span class="status-badge status-pendente">Pendente</span>
                </div>
                
                <div class="receipt-info">
                    <div class="receipt-info-item">
                        <span>Nome:</span>
                        <span><?= htmlspecialchars($receipt['nome']) ?></span>
                    </div>
                    <div class="receipt-info-item">
                        <span>Tipo:</span>
                        <span><?= htmlspecialchars($receipt['tipo']) ?></span>
                    </div>
                    <div class="receipt-info-item">
                        <span>Data de Retirada:</span>
                        <span><?= date('d/m/Y', strtotime($receipt['dt_retirada'])) ?></span>
                    </div>
                    <div class="receipt-info-item">
                        <span>Finalidade:</span>
                        <span><?= htmlspecialchars($receipt['finalidade']) ?></span>
                    </div>
                    <div class="receipt-info-item">
                        <span>Cor da Tinta:</span>
                        <span><?= htmlspecialchars($receipt['cor_tinta']) ?></span>
                    </div>
                    <div class="receipt-info-item">
                        <span>Quantidade:</span>
                        <span><?= htmlspecialchars($receipt['quantidade']) ?></span>
                    </div>
                </div>

                <textarea 
                    class="observation-field" 
                    placeholder="Adicione observações sobre o recebimento..."
                ></textarea>

                <div class="action-buttons">
                    <button 
                        class="btn btn-approve" 
                        onclick="updateStatus(<?= htmlspecialchars($receipt['id_usuario']) ?>, <?= htmlspecialchars($receipt['fk_tintas_cod_tinta']) ?>, 'aprovado')"
                    >
                        Confirmar Recebimento
                    </button>
                    <button 
                        class="btn btn-reject" 
                        onclick="updateStatus(<?= htmlspecialchars($receipt['id_usuario']) ?>, <?= htmlspecialchars($receipt['fk_tintas_cod_tinta']) ?>, 'rejeitado')"
                    >
                        Reportar Problema
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        async function updateStatus(id_usuario, cod_tinta, action) {
            const card = document.querySelector(`[data-id="${id_usuario}"][data-cod-tinta="${cod_tinta}"]`);
            const observacao = card.querySelector('.observation-field').value;
            
            try {
                const response = await fetch('aprovarReceb.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id_usuario=${id_usuario}&cod_tinta=${cod_tinta}&action=${action}&observacao=${encodeURIComponent(observacao)}`
                });

                const data = await response.json();
                
                if (data.success) {
                    const badge = card.querySelector('.status-badge');
                    badge.className = `status-badge status-${action}`;
                    badge.textContent = action.charAt(0).toUpperCase() + action.slice(1);

                    const buttons = card.querySelectorAll('.btn');
                    buttons.forEach(btn => btn.disabled = true);

                    alert(`Recebimento ${action} com sucesso!`);
                } else {
                    throw new Error(data.error || 'Erro desconhecido');
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao processar a solicitação: ' + error.message);
            }
        }
    </script>
</body>
</html>