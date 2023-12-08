<?php

class api_boleto {
    
    private $clientId = 'ea317d52-378d-4abe-9e29-0bfb43dcff01';
    private $clientSecret = '0d78a443-4f20-4e8d-a574-743ea61e9350';

    public function __construct() {
        
    }
    
    public function atualizar_certificado($token,$certificado) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://sts.itau.com.br/seguranca/v1/certificado/solicitacao");
        curl_setopt($ch, CURLOPT_PORT, 443);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $certificado);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain', "Authorization: Bearer $token"));
                
        $response = curl_exec($ch);
        $info = curl_errno($ch) > 0 ? array("curl_error" . curl_errno($ch) => curl_error($ch)) : curl_getinfo($ch);
        curl_close($ch);
        return $response;
    }
    
    public function getToken() {
        $endpoint = '/api/oauth/token';
        $url = "https://sts.itau.com.br/$endpoint";
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_SSLCERT => './certs/certificado.crt',
            CURLOPT_SSLKEY => './certs/ARQUIVO_CHAVE_PRIVADA.key',
            CURLOPT_POSTFIELDS => "grant_type=client_credentials&client_id=$this->clientId&client_secret=$this->clientSecret",
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));
        $response = curl_exec($curl);
        $error = curl_error($curl);
        echo $error;
        curl_close($curl);
        echo $response;
        die();
        if ($response) {
            return json_decode($response);
        }
        return FALSE;
    }
    
    public function habilitarWebhookBoletosItau($parametros,$token){
        $json = json_encode(array(
            'data' => array(
                'id_beneficiario' => $parametros['id_beneficiario'],
                'webhook_url' => 'https://orchestrator.siqueirajr.com/api/boleto/notificar',
                'webhook_client_id' => $parametros['webhook_client_id'],
                'webhook_client_secret' => $parametros['webhook_client_secret'],
                'webhook_oauth_url' => 'https://orchestrator.siqueirajr.com/api/oauth/token',
                'valor_minimo' => 0,
                'tipos_notificacoes' => array(
                    'BAIXA_EFETIVA',
                    'BAIXA_OPERACIONAL'
                ),
            )
        ));
        //$url = "https://api.itau.com.br/boletos/v3/notificacoes_boletos";
        $url = "https://boletos.cloud.itau.com.br/boletos/v3/notificacoes_boletos";
        
        echo "$url<br>";
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_SSLCERT => './certs/certificado.crt',
            CURLOPT_SSLKEY => './certs/ARQUIVO_CHAVE_PRIVADA.key',
            CURLOPT_HEADER => TRUE,
            CURLOPT_HTTPHEADER => array(
                "x-itau-apikey: $this->clientSecret",
                'x-itau-correlationID: 9e1bb2db-2b0b-41b3-b2e7-a9a47fcd75a9',
                'x-itau-flowID: 7759b093-3a03-47b7-a2d6-a5b14ac40eca',
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
    
    public function francesinha($id_beneficiario, $data_movimentacao, $token) {
        $data = array(
            'data' => $data_movimentacao,
            'tipo_cobranca' => 'boleto'
        );
        $query_string = http_build_query($data);
        $url = "https://boletos.cloud.itau.com.br/boletos/v3/francesas/$id_beneficiario/movimentacoes";
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "$url?$query_string",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_SSLCERT => './certs/certificado.crt',
            CURLOPT_SSLKEY => './certs/ARQUIVO_CHAVE_PRIVADA.key',
            CURLOPT_HTTPHEADER => array(
                "x-itau-apikey: $this->clientSecret",
                'x-itau-correlationID: 9e1bb2db-2b0b-41b3-b2e7-a9a47fcd75a9',
                'x-itau-flowID: 7759b093-3a03-47b7-a2d6-a5b14ac40eca',
                'Content-Type: application/json',
                "Authorization: Bearer $token"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        if ($response) {
            return json_decode($response);
        }
        return FALSE;
    }

    public function consultar($id_beneficiario, $codigo_carteira, $nosso_numero, $token) {
        $data = array(
            'view' => 'specific',
            'id_beneficiario' => $id_beneficiario,
            'codigo_carteira' => $codigo_carteira,
            'nosso_numero' => $nosso_numero
        );
        $query_string = http_build_query($data);
        $url = "https://secure.api.cloud.itau.com.br/boletoscash/v2/boletos";
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "$url?$query_string",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_SSLCERT => './certs/certificado.crt',
            CURLOPT_SSLKEY => './certs/ARQUIVO_CHAVE_PRIVADA.key',
            CURLOPT_HTTPHEADER => array(
                "x-itau-apikey: $this->clientSecret",
                'x-itau-correlationID: 9e1bb2db-2b0b-41b3-b2e7-a9a47fcd75a9',
                'x-itau-flowID: 7759b093-3a03-47b7-a2d6-a5b14ac40eca',
                'Content-Type: application/json',
                "Authorization: Bearer $token"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        if ($response) {
            return json_decode($response);
        }
        return FALSE;
    }

    public function registrar($boleto,$token) {
        if($boleto->juros_dia == 0){
            $boleto->juros_dia = $boleto->valor_boleto * 0.02 / 30;
        }
        if($boleto->juros_dia < 0.01){
            $boleto->juros_dia = 0.01;
        }
        $dados = array(
            'data' => array(
                'etapa_processo_boleto' => 'efetivacao',
                'codigo_canal_operacao' => 'API',
                'beneficiario' => array(
                    'id_beneficiario' => $boleto->id_beneficiario
                ),
                'dado_boleto' => array(
                    'descricao_instrumento_cobranca' => 'boleto',
                    'tipo_boleto' => 'a vista',
                    'codigo_carteira' => $boleto->carteira,
                    'valor_total_titulo' => str_pad(round($boleto->valor_boleto * 100, 0), 17, '0', STR_PAD_LEFT),
                    'codigo_especie' => '01',
                    'data_emissao' => date('Y-m-d'),
                    'indicador_pagamento_parcial' => false,
                    'quantidade_maximo_parcial' => 0,
                    'pagador' => array(
                        'pessoa' => array(
                            'nome_pessoa' => substr(utf8_encode($boleto->pagador_nome),0,50),
                            'tipo_pessoa' => array(
                                'codigo_tipo_pessoa' => 'F',
                                'numero_cadastro_pessoa_fisica' => $boleto->pagador_documento
                            )
                        ),
                        'endereco' => array(
                            'nome_logradouro' => substr(utf8_encode($boleto->pagador_logradouro),0,45),
                            'nome_bairro' => substr(utf8_encode($boleto->pagador_bairro),0,15),
                            'nome_cidade' => substr(utf8_encode($boleto->pagador_municipio),0,20),
                            'sigla_UF' => $boleto->pagador_uf,
                            'numero_CEP' => only_numbers($boleto->pagador_cep)
                        )
                    ),
                    'dados_individuais_boleto' => array(
                        array(
                            'numero_nosso_numero' => $boleto->nosso_numero,
                            'data_vencimento' => $boleto->data_vencimento,
                            'valor_titulo' => str_pad(round($boleto->valor_boleto * 100, 0), 17, '0', STR_PAD_LEFT),
                            'texto_uso_beneficiario' => '',
                            'texto_seu_numero' => $boleto->bol_id
                        )
                    ),
                    'multa' => array(
                        'codigo_tipo_multa' => '02',
                        'quantidade_dias_multa' => 0,
                        'percentual_multa' => $boleto->per_multa*100000
                    ),
                    'juros' => array(
                        'codigo_tipo_juros' => '93',
                        'quantidade_dias_juros' => 0,
                        'valor_juros' => str_pad(round($boleto->juros_dia * 100, 0), 17, '0', STR_PAD_LEFT),
                    ),
                    'recebimento_divergente' => array(
                        'codigo_tipo_autorizacao' => '03'
                    ),
                    'desconto_expresso' => false
                )
            )
        );
        $json = json_encode($dados);
        if(!$json){
            return false;
        }
        
        //echo $json;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.itau.com.br/cash_management/v2/boletos',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_SSLCERT => './certs/certificado.crt',
            CURLOPT_SSLKEY => './certs/ARQUIVO_CHAVE_PRIVADA.key',
            CURLOPT_HEADER => TRUE,
            CURLOPT_HTTPHEADER => array(
                "x-itau-apikey: $this->clientSecret",
                'x-itau-correlationID: e9301eda-1978-4a12-aae6-663237947fe0',
                'x-itau-flowID: a9f44e6c-9f37-4864-8c89-73804a088c13',
                'Content-Type:  application/json',
                "Authorization: Bearer $token"
            ),
            CURLOPT_VERBOSE => true,
            
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

    public function alterar($boleto,$token) {
        $dados = array(
            'data' => array(
                'etapa_processo_boleto' => 'efetivacao',
                'codigo_canal_operacao' => 'API',
                'beneficiario' => array(
                    'id_beneficiario' => $boleto->id_beneficiario
                ),
                'dado_boleto' => array(
                    'descricao_instrumento_cobranca' => 'boleto',
                    'tipo_boleto' => 'a vista',
                    'codigo_carteira' => $boleto->carteira,
                    'valor_total_titulo' => str_pad(round($boleto->valor_boleto * 100, 0), 17, '0', STR_PAD_LEFT),
                    'codigo_especie' => '01',
                    'data_emissao' => date('Y-m-d'),
                    'indicador_pagamento_parcial' => false,
                    'quantidade_maximo_parcial' => 0,
                    'pagador' => array(
                        'pessoa' => array(
                            'nome_pessoa' => substr(utf8_encode($boleto->pagador_nome),0,50),
                            'tipo_pessoa' => array(
                                'codigo_tipo_pessoa' => 'F',
                                'numero_cadastro_pessoa_fisica' => $boleto->pagador_documento
                            )
                        ),
                        'endereco' => array(
                            'nome_logradouro' => substr(utf8_encode($boleto->pagador_logradouro),0,45),
                            'nome_bairro' => substr($boleto->pagador_bairro,0,15),
                            'nome_cidade' => substr(utf8_encode($boleto->pagador_municipio),0,20),
                            'sigla_UF' => $boleto->pagador_uf,
                            'numero_CEP' => only_numbers($boleto->pagador_cep)
                        )
                    ),
                    'dados_individuais_boleto' => array(
                        array(
                            'numero_nosso_numero' => $boleto->nosso_numero,
                            'data_vencimento' => $boleto->data_vencimento,
                            'valor_titulo' => str_pad(round($boleto->valor_boleto * 100, 0), 17, '0', STR_PAD_LEFT),
                            'texto_uso_beneficiario' => '',
                            'texto_seu_numero' => $boleto->bol_id
                        )
                    ),
                    'multa' => array(
                        'codigo_tipo_multa' => '02',
                        'quantidade_dias_multa' => 0,
                        'percentual_multa' => $boleto->per_multa*100000
                    ),
                    'juros' => array(
                        'codigo_tipo_juros' => '93',
                        'quantidade_dias_juros' => 0,
                        'valor_juros' => str_pad(round($boleto->juros_dia * 100, 0), 17, '0', STR_PAD_LEFT),
                    ),
                    'recebimento_divergente' => array(
                        'codigo_tipo_autorizacao' => '03'
                    ),
                    'desconto_expresso' => false
                )
            )
        );
        $json = json_encode($dados);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.itau.com.br/cash_management/v2/boletos',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_SSLCERT => './certs/certificado.crt',
            CURLOPT_SSLKEY => './certs/ARQUIVO_CHAVE_PRIVADA.key',
            CURLOPT_HTTPHEADER => array(
                "x-itau-apikey: $this->clientSecret",
                'x-itau-correlationID: e9301eda-1978-4a12-aae6-663237947fe0',
                'x-itau-flowID: a9f44e6c-9f37-4864-8c89-73804a088c13',
                'Content-Type: application/json',
                "Authorization: Bearer $token"
            ),
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);
        if($error){
            return array(
                'resultado' => 'erro',
                'conteudo' => $error
            );
        }
        else{
            return $response;
        }
    }
    
    
    public function alterar_valor_nominal($boleto,$token){
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.itau.com.br/cash_management/v2/boletos/%7B%7Bid_boleto%7D%7D/valor_nominal',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'PATCH',
          CURLOPT_POSTFIELDS =>'{
          "valor_titulo": "250.00"
        }',
          CURLOPT_HTTPHEADER => array(
            'x-itau-apikey: {{client_Id}}',
            'x-itau-correlationID: 8ec0e2c4-d113-4148-a04b-830d8e9debfc',
            'x-itau-flowID: fe541896-4a02-4549-a605-7569fbe9b058',
            'Content-Type: application/json',
            'Authorization: Bearer {{access_token}}'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
    }
    
    public function alterar_juros($boleto,$token){
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.itau.com.br/cash_management/v2/boletos/%7B%7Bid_boleto%7D%7D/juros',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'PATCH',
          CURLOPT_POSTFIELDS =>'{
            "juros": {
                "codigo_tipo_juros": "90",
                "quantidade_dias_juros": 2,
                "percentual_juros": "15.00000"
            }
        }',
          CURLOPT_HTTPHEADER => array(
            'x-itau-apikey: {{client_Id}}',
            'x-itau-correlationID: dd6ed0f5-cc02-496a-9a26-f15eb1425682',
            'x-itau-flowID: 5384c128-7836-408a-9cd4-97e3d05c596e',
            'Content-Type: application/json',
            'Authorization: Bearer {{access_token}}'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
    }
    
    public function alterar_data_vencimento($boleto,$token){
        
    }
    
    public function alterar_desconto($boleto,$token){
        
    }
    
    public function alterar_abatimento($boleto,$token){
        
    }
    
    public function alterar_multa($boleto,$token){
        
    }
    
    public function alterar_protesto($boleto,$token){
        
    }
    
    public function alterar_seu_numero($boleto,$token){
        
    }
    
    public function alterar_data_limite($boleto,$token){
        
    }
    
    public function alterar_negativacao($boleto,$token){
        
    }
    
    public function alterar_pagador($boleto,$token){
        
    }
}
