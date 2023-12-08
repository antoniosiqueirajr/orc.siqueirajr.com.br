<?php
    $notificacoes   =   $this->notificacao->get_all(array('pessoa'=>$this->usuario->get_user('cliente'),'status'=>0));
    $count          =   count($notificacoes);
?>
<li class="dropdown"><a data-toggle="dropdown" href="#" class="dropdown-toggle"><i class="fa fa-bell fa-fw"></i><?php if($count): ?><span class="badge badge-yellow"><?php echo $count; ?></span><?php endif; ?></a>
    <ul class="dropdown-menu dropdown-alerts animated bounceInDown">
        <?php
            foreach($notificacoes as $notificacao):
                $data_notificacao = new DateTime($notificacao->data);
                $data_now = new DateTime(date('Y-m-d H:i:s'));
                $datediff = $data_now->diff($data_notificacao);
                if ($datediff->y > 0) {
                    if ($datediff->y > 1) {
                        $tempo = $datediff->y . ' anos';
                    } else {
                        $tempo = '1 ano';
                    }
                }
                elseif ($datediff->m > 0) {
                    if ($datediff->m > 1) {
                        $tempo = $datediff->m . ' meses';
                    } else {
                        $tempo = '1 mes';
                    }
                }
                elseif ($datediff->d > 0) {
                    if ($datediff->d > 1) {
                        $tempo = $datediff->d . ' dias';
                    } else {
                        $tempo = 'ontem';
                    }
                } 
                elseif ($datediff->h > 0) {
                    if ($datediff->h > 1) {
                        $tempo = $datediff->h . ' horas';
                    } else {
                        $tempo = '1 hora';
                    }
                }
                elseif ($datediff->i > 0) {
                    if ($datediff->i > 1) {
                        $tempo = $datediff->i . ' minutos';
                    } else {
                        $tempo = '1 minuto';
                    }
                }
                else {
                    $tempo = 'segundos';
                }
                $tempo .= ' atrás';
                if($notificacao->link == '' && $notificacao->conteudo != ''){
                    $notificacao->link = 'notificacoes/conteudo/'.$notificacao->id;
                }
        ?>
        <li><a href="<?php echo $notificacao->link; ?>"><span class="label label-<?php echo $notificacao->classe; ?>"><i class="fa <?php echo $notificacao->icone ?> fa-fw"></i></span><?php output($notificacao->titulo); ?><span class="pull-right text-muted small"><?php echo $tempo; ?></span></a></li>
        <?php endforeach; ?>
        <li><a href="<?php echo base_url('notificacoes'); ?>" class="text-right">Ver todas as notificações</a></li>
    </ul>
</li>