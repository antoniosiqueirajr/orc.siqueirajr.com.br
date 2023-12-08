<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class orchestrator_job_parameter extends B10_Model{
    
    public function __construct() {
        parent::__construct();
    }
    
    public function before_insert($_input, $input) {
        $input['job']   = get_last_segment();
        return $input;
    }
    
    public function after_insert($where, $_input, $input) {
        $parameter  =   $this->get($where);
        foreach($this->orchestrator_task->get_all(array('job'=>$parameter->job)) as $task){
            $this->orchestrator_task_parameter->insert(array(
                'task'      =>  $task->id,
                'parameter' =>  $where['id'],
                'value'     =>  $parameter->default_value
            ));
        }
    }
}