<?php
    foreach($menu[0] as $l1):
        if($l1->tipo == 4):
?>
    <li><a><i class="fa <?php echo($l1->classe) ?>"></i> <?php echo($l1->label); ?> <span class="fa fa-chevron-down"></span></a>
        <ul class="nav child_menu">
        <?php
            foreach($menu[$l1->id] as $l2):
                if($l2->tipo == 1):
                    $l2_action  =   'href="'.  base_url($l2->link).'"';
                elseif($l2->tipo == 2):
                    $l2_action  =   'href="'.$l2->link.'"';
                elseif($l2->tipo == 3):
                    $l2_action  =   'class="'.$l2->classe.'"';
                endif;
                if($l2->tipo == 5):
        ?>
            <li class="divider"></li>
        <?php       
                elseif($l2->tipo == 4):
        ?>
        <li><a><?php echo($l2->label); ?><span class="fa fa-chevron-down"></span></a>
            <ul class="nav child_menu">
                <?php
                    foreach($menu[$l2->id] as $l3):
                        if($l3->tipo == 1):
                            $l3_action  =   'href="'.  base_url($l3->link).'"';
                        elseif($l3->tipo == 2):
                            $l3_action  =   'href="'.$l3->link.'"';
                        elseif($l3->tipo == 3):
                            $l3_action  =   'class="'.$l3->classe.'"';
                        endif;
                        if($l3->tipo == 5):
                ?>
                    <li class="divider"></li>
                <?php       
                        else:
                ?>
                    <li><a <?php echo($l3_action); ?>><?php echo($l3->label); ?></a></li>
                <?php  
                            endif;
                    endforeach;
                ?>
            </ul>
        </li>
        <?php       
                else:
        ?>
            <li><a <?php echo($l2_action); ?>><?php echo($l2->label); ?></a></li>
        <?php  
                    endif;
            endforeach;
        ?>
        </ul>
    </li>
<?php
        else:
?>
    <li><a href="<?php echo base_url($l1->link); ?>"><i class="fa <?php echo($l1->classe); ?>"></i> <?php echo($l1->label); ?> </a></li>
<?php
        endif;
    endforeach;