<?php

class Autorizar {
    private $fk_doacao_doar_dias_disp;
    private $fk_doacao_doar_id_usuario;
    private $fk_doacao_doar_cod_tinta;
    private $fk_adm_email_inst;
    private $status;

    public function __construct($fk_doacao_doar_dias_disp, $fk_doacao_doar_id_usuario, $fk_doacao_doar_cod_tinta, $fk_adm_email_inst, $status) {
        $this->fk_doacao_doar_dias_disp = $fk_doacao_doar_dias_disp;
        $this->fk_doacao_doar_id_usuario = $fk_doacao_doar_id_usuario;
        $this->fk_doacao_doar_cod_tinta = $fk_doacao_doar_cod_tinta;
        $this->fk_adm_email_inst = $fk_adm_email_inst;
        $this->status = $status;
    }

    public function getFk_doacao_doar_dias_disp() { return $this->fk_doacao_doar_dias_disp; }
    public function getFk_doacao_doar_id_usuario() { return $this->fk_doacao_doar_id_usuario; }
    public function getFk_doacao_doar_cod_tinta() { return $this->fk_doacao_doar_cod_tinta; }
    public function getFk_adm_email_inst() { return $this->fk_adm_email_inst; }
    public function getStatus() { return $this->status; }

    public function setFk_doacao_doar_dias_disp($fk_doacao_doar_dias_disp) { $this->fk_doacao_doar_dias_disp = $fk_doacao_doar_dias_disp; }
    public function setFk_doacao_doar_id_usuario($fk_doacao_doar_id_usuario) { $this->fk_doacao_doar_id_usuario = $fk_doacao_doar_id_usuario; }
    public function setFk_doacao_doar_cod_tinta($fk_doacao_doar_cod_tinta) { $this->fk_doacao_doar_cod_tinta = $fk_doacao_doar_cod_tinta; }
    public function setFk_adm_email_inst($fk_adm_email_inst) { $this->fk_adm_email_inst = $fk_adm_email_inst; }   
    public function setStatus($status) { $this->status = $status; }
}
?>
