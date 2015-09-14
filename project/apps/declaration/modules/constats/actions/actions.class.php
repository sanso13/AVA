<?php

class constatsActions extends sfActions {

    public function executeIndex(sfWebRequest $request) {
        $this->getUser()->signOutEtablissement();
        if(($tourneesRecapDate = $request->getParameter('tourneesRecapDate')) && $request->isMethod(sfWebRequest::POST)){
            $this->jour =Date::getIsoDateFromFrenchDate($tourneesRecapDate['date']);
            return $this->redirect('constats', array('jour' => $this->jour));
        }
        $this->jour = $request->getParameter('jour');

        $this->organisationJournee = RendezvousClient::getInstance()->buildOrganisationNbDays(2, $this->jour);
        $this->rendezvousNonPlanifies = RendezvousClient::getInstance()->getRendezvousByNonPlanifiesNbDays(2, $this->jour);
        $this->formDate = new TourneesRecapDateForm(array('date' => Date::francizeDate($this->jour)));
        $this->form = new LoginForm();

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }
        $this->getUser()->signInEtablissement($this->form->getValue('etablissement'));

        return $this->redirect('rendezvous_declarant', $this->getUser()->getEtablissement()->getCompte());
    }

    public function executePlanificationJour(sfWebRequest $request) {
        $this->jour = $request->getParameter('jour');
        $this->rendezvousJournee = RendezvousClient::getInstance()->buildRendezvousJournee($this->jour);
        $this->tourneesJournee = TourneeClient::getInstance()->buildTourneesJournee($this->jour);
    }

    public function executeTourneeAgentRendezvous(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();
        $this->agent = $this->tournee->getFirstAgent();
        $this->date = $this->tournee->getDate();
        $this->lock = (!$request->getParameter("unlock") && $this->tournee->statut != TourneeClient::STATUT_TOURNEES);
        $this->constructProduitsList();
        $this->contenants = ConstatsClient::getInstance()->getContenantsLibelle();
        $this->constats = array();

        $this->setLayout('layoutResponsive');
    }

    public function executeTourneeAgentJsonRendezvous(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();

        $json = array();
        $constats = array();

        foreach ($this->tournee->getRendezvous() as $idrendezvous => $rendezvous) {
            $constats[$rendezvous->constat] = ConstatsClient::getInstance()->find($rendezvous->constat);
        }
        foreach ($this->tournee->getRendezvous() as $idrendezvous => $rendezvous) {
            $json[$idrendezvous] = array();
            $json[$idrendezvous]['rendezvous'] = $rendezvous->toJson();
            $json[$idrendezvous]['constats'] = array();

            foreach ($constats[$rendezvous->constat]->constats as $constatkey => $constatNode) {
                $constatNodeJson = $constatNode->toJson();
                $isConstatVolume = ($rendezvous->type_rendezvous == RendezvousClient::RENDEZVOUS_TYPE_VOLUME);

                if ($isConstatVolume) {
                    if (substr($constatNode->date_volume, 0, 8) == str_replace('-', '', $this->tournee->getDate())) {
                        $constatNodeJson->type_constat = 'volume';
                        $json[$idrendezvous]['constats'][$rendezvous->constat . '_' . $constatkey] = $constatNodeJson;
                    }
                } else {
                    if (substr($constatkey, 0, 8) == str_replace('-', '', $this->tournee->getDate())) {
                        $constatNodeJson->type_constat = 'raisin';
                        $json[$idrendezvous]['constats'][$rendezvous->constat . '_' . $constatkey] = $constatNodeJson;
                    }
                }
            }
        }

        if (!$request->isMethod(sfWebRequest::POST)) {
            $this->response->setContentType('application/json');

            return $this->renderText(json_encode($json));
        }

        $json = json_decode($request->getContent());
        $json_return = array();

        foreach ($json as $json_content) {

            $splitted_id = split('_', $json_content->_idNode);
            $constat = ConstatsClient::getInstance()->find($splitted_id[0]);
            $constat->updateAndSaveConstatNodeFromJson($splitted_id[1], $json_content);
        }

        $this->response->setContentType('application/json');

        return $this->renderText(json_encode($json_return));
    }

    public function executeAjoutAgentTournee(sfWebRequest $request) {
        sfContext::getInstance()->getConfiguration()->loadHelpers(array('Date'));
        $this->jour = $request->getParameter('jour');
        $this->retour = $request->getParameter('retour', null);
        $this->form = new TourneeAddAgentForm(array('date' => format_date($this->jour, "dd/MM/yyyy", "fr_FR")));
        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));
        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }
        $compteAgent = CompteClient::getInstance()->find('COMPTE-' . $this->form->getValue('agent'));
        $tournee = TourneeClient::getInstance()->findOrAddByDateAndAgent($this->form->getValue('date'), $compteAgent);
        if ($this->retour && $this->retour == 'planification') {
            $this->redirect('constats_planifications', array('date' => $this->jour));
        }
        $this->redirect('constats_planification_jour', array('jour' => $this->jour));
    }

    public function executePlanifications(sfWebRequest $request) {
        $this->jour = $request->getParameter('date');
        $this->couleurs = array("#91204d", "#fa6900", "#1693a5", "#e05d6f", "#7ab317", "#ffba06", "#907860");
        $this->rdvsPris = RendezvousClient::getInstance()->getRendezvousByDateAndStatut($this->jour, RendezvousClient::RENDEZVOUS_STATUT_PRIS);
        $this->tournees = TourneeClient::getInstance()->getTourneesByDate($this->jour);

        $this->heures = array();
        for ($i = 7; $i <= 22; $i++) {
            $this->heures[sprintf("%02d:00", $i)] = sprintf("%02d", $i);
        }

        $this->tourneesCouleur = array();
        $i = 0;
        foreach ($this->tournees as $tournee) {
            $this->tourneesCouleur[$tournee->_id] = $this->couleurs[$i];
            $i++;
        }

        $this->rdvs = array();
        $this->rdvsSansHeure = array();
        foreach ($this->tournees as $tournee) {
            foreach ($tournee->rendezvous as $id => $rendezvous) {
                if ($rendezvous->type_rendezvous == RendezvousClient::RENDEZVOUS_TYPE_RAISIN) {
                    $this->rdvs[$rendezvous->getHeure()][$tournee->_id][$id] = $rendezvous;
                }
                if ($rendezvous->type_rendezvous == RendezvousClient::RENDEZVOUS_TYPE_VOLUME) {
                    $this->rdvs['no-hour'][$tournee->_id][$id] = $rendezvous;
                }
            }
        }

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $rdvValues = $request->getParameter("rdvs", array());

        foreach ($rdvValues as $id_rdv => $values) {
            if ($values['tournee']) {

                $tournee = $this->tournees[$values['tournee']];
                $tournee->addRendezVousAndGenerateConstat($id_rdv);
                $tournee->save();
            }
        }
        return $this->redirect('constats_planification_jour', array('jour' => $this->jour));
    }

    public function executeRendezvousDeclarant(sfWebRequest $request) {
        $this->compte = $this->getRoute()->getCompte();
        $this->rendezvousDeclarant = RendezvousClient::getInstance()->getRendezvousByCompte($this->compte->cvi);
        $this->formsRendezVous = array();
        $this->form = new LoginForm();
        foreach ($this->compte->getChais() as $chaiKey => $chai) {
            $rendezvous = new Rendezvous();
            $rendezvous->identifiant = $this->compte->identifiant;
            $rendezvous->idchai = $chaiKey;
            $this->formsRendezVous[$chaiKey] = new RendezvousDeclarantForm($rendezvous);
        }

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }
        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }
        $this->getUser()->signInEtablissement($this->form->getValue('etablissement'));

        return $this->redirect('rendezvous_declarant', $this->getUser()->getEtablissement()->getCompte());
    }

    public function executeRendezvousModification(sfWebRequest $request) {
        $this->rendezvous = $this->getRoute()->getRendezvous();
        $this->chai = $this->rendezvous->getChai();
        $this->retour = $request->getParameter('retour', null);
        $this->form = new RendezvousDeclarantForm($this->rendezvous);
        if (!$request->isMethod(sfWebRequest::POST)) {
            return sfView::SUCCESS;
        }
        $this->form->bind($request->getParameter($this->form->getName()));
        if (!$this->form->isValid()) {
            return $this->getTemplate('rendezvousDeclarant');
        }
        $this->form->save();
        if ($this->retour && $this->retour == 'planification') {
            $this->redirect('constats_planifications', array('date' => $this->rendezvous->getDate()));
        }
        $this->redirect('rendezvous_declarant', $this->rendezvous->getCompte());
    }

    public function executeRendezvousCreation(sfWebRequest $request) {
        $this->compte = $this->getRoute()->getCompte();
        $this->idchai = $request->getParameter('idchai');
        $rendezvous = new Rendezvous();
        $rendezvous->idchai = $this->idchai;
        $this->form = new RendezvousDeclarantForm($rendezvous);

        if (!$request->isMethod(sfWebRequest::POST)) {
            return sfView::SUCCESS;
        }
        $this->form->bind($request->getParameter($this->form->getName()));
        if (!$this->form->isValid()) {
            return $this->getTemplate('rendezvousDeclarant');
        }
        $date = $this->form->getValue('date');
        $heure = $this->form->getValue('heure');
        $commentaire = $this->form->getValue('commentaire');
        $rendezvous = RendezvousClient::getInstance()->findOrCreate($this->compte, $this->idchai, $date, $heure, $commentaire);
        $rendezvous->save();
        $this->redirect('rendezvous_declarant', $this->compte);
    }

    public function executeConstatPdf(sfWebRequest $request) {
        $this->constats = $this->getRoute()->getConstats();
        $this->constatNode = $request->getParameter('identifiantconstat');


        $this->document = new ExportConstatPdf($this->constats, $this->constatNode, $this->getRequestParameter('output', 'pdf'), false);
        $this->document->setPartialFunction(array($this, 'getPartial'));

        if ($request->getParameter('force')) {
            $this->document->removeCache();
        }

        $this->document->generate();

        $this->document->addHeaders($this->getResponse());

        return $this->renderText($this->document->output());
    }

    private function constructProduitsList() {
        $this->produits = array();
        foreach (ConstatsClient::getInstance()->getProduits() as $produit) {
            $this->produits[$produit->getHash()] = $produit->libelle_complet;
        }
    }

}
