<?php include_partial('drev/step', array('step' => 'degustation_conseil', 'drev' => $drev)) ?>

<div class="page-header no-border">
    <h2>Dégustation conseil <small>Réaliser par l'AVA</small></h2>
</div>

<?php include_partial('drev/stepDegustationConseil', array('step' => 'lot_alsace', 'drev' => $drev)) ?>

<form method="post" action="<?php echo url_for('drev_lots', $drev->addPrelevement(Drev::CUVE_ALSACE)); ?>" role="form" class="ajaxForm">
	<p>Veuillez indiquer le nombre de lots susceptibles d'être prélevés en AOC Alsace (AOC Alsace Communale et Lieu-dit inclus).</p>
	
	<p>Un lot doit correspondre au maximum à 4 récipients et au maximum à 2000 hl.</p>

   	<?php include_partial('drev/lotsForm', array('drev' => $drev, 'form' => $form, 'ajoutForm' => $ajoutForm)); ?>
    
    <div class="row row-margin row-button">
        <div class="col-xs-6">
            <a href="<?php echo url_for("drev_degustation_conseil", $drev) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'organisation</small></a>
        </div>
        <div class="col-xs-6 text-right">
            <button type="submit" class="btn btn-default btn-lg btn-upper">Valider <small>et répartir les lots suivant</small>&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
        </div>
    </div>
</form>

<?php include_partial('drev/popupAjoutForm', array('url' => url_for("drev_lots_ajout", $prelevement), 'form' => $ajoutForm)); ?>
