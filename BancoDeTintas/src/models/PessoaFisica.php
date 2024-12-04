<?php
class PessoaFisica {
    private $pdo;
    private $nome_completo;
    private $cpf;
    private $dt_nascimento;
    private $fk_usuario_id_usuario;

    public function __construct($pdo, $nome_completo, $cpf, $dt_nascimento, $fk_usuario_id_usuario) {
        $this->pdo = $pdo;
        $this->nome_completo = $nome_completo;
        $this->cpf = $cpf;
        $this->dt_nascimento = $dt_nascimento;
        $this->fk_usuario_id_usuario = $fk_usuario_id_usuario;
    }

    public function getNomeCompleto() { return $this->nome_completo; }
    public function getCpf() { return $this->cpf; }
    public function getDtNascimento() { return $this->dt_nascimento; }
    public function getFkUsuarioIdUsuario() { return $this->fk_usuario_id_usuario; }
    public function setNomeCompleto($nome_completo) { $this->nome_completo = $nome_completo; }
    public function setCpf($cpf) { $this->cpf = $cpf; }
    public function setDtNascimento($dt_nascimento) { $this->dt_nascimento = $dt_nascimento; }
    public function setFkUsuarioIdUsuario($fk_usuario_id_usuario) { $this->fk_usuario_id_usuario = $fk_usuario_id_usuario; }

    public function create() {
        $sql = "INSERT INTO pessoa_fisica (nome_completo, CPF, dt_nascimento, fk_usuario_id_usuario) VALUES (:nome_completo, :cpf, :dt_nascimento, :fk_usuario_id_usuario)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nome_completo' => $this->nome_completo,
            ':cpf' => $this->cpf,
            ':dt_nascimento' => $this->dt_nascimento,
            ':fk_usuario_id_usuario' => $this->fk_usuario_id_usuario
        ]);
    }

    public function read($fk_usuario_id_usuario) {
        $sql = "SELECT * FROM pessoa_fisica WHERE fk_usuario_id_usuario = :fk_usuario_id_usuario";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':fk_usuario_id_usuario' => $fk_usuario_id_usuario]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $this->setNomeCompleto($result['nome_completo']);
            $this->setCpf($result['CPF']);
            $this->setDtNascimento($result['dt_nascimento']);
            $this->setFkUsuarioIdUsuario($result['fk_usuario_id_usuario']);
            return true;
        }
        return false;
    }

    public function update() {
        $sql = "UPDATE pessoa_fisica SET nome_completo = :nome_completo, CPF = :cpf, dt_nascimento = :dt_nascimento WHERE fk_usuario_id_usuario = :fk_usuario_id_usuario";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nome_completo' => $this->nome_completo,
            ':cpf' => $this->cpf,
            ':dt_nascimento' => $this->dt_nascimento,
            ':fk_usuario_id_usuario' => $this->fk_usuario_id_usuario
        ]);
    }

    public function delete() {
        $sql = "DELETE FROM pessoa_fisica WHERE fk_usuario_id_usuario = :fk_usuario_id_usuario";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':fk_usuario_id_usuario' => $this->fk_usuario_id_usuario]);
    }
}
?>