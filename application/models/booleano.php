<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');class booleano extends B10_Model{    public function __construct() {        parent::__construct();        $nao    =   new stdClass();        $nao->id            =   0;        $nao->naosim        =   'Não';        $nao->inativoativo  =   'Inativo';        $sim    =   new stdClass();        $sim->id            =   1;        $sim->naosim        =   'Sim';        $sim->inativoativo  =   'Ativo';        $dados  =   array(            $nao,            $sim        );        $this->dados    =   $dados;    }        private $dados;        public function get_all($where = FALSE, $order_by = FALSE, $limit = FALSE) {        return $this->dados;    }}