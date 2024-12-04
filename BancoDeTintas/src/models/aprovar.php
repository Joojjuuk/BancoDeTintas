<?php

class Aprovar {
    private $fk_adm_email_inst;
    private $fk_pedido_pedir_dt_retira;
    private $fk_pedido_pedir_id_usuario;
    private $fk_pedido_pedir_cod_tinta;
    private $status;

    public function __construct($fk_adm_email_inst, $fk_pedido_pedir_dt_retira, $fk_pedido_pedir_id_usuario, $fk_pedido_pedir_cod_tinta,  $status) {
        
        $this->fk_adm_email_inst = $fk_adm_email_inst;
        $this->fk_pedido_pedir_dt_retira = $fk_pedido_pedir_dt_retira;
        $this->fk_pedido_pedir_id_usuario = $fk_pedido_pedir_id_usuario;
        $this->fk_pedido_pedir_cod_tinta = $fk_pedido_pedir_cod_tinta;
        $this->status = $status;
    }

    public function getFk_adm_email_inst() { return $this->fk_adm_email_inst; }
    public function getFk_pedido_pedir_dt_retira() { return $this->fk_pedido_pedir_dt_retira; }
    public function getFk_pedido_pedir_id_usuario() { return $this->fk_pedido_pedir_id_usuario; }
    public function getFk_pedido_pedir_cod_tinta() { return $this->fk_pedido_pedir_cod_tinta; }
    public function getStatus() { return $this->status; }
    
    public function setFk_adm_email_inst($fk_adm_email_inst) { $this->fk_adm_email_inst = $fk_adm_email_inst; }   
    public function setFk_pedido_pedir_dt_retira($fk_pedido_pedir_dt_retira) { $this->fk_pedido_pedir_dt_retira = $fk_pedido_pedir_dt_retira; }
    public function setFk_pedido_pedir_id_usuario($fk_pedido_pedir_id_usuario) { $this->fk_pedido_pedir_id_usuario = $fk_pedido_pedir_id_usuario; }
    public function setFk_pedido_pedir_cod_tinta($fk_pedido_pedir_cod_tinta) { $this->fk_pedido_pedir_cod_tinta = $fk_pedido_pedir_cod_tinta; }
    public function setStatus($status) { $this->status = $status; }
}
?>
