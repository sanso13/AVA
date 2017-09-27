<?php

class etablissementActions extends sfCredentialActions {

    public function executeAjout(sfWebRequest $request) {
        $this->societe = $this->getRoute()->getSociete();
        $this->applyRights();
        if (!$this->modification) {
            $this->forward('acVinCompte', 'forbidden');
        }
        $this->famille = $request->getParameter('famille');
        $this->etablissement = $this->societe->createEtablissement($this->famille);
        $this->processFormEtablissement($request);
        $this->setTemplate('modification');
    }

    public function executeModification(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->societe = $this->etablissement->getSociete();
        $this->applyRights();
        if (!$this->modification) {
            $this->forward('acVinCompte', 'forbidden');
        }
        $this->processFormEtablissement($request);
    }

    protected function processFormEtablissement(sfWebRequest $request) {
        $this->etablissementModificationForm = new EtablissementModificationForm($this->etablissement);
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->etablissementModificationForm->bind($request->getParameter($this->etablissementModificationForm->getName()));
            if ($this->etablissementModificationForm->isValid()) {
                $this->etablissementModificationForm->save();
                $this->redirect('etablissement_visualisation', array('identifiant' => $this->etablissement->identifiant));
            }
        }
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->societe = $this->etablissement->getSociete();
        $this->contact = $this->etablissement->getContact();
        $this->contact->updateCoordonneesLongLat();
        $this->interlocuteurs = array();

        foreach(SocieteClient::getInstance()->getInterlocuteursWithOrdre($this->societe->identifiant, true) as $interlocuteur) {
            if(!$interlocuteur) {
                continue;
            }
            if ($interlocuteur->isSocieteContact() || $interlocuteur->isEtablissementContact()) {
                continue;
            }
            $this->interlocuteurs[$interlocuteur->_id] = $interlocuteur;
        }

        $this->applyRights();

        //$this->redirect('etablissement_visualisation', array('sf_subject' => $this->etablissement));
    }

     public function executeSwitchStatus(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $newStatus = "";
        if($this->etablissement->isActif()){
           $newStatus = SocieteClient::STATUT_SUSPENDU;
        }
        if($this->etablissement->isSuspendu()){
           $newStatus = SocieteClient::STATUT_ACTIF;
        }
        $compte = $this->etablissement->getMasterCompte();
        if($compte && !$this->etablissement->isSameCompteThanSociete()){
            $compte->setStatut($newStatus);
            $compte->save();
        }
        $this->etablissement->setStatut($newStatus);
        $this->etablissement->save();
        return $this->redirect('etablissement_visualisation', array('identifiant' => $this->etablissement->identifiant));
    }

}