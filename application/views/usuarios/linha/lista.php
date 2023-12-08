<nobr>
    <a class="btn btn-xs btn-primary" href="<?php echo base_url('usuarios/editar/'.$row->id); ?>" data-toggle="tooltip" data-placement="top" title="Editar">
        <i class="fa fa-edit"></i>
    </a>
    <a class="btn btn-xs btn-danger" onclick="confirmar('Deseja realmente excluir esse registro?','<?php echo base_url('usuarios/excluir/'.$row->id); ?>','btn-danger');" data-toggle="tooltip" data-placement="top" title="Excluir">
        <i class="fa fa-times"></i>
    </a>
    <a class="btn btn-xs btn-warning" onclick="modal_load('<?php echo base_url('usuarios/alterar_senha_lista/'.$row->id); ?>')" data-toggle="tooltip" data-placement="top" title="Alterar Senha">
        <i class="fa fa-lock"></i>
    </a>
    <a class="btn btn-xs btn-info" href="<?php echo base_url('usuarios/token'.$row->id) ?>" data-toggle="tooltip" data-placement="top" title="Enviar Nova Senha">
        <i class="fa fa-envelope"></i>
    </a>
</nobr>