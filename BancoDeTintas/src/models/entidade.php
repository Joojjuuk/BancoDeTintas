<?php
class Entidade {
    private $pdo;
    private $razao_social;
    private $cnpj;
    private $fk_usuario_id_usuario;

    public function __construct($pdo, $razao_social, $cnpj, $fk_usuario_id_usuario) {
        $this->pdo = $pdo;
        $this->razao_social = $razao_social;
        $this->cnpj = $cnpj;
        $this->fk_usuario_id_usuario = $fk_usuario_id_usuario;
    }

    public function getRazaoSocial() { return $this->razao_social; }
    public function getCnpj() { return $this->cnpj; }
    public function getFkUsuarioIdUsuario() { return $this->fk_usuario_id_usuario; }

    public function setRazaoSocial($razao_social) { $this->razao_social = $razao_social; }
    public function setCnpj($cnpj) { $this->cnpj = $cnpj; }
    public function setFkUsuarioIdUsuario($fk_usuario_id_usuario) { $this->fk_usuario_id_usuario = $fk_usuario_id_usuario; }

    public function create() {
        $sql = "INSERT INTO entidade (razao_social, CNPJ, fk_usuario_id_usuario) VALUES (:razao_social, :cnpj, :fk_usuario_id_usuario)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':razao_social' => $this->razao_social,
            ':cnpj' => $this->cnpj,
            ':fk_usuario_id_usuario' => $this->fk_usuario_id_usuario
        ]);
    }

    public function read($fk_usuario_id_usuario) {
        $sql = "SELECT * FROM entidade WHERE fk_usuario_id_usuario = :fk_usuario_id_usuario";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':fk_usuario_id_usuario' => $fk_usuario_id_usuario]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $this->setRazaoSocial($result['razao_social']);
            $this->setCnpj($result['CNPJ']);
            $this->setFkUsuarioIdUsuario($result['fk_usuario_id_usuario']);
            return true;
        }
        return false;
    }

    public function update() {
        $sql = "UPDATE entidade SET razao_social = :razao_social, CNPJ = :cnpj WHERE fk_usuario_id_usuario = :fk_usuario_id_usuario";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':razao_social' => $this->razao_social,
            ':cnpj' => $this->cnpj,
            ':fk_usuario_id_usuario' => $this->fk_usuario_id_usuario
        ]);
    }

    public function delete() {
        $sql = "DELETE FROM entidade WHERE fk_usuario_id_usuario = :fk_usuario_id_usuario";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':fk_usuario_id_usuario' => $this->fk_usuario_id_usuario]);
    }
}
?>