<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('contabil_7')){
    function contabil_7($input){
        $parte1 = substr(trim($input), 0, 2);
        $parte2 = substr(trim($input), 2, 2);
        $parte3 = substr(trim($input), 4, 3);
        return "$parte1.$parte2.$parte3";
    }   
}

if ( ! function_exists('sql_moeda')){
    function sql_moeda($input,$casas_decimais=2,$separador_de_milheiro='.',$simbolo='R$',$separador_decimal = ','){
        $output = number_format($input, $casas_decimais, $separador_decimal, $separador_de_milheiro);
        return trim("$simbolo $output");
    }   
}

if ( ! function_exists('sql_numero')){
    function sql_numero($input,$casas_decimais=2){
        return sql_moeda($input, $casas_decimais, '', '', ',');
    }   
}

if ( ! function_exists('numero_sql')){
    function numero_sql($input){
        
        $sem_letras=  preg_replace('/[^0-9,]/', '', $input);
        $output=  str_replace(',', '.', $sem_letras);
        if($output === ''){
            $output = 0;
        }
        
        return $output;
    }   
}

if (!function_exists('sql_porcentagem')) {

    function sql_porcentagem($input, $casas_decimais = FALSE,$separador_de_milheiro = FALSE, $simbolo = '%',$separador_decimal = FALSE){
        $CI = & get_instance();
        if ($casas_decimais === FALSE) {
            $casas_decimais        = 4;
            $separador_decimal     = ',';
            $separador_de_milheiro = '.';
        }
        $input  =   ((float)$input) * 100;
        $output = number_format((float)$input, $casas_decimais, $separador_decimal,$separador_de_milheiro);
        return trim("$simbolo $output");
    }
}

if (!function_exists('porcentagem_sql')) {

    function porcentagem_sql($input)
    {
        return numero_sql($input) / 100;
    }
}

if ( ! function_exists('data_sql')){
    function data_sql($input){
        $data_hora = explode(' ',$input);
        $data=explode('/',$data_hora[0]);
        if(isset($data[2])){
            $output=preg_replace('/[^0-9,]/', '',$data[2]).'-'.preg_replace('/[^0-9,]/', '',$data[1]).'-'.preg_replace('/[^0-9,]/', '',$data[0]);

            return $output;
        }
        return NULL;
    }
}

if ( ! function_exists('data_hora_sql')){
    function data_hora_sql($input){
        $data_hora = explode(' ',$input);
        $data=explode('/',$data_hora[0]);
        if(isset($data[2])){
            $output=preg_replace('/[^0-9,]/', '',$data[2]).'-'.preg_replace('/[^0-9,]/', '',$data[1]).'-'.preg_replace('/[^0-9,]/', '',$data[0]);

            return "$output $data_hora[1]";
        }
        return '';
    }   
}

if ( ! function_exists('data_corrida_sql')){
    function data_corrida_sql($input){
        return substr($input,4,4).'-'.substr($input,2,2).'-'.substr($input,0,2);
    }   
}

if ( ! function_exists('sql_data')){
    function sql_data($input){
        $data=explode('-',$input);
        if(isset($data[2])){
            $output=  substr('00'.$data[2],-2).'/'.substr('00'.$data[1],-2).'/'.substr('0000'.$data[0],-4);
            return $output;
        }
        return $input;
    }   
}

if ( ! function_exists('sql_data_hora')){
    function sql_data_hora($input){
        $data_hora = explode(' ', $input);
        $data=explode('-',$data_hora[0]);
        if(isset($data[2])){
            $output=  substr('00'.$data[2],-2).'/'.substr('00'.$data[1],-2).'/'.substr('0000'.$data[0],-4);
            return "$output $data_hora[1]";
        }
        return $input;
    }   
}

if ( ! function_exists('data_hora_nao_formatada_para_sql')){
    function data_hora_nao_formatada_para_sql($input){
        $data   = substr($input, 0, -6);
        $hora   = substr($input, -6);
        if(strlen($data) == 8){
            $data_formatada = substr($data,-4).'-'.substr($data,2,2).'-'.substr($data, 0, 2);
        }
        elseif(strlen($data) == 6){
            $data_formatada = substr(date('Y'),0,2).substr($data,-2).'-'.substr($data,2,2).'-'.substr($data, 0, 2);
        }
        else{
            return FALSE;
        }
        $hora_formatada =   substr($hora,0,2).':'.substr($hora,2,2).':'.substr($hora,4,2);
        return "$data_formatada $hora_formatada";
    }   
}

if ( ! function_exists('fone_sql')){
    function fone_sql($input){
        $output=  preg_replace('/[^0-9]/', '', $input);
        return $output;
    }   
}

if ( ! function_exists('sql_fone')){
    function sql_fone($input){
        $ddd='';
        if(strlen($input)>9){
            $ddd=substr($input, 0, 2);
            $input=  substr($input, 2);
        }
        $fim=  substr($input, -4);
        $input=  substr($input, 0, -4);
        $output="($ddd) $input-$fim";
        return $output;
        
    }   
}

if ( ! function_exists('db_array')){
    function db_array($input,$index_field='id',$return_object=TRUE,$return_fields='',$separator=' | ',$first_empty = FALSE){
        if(is_string($input)){
            $CI =& get_instance();
            $input = $CI->$input->get_all();
        }
        if(!is_bool($return_object)){
            $return_fields = $return_object;
            $return_object = FALSE;
        }
        if($first_empty){
            $output['']=$first_empty;
        }
        if(count($input)){
            foreach($input as $linha){
                if($return_object){
                    $output[$linha->$index_field]=$linha;
                }
                else{
                    if(!is_array($return_fields)){
                        $return_fields = array($return_fields);
                    }
                    $string=array();
                    foreach($return_fields as $field){
                        $string[]=$linha->$field;
                    }
                    $output[$linha->$index_field]=implode($separator,$string);
                }
            }
            if(isset($output)){
                return $output;
            }
        }
        return array();
    }
}

if ( ! function_exists('soma_dias_uteis')){
    function soma_dias_uteis($data_ini,$dias_somar,$dias_semana=array('1','2','3','4','5'),$feriados=array('12-25','01-01'),$op = '+'){
        $dias=0;
        while($dias<$dias_somar){
            $data_ini=date('Y-m-d', strtotime($op.'1 days',strtotime($data_ini)));
            if(!in_array(date('m-d',strtotime($data_ini)), $feriados)){
                if(in_array(date('w',strtotime($data_ini)), $dias_semana)){
                    $dias++;
                }
            }
        }
        return $data_ini;
    }
}

if ( ! function_exists('soma_meses')){
    function soma_meses($data_ini,$meses_somar,$op = '+'){
        return  date('Y-m-d', strtotime($op."$meses_somar months",strtotime($data_ini)));
    }
}

if( !function_exists('preencher')){
    function preencher($input,$comprimento,$preenchedor='0',$posicao = 'anterior'){
        $preencher = '';
        $x=0;
        while($x<$comprimento){
            $preencher.=$preenchedor;
            $x++;
        }
        if($posicao==='anterior'){
            $output = substr($preencher.$input, -$comprimento);
        }
        else{
                $output = substr($input.$preencher, 0, $comprimento);
        }
        return $output;
    }
}

if( !function_exists('sql_cep')){
    function sql_cep($input){
        $output = substr($input, 0, 2).'.'.substr($input, 2, 3).'-'.substr($input, 5);
        return $output;
    }
}

if( !function_exists('cpf_cnpj_mask')){
    function cpf_cnpj_mask($input){
        if(strlen($input) == 11){
            $parte1 = substr($input, 0, 3);
            $parte2 = substr($input, 3, 3);
            $parte3 = substr($input, 6, 3);
            $parte4 = substr($input, 9, 2);
            $output = "$parte1.$parte2.$parte3-$parte4";
        }
        else{
            $parte1 = substr($input, 0, 2);
            $parte2 = substr($input, 2, 3);
            $parte3 = substr($input, 5, 3);
            $parte4 = substr($input, 8, 4);
            $parte5 = substr($input, 12, 2);
            $output = "$parte1.$parte2.$parte3/$parte4-$parte5";
        }
        return $output;
    }
}

if( !function_exists('telefone_mask')){
    function telefone_mask($input){
        
        $sem_letras =   preg_replace('/[^0-9]/', '', $input);
        $count      =   strlen($sem_letras);
        switch ($count) {
            case 8:
                $output = substr($sem_letras, 0, 4).'-'.substr($sem_letras, 4);

                break;
            case 9:
                $output = substr($sem_letras, 0, 5).'-'.substr($sem_letras, 5);

                break;
            case 10:
                $output = '('.substr($sem_letras, 0, 2).') '.substr($sem_letras, 2, 4).'-'.substr($sem_letras, 6);

                break;
            case 11:
                $output = '('.substr($sem_letras, 0, 2).') '.substr($sem_letras, 2, 5).'-'.substr($sem_letras, 7);

                break;

            default:
                $output = $sem_letras;
                break;
        }
        return $output;
    }
}

if (!function_exists('mb_ucfirst')) {
    function mb_ucfirst($str, $encoding = "UTF-8", $lower_str_end = TRUE) {
        $first_letter = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding);
        $str_end = "";
        if ($lower_str_end){
            $str_end = mb_strtolower(mb_substr($str, 1, mb_strlen($str, $encoding), $encoding), $encoding);
        }
        else {
            $str_end = mb_substr($str, 1, mb_strlen($str, $encoding), $encoding);
        }
        $str = $first_letter . $str_end;
        return $str;
    }
}

if ( ! function_exists('make_var')){
    function make_var($input){
        
        $search=array('á','à','ã','â','é','ê','í','ó','ô','õ','ú','ü','ç');
        $replace=array('a','a','a','a','e','e','i','o','o','o','u','u','c');
        
        $filtro1=  mb_strtolower(trim($input));
        $filtro2=  str_replace($search,$replace,$filtro1);
        $filtro3=  str_replace(array(' ','+'), '_', $filtro2);
        $filtro4 = preg_replace('/[^a-z0-9]/i', '_', $filtro3);
        $output=$filtro4;
        $return = preg_replace('/--+/', '_', $output);
        if(substr($return, -1) == '_'){
            $return = substr($return, 0, -1);
        }   
        return $return;
    }
}
if ( ! function_exists('make_link')){
    function make_link($input){
        
        $search=array('á','à','ã','â','é','ê','í','ó','ô','õ','ú','ü','ç');
        $replace=array('a','a','a','a','e','e','i','o','o','o','u','u','c');
        
        $filtro1=  mb_strtolower(trim($input));
        $filtro2=  str_replace($search,$replace,$filtro1);
        $filtro3=  str_replace(array(' ','_','+'), '-', $filtro2);
        $filtro4 = preg_replace('/[^a-z0-9]/i', '-', $filtro3);
        $output=$filtro4;
        $return = preg_replace('/--+/', '-', $output);
        if(substr($return, -1) == '-'){
            $return = substr($return, 0, -1);
        }   
        return $return;
    }
}
if ( ! function_exists('make_link_pasta')){
    function make_link_pasta($input){
        
        $search=array('á','à','ã','â','é','ê','í','ó','ô','õ','ú','ü','ç');
        $replace=array('a','a','a','a','e','e','i','o','o','o','u','u','c');
        
        $filtro1=  mb_strtolower(trim($input));
        $filtro2=  str_replace($search,$replace,$filtro1);
        $filtro3=  str_replace(array(' ','-','+'), '_', $filtro2);
        $filtro4 = preg_replace('/[^a-z0-9]/i', '_', $filtro3);
        $output=$filtro4;
        $return = preg_replace('/--+/', '_', $output);
        if(substr($return, -1) == '_'){
            $return = substr($return, 0, -1);
        }   
        return $return;
    }
}

if ( ! function_exists('only_numbers')){
    function only_numbers($input){
        return preg_replace('/[^0-9]/', '', $input);
    }   
}

if ( ! function_exists('vazio_para_nulo')){
    function vazio_para_nulo($input){
        if($input == ''){
            return NULL;
        }
        return $input;
    }   
}


if (!function_exists('diferenca_entre_datas')) {

    function diferenca_entre_datas($tipo, $data, $base = FALSE){
        if ($base === FALSE) {
            $base = date('Y-m-d');
        }
        
        if(strtotime($base) <= strtotime($data)){
            $date_ini = new DateTime($base);
            $date_fin = new DateTime($data);
            $mult     = 1;
        }
        else{
            $date_ini = new DateTime($data);
            $date_fin = new DateTime($base);
            $mult     = -1;
        }

        $datediff = $date_ini->diff($date_fin);
        
        if($tipo == 'meses'){
            $return = ($datediff->y * 12) + $datediff->m;
        }
        else{
            $return = $datediff->$tipo;
        }
        return $return * $mult;
    }
}


if (!function_exists('get_period')) {

    function get_period($periodicity)
    {
        switch ($periodicity) {
            case 'daily':
                $period = "+1 day";
                break;
            case 'monthly':
                $period = "+1 month";
                break;
            case 'quarterly':
                $period = "+3 months";
                break;
            case 'yearly':
                $period = "+1 year";
                break;

            default:
                $period = "+0 day";
                break;
        }
        return $period;
    }
}

if (!function_exists('ajustar_numero_telefone')) {

    function ajustar_numero_telefone($input){
        $numeros    = only_numbers($input);
        if(strpos($numeros, '55') === 0){
            $numeros    = substr($numeros, 2);
        }
        if(strlen($numeros) == 8 || strlen($numeros) == 9){
            $numeros    =   '43'.$numeros;
        }
        if(strlen($numeros) < 10 || strlen($numeros) > 11){
            return FALSE;
        }
        return $numeros;
    }
}