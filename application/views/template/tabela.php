<?php
if(isset($dropdowns) and is_array($dropdowns)){
    foreach ($dropdowns as $key => $value) {
        $$key = $value;
    }
}
$table_infos = db_array($this->table_info->get_all(array('table' => $tabela)),'field',TRUE);
foreach($campos as $campo){
    $field = @$table_infos[$campo];
    if(!is_object($field)){
        $field =   new stdClass();
        $field->field          =   $campo;
        $field->label          =   $campo;
        $field->dropdown_table =   '';
        $field->outer_function =   '';
        $field->class          =   '';
    }
    if($field->dropdown_table){
        $table = $field->dropdown_table;
        if(!isset($$table)){
            $$table = db_array($table, $field->dropdown_field, $field->dropdown_label);
        }
    }
    $fields[] = $field;
}
?>
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
                    <li><a onclick="change_size($(this));"><i class="fa fa-arrows-alt"></i></a></li>
                    <li><a class="close-link"><i class="fa fa-close"></i></a>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content form-horizontal">
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
                <div class="clearfix"></div>
                <?php if(isset($totais)): ?>
                <div class="row tile_count">
                <?php foreach($totais as $total): if(!isset($total['icone'])) continue; ?>
                    <div class="<?php echo $total['tamanho'] ?? 'col-md-4 col-sm-6'; ?> tile_stats_count">
                        <span class="count_top"><i class="fa <?php echo $total['icone'] ?>"></i> <?php echo $total['nome'] ?></span>
                        <div class="count <?php echo @$total['cor'] ?>"><?php echo $total['valor'] ?></div>
                    </div>
                <?php endforeach; ?>
                </div>
                <div class="clearfix"></div>
                <?php endif; ?>
                <?php echo form_open('',array('id'=>make_link($titulo).'_form')); ?>
                <table id="grid" class="datatable_init table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th></th>
                            <?php foreach($fields as $field): ?>
                            <th><?php echo ($field->label); ?></th>
                            <?php endforeach; ?>
                            <?php if(file_exists(dirname(__FILE__).'/../'.$this->uri->segment(1).'/linha/'.$this->uri->segment(2).'.php')): ?>
                            <th></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($datasource as $row): $field_name = $fields[0]->field; ?>
                        <tr>
                            <td>
                                <input type="checkbox" value="<?php echo ($row->$field_name); ?>" class="<?php echo make_link($titulo); ?>" name="<?php echo make_link($titulo); ?>[]">
                            </td>
                            <?php foreach($fields as $field): ?>
                            <td class="<?php echo $field->class; ?>">
                                <?php
                                    $field_name = $field->field;
                                    $value = $row->$field_name;
                                    if($field->dropdown_table){
                                        $table = $field->dropdown_table;
                                        $value = @${$table}[$value];
                                    }
                                    if($field->outer_function){
                                        $functions = explode(',',$field->outer_function);
                                        foreach($functions as $function){
                                            $function = trim($function);
                                            $value = @$function($value);
                                        }
                                    }
                                    echo ($value);
                                ?>
                            </td>
                            <?php endforeach; ?>
                            <?php if(file_exists(dirname(__FILE__).'/../'.$this->uri->segment(1).'/linha/'.$this->uri->segment(2).'.php')): ?>
                            <td>
                                <?php require(dirname(__FILE__).'/../'.$this->uri->segment(1).'/linha/'.$this->uri->segment(2).'.php'); ?>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php echo form_close(); ?>
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
<?php if(@$totais): ?>
<script>
<?php foreach($totais as $total){ echo 'const total_'.make_var($total['nome'])." = '".$total['valor']."';"; } ?>
</script>
<?php endif; ?>