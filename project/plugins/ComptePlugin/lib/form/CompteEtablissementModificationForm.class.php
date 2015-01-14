<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CompteEtablissementModificationForm
 *
 * @author mathurin
 */
class CompteEtablissementModificationForm extends CompteModificationForm {

    private $syndicats;
    private $nbChais;

    public function __construct(\acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
        $this->initDefaultSyndicats();
    }

    public function configure() {
        parent::configure();

        $this->setWidget("raison_sociale", new sfWidgetFormInput(array("label" => "Raison sociale")));

        $this->setValidator('raison_sociale', new sfValidatorString(array("required" => true)));

        if($this->getObject()->isNew()) {
            $this->setWidget("cvi", new sfWidgetFormInput(array("label" => "CVI")));
            $this->setValidator('cvi', new sfValidatorRegex(array("required" => true, "pattern" => "/^[0-9]{10}$/"), array("invalid" => "Le cvi doit être un nombre à 10 chiffres")));
        }

        $this->setWidget("siret", new sfWidgetFormInput(array("label" => "N° SIRET / SIREN")));
        $this->setValidator('siret', new sfValidatorRegex(array("required" => false, "pattern" => "/^([0-9]{14})|([0-9]{9})$/"), array("invalid" => "Le SIRET doit être un nombre à 14 chiffres ou à 9 chiffres pour le SIREN")));


        $this->setWidget("syndicats", new sfWidgetFormChoice(array('multiple' => true, 'choices' => $this->getSyndicats())));
        $this->setValidator('syndicats', new sfValidatorChoice(array("required" => false, 'multiple' => true, 'choices' => array_keys($this->getSyndicats()))));


        $nbChais = $this->getNbChais();
        $formChais = new CompteChaisCollectionForm($this->getObject(), array(), array(
            'nbChais' => $nbChais));
        $this->embedForm('chais', $formChais);
        
       $this->validatorSchema->setPostValidator(new ValidatorCompteEtablissementModification());

    }

    public function initDefaultSyndicats() {
        $default_syndicats = array();
        foreach ($this->getObject()->getInfosSyndicats() as $syndicats_key => $syndicats_libelle) {
            $default_syndicats[] = $syndicats_key;
        }
        $this->widgetSchema['syndicats']->setDefault($default_syndicats);
    }

    private function getSyndicats() {
        $compteClient = CompteClient::getInstance();
        if (!$this->syndicats) {
            $this->syndicats = array();
            foreach ($compteClient->getAllSyndicats() as $syndicatId) {
                $syndicat = CompteClient::getInstance()->find($syndicatId);
                $this->syndicats[$syndicatId] = $syndicat->nom_a_afficher;
            }
        }
        return $this->syndicats;
    }

    public function getNbChais() {
        if (is_null($this->nbChais)) {
            if (is_null($this->getObject()->getChais())) {
                $this->nbChais = 0;
            } else {
                $this->nbChais = count($this->getObject()->getChais());
            }
        }
        return $this->nbChais;
    }

    public function save($con = null) {
        if ($syndicats = $this->values['syndicats']) {
            $this->getObject()->updateLocalSyndicats($syndicats);
        }
        parent::save($con);
    }

}
