<?php if(!isset($header)) $header = 'Servidor:'; ?>
<div class="modal fade">
    <div class="modal-dialog <?php output(@$class); ?>">
        <?php output(@$precontent); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close">&times;</button>
                <h4 class="modal-title"><?php output($header); ?></h4></div>
            <div class="modal-body"><?php output(@$body); ?></div>
            <div class="modal-footer"><?php output(@$footer); ?></div>
        </div>
        <?php output(@$postcontent); ?>
    </div>
</div>;