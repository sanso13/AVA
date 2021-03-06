<?php
/**
 * Model for TemplateFactureCotisation
 *
 */

class TemplateFactureCotisation extends BaseTemplateFactureCotisation {

	public function generateCotisations($document) {
		$cotisationsCollection = $this->getInstanceCotisation($document);

		return $cotisationsCollection->getCotisations();
	}

	protected function getInstanceCotisation($document) {
		$modele = $this->modele;

		return new $modele($this, $document);
	}
}
