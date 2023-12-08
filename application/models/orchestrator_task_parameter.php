<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class orchestrator_task_parameter extends B10_Model{
    
    public function __construct() {
        parent::__construct();
    }
    
    public function get_params($task){
        $where  =   array('task'=>$task);
        $this->db->join('orchestrator_job_parameter','orchestrator_job_parameter.id = orchestrator_task_parameter.parameter');
        return $this->get_all($where);
    }
    
    public function before_insert($_input, $input) {
        $input['task'] = get_last_segment();
        return parent::before_insert($_input, $input);
    }
}