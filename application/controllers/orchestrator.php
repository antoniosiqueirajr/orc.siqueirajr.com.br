<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class orchestrator extends B10_Controller{

    public function __construct(){
        parent::__construct();
        $this->load->model('orchestrator_job');
        $this->load->model('orchestrator_job_parameter');
        $this->load->model('orchestrator_periodicity');
        $this->load->model('orchestrator_task');
        $this->load->model('orchestrator_task_parameter');
        $this->load->model('orchestrator_task_result');
    }
    
    public function run($id_task){
        $task = $this->orchestrator_task->get_task($id_task);
        $params     = db_array($this->orchestrator_task_parameter->get_params($task->id),'param', 'value');
        $params['task_id'] = $task->id;
        $result     = $this->{$task->function}($params,$task);
        echo $result;
    }
    
    public function index(){
        $tasks = $this->orchestrator_task->get_tasks();
        foreach ($tasks as $task){
            if($task->periodicity_type == 5 && $task->next_date == ''){
                continue;
            }
            echo "--- atividade $task->id iniciada ---<br>";
            $this->orchestrator_task->start_task($task->id);
            $start_date = date('Y-m-d H:i:s');
            try{
                $ctx = stream_context_create(array('http'=>array('timeout' => 1200)));
                $result = @file_get_contents(base_url("orchestrator/run/$task->id"),false,$ctx);
                if(!$result){
                    $result = 'Falha na chamada da atividade';
                }
                $this->orchestrator_task_result->insert(array(
                    'task'          =>  $task->id,
                    'start_date'    =>  $start_date,
                    'end_date'      =>  date('Y-m-d H:i:s'),
                    'result'        =>  $result
                ));
                echo "result: $result<br>";
            } catch (Exception $ex) {
                $this->orchestrator_task_result->insert(array(
                    'task'          =>  $task->id,
                    'start_date'    =>  $start_date,
                    'end_date'      =>  date('Y-m-d H:i:s'),
                    'result'        =>  'Erro na execução: '. json_encode($ex)
                ));
                echo "exception: $ex<br>";
            }
            $this->orchestrator_task->end_task($task);
            echo "--- atividade $task->id encerrada ---<br>";
        }
        echo count($tasks).' tasks executeds';
    }

    public function restart(){
        foreach ($this->orchestrator_task->get_all(array('status' => 1)) as $task) {
            $this->orchestrator_task->end_task($task);
        }
        message('orchestrator-restarted-sucefully', 'success');
        echo json_encode(get_message());
    }
    
    public function reativar(){
        $this->orchestrator_task->reativar();
    }
    
    public function orchestrator_tasks(){
        $orchestrator_tasks = $this->orchestrator_task->get_all(array('next_date >'=>'2000-01-01'));
        $page = array(
            'body'  =>  $this->view('template/tabela',array(
                'titulo'        => 'Tasks',
                'tabela'        => 'orchestrator_task',
                'datasource'    => $orchestrator_tasks,
                'campos'        => array('id', 'job','start_date','periodicity_type','periodicity_interval','last_execution_start','next_date','status'),
                'dropdowns'     => array(
                    'orchestrator_status'   =>  array(0=>'waiting',1=>'in-progress')
                ),
            ),TRUE),
        );
        $this->makePage(FALSE, $page);
    }

    public function orchestrator_tasks_edit($id = FALSE){
        if($return = $this->orchestrator_task->input_validation($id)){
            redirect("orchestrator/orchestrator_tasks_edit/$return");
        }
        $page = array(
            'body'  =>  $this->view('template/formulario_abas',array(
                'id'            =>  $id,
                'titulo'        =>  'Task',
                'tabela'        =>  'orchestrator_task',
                'datasource'    =>  $this->orchestrator_task->get(array('id'=>$id)),
                'campos'        =>  array('id', 'job','start_date','periodicity_type','periodicity_interval','time_fixed','next_date','status'),
                'abas'          =>  array(
                    array(
                        'titulo'    =>  'Parâmetros',
                        'icone'     =>  'fa-sitemap featured',
                        'conteudo'  =>  $this->orchestrator_task_parameters($id)
                    ),
                    array(
                        'titulo'    =>  'Resultados',
                        'icone'     =>  'fa-file success',
                        'conteudo'  =>  $this->orchestrator_task_results($id)
                    ),
                ),
                'dropdowns'     =>  array(
                ),
            ),TRUE)
        );
        $this->makePage(FALSE, $page);
    }

    public function orchestrator_tasks_delete($_id = FALSE){
        $id       = (int) $_id;
        $restrict = array(
        );
        $cascade  = array(
        );
        $this->orchestrator_task->delete_validation(array('id' => $id),$cascade, $restrict);
        redirect('orchestrator/orchestrator_tasks');
        return;
    }
    
    public function orchestrator_task_parameters($task_id){
        $orchestrator_task_parameters = $this->orchestrator_task_parameter->get_all(array('task' => $task_id));
        return $this->view('template/tabela_sub',array(
            'titulo'         => 'orchestrator_task_parameter',
            'tabela'         => 'orchestrator_task_parameter',
            'datasource'    => $orchestrator_task_parameters,
            'campos'        => array('id', 'parameter', 'value'),
            'metodo'        =>  'orchestrator_task_parameters',
            'dropdowns'     => array(
            ),
        ),TRUE);
    }

    public function orchestrator_task_parameters_edit($task_id, $id = FALSE){
        if($return = $this->orchestrator_task_parameter->input_validation($id)){
            redirect("orchestrator/orchestrator_tasks_edit/$task_id#parametros");
        }
        $task = $this->orchestrator_task->get(array('id'=>$task_id));
        $this->view('template/formulario_modal',array(
            'id'        => $id,
            'titulo'    => 'Parâmetro',
            'tabela'    => 'orchestrator_task_parameter',
            'datasource'=> $this->orchestrator_task_parameter->get(array('id' => $id)),
            'campos'    => array('id', 'parameter', 'value'),
            'dropdowns' => array(
                'orchestrator_job_parameter' => db_array($this->orchestrator_job_parameter->get_all(array('job'=>$task->job)),'id','name')
            ),
        ));
    }

    public function orchestrator_task_parameters_excluir($id = FALSE){
        $parametro = $this->orchestrator_task_parameter->get(array('id'=>$id));
        $restrict = array();
        $cascade  = array();
        $this->orchestrator_task_parameter->delete_validation(array('id' => $id),$cascade, $restrict);
        redirect("orchestrator/orchestrator_tasks_edit/$parametro->task");
    }
    
    public function orchestrator_task_results($task_id){
        $orchestrator_task_results = $this->orchestrator_task_result->get_all(array('task' => $task_id),array('id','desc'),array('start'=>0,'lenght'=>100));
        return $this->view('template/tabela_sub',array(
            'title'         => 'orchestrator_task_result',
            'tabela'         => 'orchestrator_task_result',
            'datasource'    => $orchestrator_task_results,
            'metodo'        =>  '',
            'campos'        => array('id', 'start_date','end_date','result'),
            'dropdowns'     => array(
            ),
        ),true);
    }

    public function orchestrator_task_results_edit($task_id, $id){
        if ($this->orchestrator_task_result->input_validation($id)) {
            echo json_encode(get_message());
            return;
        }
        if ($id) {
            $titulo =   'Edi&ccedil;&atilde;o de orchestrator_task_result';
            $fields =   array('id', 'start_date','end_date','result');
        }
        $orchestrator_task_result = $this->orchestrator_task_result->get(array('id' => $id));
        $this->view('template/form_columns',array(
            'id'            => $id,
            'title'         => $titulo,
            'tabela'         => 'orchestrator_task_result',
            'datasource'    => $orchestrator_task_result,
            'log'           => $this->orchestrator_task_result->get_log(),
            'fields'        => $fields,
            'dropdowns' => array(
            ),
        ));
    }

    public function orchestrator_task_results_delete($task_id, $_id = FALSE){
        $id       = (int) $_id;
        $restrict = array(
        );
        $cascade  = array(
        );
        $this->orchestrator_task_result->delete_validation(array('id' => $id),$cascade, $restrict);
        echo json_encode(get_message());
        return;
    }
    
    public function orchestrator_jobs(){
        $orchestrator_jobs = $this->orchestrator_job->get_all();
        $page = array(
            'body'  =>  $this->view('template/tabela',
                array(
                    'titulo'         => 'Jobs',
                    'tabela'         => 'orchestrator_job',
                    'datasource'    => $orchestrator_jobs,
                    'campos'        => array('id', 'name', 'function'),
                    'dropdowns'     => array(

                    ),
                ),TRUE)
        );
        $this->makePage(FALSE, $page);
    }

    public function orchestrator_jobs_edit($id = FALSE){
        if($return = $this->orchestrator_job->input_validation($id)){
            redirect("orchestrator/orchestrator_jobs_edit/$return");
        }
        $page = array(
            'body'  =>  $this->view('template/formulario_abas',array(
                'id'            =>  $id,
                'titulo'        =>  'Jobs',
                'tabela'        =>  'orchestrator_job',
                'datasource'    =>  $this->orchestrator_job->get(array('id'=>$id)),
                'campos'        =>  array('id','name', 'description', 'function'),
                'abas'          =>  array(
                    array(
                        'titulo'    =>  'Parâmetros',
                        'icone'     =>  'fa-sitemap featured',
                        'conteudo'  =>  $this->orchestrator_job_parameters($id)
                    ),
                ),
                'dropdowns'     =>  array(
                ),
            ),TRUE)
        );
        $this->makePage(FALSE, $page);
    }

    public function orchestrator_jobs_delete($_id = FALSE){
        $id       = (int) $_id;
        $restrict = array(
            'orchestrator_tasks.job'
        );
        $cascade  = array(
            'orchestrator_job_parameters.job'
        );
        $this->orchestrator_job->delete_validation(array('id' => $id),$cascade, $restrict);
        echo json_encode(get_message());
        return;
    }
    
    public function orchestrator_job_parameters($job_id){
        $orchestrator_job_parameters = $this->orchestrator_job_parameter->get_all(array('job' => $job_id));
        return $this->view('template/tabela_sub',array(
            'titulo'         => 'orchestrator_job_parameter',
            'tabela'         => 'orchestrator_job_parameter',
            'datasource'    => $orchestrator_job_parameters,
            'campos'        => array('id', 'name', 'param', 'default_value'),
            'metodo'        =>  'orchestrator_job_parameters',
            'dropdowns' => array(
            ),
        ),TRUE);
    }

    public function orchestrator_job_parameters_edit($job_id, $id = FALSE){
        if($return = $this->orchestrator_job_parameter->input_validation($id)){
            redirect("orchestrator/orchestrator_jobs_edit/$job_id#parametros");
        }
        $job = $this->orchestrator_job->get(array('id'=>$job_id));
        $this->view('template/formulario_modal',array(
            'id'        => $id,
            'titulo'    => 'Parâmetro',
            'tabela'    => 'orchestrator_job_parameter',
            'datasource'=> $this->orchestrator_job_parameter->get(array('id' => $id)),
            'campos'    => array('id', 'name', 'param', 'default_value'),
            'dropdowns' => array(
                'orchestrator_job_parameter' => db_array($this->orchestrator_job_parameter->get_all(array('job'=>$job->id)),'id','name')
            ),
        ));
    }

    public function orchestrator_job_parameters_delete($finance_id, $_id = FALSE){
        $id       = (int) $_id;
        $restrict = array(
        );
        $cascade  = array(
        );
        $this->orchestrator_job_parameter->delete_validation(array('id' => $id),$cascade, $restrict);
        echo json_encode(get_message());
        return;
    }

    
    
    /* Functions */
    
    public function reabilitarAtividade($param = array()){
        $id = $param['task_id'];
        $data = date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')." -$param[tempo_inativo] minutes"));
        $this->orchestrator_task->update(array(
            'next_date <' => $data,
            'id' => $id,
            'status' => 1
        ),array('status'=>0));
    }
    
    public function baixarBoletosExpirados($param = FALSE) {
        $this->load->model('benepop');
        $registros = $this->benepop->baixar_boletos_expirados();
        return "$registros boletos baixados";
    }
    
    public function baixarBoletosParcelaRecebida($param = FALSE) {
        $this->load->model('benepop');
        $registros = $this->benepop->baixar_boletos_contas_a_receber_baixado();
        return "$registros boletos baixados";
    }
    
    public function atualizarStatusBoletosApiItau($param = array()) {
        $this->load->model('benepop');
        $this->load->library('api_boleto');
        
        $token = json_decode($this->parametro->get_valor('api_itau_token'));
        
        $boletos = $this->benepop->get_boletos_a_consultar(array(
            'limite_dias_vencimento'=>$param['limite_dias_vencimento'],
            'limite_documentos'=>$param['limite_consultas'],
        ));
        $n_boletos = count($boletos);
        $n_liquidados = 0;
        $n_baixados = 0;
        $n_registrado = 0;
        $x = 0;
        foreach($boletos as $boleto){
            $x++;
            set_time_limit(600);
            if(strtotime($token->token_validade) < strtotime(date('Y-m-d H:i:s'))){
                $token = $this->api_boleto->getToken();
                if(!property_exists($token, 'access_token')){
                    print_r($token);
                    continue;
                }
                $token->token_validade = date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')." + $token->expires_in seconds"));
                $this->parametro->set_valor('api_itau_token',json_encode($token));
            }
            $id_beneficiario = $boleto->agencia_numero.str_pad($boleto->beneficiario_cod_cliente, 8, '0', STR_PAD_LEFT);
            $codigo_carteira = $boleto->carteira;
            $nosso_numero = $boleto->nosso_numero;
            $retorno = $this->api_boleto->consultar($id_beneficiario, $codigo_carteira, $nosso_numero, $token->access_token);
            if(!isset($retorno->data[0])){
                echo "$id_beneficiario - $nosso_numero: não encontrado<br>";
                continue;
            }
            $dados_boleto = $retorno->data[0];
            $situacao_geral_boleto = $dados_boleto->dado_boleto->dados_individuais_boleto[0]->situacao_geral_boleto;
            echo "$id_beneficiario - $nosso_numero: $situacao_geral_boleto<br>";
            
            switch ($situacao_geral_boleto) {
                case 'Baixada':
                    $this->benepop->baixar_boleto($boleto,$dados_boleto);
                    $n_baixados++;
                    break;
                case 'Paga':
                    $this->benepop->liquidar_boleto($boleto,$dados_boleto);
                    $n_liquidados++;
                    break;
                case 'Aguardando Confirmação de Pagamento':
                    $this->benepop->provisionar_pagamento_boleto($boleto,$dados_boleto);
                    $n_liquidados++;
                    break;

                default:
                    $this->benepop->atualizar_boleto($boleto,$dados_boleto);
                    break;
            }
        }
        $return = "$n_boletos boletos consultados, $n_registrado registrados, $n_liquidados liquidados e $n_baixados baixados";
        return $return;
    }
    
    public function registrarBoletosApiItau($param = array()){
        //return "Inativo";
        $this->load->model('benepop');
        $this->load->library('api_boleto');
        $boletos = $this->benepop->get_boletos_a_registrar();
        $token = json_decode($this->parametro->get_valor('api_itau_token'));
        $enviados = 0;
        $nao_enviados = 0;
        $erros = 0;
        foreach($boletos as $boleto){
            $boleto = $this->benepop->validar_boleto($boleto->id_unidade,$boleto->nosso_numero);
            if($boleto === FALSE){
                //echo "invalido<br>";
                $nao_enviados++;
                continue;
            }
            echo "$boleto->id_unidade-$boleto->nosso_numero<br>";
            $registrar = file_get_contents("https://orc.sjrcapital.com.br/api/boleto_registrar/$boleto->id_unidade-$boleto->nosso_numero");
            echo $registrar;
            $retorno = json_decode($registrar);
            if(@$retorno->campos[0]->mensagem == 'Título já cadastrado na cobrança'){
                $this->benepop->registrar_boleto($boleto);
            }
            echo '<br>';
            continue;
            if(strtotime($token->token_validade) < strtotime(date('Y-m-d H:i:s'))){
                $token = $this->api_boleto->getToken();
                if(!property_exists($token, 'access_token')){
                    print_r($token);
                    continue;
                }
                $token->token_validade = date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')." + $token->expires_in seconds"));
                $this->parametro->set_valor('api_itau_token',json_encode($token));
            }
            $resultado = $this->api_boleto->registrar($boleto,$token->access_token);
            
            $retorno = json_decode($resultado);
            if(!is_object($retorno)){
                echo "$resultado<br>";
                $nao_enviados++;
                continue;
            }
            if(property_exists($retorno, 'data')){
                echo "registrado!<br>";
                $enviados++;
                $this->benepop->registrar_boleto($boleto);
            }
            else{
                if(property_exists($retorno, 'mensagem')){
                    echo $retorno->mensagem;
                }
                elseif(property_exists($retorno, 'conteudo')){
                    echo $retorno->conteudo;
                }
                echo '<br>';
                $erros++;
            }
        }
        return "$enviados boletos registrados, $nao_enviados não registrados e $erros erros de validação";
    }
    
    public function buscarBoletosFrancesinha($param = array()){
        $this->load->model('apiboletousecases');
        $this->apiboletousecases->buscarFrancesinha($param);
    }
    
    public function atualizarCertificadoItau($param = FALSE){
        $this->load->model('benepop');
        $this->load->library('api_boleto');
        
        $token = 'eyJraWQiOiIxNDZlNTY1Yy02ZjQ4LTRhN2EtOTU3NS1kYjg2MjE5YTc5N2MucHJkLmdlbi4xNTk3NjAwMTI1ODQ4Lmp3dCIsImFsZyI6IlJTMjU2In0.eyJzdWIiOiJlYTMxN2Q1Mi0zNzhkLTRhYmUtOWUyOS0wYmZiNDNkY2ZmMDEiLCJpc3MiOiJodHRwczovL29wZW5pZC5pdGF1LmNvbS5ici9hcGkvb2F1dGgvdG9rZW4iLCJpYXQiOjE2ODQ1MzA4ODgsImV4cCI6MTY4NTEzNTY4OCwiQWNjZXNzX1Rva2VuIjoiQmdlemFZWElTMG5Ea0Y3TW8wSFhuWnJZSjVQdEtYVDhQdERja1g3cjEwNkdZVExsWXk1SVZPIiwianRpIjoiQmdlemFZWElTMG5Ea0Y3TW8wSFhuWnJZSjVQdEtYVDhQdERja1g3cjEwNkdZVExsWXk1SVZPIiwidXNyIjoibnVsbCIsImZsb3ciOiJUT0tFTlRFTVAiLCJzb3VyY2UiOiJFWFQiLCJzaXRlIjoiY3RtbTEiLCJlbnYiOiJQIiwibWJpIjoidHJ1ZSIsImF1dCI6Ik1BUiIsInZlciI6InYxLjAiLCJzY29wZSI6ImNlcnRpZmljYXRlLndyaXRlIn0.FnY3rGf4j0AwxutO6d0olSvs-95YQrjlz2zMCsL_6dDtSZzXvz3gXG0XN27Vf8JI2dLDFpaAvgu3pyBxzSRLlb6lBUQJXpPI3zoG4Izkw3MDKrZVULoGHKvxH2YpPGn658gpng5FigGoRncgRf3uY_u7bOCS3XkjZJxrBOlV1Zq6B7KdibaPUFbCTVJAkmgb_IonYzHGf-tyT4e7ngGWtFYAADgJVWWKCxANHNC89rMwfodiE6RmnHeDy7B5BHlNW8hJeVS7vUq1uTDoRKu5YkCG_13uWcxMYpyB9kX4CHVFVFMIL2C-9PnRHzk46IuyDmAKNp5BW6c3ksYj0DuxPQ';
        $certificado = file_get_contents('./certs/ARQUIVO_REQUEST_CERTIFICADO.csr');
        $novo_certificado = $this->api_boleto->atualizar_certificado($token,$certificado);
        echo $novo_certificado;
    }
    
    public function habilitarWebhookBoletosItau(){
        $this->load->model('benepop');
        $this->load->library('api_boleto');
        
        $token = json_decode($this->parametro->get_valor('api_itau_token'));
        
        foreach($this->benepop->get_unidades() as $unidade){
            if(!$unidade->beneficiario_cod_cliente){
                continue;
            }
            if(strtotime($token->token_validade) < strtotime(date('Y-m-d H:i:s'))){
                $token = $this->api_boleto->getToken();
                if(!property_exists($token, 'access_token')){
                    print_r($token);
                    return;
                }
                $token->token_validade = date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')." + $token->expires_in seconds"));
                $this->parametro->set_valor('api_itau_token',json_encode($token));
            }
            $id_beneficiario = $unidade->agencia_numero.str_pad($unidade->beneficiario_cod_cliente, 8, '0', STR_PAD_LEFT);
            $retorno = $this->api_boleto->habilitarWebhookBoletosItau(array(
                'id_beneficiario'  =>  $id_beneficiario,
                'webhook_client_id'  =>  $this->parametro->get_valor('webhook_client_id'),
                'webhook_client_secret'  =>  $this->parametro->get_valor('webhook_client_secret'),
            ),$token->access_token);
            echo "unidade $id_beneficiario:";
            print_r($retorno);
            echo '<br>';
        }
    }
    
    public function atualizarStatusCartoesVendas(){
        $this->load->model('benepop');
        $parcelas = $this->benepop->atualizar_status_cartoes_vendas();
        echo "$parcelas atualizadas";
        return "$parcelas atualizadas";
    }
    
    public function atualizarStatusCartoesVendasApiDias(){
        $this->load->model('benepop');
        $this->load->model('parametro');
        $this->load->library('api_userede');
        $token = $this->api_userede->getToken();
        if(!property_exists($token, 'access_token')){
            echo json_encode($token);
            die();
        }
        $resultado = '';
        $dias = array(
            '2023-08-22',
            '2023-08-31',
            '2023-09-05',
            '2023-09-12',
            '2023-10-03',
            '2023-10-19',
            '2023-10-27',
            '2023-10-31',
            '2023-11-01',
            '2023-11-10',
            '2023-11-13',
            '2023-11-14',
            '2023-11-16',
            '2023-11-17',
            '2023-11-18',
            '2023-11-20',
            '2023-11-21',
            '2023-11-22',
        );
        foreach($this->benepop->get_unidades() as $unidade){
            if($unidade->erede_companynumber == ''){
                continue;
            }
            $transacoes = array();
            $parcelas = array();
            foreach($dias as $dia_busca){
                $hasNextKey = TRUE;
                $nextKey = '';
                $resultado .= "Unidade: $unidade->id_unidade, de $dia_busca a $dia_busca dias<br>";
                echo "Unidade: $unidade->id_unidade, dia $dia_busca<br>";
                set_time_limit(600);
                while($hasNextKey === TRUE){
                    $retorno = $this->api_userede->consultarVendas(array(
                        'data_inicial' => $dia_busca,
                        'data_final' => $dia_busca,
                        'nextKey' => $nextKey,
                        'matriz' => $unidade->erede_companynumber,
                    ),$token->access_token);
                    $dados = json_decode($retorno);
                    if(property_exists($dados, 'content')){
                        foreach($dados->content->salesDaily as $dia){
                            $transacoes += $dia->sales;
                        }
                        $hasNextKey = $dados->cursor->hasNextKey;
                        $nextKey = @$dados->cursor->nextKey;
                    }
                    else{
                        $hasNextKey = FALSE;
                        $nextKey = '';
                    }
                }
                $hasNextKey = TRUE;
                $nextKey = '';
                while($hasNextKey === TRUE){
                    $retorno = $this->api_userede->consultarVendasParcelas(array(
                        'data_inicial' => $dia_busca,
                        'data_final' => $dia_busca,
                        'nextKey' => $nextKey,
                        'matriz' => $unidade->erede_companynumber,
                        'filial' => $unidade->erede_companynumber,
                    ),$token->access_token);
                    $dados = json_decode($retorno);
                    if(property_exists($dados, 'content')){
                        foreach($dados->content->installments as $parcela){
                            if(!isset($parcelas[$parcela->nsu])){
                                $parcelas[$parcela->nsu] = array();
                            }
                            $parcelas[$parcela->nsu][$parcela->installmentNumber] = $parcela;
                        }
                        $hasNextKey = $dados->cursor->hasNextKey;
                        $nextKey = @$dados->cursor->nextKey;
                    }
                    else{
                        $hasNextKey = FALSE;
                        $nextKey = '';
                    }
                }
            }
            foreach($transacoes as $transacao){
                $this->benepop->lancar_transacao_cartao($transacao,$unidade);
                $this->benepop->atualizar_status_cartao($transacao,$parcelas);
            }
            $resultado .= count($transacoes)." transações<br>";
        }
        $this->benepop->remover_vendas_duplicadas();
        return $resultado;
    }
    public function atualizarStatusCartoesVendasApi(){
        $this->load->model('benepop');
        $this->load->model('parametro');
        $this->load->library('api_userede');
        $this->parametro->criar_parametro('api_userede_ciclo',1);
        $ciclo = $this->parametro->get_valor('api_userede_ciclo');
        $dias_ciclo = 1;
        $ciclos = 7;
        if($ciclo >= $ciclos){
            $this->parametro->set_valor('api_userede_ciclo',0);
            return;
        }
        $token = $this->api_userede->getToken();
        echo "ciclo: $ciclo<br>";
        $resultado = '';
        foreach($this->benepop->get_unidades() as $unidade){
            if($unidade->erede_companynumber == ''){
                continue;
            }
            $transacoes = array();
            $hasNextKey = TRUE;
            $nextKey = '';
            $inicio = 0 + ($ciclo * $dias_ciclo);
            $fim = (-1 * $dias_ciclo) + ($ciclo * $dias_ciclo);
            $resultado .= "Unidade: $unidade->id_unidade, de $inicio a $fim dias<br>";
            echo "Unidade: $unidade->id_unidade, de $inicio a $fim dias<br>";
            set_time_limit(600);
            while($hasNextKey === TRUE){
                $retorno = $this->api_userede->consultarVendas(array(
                    'data_inicial' => soma_dias_uteis(date('Y-m-d'),$inicio,array(0,1,2,3,4,5,6),array(),'-'),
                    'data_final' => soma_dias_uteis(date('Y-m-d'),$fim,array(0,1,2,3,4,5,6),array(),'-'),
                    'nextKey' => $nextKey,
                    'matriz' => $unidade->erede_companynumber,
                ),$token->access_token);
                $dados = json_decode($retorno);
                if(property_exists($dados, 'content')){
                    foreach($dados->content->salesDaily as $dia){
                        $transacoes += $dia->sales;
                    }
                    $hasNextKey = $dados->cursor->hasNextKey;
                    $nextKey = @$dados->cursor->nextKey;
                }
                else{
                    $hasNextKey = FALSE;
                    $nextKey = '';
                }
            }
            $parcelas = array();
            $hasNextKey = TRUE;
            $nextKey = '';
            while($hasNextKey === TRUE){
                $retorno = $this->api_userede->consultarVendasParcelas(array(
                    'data_inicial' => soma_dias_uteis(date('Y-m-d'),$inicio,array(0,1,2,3,4,5,6),array(),'-'),
                    'data_final' => soma_dias_uteis(date('Y-m-d'),$fim,array(0,1,2,3,4,5,6),array(),'-'),
                    'nextKey' => $nextKey,
                    'matriz' => $unidade->erede_companynumber,
                    'filial' => $unidade->erede_companynumber,
                ),$token->access_token);
                $dados = json_decode($retorno);
                if(property_exists($dados, 'content')){
                    foreach($dados->content->installments as $parcela){
                        if(!isset($parcelas[$parcela->nsu])){
                            $parcelas[$parcela->nsu] = array();
                        }
                        $parcelas[$parcela->nsu][$parcela->installmentNumber] = $parcela;
                    }
                    $hasNextKey = $dados->cursor->hasNextKey;
                    $nextKey = @$dados->cursor->nextKey;
                }
                else{
                    $hasNextKey = FALSE;
                    $nextKey = '';
                }
            }
            foreach($transacoes as $transacao){
                $this->benepop->lancar_transacao_cartao($transacao,$unidade);
                $this->benepop->atualizar_status_cartao($transacao,$parcelas);
            }
            $resultado .= count($transacoes)." transações<br>";
        }
        $this->benepop->remover_vendas_duplicadas();
        $this->parametro->set_valor('api_userede_ciclo',$ciclo+1);
        return $resultado;
    }
    
    public function atualizarStatusCartoesRecebimentosApi(){
        $this->load->model('benepop');
        $this->load->library('api_userede');
        $token = $this->api_userede->getToken();
        foreach($this->benepop->get_unidades() as $unidade){
            if($unidade->erede_companynumber == ''){
                continue;
            }
            $transacoes = array();
            $hasNextKey = TRUE;
            $nextKey = '';
            while($hasNextKey === TRUE){
                $retorno = $this->api_userede->consultarPagamentos(array(
                    'data_inicial' => soma_dias_uteis(date('Y-m-d'),7,array(0,1,2,3,4,5,6),array(),'-'),
                    'data_final' => soma_dias_uteis(date('Y-m-d'),1,array(0,1,2,3,4,5,6),array(),'-'),
                    'nextKey' => $nextKey,
                    'matriz' => $unidade->erede_companynumber,
                    'filial' => $unidade->erede_companynumber,
                ),$token->access_token);
                $dados = json_decode($retorno);
                if(property_exists($dados, 'content')){
                    $transacoes += $dados->content->payments;
                    $hasNextKey = $dados->cursor->hasNextKey;
                    $nextKey = @$dados->cursor->nextKey;
                }
                else{
                    $hasNextKey = FALSE;
                    $nextKey = '';
                }
            }
            foreach($transacoes as $transacao){
                switch ($transacao->status) {
                    case 'PAID':
                        if($transacao->type == 'ANTICIPATION'){
                            //$this->benepop->antecipar_cartao($transacao);
                        }
                        break;

                    default:
                        break;
                }
            }
        }
    }
    
    public function liquidar_cartoes_baixados(){
        $this->load->model('benepop');
        $this->benepop->liquidar_cartoes_baixados();
    }
    
    public function remover_cartoes_duplicados(){
        $this->load->model('benepop');
        $this->benepop->remover_cartoes_duplicados();
    }
    
    
    public function buscarCartoesPendentesConciliacao($param = array()){
        $this->load->model('benepop');
        $this->load->model('apicartaousecases');
        $cartoes = $this->benepop->buscar_cartoes_pendentes_conciliacao($param);
        foreach($cartoes as $cartao){
            $this->apicartaousecases->buscarTransacaoCartao($cartao->codigo_autenticacao,$cartao->id_unidade);
        }
    }
    
    public function buscar_cartoes_previstos(){
        $this->load->model('benepop');
        $this->load->model('apicartaousecases');
        $cartoes = $this->benepop->get_cartoes_previstos();
        $consultados = array();
        foreach($cartoes as $cartao){
            if(!in_array("$cartao->codigo_autenticacao,$cartao->id_unidade", $consultados)){
                $this->apicartaousecases->buscarTransacaoCartao($cartao->codigo_autenticacao,$cartao->id_unidade);
                $consultados[] = "$cartao->codigo_autenticacao,$cartao->id_unidade";
            }
        }
    }
    
    
    
    
    
    public function reprocessar_dias_venda_cartoes(){
        $this->load->model('benepop');
        $this->load->model('parametro');
        $this->load->library('api_userede');
        $this->parametro->criar_parametro('api_userede_ciclo',1);
        $token = $this->api_userede->getToken();
        foreach($this->benepop->get_unidades() as $unidade){
            if($unidade->erede_companynumber == ''){
                echo "$unidade->id_unidade sem erede_companynumber<br>";
                continue;
            }
            foreach($this->benepop->reprocessar_dias_venda_cartoes($unidade->id_unidade) as $data){
                $transacoes = array();
                $hasNextKey = TRUE;
                $nextKey = '';
                echo "Unidade: $unidade->id_unidade, $data->data_venda<br>";
                set_time_limit(600);
                while($hasNextKey === TRUE){
                    $retorno = $this->api_userede->consultarVendas(array(
                        'data_inicial' => $data->data_venda,
                        'data_final' => $data->data_venda,
                        'nextKey' => $nextKey,
                        'matriz' => $unidade->erede_companynumber,
                    ),$token->access_token);
                    $dados = json_decode($retorno);
                    if(property_exists($dados, 'content')){
                        foreach($dados->content->salesDaily as $dia){
                            $transacoes += $dia->sales;
                        }
                        $hasNextKey = $dados->cursor->hasNextKey;
                        $nextKey = @$dados->cursor->nextKey;
                    }
                    else{
                        $hasNextKey = FALSE;
                        $nextKey = '';
                    }
                }
                $parcelas = array();
                $hasNextKey = TRUE;
                $nextKey = '';
                while($hasNextKey === TRUE){
                    $retorno = $this->api_userede->consultarVendasParcelas(array(
                        'data_inicial' => $data->data_venda,
                        'data_final' => $data->data_venda,
                        'nextKey' => $nextKey,
                        'matriz' => $unidade->erede_companynumber,
                        'filial' => $unidade->erede_companynumber,
                    ),$token->access_token);
                    $dados = json_decode($retorno);
                    if(property_exists($dados, 'content')){
                        foreach($dados->content->installments as $parcela){
                            if(!isset($parcelas[$parcela->nsu])){
                                $parcelas[$parcela->nsu] = array();
                            }
                            $parcelas[$parcela->nsu][$parcela->installmentNumber] = $parcela;
                        }
                        $hasNextKey = $dados->cursor->hasNextKey;
                        $nextKey = @$dados->cursor->nextKey;
                    }
                    else{
                        $hasNextKey = FALSE;
                        $nextKey = '';
                    }
                }
                foreach($transacoes as $transacao){
                    $this->benepop->lancar_transacao_cartao($transacao,$unidade);
                    $this->benepop->atualizar_status_cartao($transacao,$parcelas);
                }
            }
        }
        $this->benepop->remover_vendas_duplicadas();
    }
    
    public function reprocessar_cartoes_nao_conciliados(){
        $this->load->model('benepop');
        $this->load->model('parametro');
        $this->load->library('api_userede');
        $this->load->model('apicartaousecases');
        $nsus_invalidos = (array)json_decode($this->parametro->get_valor('transacoes_nao_encontradas_userede'));
        $resultados = array();
        set_time_limit(600);
        foreach($this->benepop->get_unidades() as $unidade){
            if($unidade->erede_companynumber == ''){
                echo "$unidade->id_unidade sem erede_companynumber<br>";
                continue;
            }
            $cartoes = $this->benepop->reprocessar_cartoes_nao_conciliados($unidade->id_unidade);
            shuffle($cartoes);
            foreach($cartoes as $cartao){
                $start = time();
                if(in_array($cartao->codigo_autenticacao, $nsus_invalidos)){
                    continue;
                }
                echo "$cartao->codigo_autenticacao: ";
                $resultado = $this->apicartaousecases->buscarTransacaoCartao($cartao->codigo_autenticacao,$unidade->id_unidade);
                $resultados["$unidade->id_unidade-$cartao->codigo_autenticacao"] = $resultado;
                print_r($resultado);
                $duracao = time() - $start;
                echo "-> $duracao segundos<br>";
            }
        }
        echo json_encode($resultados);
    }
    
    
    public function baixarCartoes($param = array()){
        if(!is_array($param)){
            $param['data_inicial'] = $param;
        }
        $this->load->model('apicartaousecases');
        $data_inicial = $param['data_inicial'];
        $data_final = soma_dias_uteis($data_inicial, 5, array(0,1,2,3,4,5,6), array());
        while(strtotime($data_inicial) <= strtotime($data_final)){
            $resultado = $this->apicartaousecases->buscarVendasPorDia($data_inicial,$data_inicial);
            $data_inicial = soma_dias_uteis($data_inicial, 1, array(0,1,2,3,4,5,6), array());
        }
        //return $resultado;
//        if(!is_array($param)){
//            $param = array('dias_a_consultar'=>$param);
//        }
//        $this->load->model('apicartaousecases');
//        $data_inicial = soma_dias_uteis(date('Y-m-d'), ($param['dias_a_consultar'] ?? 1), array(1,2,3,4,5), array(), '-');
//        $data_final = soma_dias_uteis(date('Y-m-d'), ($param['dias_a_consultar'] ?? 1), array(1,2,3,4,5), array(), '-');
//        $resultado = $this->apicartaousecases->buscarVendasPorDia($data_inicial,$data_final);
//        $resultado = $this->apicartaousecases->bucarCartoesPrevistosPorDia($data_inicial,$data_final);
        if(strtotime($data_final) < strtotime(date('Y-m-d'))){
            $task_id = $this->orchestrator_task->insert(array(
                'job' => 10,
                'start_date' => date('Y-m-d H:i:s'),
                'periodicity_type' => 5,
                'periodicity_interval' => 0,
                'next_date' => date('Y-m-d H:i:s'),
                'status' => 0,
                'time_fixed' => 0,
            ));
            $this->orchestrator_task_parameter->insert(array(
                'task' => $task_id,
                'parameter' => 9,
                'value' => soma_dias_uteis($data_final, 1, array(0,1,2,3,4,5,6), array())
            ));
        }
        return $resultado;
    }
    
    public function baixarBoletos($param = array()){
        if(!is_array($param)){
            $param = array('dias_a_consultar'=>$param);
        }
        $this->load->model('apiboletousecases');
        $resultado = $this->apiboletousecases->buscarFrancesinha($param);
        if($param['dias_a_consultar'] > 1){
            $task_id = $this->orchestrator_task->insert(array(
                'job' => 11,
                'start_date' => date('Y-m-d H:i:s'),
                'periodicity_type' => 5,
                'periodicity_interval' => 0,
                'next_date' => date('Y-m-d H:i:s'),
                'status' => 0,
                'time_fixed' => 0,
            ));
            $this->orchestrator_task_parameter->insert(array(
                'task' => $task_id,
                'parameter' => 8,
                'value' => $param['dias_a_consultar']-1
            ));
        }
        return $resultado;
    }
    
    public function carregar_cache_cartoes($param = array()){
        $this->load->model('benepop');
        $this->load->model('apicartaousecases');
        $versao = $param['versao'] ?? 1;
        if($versao == 1){
            $this->apicartaousecases->gerar_cache();
        }
        elseif($versao == 2){
            $this->apicartaousecases->gerar_cache2();
        }
        elseif($versao == 3){
            $this->apicartaousecases->gerar_cache3();
        }
    }
}