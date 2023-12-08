<script>
    const cartoes = JSON.parse('<?php echo json_encode($cartoes) ?>');
    function buscar_cartoes(){
    cartoes.map(item=>{
        const url = `${base_url}api/cartao_consultar/${item.unidade}-${item.nsu}`;
        $.ajax({
            url: url,
            type: 'GET',
            async: false,
            success: function (retorno) {
                $('#resultados').append(`<div>
                    <h5>${item.unidade}-${item.nsu}</h5>
                    <div>${retorno}</div>
                </div>`);
            }
        });
    });
}
</script>
<div class="clearfix"></div>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Atualização de cartões</h2>
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
            <div class="x_content form-horizontal">
                <a class="btn btn-success" onclick="buscar_cartoes();">Buscar Cartões</a>
                <div id="resultados"></div>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>