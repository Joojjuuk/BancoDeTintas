<?php
class PontoColeta {
    private $cod_ponto;
    private $cep;
    private $endereco;
    private $cidade;
    private $latitude;
    private $longitude;

    public function __construct($cod_ponto, $cep, $endereco, $cidade, $latitude, $longitude) {
        $this->cod_ponto = $cod_ponto;
        $this->cep = $cep;
        $this->endereco = $endereco;
        $this->cidade = $cidade;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function getCodPonto() { return $this->cod_ponto; }
    public function getCep() { return $this->cep; }
    public function getEndereco() { return $this->endereco; }
    public function getCidade() { return $this->cidade; }
    public function getLatitude() { return $this->latitude; }
    public function getLongitude() { return $this->longitude; }

    public function setCodPonto($cod_ponto) { $this->cod_ponto = $cod_ponto; }
    public function setCep($cep) { $this->cep = $cep; }
    public function setEndereco($endereco) { $this->endereco = $endereco; }
    public function setCidade($cidade) { $this->cidade = $cidade; }
    public function setLatitude($latitude) { $this->latitude = $latitude; }
    public function setLongitude($longitude) { $this->longitude = $longitude; }
}
?>