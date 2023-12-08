<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('output'))
{
    function output($output)
    {
        echo utf8_encode($output);
    }   
}

if( !function_exists('autofocus')){
    function autofocus($focus){
        $CI=  get_instance();
        $CI->session->set_userdata('autofocus', $focus);
    }
}

if( !function_exists('window_open')){
    function window_open($url){
        $CI=  get_instance();
        $CI->session->set_userdata('window_open', $url);
    }
}

if( !function_exists('mensagem')){
    function mensagem($output){
        $CI=  get_instance();
        $CI->session->set_userdata('mensagem', '<p>'.$output.'</p>');
    }
}

if( !function_exists('swal')){
    function swal($title, $message, $type){
        $CI=  get_instance();
        $CI->session->set_userdata('swal',array(
            'title'     =>  $title,
            'message'   =>  $message,
            'type'      =>  $type
        ));
    }
}

if( !function_exists('dialog')){
    function dialog($body,$title = 'Diálogo',$add_p_tag = FALSE){
        if($add_p_tag){
            $body = "<p>$body</p>";
        }
        $CI=  get_instance();
        $CI->load->view('site/dialog',array('body'=>$body,'title'=>$title,));
    }
}

if( !function_exists('set_headers')){
    function set_headers(){
        
    }
}

if( !function_exists('page_break')){
    function page_break($position = 'before'){
        echo "<div style=\"page-break-$position: always;text-align: center;\"></div>";
    }
}

if( !function_exists('instant_print')){
    function instant_print(){
        echo '<script>','window.print();','</script>';
    }
}


if( !function_exists('output_file')){
    function output_file($Source_File, $Download_Name, $mime_type=''){
     /*
    $Source_File = path to a file to output
    $Download_Name = filename that the browser will see 
    $mime_type = MIME type of the file (Optional)
    */
     if(!is_readable($Source_File)) die('File not found or inaccessible!');

     $size = filesize($Source_File);
     $Download_Name = rawurldecode($Download_Name);

     /* Figure out the MIME type (if not specified) */
     $known_mime_types=array(
        "pdf" => "application/pdf",
        "csv" => "application/csv",
        "txt" => "text/plain",
        "html" => "text/html",
        "htm" => "text/html",
        "exe" => "application/octet-stream",
        "zip" => "application/zip",
        "doc" => "application/msword",
        "xls" => "application/vnd.ms-excel",
        "ppt" => "application/vnd.ms-powerpoint",
        "gif" => "image/gif",
        "png" => "image/png",
        "jpeg"=> "image/jpg",
        "jpg" =>  "image/jpg",
        "php" => "text/plain"
     );

     if($mime_type==''){
         $file_extension = strtolower(substr(strrchr($Source_File,"."),1));
         if(array_key_exists($file_extension, $known_mime_types)){
            $mime_type=$known_mime_types[$file_extension];
         } else {
            $mime_type="application/force-download";
         };
     };

     @ob_end_clean(); //off output buffering to decrease Server usage

     // if IE, otherwise Content-Disposition ignored
     if(ini_get('zlib.output_compression'))
      ini_set('zlib.output_compression', 'Off');

     header('Content-Type: ' . $mime_type);
     header('Content-Disposition: attachment; filename="'.$Download_Name.'"');
     header("Content-Transfer-Encoding: binary");
     header('Accept-Ranges: bytes');

     header("Cache-control: private");
     header('Pragma: private');
     header("Expires: Thu, 26 Jul 2012 05:00:00 GMT");

     // multipart-download and download resuming support
     if(isset($_SERVER['HTTP_RANGE']))
     {
        list($a, $range) = explode("=",$_SERVER['HTTP_RANGE'],2);
        list($range) = explode(",",$range,2);
        list($range, $range_end) = explode("-", $range);
        $range=intval($range);
        if(!$range_end) {
            $range_end=$size-1;
        } else {
            $range_end=intval($range_end);
        }

        $new_length = $range_end-$range+1;
        header("HTTP/1.1 206 Partial Content");
        header("Content-Length: $new_length");
        header("Content-Range: bytes $range-$range_end/$size");
     } else {
        $new_length=$size;
        header("Content-Length: ".$size);
     }

     /* output the file itself */
     $chunksize = 1*(1024*1024); //you may want to change this
     $bytes_send = 0;
     if ($Source_File = fopen($Source_File, 'r'))
     {
        if(isset($_SERVER['HTTP_RANGE']))
        fseek($Source_File, $range);

        while(!feof($Source_File) && 
            (!connection_aborted()) && 
            ($bytes_send<$new_length)
              )
        {
            $buffer = fread($Source_File, $chunksize);
            print($buffer); //echo($buffer); // is also possible
            flush();
            $bytes_send += strlen($buffer);
        }
     fclose($Source_File);
     } else die('Error - can not open file.');

    die();
    }
}

if( !function_exists('visualizar_arquivo')){
    function visualizar_arquivo($path){
        $path_arr = explode('.',$path);
        $tipo   = end($path_arr);
        switch ($tipo) {
            case 'pdf':
                header('Content-Type: application/pdf');
                break;
            case 'mp4':
                $fp = @fopen($path, 'rb');
                $size   = filesize($path); // File size
                $length = $size;           // Content length
                $start  = 0;               // Start byte
                $end    = $size - 1;       // End byte
                header('Content-type: video/mp4');
                header("Accept-Ranges: 0-$length");
                if (isset($_SERVER['HTTP_RANGE'])) {
                    $c_start = $start;
                    $c_end   = $end;
                    list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
                    if (strpos($range, ',') !== false) {
                        header('HTTP/1.1 416 Requested Range Not Satisfiable');
                        header("Content-Range: bytes $start-$end/$size");
                        exit;
                    }
                    if ($range == '-') {
                        $c_start = $size - substr($range, 1);
                    }else{
                        $range  = explode('-', $range);
                        $c_start = $range[0];
                        $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
                    }
                    $c_end = ($c_end > $end) ? $end : $c_end;
                    if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
                        header('HTTP/1.1 416 Requested Range Not Satisfiable');
                        header("Content-Range: bytes $start-$end/$size");
                        exit;
                    }
                    $start  = $c_start;
                    $end    = $c_end;
                    $length = $end - $start + 1;
                    fseek($fp, $start);
                    header('HTTP/1.1 206 Partial Content');
                }
                header("Content-Range: bytes $start-$end/$size");
                header("Content-Length: ".$length);
                $buffer = 1024 * 8;
                while(!feof($fp) && ($p = ftell($fp)) <= $end) {
                    if ($p + $buffer > $end) {
                        $buffer = $end - $p + 1;
                    }
                    set_time_limit(0);
                    echo fread($fp, $buffer);
                    flush();
                }
                fclose($fp);
                exit();
                break;
            case 'jpg':
                header('Content-Type: image/jpeg');
                header('Content-Length: ' . filesize($path));
                readfile($path);
                break;
            case 'png':
                //die($path);
                header('Content-Type: image/png');
                header('Content-Length: ' . filesize($path));
                readfile($path);
                break;
            case 'gif':
                header('Content-Type: image/gif');
                header('Content-Length: ' . filesize($path));
                readfile($path);
                break;

            default:
                return FALSE;
                break;
        }
        return TRUE;
    }
}

if( !function_exists('limitar')){
    function limitar($input,$caracteres){
        return '<span data-tooltip aria-haspopup="true" class="has-tip" title="'.$input.'">'.substr($input,0,$caracteres).'...</span>';
    }
}

if(!function_exists('get_file_type')){
    function get_file_type($arquivo,$nome){
        $num       = exif_imagetype($arquivo);
        $finfo     = new finfo(FILEINFO_MIME);
        $extention = $finfo->file($arquivo);

        switch ($extention):
            case($extention == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document; charset=binary'):
                $num = 21;
                break;
            case($extention == 'application/msword; charset=binary'):
                $num = 20;
                break;
            case($extention == 'application/pdf; charset=binary'):
                $num = 19;
                break;
            case($extention == 'image/svg+xml'):
                $num = 18;
                break;
        endswitch;
        $data = array(
            1 => '.gif',
            2 => '.jpg',
            3 => '.png',
            5 => '.psd',
            17 => '.ico',
            18 => '.svg',
            19 => '.pdf',
            20 => '.doc',
            21 => '.dox',
        );
        if($num == 0){
            $ext_arr    =   explode('.',$nome);
            $ext        =   end($ext_arr);
            return ".$ext";
        }
        return $data[$num];
    }
}

if(!function_exists('gerar_tag_midia')){
    function gerar_tag_midia($arquivo, $info_extensao = FALSE){
        if(!$info_extensao){
            $info_extensao = $arquivo;
        }
        $arquivo_arr    =   explode('.',$info_extensao);
        $extensao       =   end($arquivo_arr);
        switch ($extensao) {
            case 'jpg':
            case 'png':
            case 'gif':
            case 'jpeg':
                $tag    =   '<img src="'.$arquivo.'" class="tag-midia">';
                break;
            
            case 'mp4':
            case 'avi':
            case 'mov':
                $tag    =   '<video controls>
                                <source src="'.$arquivo.'" type="video/mp4">
                                <source src="'.$arquivo.'" type="video/avi">
                                <source src="'.$arquivo.'" type="video/mov">
                                Your browser does not support the video tag.
                            </video>';
                break;

            default:
                $tag    =   '<iframe src="'.$arquivo.'" class="tag-midia">';
                break;
        }
        return $tag;
    }
}

if(!function_exists('quebrar_linha_n')){
    function quebrar_linha_n($input){
        $output = str_replace('\n', '&#13;', $input);
        return $output;
    }
}


if( !function_exists('view_file')){
    function view_file($file){
        $filename = 'Custom file name for the.pdf'; /* Note: Always use .pdf at the end. */

        header('Content-type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($file));
        header('Accept-Ranges: bytes');

        @readfile($file);
    }
}