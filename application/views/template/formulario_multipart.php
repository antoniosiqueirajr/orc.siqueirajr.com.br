<?php
    foreach($dropdowns as $key=>$value){
        $$key = $value;
    }
    $fields         =   array();
    $fields_info    =   db_array($this->table_info->get_all(array('table'=>$tabela)),'field',TRUE);
    foreach($campos as $campo){
        $field = $fields_info[$campo];
        if(!is_object($field)){
            $field                  =   new stdClass();
            $field->table           =   $tabela;
            $field->field           =   $field;
            $field->type            =   'pseudo';
            $field->outer_function  =   '';
            $field->default_value   =   '';
            $field->dropdown_table  =   '';
            $field->class           =   '';
        }
        if($field->dropdown_table){
            $table = $field->dropdown_table;
            if(!isset($$table)){
                $$table = db_array($table, $field->dropdown_field, $field->dropdown_label);
            }
        }
        elseif($field->class == 'ckeditor'){
            $ckeditor   =   $field->field;
        }
        if(is_object(@$datasource)){
            $field->default_value = $datasource->{$field->field};
        }
        $fields[] = $field;
    }
    if(isset($ckeditor)):
?>
<script type="text/javascript" src="<?php echo base_url('ckeditor/ckeditor.js'); ?>"></script>
<script type="text/javascript">
    window.onload = function()  {
      CKEDITOR.replace( '<?php echo $ckeditor; ?>' );
    };
</script> 
<?php endif; ?>
<div class="clearfix"></div>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><?php echo $titulo; ?></small></h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li><a data-toggle="modal" data-target="#modal-action"><i class="fa fa-bolt"></i></a></li>
                    <li><a data-toggle="modal" data-target="#modal-help"><i class="fa fa-question"></i></a></li>
                    <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
                    <li><a onclick="location.reload();"><i class="fa fa-refresh"></i></a></li>
                    <li><a class="close-link"><i class="fa fa-close"></i></a>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="acoes">
                <?php
                    if(isset($acoes)){ 
                        echo $acoes;
                    }else{
                        $file = $this->uri->segment(1).'/acoes/'.$this->uri->segment(2);
                        if(file_exists(dirname(__FILE__).'/../'.$file.'.php')){
                            $this->load->view($file);
                        }
                    }
                ?>
            </div>
            <?php echo form_open_multipart(uri_string()); ?>
            <div class="x_content form-horizontal">
                <div class="clearfix"></div>
                <?php foreach($fields as $field): ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-sm-4 col-lg-2 control-label"><?php output($field->label); ?></label>
                            <div class="col-sm-8 col-lg-10">
                                <?php
                                    foreach(explode(',',$field->outer_function) as $function){
                                        if(strlen($function)){
                                            $field->default_value = $function($field->default_value);
                                        }
                                    }
                                    if($field->dropdown_table){
                                        echo form_dropdown($field->field, ${$field->dropdown_table}, $field->default_value,'class="form-control '.$field->class.'"');
                                    }
                                    elseif($field->type == 'text'){
                                        echo form_textarea($field->field, $field->default_value,'class="form-control '.$field->class.'"');
                                    }
                                    elseif($field->type == 'file'){
                                        echo form_upload($field->field,'','class="form-control '.$field->class.'"');
                                    }
                                    else{
                                        echo form_input($field->field, $field->default_value,'class="form-control '.$field->class.'"');
                                    }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="clearfix"></div>
            <div class="ln_solid"></div>
            <?php if(!@$sem_acoes): ?>
            <div class="form-group">
                <div class="col-sm-8 col-lg-10 col-sm-offset-4 col-lg-offset-2">
                    <button type="submit" class="btn btn-primary"> <i class="fa fa-check"></i> Salvar </button>
                    <button type="reset" class="btn btn-danger"> <i class="fa fa-times"></i> Cancelar </button>
                </div>
            </div>
            <?php endif; ?>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<div id="modal-help" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close">&times;</button>
                <h4 class="modal-title">Ajuda</h4>
            </div>
            <div class="modal-body">
                <?php
                    if(isset($ajuda)):
                      echo ($ajuda);
                    else:
                        $ajuda  =   $this->ajuda->get(array('controller'=>$this->uri->segment(1),'method'=>$this->uri->segment(2)));
                        if(is_object($ajuda)):
                            output($ajuda->conteudo);
                        else:
                            $file   =   $this->uri->segment(1).'/ajuda/'.$this->uri->segment(2);
                            if(file_exists(dirname(__FILE__).'/../'.$file.'.php')):
                                $this->load->view($file);
                            else:
                ?>
                <p>
                    <i>Não disponível nesse momento.</i>
                </p>
                <?php endif;endif;endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-default">Fechar</button>
            </div>
        </div>
    </div>
</div>