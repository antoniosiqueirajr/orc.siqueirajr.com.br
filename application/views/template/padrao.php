<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <!-- Meta, title, CSS, favicons, etc. -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo $title; ?></title>

        <!-- Bootstrap -->
        <link href="<?php echo base_url(); ?>assets/template/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="<?php echo base_url(); ?>assets/template/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
        
        <!--LOADING SCRIPTS FOR PAGE-->
        <?php foreach($ext_files['css'] as $css){ echo $css; } ?>
        
        <!-- Custom Theme Style -->
        <link href="<?php echo base_url(); ?>assets/template/css/template.css" rel="stylesheet">
        
        <link type="text/css" rel="stylesheet" href="<?php echo base_url(); ?>assets/custom/css/default.css">
        <link rel="shortcut icon" href="<?php echo base_url(); ?>assets/img/favicon.png">
        
        <script>
            const base_url='<?php echo base_url(); ?>';
            const project='<?php echo $this->config->item('csrf_token_name'); ?>';
            const currenUserName='<?php echo $this->usuario->get_user('nome'); ?>';
        </script>
    </head>

  <body class="nav-md footer_fixed">
    <div class="container body">
      <div class="main_container">
       <div class="col-md-3 left_col">
          <div class="left_col scroll-view">
            <div class="navbar nav_title" style="border: 0;">
                <a href="<?php echo base_url(); ?>" class="site_title">
                    <img src="<?php echo base_url(); ?>assets/img/logo.png">
                </a>
            </div>

            <div class="clearfix"></div>

            <!-- sidebar menu -->
            <?php $this->load->view('template/menu') ?>
          </div>
        </div>

        <!-- top navigation -->
        <div class="top_nav">
          <div class="nav_menu">
            <nav>
              <div class="nav toggle">
                <a id="menu_toggle"><i class="fa fa-bars"></i></a>
              </div>
              <ul class="nav navbar-nav navbar-right">
                <li class="">
                  <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <img src="<?php echo base_url($this->usuario->get_user('avatar')); ?>" alt=""><?php echo ($this->usuario->get_user('nome')); ?>
                    <span class=" fa fa-angle-down"></span>
                  </a>
                  <ul class="dropdown-menu dropdown-usermenu pull-right">
                    <li><a onclick="$('#modal-avatar').modal();"><i class="fa fa-file-photo-o pull-right"></i> Alterar Avatar</a></li>
                    <li><a onclick="$('#modal-senha').modal();"><i class="fa fa-key pull-right"></i> Alterar Senha</a></li>
                    <li><a href="<?php echo base_url('logoff'); ?>"><i class="fa fa-sign-out pull-right"></i> Log Out</a></li>
                  </ul>
                </li>
                <li role="presentation" class="dropdown">
                    <a href="javascript:;" class="dropdown-toggle info-number" data-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-bell-o"></i>
                        <?php
                            $notificacoes   =   $this->notificacao->get_notificacoes_nao_lidas();
                        ?>
                        <span class="notificacao_contador badge <?php if($notificacoes) echo'badge-warning'; ?>"><?php echo count($notificacoes) ?></span>
                    </a>
                    <ul id="menu1" class="dropdown-menu list-unstyled msg_list" role="menu">
                        <?php foreach($notificacoes as $notificacao): ?>
                        <li data-notificacao-menu-item="<?php echo $notificacao->id; ?>">
                            <a>
                                <span class="image <?php echo $notificacao->classe; ?>"><i class="fa <?php echo $notificacao->icone; ?>"></i></span>
                                <span>
                                    <span>Sistema</span>
                                    <span class="time"><?php echo sql_data_hora($notificacao->data); ?></span>
                                </span>
                                <span class="message">
                                    <?php echo $notificacao->conteudo; ?>
                                </span>
                                <span class="message">
                                    <button class="btn btn-xs" onclick="notificacao_marcar_como_lido($(this));" data-notificacao="<?php echo $notificacao->id; ?>"><i class="fa fa-check-square-o"></i> Marcar como lido</button>
                                </span>
                            </a>
                        </li>
                        <?php endforeach ?>
                        <li>
                            <div class="text-center">
                                <a href="<?php echo base_url('comunicacao/notificacoes'); ?>">
                                    <strong>Ver todos</strong>
                                    <i class="fa fa-angle-right"></i>
                                </a>
                            </div>
                        </li>
                    </ul>
                </li>
                <li class="">
                  <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                      Atalhos &nbsp;&nbsp;<i class="fa fa-flash"></i>
                  </a>
                  <ul class="dropdown-menu dropdown-usermenu pull-right">
                      <?php foreach($this->usuario->get_atalhos() as $atalho): ?>
                      <li><a onclick="modal_load('<?php echo base_url($atalho->link); ?>');"><i class="fa <?php echo $atalho->classe; ?> pull-right"></i> <?php echo $atalho->label; ?></a></li>
                      <?php endforeach; ?>
                  </ul>
                </li>
              </ul>
            </nav>
          </div>
        </div>
        <!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
            <?php echo @$body; ?>
          </div>
        </div>
        <!-- /page content -->

        <!-- footer content -->
        <footer>
            <div class="pull-right">
                Parque das Allamandas. Todos os direitos reservados
            </div>
            <div class="clearfix"></div>
        </footer>
        <!-- /footer content -->
      </div>
    </div>
    <div id="modal-mensagem" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" data-dismiss="modal" aria-hidden="true" class="close">&times;</button>
                    <h4 class="modal-title"><?php echo @$mensagem_titulo; ?></h4>
                </div>
                <div class="modal-body">
                    <?php echo @$mensagem; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn btn-default">Fechar</button>
                </div>
            </div>
        </div>
    </div>
    <div id="modal-senha" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" data-dismiss="modal" aria-hidden="true" class="close">&times;</button>
                    <h4 class="modal-title">Alterar Senha</h4>
                </div>
                <?php echo form_open('usuarios/alterar_senha'); ?>
                <div class="modal-body">
                    <div class="row form-horizontal">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="col-sm-6 col-lg-4 control-label">Senha atual</label>
                                <div class="col-sm-6 col-lg-8">
                                    <input type="password" name="senha" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-6 col-lg-4 control-label">Nova senha</label>
                                <div class="col-sm-6 col-lg-8">
                                    <input type="password" name="nova_senha" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-6 col-lg-4 control-label">Repita a nova senha</label>
                                <div class="col-sm-6 col-lg-8">
                                    <input type="password" name="confirma_senha" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn btn-danger">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Alterar</button>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
    <div id="modal-avatar" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" data-dismiss="modal" aria-hidden="true" class="close">&times;</button>
                    <h4 class="modal-title">Mudar Avatar</h4>
                </div>
                <?php echo form_open_multipart('usuarios/alterar_avatar'); ?>
                <div class="modal-body">
                    <div class="row form-horizontal">
                        <div class="col-md-6 col-lg-4">
                            <img src="<?php echo $this->usuario->get_user('avatar'); ?>" class="img-responsive img-circle">
                        </div>
                        <div class="col-md-6 col-lg-8">
                            <input type="file" name="avatar">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn btn-danger">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Alterar</button>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="<?php echo base_url(); ?>assets/template/vendors/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="<?php echo base_url(); ?>assets/template/vendors/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/template/vendors/fastclick/lib/fastclick.js"></script>
    <script src="<?php echo base_url(); ?>assets/template/vendors/nprogress/nprogress.js"></script>
    <script src="<?php echo base_url(); ?>assets/vendors/maskMoney/jquery.maskMoney.js"></script>
    <script src="<?php echo base_url(); ?>assets/custom/js/default.js"></script>

    <?php foreach($ext_files['js'] as $js){ echo $js; } ?>
    
    <!-- Custom Theme Scripts -->
    <script src="<?php echo base_url(); ?>assets/template/js/custom.js"></script>
    <script>
        $(document).ready(function(){
            <?php if(@$mensagem): ?>
            $('#modal-mensagem').modal();
            <?php endif; ?>
            <?php if($this->usuario->get_user('login') != 'suporte@b10web.com.br'): ?>
            $('.menu_arquitetura').parents('li').addClass('hidden');
            <?php endif; ?>
            var ancora = $(location).attr('hash');
            if(ancora){
                $('.nav-tabs a[href="'+ancora+'"]').click();
            }
        });
    </script>
</body>
</html>