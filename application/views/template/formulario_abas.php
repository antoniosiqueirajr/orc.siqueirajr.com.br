<?php
    foreach($dropdowns as $key=>$value){
        $$key = $value;
    }
    $fields         =   array();
    $fields_info    =   db_array($this->table_info->get_all(array('table'=>$tabela)),'field',TRUE);
    if(isset($campos_detalhes)){
        foreach($campos_detalhes as $campo){
            foreach($campo as $key=>$value){
                $fields_info[$campo['field']]->$key = $value;
            }
        }
    }
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
            $field->disabled        =   0;
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
<script type="text/javascript" src="/custom/assets/ckeditor/ckeditor.js"></script>
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
                <h2><?php echo $id ? 'Editar' : 'Cadastrar', " $titulo"; ?></small></h2>
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
            <?php
                if(isset($acoes)){ 
                    echo "<div class=\"acoes\">$acoes</div>";
                }else{
                    $file = $this->uri->segment(1).'/linha/'.$this->uri->segment(2);
                    if(file_exists(dirname(__FILE__).'/../'.$file.'.php')){
                        echo '<div class="acoes">';
                        $this->load->view($file,array('row'=>$datasource));
                        echo '</div>';
                    }
                }
            ?>
            <div class="clearfix"></div>
            <div class="x_content form-horizontal no-gutters">
                <div class="col-lg-2 col-sm-5">
                    <!-- required for floating -->
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs tabs-left">
                        <li class="active"><a href="#principal" data-toggle="tab"><span class="icone_aba"><i class="fa fa-home"></i></span>Principal</a>
                        </li>
                        <?php
                            if($id):
                                foreach($abas as $aba): ?>
                        <li>
                            <a href="#<?php echo make_link($aba['titulo']) ?>" data-toggle="tab">
                                <span class="icone_aba"><i class="fa <?php echo $aba['icone'] ?? 'fa-list-ul'; ?>"></i></span>
                                <?php echo $aba['titulo']; ?>
                            </a>
                        </li>
                        <?php
                                endforeach;
                            endif;
                        ?>
                    </ul>
                </div>

                <div class="col-lg-10 col-sm-7">
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div class="tab-pane active" id="principal">
                            <?php echo form_open_multipart(uri_string()); ?>
                            <?php foreach($fields as $field): ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="col-sm-4 col-lg-2 control-label"><?php echo ($field->label); ?></label>
                                        <div class="col-sm-8 col-lg-10">
                                            <?php
                                                $disabled   =   $field->disabled ? 'disabled' : '';
                                                foreach(explode(',',$field->outer_function) as $function){
                                                    if(strlen($function)){
                                                        $field->default_value = $function($field->default_value);
                                                    }
                                                }
                                                if($field->dropdown_table){
                                                    echo form_dropdown($field->field, ${$field->dropdown_table}, $field->default_value,'class="form-control '.$field->class.'" '.$disabled);
                                                }
                                                elseif($field->type == 'text'){
                                                    echo form_textarea($field->field, $field->default_value,'class="form-control '.$field->class.'" '.$disabled);
                                                }
                                                elseif($field->type == 'file'){
                                                    if($field->default_value){
                                                        echo '<div class="form-preview">';
                                                        echo gerar_tag_midia('/'.$field->default_value);
                                                        echo '</div>';
                                                        
                                                    }
                                                    echo form_upload($field->field,$field->default_value,'class="form-control '.$field->class.'" '.$disabled);
                                                }
                                                else{
                                                    echo form_input($field->field, $field->default_value,'class="form-control '.$field->class.'" '.$disabled);
                                                }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
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
                        <?php foreach($abas as $aba): ?>
                        <div class="tab-pane" id="<?php echo make_link($aba['titulo']) ?>"><?php echo $aba['conteudo']; ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                            
            </div>
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