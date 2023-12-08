<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if( !function_exists('voltar')){
    function voltar(){
        $CI=  get_instance();
        echo base_url($CI->usuario->persistencia('lastPage'));
    }
}

if( !function_exists('lastPage')){
    function lastPage(){
        $CI=  get_instance();
        return $CI->usuario->persistencia('lastPage');
    }
}

if (!function_exists('get_last_segment')) {
    function get_last_segment(){
        $url = explode('/', uri_string());
        return end($url);
    }
}

if (!function_exists('set_after_response')) {

    function set_after_response($after_response)
    {
        $CI                        = get_instance();
        $mensagem                   = $CI->session->userdata('mensagem');
        $mensagem['after_response'] = $after_response;
        $CI->session->set_userdata('mensagem', $mensagem);
    }
}
if (!function_exists('set_last_id')) {

    function set_last_id($last_id)
    {
        $CI                     = get_instance();
        $CI->session->set_userdata('last_id', $last_id);
    }
}