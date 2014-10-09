<?php
/**
 * Model for Etablissement
 *
 */

class Etablissement extends BaseEtablissement {

    public function constructId() 
    {
        $this->_id = sprintf("%s-%s", EtablissementClient::TYPE_COUCHDB, $this->identifiant);
    }

    public function getChaiDefault() {
        if(count($this->chais) < 1) {

            return null; 
        }

        return $this->chais->getFirst();
    }

    public function hasFamille($famille) {

        return $this->familles->exist($famille);
    }

}