<?php
    foreach($dropdowns ?? array() as $key=>$value){
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
<?php echo form_open($action ?? uri_string()); ?>
<?php
    foreach($fields as $field):
        if($field->disabled == 1 || $field->primary){
            $desabilitado = 'disabled';
        }
        else{
            $desabilitado = '';
        }
?>
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
                        echo form_dropdown($field->field, ${$field->dropdown_table}, $field->default_value,'class="form-control '.$field->class.'" '.$desabilitado);
                    }
                    elseif($field->type == 'text'){
                        echo form_textarea($field->field, $field->default_value,'class="form-control '.$field->class.'" '.$desabilitado);
                    }
                    elseif($field->type == 'file'){
                        echo form_upload($field->field,'','class="form-control '.$field->class.'"');
                    }
                    else{
                        echo form_input($field->field, $field->default_value,'class="form-control '.$field->class.'" '.$desabilitado);
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