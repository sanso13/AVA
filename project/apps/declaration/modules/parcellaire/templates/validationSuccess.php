<?php include_partial('parcellaire/step', array('step' => 'validation', 'parcellaire' => $parcellaire)) ?>
<div class="page-header">
    <h2>Validation de votre parcellaire</h2>
</div>

<form role="form" action="<?php echo url_for("parcellaire_validation", $parcellaire) ?>" method="post">
    <?php echo $form->renderHiddenFields() ?>
    <?php echo $form->renderGlobalErrors() ?>
    <?php if ($validation->hasPoints()): ?>
        <?php //include_partial('drev/pointsAttentions', array('drev' => $drev, 'validation' => $validation)); ?>
    <?php endif; ?>
    <?php include_partial('parcellaire/recap', array('parcellaire' => $parcellaire,'parcellesByCommunes' => $parcellesByCommunes)); ?>

     <div class="row row-margin row-button">
        <div class="col-xs-4">
            <a href="<?php echo url_for("parcellaire_parcelles", array('id' => $parcellaire->_id,'appellation' => 'LIEUDIT')); ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'étape précédente</small></a>
        </div>
        <div class="col-xs-4 text-center">
            <a href="<?php echo url_for("parcellaire_export_pdf", $parcellaire) ?>" class="btn btn-warning btn-lg">
                <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Prévisualiser
            </a>
        </div>
        <div class="col-xs-4 text-right">
            <button type="submit" <?php if($validation->hasErreurs()): ?>disabled="disabled"<?php endif; ?> class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Valider la déclaration</button>
        </div>
    </div>

</form>