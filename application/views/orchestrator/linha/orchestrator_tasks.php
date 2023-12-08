<nobr>
    <a class="btn btn-xs btn-primary" href="<?php echo base_url('orchestrator/orchestrator_tasks_edit/'.$row->id); ?>" data-toggle="tooltip" data-placement="top" title="Editar">
        <i class="fa fa-edit"></i>
    </a>
    <a class="btn btn-xs btn-danger" onclick="confirmar('Are you sure?','<?php echo base_url('orchestrator/orchestrator_tasks_delete/'.$row->id); ?>','btn-danger');" data-toggle="tooltip" data-placement="top" title="Excluir">
        <i class="fa fa-times"></i>
    </a>
</nobr>