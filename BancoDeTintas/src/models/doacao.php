<?php
class Doacao {
    private $horario_disp;
    private $dias_disp;
    private $fk_tintas_cod_tinta;
    private $fk_usuario_id_usuario;

    public function __construct($horario_disp, $dias_disp, $fk_tintas_cod_tinta, $fk_usuario_id_usuario) {
        $this->horario_disp = $horario_disp;
        $this->dias_disp = $dias_disp;
        $this->fk_tintas_cod_tinta = $fk_tintas_cod_tinta;
        $this->fk_usuario_id_usuario =$fk_usuario_id_usuario;
    } 

    public function getHorarioDisp() { return $this->horario_disp; }
    public function getDiasDisp() { return $this->dias_disp; }
    public function getFkTintasCodTinta() { return $this->fk_tintas_cod_tinta; }
    public function getFkUsuarioIdUsuario() { return $this->fk_usuario_id_usuario; }

    public function setHorarioDisp($horario_disp) { $this->horario_disp = $horario_disp; }
    public function setDiasDisp($dias_disp) { $this->dias_disp = $dias_disp; }
    public function setFkTintasCodTinta($fk_tintas_cod_tinta) { $this->fk_tintas_cod_tinta = $fk_tintas_cod_tinta; }
    public function setFkUsuarioIdUsuario($fk_usuario_id_usuario) { $this->fk_usuario_id_usuario = $fk_usuario_id_usuario; }
    
}

?>