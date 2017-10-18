<?php use_helper('Float'); ?>
<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>
<?php include_partial('drev/step', array('step' => 'vci', 'drev' => $drev)) ?>

    <div class="page-header"><h2>Répartition du VCI</h2></div>

    <form role="form" action="<?php echo url_for("drev_vci", $drev) ?>" method="post" class="ajaxForm" id="form_vci_drev_<?php echo $drev->_id; ?>">

    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <table class="table table-bordered table-striped table-condensed">
        <thead>
            <tr>
                <th class="text-left"></th>
                <th class="text-center col-xs-1"></th>
                <th class="text-center col-xs-1"></th>
                <th class="text-center col-xs-1" colspan="4">Utilisation</th>
            </tr>
            <tr>
                <th class="text-left col-xs-3">Appellation revendiquée</th>
                <th style="position: relative;" class="text-center col-xs-1">Plafond<br />&nbsp;<a title="A définir" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th style="position: relative;" class="text-center col-xs-1">Stock <?php echo ($drev->campagne - 1) ?><br />&nbsp;<a title="A définir" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th style="position: relative;" class="text-center col-xs-1">Destruction<br />&nbsp;<a title="A définir" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th style="position: relative;" class="text-center col-xs-1">Complément<br />&nbsp;<a title="A définir" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th style="position: relative;" class="text-center col-xs-1">Substitution<br />&nbsp;<a title="A définir" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th style="position: relative;" class="text-center col-xs-1">Rafraichi<br />&nbsp;<a title="A définir" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
            </tr>
        </thead>
        <tbody class="edit_vci">
            <?php foreach($form['produits'] as $hash => $formProduit): ?>
                <?php $produit = $drev->get($hash); ?>
                <tr class="produits vertical-center">
                    <td><?php echo $produit->getLibelleComplet() ?> <small class="text-muted">(<?php echoFloat($produit->recolte->superficie_total) ?> ha)</small></td>
                    <td class="text-right"><?php echoFloat($produit->getPlafondStockVci()) ?> <small class="text-muted">hl</small></td>
                    <td><?php echo $formProduit['stock_precedent']->render(array( 'placeholder' => "hl")) ?></td>
                    <td><?php echo $formProduit['destruction']->render(array( 'placeholder' => "hl")) ?></td>
                    <td><?php echo $formProduit['complement']->render(array( 'placeholder' => "hl")) ?></td>
                    <td><?php echo $formProduit['substitution']->render(array( 'placeholder' => "hl")) ?></td>
                    <td><?php echo $formProduit['rafraichi']->render(array('class' => 'form-control text-right input-float  sum_stock_final', 'placeholder' => "hl")) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 20px;" class="row row-margin row-button">
        <div class="col-xs-6">
            <a href="<?php echo url_for("drev_revendication", $drev) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
        </div>
        <div class="col-xs-6 text-right">
        <?php if ($drev->exist('etape') && $drev->etape == DrevEtapes::ETAPE_VALIDATION): ?>
            <button id="btn-validation" type="submit" class="btn btn-primary btn-upper">Valider et retourner à la validation <span class="glyphicon glyphicon-check"></span></button>
            <?php else: ?>
            <button type="submit" class="btn btn-primary btn-upper">Valider et continuer</span>  <span class="glyphicon glyphicon-chevron-right"></span></button>
        <?php endif; ?>
    </div>
    </form>
</div>