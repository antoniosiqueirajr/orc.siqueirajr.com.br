<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class B10_Security extends CI_Security{
    
    public function __construct() {
        parent::__construct();
    }
    
    public function csrf_show_error(){
        require 'application/config/config.php';
        header('location: '.$config['base_url']);
    }
}