<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="description" content="<?php echo $description; ?>">
        <meta name="robots" content="no-follow">
        <link rel="shortcut icon" type="image/png" href="<?php echo base_url(); ?>assets/img/favicon.png">
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/custom/css/login.css" />
        <title><?php echo $title; ?></title>
    </head>
    <body>
        <div class="background">
            <div class="informacoes">
                <div class="logo">
                    <strong>Orchestrator</strong>
                </div>
            </div>
            <div class="painel">
                <div class="formulario">
                    <div class="runtech">
                        <img src="<?php echo base_url(); ?>assets/img/logo.png" alt="Parque das Allamandas">
                    </div>
                    <div class="campos">
                        <?php echo form_open('logar'); ?>
                        <label>Login</label>
                        <div class="campo login">
                            <i class="fa fa-user"></i>
                            <input name="login" placeholder="Ex: FNOGUEIRA" />
                        </div>
                        <label>Senha</label>
                        <div class="campo senha">
                            <i class="fa fa-user"></i>
                            <input type="password" name="senha" placeholder="******" />
                        </div>
                        <label>&nbsp;</label>
                        <div class="botao">
                            <button type="submit">Entrar</button>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
        <script>
            <?php if($swal): ?>
            Swal.fire(
                '<?php echo $swal['title']; ?>',
                '<?php echo $swal['message']; ?>',
                '<?php echo $swal['type']; ?>',
            );
            <?php endif; ?>
        </script>
    </body>
</html>