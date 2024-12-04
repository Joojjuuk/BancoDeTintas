<?php

class Tintas
{
    private string $imagem;
    private string $dt_validade;
    private string $cor_tinta;
    private string $quantidade;

    public function __construct(string $dt_validade = 'N/A', string $cor_tinta = 'N/A', string $quantidade = 'N/A', $imagem)
    {
        $this->dt_validade = $dt_validade;
        $this->cor_tinta = $cor_tinta;
        $this->quantidade = $quantidade;
    }

    public function getDtValidade(): string { return $this->dt_validade; }
    public function getCorTinta(): string { return $this->cor_tinta; }
    public function getQuantidade(): string { return $this->quantidade; }

}