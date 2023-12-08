<?php
    foreach($dropdowns ?? array() as $key=>$value){
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
<div class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close">&times;</button>
                <h4 class="modal-title"><?php echo $titulo ?></h4>
            </div>
            <?php echo form_open_multipart(uri_string()); ?>
            <div class="modal-body form-horizontal">
                <?php foreach($fields as $field): ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-sm-4 col-lg-2 control-label"><?php echo ($field->label); ?></label>
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
                                        if($datasource){
                                            echo '<div class="form-preview-container"><iframe src="'.base_url('downloader/visualizar/'.$tabela.'/'.$field->field.'/'.$datasource->id).'" class="form-preview"></iframe></div>';
                                        }
                                        echo form_upload($field->field,'','class="form-control '.$field->class.'"');
                                    }
                                    elseif($field->type == 'password'){
                                        echo form_password($field->field, '','class="form-control '.$field->class.'"');
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
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-danger"><?php echo $btn_cancelar ?? 'Cancelar'; ?></button>
                <button type="submit" class="btn btn-primary"><?php echo $btn_submit ?? 'Salvar'; ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>