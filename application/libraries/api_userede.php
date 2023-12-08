<?php

class api_userede {

    private $baseUrl = 'https://api.userede.com.br/redelabs';
    private $clientId = '95642e98-ed8a-46ab-bee9-c9441950a809';
    private $secretCode = 'QeWDCkNBki';
    private $userName = 'antonio@benepop.com.br';
    private $password = 'g)uheOIkvnK&';
    private $companyNumber = '80239366';
    private $cachePath = 'cache/apis/userede/';

    public function __construct() {
        
    }
    
    private function makeCacheName($input){
        return str_replace(array('/','?','=',':'), '_', $input);
    }

    public function getToken() {
        $curl = curl_init();

        $auth = base64_encode("$this->clientId:$this->secretCode");
        $data = array(
            'grant_type' => 'password',
            'username' => $this->userName,
            'password' => $this->password
        );

        curl_setopt_array($curl, array(
            CURLOPT_URL => "$this->baseUrl/oauth/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
                "Authorization: Basic $auth"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        if ($response) {
            return json_decode($response);
        }
        return FALSE;
    }
    
    private function retornarArquivo($url,$force = FALSE){
        if($force){
            return FALSE;
        }
        $filename = $this->cachePath.$this->makeCacheName($url);
        if(!file_exists($filename)){
            return FALSE;
        }
        $arquivo = file_get_contents($filename);
        if(in_array($arquivo, array(
            '{"message":"Requisição inválida."}',
            '{"message":"User is not authorized to access this resource with an explicit deny"}',
            '{"message":"User is not authorized to access this resource"}',
            '{"message": "Sorry, an error has occurred, but we are already working on it. Please try again later."}',
            '{"message":"Requisição inválida."}',
            '{"endDate": ["Missing data for required field."], "startDate": ["Missing data for required field."]}',
            '{"content": {"salesDaily": []}, "cursor": {"hasNextKey": false}}',
            '{"cursor": {"hasNextKey": false}, "content": {"salesDaily": []}}'
        ))){
            unlink($filename);
            return FALSE;
        }
        return $arquivo;
    }
    private function escreverArquivo($url,$conteudo){
        $filename = $this->cachePath.$this->makeCacheName($url);
        return file_put_contents($filename, $conteudo);
    }

    public function consultarVendas($filtros,$token){
        $parametros = array(
            'startDate'=>$filtros['data_inicial'],
            'endDate'=>$filtros['data_final'],
            'companyNumber'=>$filtros['matriz'] ?? $this->companyNumber,
        );
        if(isset($filtros['nsu'])){
            $parametros['nsu'] = $filtros['nsu'];
        }
        if(@$filtros['pageKey']){
            $parametros['pageKey'] = $filtros['pageKey'];
        }
        $query = http_build_query($parametros);
        $url = "$this->baseUrl/merchant-statement/v1/sales/$filtros[matriz]/daily?$query";
        $arquivo = $this->retornarArquivo($url,TRUE);
        if(@$parametros['nsu'] == '20004252'){
            echo $arquivo;
        }
        if($arquivo){
            return $arquivo;
        }
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $token"
            ),
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);
        print_r($error);
        curl_close($curl);
        
        $this->escreverArquivo($url,$response);
        return $response;
    }
    public function consultarVendasParcelas($filtros,$token,$force = FALSE){
        $parametros = http_build_query(array(
            'startDate'=>$filtros['data_inicial'],
            'endDate'=>$filtros['data_final'],
            'parentCompanyNumber'=>$filtros['matriz'] ?? $this->companyNumber,
            'subsidiaries'=>$filtros['filial'] ?? $this->companyNumber,
            'size'=>100,
            'pageKey'=>$filtros['nextKey'] ?? ''
        ));
        $url = "$this->baseUrl/merchant-statement/v1/sales/installments?$parametros";
        if($arquivo = $this->retornarArquivo($url,$force)){
            return $arquivo;
        }
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $token"
            ),
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);
        print_r($error);
        curl_close($curl);
        
        $this->escreverArquivo($url,$response);
        return $response;
    }
    public function consultarParcelas($filtros,$token,$force = FALSE){
        $parametros = http_build_query(array(
            'saleDate'=>$filtros['saleDate'],
            'nsu'=>$filtros['nsu'],
        ));
        $url = "$this->baseUrl/merchant-statement/v2/payments/installments/$filtros[companyNumber]?$parametros";
        if($arquivo = $this->retornarArquivo($url,$force)){
            return $arquivo;
        }

        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HEADER => TRUE,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Bearer $token"
            ),
        ));

        $response = curl_exec($curl);
        
        $headerSize = curl_getinfo( $curl , CURLINFO_HEADER_SIZE );
        $headerStr = substr( $response , 0 , $headerSize );
        $bodyStr = substr( $response , $headerSize );
        
        
        $error = curl_error($curl);
        curl_close($curl);
        
        if($error){
            return json_encode($error);
        }
        elseif($bodyStr){
            $this->escreverArquivo($url,$bodyStr);
            return $bodyStr;
        }
        else{
            $headers = explode("\r\n",$headerStr);
            foreach($headers as $header){
                if(strpos($header, 'HTTP') !== FALSE){
                    $resultado = $header;
                }
            }
            switch ($resultado) {
                case '"HTTP\/1.1 204 No Content"':
                    $retorno = 'A Consulta não Retornou Dados.';
                    break;

                default:
                    $retorno = $resultado;
                    break;
            }
            return json_encode($retorno);
        }
    }
    
    public function consultarIdPagamento($filtros,$token){
        $url = "$this->baseUrl/merchant-statement/v1/payments/$filtros[companyNumber]/$filtros[paymentId]";
        
        if($arquivo = $this->retornarArquivo($url)){
            return $arquivo;
        }
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HEADER => TRUE,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Bearer $token"
            ),
        ));
        

        $response = curl_exec($curl);
        
        $headerSize = curl_getinfo( $curl , CURLINFO_HEADER_SIZE );
        $headerStr = substr( $response , 0 , $headerSize );
        $bodyStr = substr( $response , $headerSize );
        
        
        $error = curl_error($curl);
        curl_close($curl);
        
        
        if($error){
            return json_encode($error);
        }
        elseif($bodyStr){
            $this->escreverArquivo($url,$bodyStr);
            return $bodyStr;
        }
        else{
            $headers = explode("\r\n",$headerStr);
            foreach($headers as $header){
                if(strpos($header, 'HTTP') !== FALSE){
                    $resultado = $header;
                }
            }
            switch ($resultado) {
                case '"HTTP\/1.1 204 No Content"':
                    $retorno = 'A Consulta não Retornou Dados.';
                    break;

                default:
                    $retorno = $resultado;
                    break;
            }
            return json_encode($retorno);
        }
    }

    public function consultarPagamentos($filtros,$token){
        
        $parametros = http_build_query(array(
            'startDate'=>$filtros['data_inicial'],
            'endDate'=>$filtros['data_final'],
            'parentCompanyNumber'=>$filtros['matriz'] ?? $this->parentCompanyNumber,
            'subsidiaries'=>$filtros['filial'] ?? $this->subsidiaries,
            'size'=>100,
            'pageKey'=>$filtros['nextKey'] ?? ''
        ));
        $url = "$this->baseUrl/merchant-statement/v1/payments?$parametros";
        
        if($arquivo = $this->retornarArquivo($url)){
            return $arquivo;
        }
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $token"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        
        $this->escreverArquivo($url,$response);
        
        return $response;
    }
}
