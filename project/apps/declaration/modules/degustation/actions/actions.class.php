<?php

class degustationActions extends sfActions {

    public function executeIndex(sfWebRequest $request) {
        $this->degustation = new Degustation();
        $this->form = new DegustationCreationForm($this->degustation);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect('degustation_creation', $this->degustation);
    }

    public function executeCreation(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();

        $this->prelevements = DegustationClient::getInstance()->getPrelevements($this->degustation->date_prelevement_debut, $this->degustation->date_prelevement_fin);

        $this->form = new DegustationCreationFinForm($this->degustation);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect('degustation_operateurs', $this->degustation);
    }

    public function executeOperateurs(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();

        $this->prelevements = DegustationClient::getInstance()->getPrelevements($this->degustation->date_prelevement_debut, $this->degustation->date_prelevement_fin);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $values = $request->getParameter("operateurs", array());

        foreach($values as $key => $value) {
            $p = $this->prelevements[$key];
            $prelevement = $this->degustation->prelevements->add($p->identifiant);
            $prelevement->raison_sociale = $p->raison_sociale;
            $prelevement->commune = $p->commune;
            $prelevement->remove("lots");
            $prelevement->add("lots");
            $lot = $prelevement->lots->add($value);
            $lot->hash_produit = $p->lots[$value]->hash_produit;
            $lot->libelle = $p->lots[$value]->libelle;
            $lot->nb = $p->lots[$value]->nb;
        }
        $this->degustation->save();

        return $this->redirect('degustation_degustateurs', $this->degustation);
    }

    public function executeDegustateurs(sfWebRequest $request) {

        return $this->redirect('degustation_degustateurs_type', array('sf_subject' => $this->getRoute()->getDegustation(), 'type' => CompteClient::ATTRIBUT_DEGUSTATEUR_PORTEUR_MEMOIRES));
    }

    public function executeDegustateursType(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();

        $this->types = CompteClient::getInstance()->getAttributsForType(CompteClient::TYPE_COMPTE_DEGUSTATEUR);

        $this->type = $request->getParameter('type', null);

        if(!array_key_exists($this->type, $this->types)) {

            return $this->forward404(sprintf("Le type de dégustateur \"%s\" est introuvable", $request->getParameter('type', null)));
        }

        $this->degustateurs = DegustationClient::getInstance()->getDegustateurs($this->type);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $values = $request->getParameter("degustateurs", array());

        foreach($values as $key => $value) {
            $d = $this->degustateurs[$key];
            $degustation = $this->degustation->degustateurs->add($d->_id);
            $degustation->nom = $d->nom_a_afficher;
        }

        $this->degustation->save(); 

        return $this->redirect('degustation_degustateurs_type_suivant', array('sf_subject' => $this->degustation, 'type' => $this->type));
    }

    public function executeDegustateursTypePrecedent(sfWebRequest $request) {
        $prev_key = null;
        foreach(CompteClient::getInstance()->getAttributsForType(CompteClient::TYPE_COMPTE_DEGUSTATEUR) as $type_key => $type_libelle) {
            if($type_key == $request->getParameter('type', null)) { $prev_key = $type_key; continue; } 

            return $this->redirect('degustation_degustateurs_type', array('sf_subject' => $this->getRoute()->getDegustation(), 'type' => $prev_key));
        }

        return $this->redirect('degustation_operateurs', $this->getRoute()->getDegustation());
    }

    public function executeDegustateursTypeSuivant(sfWebRequest $request) {
        foreach(CompteClient::getInstance()->getAttributsForType(CompteClient::TYPE_COMPTE_DEGUSTATEUR) as $type_key => $type_libelle) {
            if($type_key != $request->getParameter('type', null)) { continue; }
            if(key($this->types)) {
                
                return $this->redirect('degustation_degustateurs_type', array('sf_subject' => $this->getRoute()->getDegustation(), 'type' => key($this->types)));
            }
        }

        return $this->redirect('degustation_agents', $this->getRoute()->getDegustation());
    }

    public function executeAgents(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();

        $this->agents = DegustationClient::getInstance()->getAgents();

        $this->jours = array();
        $date = new DateTime($this->degustation->date);
        $date->modify('-7 days');

        for($i=1; $i <= 7; $i++) {
            $this->jours[] = $date->format('Y-m-d');
            $date->modify('+ 1 day');
        }

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $values = $request->getParameter("agents", array());

        foreach($values as $key => $value) {
            $agent = $this->degustation->agents->add($key);
            $a = $this->agents[$key];
            $agent->nom = sprintf("%s %s.", $a->prenom, substr($a->nom, 0, 1));
            $agent->dates = $value;
        }

        $this->degustation->save();

        return $this->redirect('degustation_prelevements', $this->degustation);
    }

    public function executePrelevements(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->couleurs = array("#91204d", "#fa6900",  "#1693a5", "#e05d6f", "#7ab317",  "#ffba06", "#907860");
        $this->heures = array();
        for($i = 8; $i <= 18; $i++) {
            $this->heures[sprintf("%02d:00", $i)] = sprintf("%02d", $i);
        }
        $this->heures["24:00"] = "24";
        $this->prelevements = $this->degustation->getPrelevementsOrderByHour();
    }

    public function executeValidation(sfWebRequest $request) {
 
    }

    public function executeTournee(sfWebRequest $request) {

    }

    public function executeAffectation(sfWebRequest $request) {

    }

    public function executeDegustation(sfWebRequest $request) {
        
    }
}