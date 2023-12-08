<nobr>
    <a class="btn btn-xs btn-primary" onclick="modal_load('<?php echo base_url("orchestrator/orchestrator_job_parameters_edit/$row->job/$row->id"); ?>');" data-toggle="tooltip" data-placement="top" title="Editar">
        <i class="fa fa-edit"></i>
    </a>
    <a class="btn btn-xs btn-danger" onclick="confirmar('Deseja realmente excluir esse registro?','<?php echo base_url('orchestrator/orchestrator_job_parameters_excluir/'.$row->id); ?>','btn-danger');" data-toggle="tooltip" data-placement="top" title="Excluir">
        <i class="fa fa-times"></i>
    </a>
</nobr>