<div class="clearfix"></div>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <?php echo form_open(uri_string()); ?>
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
            <div class="x_content form-horizontal">
                <?php echo $body; ?>
            </div>
            <div class="clearfix"></div>
            <div class="ln_solid"></div>
            <div class="form-group">
                <div class="col-sm-8 col-lg-10 col-sm-offset-4 col-lg-offset-2">
                    <button type="submit" class="btn btn-primary"> <i class="fa fa-check"></i> Salvar </button>
                    <button type="reset" class="btn btn-danger"> <i class="fa fa-times"></i> Cancelar </button>
                </div>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<div id="modal-action" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close">&times;</button>
                <h4 class="modal-title">Ações</h4>
            </div>
            <div class="modal-body">
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
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-default btn-danger">Cancelar</button>
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
                        $file = $this->uri->segment(1).'/ajuda/'.$this->uri->segment(2);
                        if(file_exists(dirname(__FILE__).'/../'.$file.'.php')):
                            $this->load->view($file);
                        else:
                ?>
                <p>
                    <i>Não disponível nesse momento.</i>
                </p>
                <?php endif;endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-default">Fechar</button>
            </div>
        </div>
    </div>
</div>