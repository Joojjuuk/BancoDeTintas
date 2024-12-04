<?php
class Tinta {
    private $cor_tinta;
    private $quantidade;
    private $aplicacao;
    private $marca;
    private $imagem;
    private $embalagem;
    private $acabamento;
    private $cod_tinta;
    private $dt_validade;
    private $fk_ponto_coleta_cod_ponto;

    public function __construct( $cor_tinta, $quantidade, $aplicacao, $marca, $imagem, $embalagem, $acabamento, $dt_validade, $fk_ponto_coleta_cod_ponto) {
        $this->cor_tinta = $cor_tinta;
        $this->quantidade = $quantidade;
        $this->aplicacao = $aplicacao;
        $this->marca = $marca;
        $this->imagem = $imagem;
        $this->embalagem = $embalagem;
        $this->acabamento = $acabamento;
        $this->dt_validade = $dt_validade;
       // $this->cod_tinta = $cod_tinta;
        $this->fk_ponto_coleta_cod_ponto = $fk_ponto_coleta_cod_ponto;
    }

    public function getCorTinta() { return $this->cor_tinta; }
    public function getQuantidade() { return $this->quantidade; }
    public function getAplicacao() { return $this->aplicacao; }
    public function getMarca() { return $this->marca; }
    public function getImagem() { return $this->imagem; }
    public function getEmbalagem() { return $this->embalagem; }
    public function getAcabamento() { return $this->acabamento; }
    public function getCodTinta() { return $this->cod_tinta; }
    public function getDtValidade() { return $this->dt_validade; }
    public function getFkPontoColetaCodPonto() { return $this->fk_ponto_coleta_cod_ponto; }

    public function setCorTinta($cor_tinta) { $this->cor_tinta = $cor_tinta; }
    public function setQuantidade($quantidade) { $this->quantidade = $quantidade; }
    public function setAplicacao($aplicacao) { $this->aplicacao = $aplicacao; }
    public function setMarca($marca) { $this->marca = $marca; }
    public function setImagem($imagem) { $this->imagem = $imagem; }
    public function setEmbalagem($embalagem) { $this->embalagem = $embalagem; }
    public function setAcabamento($acabamento) { $this->acabamento = $acabamento; }
    public function setCodTinta($cod_tinta) { $this->cod_tinta = $cod_tinta; }
    public function setDtValidade($dt_validade) { $this->dt_validade = $dt_validade; }
    public function setFkPontoColetaCodPonto($fk_ponto_coleta_cod_ponto) { $this->fk_ponto_coleta_cod_ponto = $fk_ponto_coleta_cod_ponto; }
}
?>