<?php
class Usuario {
    private $pdo;
    private $id_usuario;
    private $cep;
    private $email;
    private $senha;
    private $endereco;
    private $cidade;

    public function __construct($pdo, $cep, $email, $senha, $endereco, $cidade) {
        $this->pdo = $pdo;
        $this->cep = $cep;
        $this->email = $email;
        $this->senha = $senha;
        $this->endereco = $endereco;
        $this->cidade = $cidade;
    }

    public function getIdUsuario() { return $this->id_usuario; }
    public function getCep() { return $this->cep; }
    public function getEmail() { return $this->email; }
    public function getSenha() { return $this->senha; }
    public function getEndereco() { return $this->endereco; }
    public function getCidade() { return $this->cidade; }

    public function setIdUsuario($id_usuario) { $this->id_usuario = $id_usuario; }
    public function setCep($cep) { $this->cep = $cep; }
    public function setEmail($email) { $this->email = $email; }
    public function setSenha($senha) { $this->senha = $senha; }
    public function setEndereco($endereco) { $this->endereco = $endereco; }
    public function setCidade($cidade) { $this->cidade = $cidade; }

    public function create() {
        $sql = "INSERT INTO usuario (cep, email, senha, endereco, cidade) VALUES (:cep, :email, :senha, :endereco, :cidade)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':cep' => $this->cep,
            ':email' => $this->email,
            ':senha' => $this->senha,
            ':endereco' => $this->endereco,
            ':cidade' => $this->cidade
        ]);
        $this->id_usuario = $this->pdo->lastInsertId();
        return $this->id_usuario;
    }

    public function read($id) {
        $sql = "SELECT * FROM usuario WHERE id_usuario = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $this->setIdUsuario($result['id_usuario']);
            $this->setCep($result['cep']);
            $this->setEmail($result['email']);
            $this->setSenha($result['senha']);
            $this->setEndereco($result['endereco']);
            $this->setCidade($result['cidade']);
            return true;
        }
        return false;
    }

    public function update() {
        $sql = "UPDATE usuario SET cep = :cep, email = :email, endereco = :endereco, cidade = :cidade WHERE id_usuario = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id' => $this->id_usuario,
            ':cep' => $this->cep,
            ':email' => $this->email,
            ':endereco' => $this->endereco,
            ':cidade' => $this->cidade
        ]);
    }

    public function delete() {
        $sql = "DELETE FROM usuario WHERE id_usuario = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $this->id_usuario]);
    }
}
?>