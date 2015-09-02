<?php
/**
 * Model for Rendezvous
 *
 */

class Rendezvous extends BaseRendezvous {

    
    public function constructId() {
        $this->set('_id', sprintf("%s-%s-%s", RendezvousClient::TYPE_COUCHDB, $this->cvi, str_replace("-", "", $this->date).str_replace(":", "", $this->heure)));
    }
    
    public function getDateHeure(){
        return str_replace('-','',$this->getDate()).str_replace(':','',$this->getHeure());
    }
    
    public function getChai() {
        return CompteClient::getInstance()->findByIdentifiant($this->identifiant)->getChais()->get($this->idchai);
    }
}