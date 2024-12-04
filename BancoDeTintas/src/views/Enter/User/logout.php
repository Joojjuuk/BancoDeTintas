<?php
require_once('../../../../config/database.php');
require_once('../../../../src/models/Tinta.php');
require_once('../../../../src/models/Usuario.php');
require_once('../../../../src/models/Autorizar.php');
require_once('../../../../src/models/PessoaFisica.php');
require_once('../../../../src/models/Entidade.php');
require_once('../../../../src/models/Aprovar.php');
require('../../../../src/controllers/auth.php');



verificarSessao();
$isLoggedIn = isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado'] === true;
$fk_usuario_id_usuario = $_SESSION['id_usuario'];

// Criar instância do usuário
$usuario = new Usuario($pdo, '', '', '', '', '');
$usuario->read($fk_usuario_id_usuario);

// Verificar se é pessoa física ou entidade
$pessoa_fisica = new PessoaFisica($pdo, '', '', '', $fk_usuario_id_usuario);
$entidade = new Entidade($pdo, '', '', $fk_usuario_id_usuario);

if ($pessoa_fisica->read($fk_usuario_id_usuario)) {
    $tipo_usuario = 'pessoa_fisica';
    $nome = $pessoa_fisica->getNomeCompleto();
    $identificacao = $pessoa_fisica->getCpf();
    $data_nascimento = $pessoa_fisica->getDtNascimento();
} elseif ($entidade->read($fk_usuario_id_usuario)) {
    $tipo_usuario = 'entidade';
    $nome = $entidade->getRazaoSocial();
    $identificacao = $entidade->getCnpj();
    $data_nascimento = 'N/A';
} else {
    // Erro: usuário não encontrado
    header("Location: error.php");
    exit();
}

// Buscar doações aprovadas
$stmt = $pdo->prepare("SELECT a.status, t.cor_tinta, t.cod_tinta, a.fk_doacao_doar_dias_disp 
                       FROM autorizar a 
                       JOIN tintas t ON a.fk_doacao_doar_cod_tinta = t.cod_tinta 
                       WHERE a.fk_doacao_doar_id_usuario = :user_id AND a.status = 'aprovado'");
$stmt->execute([':user_id' => $fk_usuario_id_usuario]);
$doacoes_aprovadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar pedidos aprovados
$stmt = $pdo->prepare("SELECT a.status, t.cor_tinta, t.cod_tinta, a.fk_pedido_pedir_dt_retira 
                       FROM aprovar a 
                       JOIN tintas t ON a.fk_pedido_pedir_cod_tinta = t.cod_tinta 
                       WHERE a.fk_pedido_pedir_id_usuario = :user_id AND a.status = 'aprovado'");
$stmt->execute([':user_id' => $fk_usuario_id_usuario]);
$pedidos_aprovados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar doações rejeitadas
$stmt = $pdo->prepare("SELECT a.status, t.cor_tinta, t.cod_tinta, a.fk_doacao_doar_dias_disp 
                       FROM autorizar a 
                       JOIN tintas t ON a.fk_doacao_doar_cod_tinta = t.cod_tinta 
                       WHERE a.fk_doacao_doar_id_usuario = :user_id AND a.status = 'rejeitado'");
$stmt->execute([':user_id' => $fk_usuario_id_usuario]);
$doacoes_rejeitadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar pedidos rejeitados
$stmt = $pdo->prepare("SELECT a.status, t.cor_tinta, t.cod_tinta, a.fk_pedido_pedir_dt_retira 
                       FROM aprovar a 
                       JOIN tintas t ON a.fk_pedido_pedir_cod_tinta = t.cod_tinta 
                       WHERE a.fk_pedido_pedir_id_usuario = :user_id AND a.status = 'rejeitado'");
$stmt->execute([':user_id' => $fk_usuario_id_usuario]);
$pedidos_rejeitados = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página do Usuário</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }

        body {
            background: linear-gradient(#4163fc, #212123);
            color: #e0e1dd;
            font-family: "Poppins", sans-serif;
        }

        .btn-logout, .btn-back {
            background: #4163fc;
            color: #e0e1dd;
            border: none;
            cursor: pointer;
            transition: background 0.5s;
            padding: 10px 20px;
            font-size: 16px;
            text-transform: uppercase;
            width: 100%;
            margin-top: 20px; 
        }

        .btn-logout:hover, .btn-back:hover {
            background: #00177e;
            box-shadow: 0 0 5px #00177e, 0 0 25px #00177e, 0 0 50px #00177e, 0 0 100px #00177e;
            border-radius: 5px;
        }

        .container {
            height: auto; 
            min-height: 100vh; 
            position: relative;
            margin: 0 auto;
            width: 90%;
            max-width: 500px;
            padding: 40px;
            background: rgba(0, 0, 0, 0.5);
            box-sizing: border-box;
            box-shadow: 0 15px 25px lightgray;
            border-radius: 10px;
            color: #fff;
        }

        .header h1 {
            margin: 0 0 30px;
            padding: 0;
            text-align: center;
            color: #fff;
        }

        .user-info, .orders {
            margin-top: 20px;
        }

        .user-info p {
            font-size: 16px;
            margin-bottom: 10px;
            color: #fff;
        }

        .orders {
            max-height: 50vh;
            overflow-y: auto;
        }

        .order-card {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            padding: 15px;
            margin: 10px 0;
        }

        .order-card p {
            margin: 0;
            color: #fff;
        }

        .rejected {
            background: rgba(255, 0, 0, 0.1);
            border: 1px solid rgba(255, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .container {
                width: 95%;
                padding: 20px;
            }

            .btn-logout, .btn-back {
                font-size: 14px;
                padding: 8px 15px;
            }

            .user-info p {
                font-size: 14px;
            }

            .order-card {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bem-vindo(a), <?php echo htmlspecialchars($nome); ?>!</h1>
        </div>
        <div class="user-info">
            <h2>Suas Informações</h2>
            <p><strong>Nome:</strong> <?php echo htmlspecialchars($nome); ?></p>
            <p><strong><?php echo $tipo_usuario == 'pessoa_fisica' ? 'CPF' : 'CNPJ'; ?>:</strong> <?php echo htmlspecialchars($identificacao); ?></p>
            <?php if ($tipo_usuario == 'pessoa_fisica'): ?>
                <p><strong>Data de Nascimento:</strong> <?php echo htmlspecialchars($data_nascimento); ?></p>
            <?php endif; ?>
        </div>
        <div class="orders">
            <h2>Doações Aprovadas</h2>
            <?php foreach ($doacoes_aprovadas as $doacao): ?>
                <div class="order-card">
                    <p><strong>Tipo:</strong> Doação</p>
                    <p><strong>Código da Tinta:</strong> <?php echo htmlspecialchars($doacao['cod_tinta']); ?></p>
                    <p><strong>Cor da Tinta:</strong> <?php echo htmlspecialchars($doacao['cor_tinta']); ?></p>
                    <p><strong>Data:</strong> <?php echo htmlspecialchars($doacao['fk_doacao_doar_dias_disp']); ?></p>
                </div>
            <?php endforeach; ?>

            <h2>Pedidos Aprovados</h2>
            <?php foreach ($pedidos_aprovados as $pedido): ?>
                <div class="order-card">
                    <p><strong>Tipo:</strong> Retirada</p>
                    <p><strong>Código da Tinta:</strong> <?php echo htmlspecialchars($pedido['cod_tinta']); ?></p>
                    <p><strong>Cor da Tinta:</strong> <?php echo htmlspecialchars($pedido['cor_tinta']); ?></p>
                    <p><strong>Data:</strong> <?php echo htmlspecialchars($pedido['fk_pedido_pedir_dt_retira']); ?></p>
                </div>
            <?php endforeach; ?>

            <h2>Doações Rejeitadas</h2>
            <?php foreach ($doacoes_rejeitadas as $doacao): ?>
                <div class="order-card rejected">
                    <p><strong>Tipo:</strong> Doação</p>
                    <p><strong>Código da Tinta:</strong> <?php echo htmlspecialchars($doacao['cod_tinta']); ?></p>
                    <p><strong>Cor da Tinta:</strong> <?php echo htmlspecialchars($doacao['cor_tinta']); ?></p>
                    <p><strong>Data:</strong> <?php echo htmlspecialchars($doacao['fk_doacao_doar_dias_disp']); ?></p>
                </div>
            <?php endforeach; ?>

            <h2>Pedidos Rejeitados</h2>
            <?php foreach ($pedidos_rejeitados as $pedido): ?>
                <div class="order-card rejected">
                    <p><strong>Tipo:</strong> Retirada</p>
                    <p><strong>Código da Tinta:</strong> <?php echo htmlspecialchars($pedido['cod_tinta']); ?></p>
                    <p><strong>Cor da Tinta:</strong> <?php echo htmlspecialchars($pedido['cor_tinta']); ?></p>
                    <p><strong>Data:</strong> <?php echo htmlspecialchars($pedido['fk_pedido_pedir_dt_retira']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <form method="POST" action="../../../controllers/logout.php">
            <button type="submit" class="btn-logout">Sair</button>
        </form>
        <form method="POST" action="javascript:history.back()">
            <button type="submit" class="btn-back">Voltar para a Página Anterior</button>
        </form>
    </div>
</body>
</html>