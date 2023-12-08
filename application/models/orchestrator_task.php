<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class orchestrator_task extends B10_Model{
    
    public function __construct() {
        parent::__construct();
    }
    
    public function get_task($id){
        $where  =   array('orchestrator_task.id'=>$id);
        $this->db->select('orchestrator_task.*, orchestrator_job.name, orchestrator_job.function');
        $this->db->join('orchestrator_job', 'orchestrator_job.id = orchestrator_task.job');
        return $this->get($where);  
    }
    
    public function get_tasks(){
        $where  =   array(
            'next_date <='  =>  date('Y-m-d H:i:s'),
            'status'        =>  0,
        );
        $this->db->select('orchestrator_task.*, orchestrator_job.name, orchestrator_job.function');
        $this->db->join('orchestrator_job', 'orchestrator_job.id = orchestrator_task.job');
        return $this->get_all($where);  
    }
    
    public function start_tasks(){
        $where  =   array(
            'next_date <='  =>  date('Y-m-d H:i:s'),
            'status'        =>  0
        );
        return $this->update($where,array('status'=>1));  
    }
    
    public function start_task($task_id){
        $where  =   array('id'=>$task_id);
        $update = array('status'=>1,'last_execution_start'=>date('Y-m-d H:i:s'));
        $task = $this->get($where);
        if($task->periodicity_type == 5){
            $update['next_date'] = NULL;
        }
        return $this->update($where,$update);
    }
    
    public function end_task($task){
        if($task->periodicity_type == 5){
            $next_date = NULL;
        }
        else{
            switch ($task->periodicity_type) {
                case 1:
                    $period =   'hours';
                    break;
                case 2:
                    $period =   'days';
                    break;
                case 3:
                    $period =   'months';
                    break;
                case 4:
                    $period =   'minutes';
                    break;

                default:
                    break;
            }
            if($task->time_fixed){
                $start_date = $task->next_date;
            }
            else{
                $start_date = date('Y-m-d H:i:s');
            }
            $interval   =   $task->periodicity_interval;
            $next_date = date('Y-m-d H:i:s', strtotime("+$interval $period", strtotime($start_date)));
        }
        $update     =   array(
            'status'    =>  '0',
            'next_date' =>  $next_date,
            'last_execution_end'=>date('Y-m-d H:i:s')
        );
        return $this->update(array('id'=>$task->id),$update);
    }
    
    public function before_insert($_input, $input) {
        if($input['start_date'] == ''){
            $input['start_date']    =   date('Y-m-d H:i:s');
        }
        if($input['periodicity_interval'] == ''){
            $input['periodicity_interval']  =   1;
        }
        return $input;
    }
    
    public function after_insert($where, $_input, $input) {
        $task   =   $this->get($where);
        foreach($this->orchestrator_job_parameter->get_all(array('job'=>$task->job)) as $parameter){
            $this->orchestrator_task_parameter->insert(array(
                'task'      =>  $where['id'],
                'parameter' =>  $parameter->id,
                'value'     =>  $parameter->default_value
            ));
        }
        if($task->next_date == ''){
            $this->end_task($task);
        }
    }
    
    public function reativar(){
        $this->db->query("UPDATE orchestrator_task SET bt_active = 1 WHERE bt_active = 0");
    }
}