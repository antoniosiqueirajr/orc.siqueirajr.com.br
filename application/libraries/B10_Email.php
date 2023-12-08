<?php
    class B10_Email extends CI_Email{
        public function __construct() {
            parent::__construct();
            $CI =& get_instance();
            $CI->config->load('email',TRUE);
            $config = $CI->config->item('email');
            $this->initialize($config);
        }
        
        public function view($view,$data = array()){
            $CI =& get_instance();
            $mensagem = $CI->load->view("email/$view",$data,TRUE);
            return $this->message($mensagem);
        }
        
        public function send(){
            $CI =& get_instance();
            $CI->config->load('email',TRUE);
            $config = $CI->config->item('email');
            $this->from($config['from'], $config['name']);
            return parent::send();
        }
    }