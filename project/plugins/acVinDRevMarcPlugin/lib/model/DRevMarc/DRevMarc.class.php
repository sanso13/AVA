<?php

/**
 * Model for DRev
 *
 */
class DRevMarc extends BaseDRevMarc implements InterfaceDeclaration {

    protected $declarant_document = null;

    public function __construct() {
        parent::__construct();
        $this->initDocuments();
    }

    public function __clone() {
        parent::__clone();
        $this->initDocuments();
    }

    protected function initDocuments() {
        $this->declarant_document = new DeclarantDocument($this);
    }

    public function constructId() {
        $this->set('_id', 'DREVMARC-' . $this->identifiant . '-' . $this->campagne);
    }

    public function initDoc($identifiant, $campagne) {
        $this->identifiant = $identifiant;
        $this->campagne = $campagne;
    }

    public function storeDeclarant() {
        $this->declarant_document->storeDeclarant();
    }
    
    public function storeEtape($etape) {
    	$this->add('etape', $etape);
    }

    public function getEtablissementObject() {

        return EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);
    }
    
    public function validate() {
        $this->validation = date('Y-m-d');
    }
    
    public function isValide() {
        return $this->exist('validation') && $this->validation;
    }

    public function isPapier() { 
        
        return $this->exist('papier') && $this->get('papier');
    }

    public function isAutomatique() { 
        
        return $this->exist('automatique') && $this->get('automatique');
    }

    public function getValidation() {

        return $this->_get('validation');
    }

    public function getValidationOdg() {

        return $this->_get('validation_odg');
    }
}