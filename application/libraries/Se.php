<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Se{

    private $CI;
    
    private $token;
    private $filter_type=array('numeric');
    private $black_list;
    
    public function __construct() {
        $this->CI =& get_instance();
        
    	$black_list=array(
            'sql'=>array('CREATE','DROP','ALTER','GRANT','DELETE','AND','OR','XOR','IF','ELSEIF','ELSE','SRC','INSERT','SELECT','KILL','JOIN','UPDATE','IN','ANY','SOME','ALL','EXISTS','UNION','BEGIN','--'),
            'javascript'=>array('SCRIPT'),
            'html'=>array(''),
	);
        
        $pre_or_post_characters=array(' ','"','\'','(',')');
        
	$reserved=array();
	
	foreach($black_list AS $languege=>$reservedForLanguage)
	{
            foreach($reservedForLanguage as $reservedWord)
            {
                foreach($pre_or_post_characters as $pre){
                    foreach($pre_or_post_characters as $post){
                        $reserved[$languege][]=  strtoupper($pre.$reservedWord.$post);
                    }
                }
            }
	}
	$this->black_list=$reserved;
    }
    
    
    /* Métodos relacionados a filtragem dos inputs */
    private function sql($input,$type='where'){
        switch ($type) {
            case 'where':
                $original_string_upped = "";
                if (!is_array($input)){
                $original_string_upped=  strtoupper($input);
                }                
                $output=  str_replace($this->black_list['sql'], '', $original_string_upped);
                if($output===$original_string_upped){
                    return $this->CI->db->escape_str($input);
                }
                else{
                    return $this->CI->db->escape_str($output);
                }
                break;
            
            case 'value':
                return $this->CI->db->escape_str($input);
                
            default:
                return false;
                break;
        }
    }
    
    private function numeric($input){
        if(is_numeric($input)){
            $output = $input;
        }
        else{
            $output = 0;
        }
        return $output;
    }
    
    private function cep($input){
        $output=preg_replace('/(^0-9)/','',$input);
        if(strlen($output)!=8){
            return FALSE;
        }
        else{
            return $output;
        }
    }
    
    public function remove_acentos_utf8($input){
        return $this->remove_acentos(utf8_encode($input));
    }
    
    public function remove_acentos($input){
        $str = preg_replace('/[áàãâä]/ui', 'a', $input);
        $str = preg_replace('/[éèêë]/ui', 'e', $str);
        $str = preg_replace('/[íìîï]/ui', 'i', $str);
        $str = preg_replace('/[óòõôö]/ui', 'o', $str);
        $str = preg_replace('/[úùûü]/ui', 'u', $str);
        $str = preg_replace('/[ç]/ui', 'c', $str);
        $str = preg_replace('/[&]/ui', 'e', $str);
        $str = preg_replace('/[^a-z0-9 ]/i', '', $str);
        $output = preg_replace('/_+/', '_', $str);
        return $output;
    }
    
    public function only_numbers($input){
        $output = preg_replace('/[^0-9]/i', '', $input);
        return $output;
    }
    
    public function set_filter_type($type){
        $type=  strtolower($type);
        $filter_type=  explode('|', $type);
        if(method_exists($this, $filter_type[0])){
            $this->filter_type=$filter_type;
            return TRUE;
        }
        else{
            return FALSE;
        }
    }
    
    public function input_filter($input){
        $filter_type    =   $this->filter_type;
        $type           =   $filter_type[0];
        if(isset($filter_type[1])){
            return $this->$type($input,$filter_type[1]);
        }
        else{
            return $this->$type($input);
        }
    }
    
    /* Fim dos métodos relacionados a filtragem dos inputs */
    
    /* Callbacks para a library form_validation */
    
    public function not_default_check($input,$default){
        if(strtolower($input)==strtolower($default)){
            return FALSE;
        }
        else{
            return TRUE;
        }
    }
    
    public function valida_cpf_cnpj($input){
        $number=preg_replace('/[^0-9]/','', $input);
        $carac=strlen($number);
        $digito=substr($number,-2);
        $dig1='';
        $dig2='';
        switch ($carac){
            case 14:
                $arrayDig1=array(5,4,3,2,9,8,7,6,5,4,3,2);
                $arrayDig2=array(6,5,4,3,2,9,8,7,6,5,4,3,2);
                for($i=0;$i<=$carac-3;$i++){
                        $dig1=$dig1+(substr($number,$i,1)*$arrayDig1[$i]);
                }
                $dig1=$dig1%11;
                if($dig1<2){
                    $dig1=0;
                }
                else{
                    $dig1=11-$dig1;
                }

                for($i=0;$i<=$carac-2;$i++){
                    $dig2=$dig2+(substr($number,$i,1)*$arrayDig2[$i]);
                }
                $dig2=$dig2%11;
                if($dig2<2){
                    $dig2=0;
                }
                else{
                    $dig2=11-$dig2;
                }

                $digCalc=$dig1.$dig2;
                if($digito==$digCalc){
                    return $number;
                }
                else{
                    return FALSE;
                }
                break;

            case 11:
                for($i=0;$i<=$carac-3;$i++){
                    $dig1=$dig1+(substr($number,$i,1)*(10-$i));
                }
                $dig1=$dig1%11;
                if($dig1<2){
                    $dig1=0;
                }
                else{
                    $dig1=11-$dig1;
                }

                for($i=0;$i<=$carac-2;$i++){
                    $dig2=$dig2+(substr($number,$i,1)*(11-$i));
                }
                $dig2=$dig2%11;
                if($dig2<2){
                    $dig2=0;
                }
                else{
                    $dig2=11-$dig2;
                }

                $digCalc=$dig1.$dig2;
                if($digito==$digCalc){
                    return $number;
                }
                else{
                    return FALSE;
                }
                break;

            default:
                return FALSE;
        }
    }
    
    /* Fim dos callbacks para a library form_validation */
    
    /* geradores de token */
    
    function make_token(){
        $token=  md5(rand());
        $this->CI->session->set_flashdata('token',$token);
        return $token;
    }
    
    /* Fim dos geradores de tolken*/
    
    function make_captcha(){
        $captcha=substr(md5(rand().'iccaptcha'),0,8);
        $this->CI->session->set_flashdata('captcha',  strtolower($captcha));
        return $captcha;
    }
    function validate_captcha($input){
        if($this->CI->session->flashdata('captcha')===strtolower($input)){
            return TRUE;
        }
        return FALSE;
    }
    
    function get_filter($filter_type='sql|where'){
        $CI     =&  get_instance();
        $input  =   $CI->input->post();
        if(!is_array($input)){
            return FALSE;
        }
        $this->set_filter_type($filter_type);
        foreach($input as $key=>$value){
            if(is_array($value)){
                foreach($value as $k=>$v){
                    $data[$key][$k]=$this->input_filter($v);
                }
            }
            else{
                $data[$key]=$this->input_filter($value);
            }
        }
        return $data;
    }
    
    function get_filter_datatable($input = FALSE){
        if($input === FALSE){
            $CI     =&  get_instance();
            $input  =   $CI->input->post();
}
        if(!is_array($input)){
            return FALSE;
        }
        $this->set_filter_type('sql|where');
        foreach($input as $key=>$value){
            if(is_array($value)){
                $input[$key]    =   $this->get_filter_datatable($value);
            }
            else{
                $input[$key]=$this->input_filter($value);
            }
        }
        return $input;
    }
}