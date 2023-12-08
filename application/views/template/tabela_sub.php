<?php
$metodo     =   $metodo ?? $this->uri->segment(2);
$variaveis  =   array();
if(isset($dropdowns) and is_array($dropdowns)){
    foreach ($dropdowns as $key => $value) {
        $variaveis[$key]   =   $value;
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
<div class="row tabela-sub">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_content form-horizontal">
            <?php
                if(isset($acoes)){ 
                    echo "<div class=\"acoes\">$acoes</div>";
                }else{
                    $file = $this->uri->segment(1).'/acoes/'.$metodo;
                    if(file_exists(dirname(__FILE__).'/../'.$file.'.php')){
                        echo '<div class="acoes">';
                        $this->load->view($file,$variaveis);
                        echo '</div>';
                    }
                }
            ?>
            <div class="clearfix"></div>
            <?php echo form_open('',array('id'=>make_link($titulo).'_form')); ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <?php foreach($fields as $field): ?>
                        <th><?php echo ($field->label); ?></th>
                        <?php endforeach; ?>
                        <?php if(file_exists(dirname(__FILE__).'/../'.$this->uri->segment(1).'/linha/'.$metodo.'.php')): ?>
                        <th></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($datasource as $row): $field_name = $fields[0]->field; ?>
                    <tr>
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
                        <?php if(file_exists(dirname(__FILE__).'/../'.$this->uri->segment(1).'/linha/'.$metodo.'.php')): ?>
                        <td>
                            <?php require(dirname(__FILE__).'/../'.$this->uri->segment(1).'/linha/'.$metodo.'.php'); ?>
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