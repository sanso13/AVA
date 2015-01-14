<?php

class compteActions extends sfActions {

    public function executeCreationAdmin(sfWebRequest $request) {
        $this->type_compte = $request->getParameter('type_compte');
        if (!$this->type_compte) {
            throw sfException("La création de compte doit avoir un type");
        }
        $this->compte = new Compte($this->type_compte);
        $this->form = $this->getCompteModificationForm();

        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->form->save();
                $this->getUser()->setFlash('maj', 'Le compte a bien été mis à jour.');
                $this->redirect('compte_visualisation_admin', array('id' => $this->compte->identifiant));
            }
        }
    }

    public function executeVisualisationAdmin(sfWebRequest $request) {
        $this->compte = $this->getRoute()->getCompte();
    }
    
    public function executeRedirectEspaceEtablissement(sfWebRequest $request) {
        $this->compte = $this->getRoute()->getCompte();        
        if(!($etablissement = $this->compte->getEtablissementObj())){
            throw new sfException("L'établissement du compte n'a pas été trouvé");
        }
        $this->getUser()->signIn($etablissement->identifiant);

        return $this->redirect('home');
    }

    public function executeModificationAdmin(sfWebRequest $request) {
        $this->compte = $this->getRoute()->getCompte();

        $this->form = $this->getCompteModificationForm();
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->form->save();
                $this->getUser()->setFlash('maj', 'Le compte a bien été mis à jour.');
                $this->redirect('compte_visualisation_admin', array('id' => $this->compte->identifiant));
            }
        }
    }

    public function executeModificationEtablissementAdmin(sfWebRequest $request) {
        $this->etablissement = $this->getUser()->getEtablissement();
        $this->compte = $this->etablissement->getCompte();
        if (!$this->compte) {
            throw new sfException("L'etablissement " . $this->etablissement->identifiant . " n'a pas de compte");
        }
        $this->redirect('compte_modification_admin', array('id' => $this->compte->identifiant));
    }

    public function executeCreation(sfWebRequest $request) {
        
    }

    public function executeCreationConfirmation(sfWebRequest $request) {
        
    }

    public function executeMotDePasseOublie(sfWebRequest $request) {
        
    }

    public function executeModification(sfWebRequest $request) {
        $this->etablissement = $this->getUser()->getEtablissement();

        $this->form = new EtablissementModificationEmailForm($this->etablissement);

        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->etablissement = $this->form->save();
                $this->getUser()->setFlash('maj', 'Vos identifiants ont bien été mis à jour.');
                $this->redirect('@mon_compte');
            }
        }
    }

    public function executeRedirectToMonCompteCiva(sfWebRequest $request) {
        $url_compte_civa = sfConfig::get('app_url_compte_mot_de_passe');
        return $this->redirect($url_compte_civa);
    }

    public function executeAllTagsManuels() {

        $qm = new acElasticaQueryMatchAll();
        $q = new acElasticaQuery();
        $q->setQuery($qm);
        $elasticaFacet = new acElasticaFacetTerms('manuels');
        $elasticaFacet->setField('tags.manuels');
        $elasticaFacet->setSize(200);
        $elasticaFacet->setOrder('count');
        $q->addFacet($elasticaFacet);
        $index = acElasticaManager::getType('compte');
        $resset = $index->search($q);
        $this->facets = $resset->getFacets();

        $results = array();

        foreach ($this->facets['manuels']['terms'] as $terms) {
            $result = new stdClass();
            $result->id = $terms['term'];
            $result->text = $terms['term'];
            $results[] = $result;
        }
        return $this->renderText(json_encode($results));
    }

    public function executeRecherche(sfWebRequest $request) {
        $this->form = new CompteRechercheForm();
        $q = $this->initSearch($request);
        $res_by_page = 15;
        $page = $request->getParameter('page', 1);
        $from = $res_by_page * ($page - 1);
        $q->setLimit($res_by_page);
        $q->setFrom($from);
        $facets = array('automatiques' => 'tags.automatiques', 'attributs' => 'tags.attributs', 'manuels' => 'tags.manuels');
        $this->facets_libelle = array('automatiques' => 'Par type', 'attributs' => 'Par attributs', 'manuels' => 'Par mots clés');
        foreach ($facets as $nom => $f) {
            $elasticaFacet = new acElasticaFacetTerms($nom);
            $elasticaFacet->setField($f);
            $elasticaFacet->setSize(200);
            $elasticaFacet->setOrder('count');
            $q->addFacet($elasticaFacet);
        }

        $index = acElasticaManager::getType('compte');
        $resset = $index->search($q);
        $this->results = $resset->getResults();
        $this->nb_results = $resset->getTotalHits();
        $this->facets = $resset->getFacets();

        $this->last_page = ceil($this->nb_results / $res_by_page);
        $this->current_page = $page;
    }

    public function executeRechercheCsv(sfWebRequest $request) {
        ini_set('memory_limit', '128M');
        $this->setLayout(false);
        $q = $this->initSearch($request);
        $q->setLimit(10000);
        $index = acElasticaManager::getType('compte');
        $resset = $index->search($q);
        $this->results = $resset->getResults();

        $attachement = "attachment; filename=export_contacts.csv";
        $this->response->setContentType('text/csv');
        $this->response->setHttpHeader('Content-Disposition',$attachement );
    }
    
    public function executeRechercheJson($request) {

        $q = $this->initSearch($request);

        $q->setLimit(60);
        $index = acElasticaManager::getType('compte');
        $resset = $index->search($q);
        $results = $resset->getResults();

        $list = array();
        foreach ($results as $res) {
            $data = $res->getData();
            $item = new stdClass();
            $item->nom_a_afficher = $data['nom_a_afficher'];
            $item->commune = $data['commune'];
            $item->code_postal = $data['code_postal'];
            $item->cvi = $data['cvi'];
            $item->siret = $data['siret'];
            $item->text = sprintf("%s (%s) à %s (%s)", $data['nom_a_afficher'], $data['cvi'], $data['commune'], $data['code_postal']);
            $item->text_html = sprintf("%s <small>(%s)</small> à %s <small>(%s)</small><br /><small>%s</small>", $data['nom_a_afficher'], $data['cvi'], $data['commune'], $data['code_postal'], implode(", ", $data['tags']['attributs']));
            $item->id = $data['_id'];
            $list[] = $item;
        }

        $this->response->setContentType('application/json');

        return $this->renderText(json_encode($list));
    }

    private function initSearch(sfWebRequest $request) {
        $this->q = $query = $request->getParameter('q', '*');
        if (!$this->q) {
            $this->q = $query = '*';
        }
        $this->tags = $request->getParameter('tags', array());
        $this->all = $request->getParameter('all', 0);
        if (!$this->all) {
            $query .= " statut:ACTIF";
        }
        foreach ($this->tags as $tag) {
            $explodeTag = explode(':', $tag);
            $query .= ' tags.' . $explodeTag[0] . ':"' . html_entity_decode($explodeTag[1], ENT_QUOTES) . '"';
        }
        $this->type_compte = $request->getParameter('type_compte', null);
        if($this->type_compte) {
            $query .= " type_compte:".$this->type_compte;
        }

        $qs = new acElasticaQueryQueryString($query);
        $q = new acElasticaQuery();
        $q->setQuery($qs);
        $this->args = array('q' => $this->q, 'all' => $this->all, 'tags' => $this->tags);
        return $q;
    }

    private function getCompteModificationForm() {
        switch ($this->compte->getTypeCompte()) {
            case CompteClient::TYPE_COMPTE_CONTACT:
                return new CompteContactModificationForm($this->compte);
            case CompteClient::TYPE_COMPTE_ETABLISSEMENT:
                return new CompteEtablissementModificationForm($this->compte);
            case CompteClient::TYPE_COMPTE_DEGUSTATEUR:
                return new CompteDegustateurModificationForm($this->compte);
            case CompteClient::TYPE_COMPTE_AGENT_PRELEVEMENT:
                return new CompteAgentPrelevementModificationForm($this->compte);
            case CompteClient::TYPE_COMPTE_SYNDICAT:
                return new CompteSyndicatModificationForm($this->compte);
        }
    }

}
