<?php
class DegustationPrelevementRoute extends sfObjectRoute implements InterfaceDegustationGeneralRoute {

    protected $degustation = null;
    protected $prelevement = null;

    protected function getObjectForParameters($parameters) {

        $this->degustation = DegustationClient::getInstance()->find($parameters['id']);
        if (!$this->degustation) {

            throw new sfError404Exception(sprintf('No Degustation found with the id "%s".', $parameters['id']));
        }

        $hash_prelevement = str_replace('-', '/', $parameters['hash_prelevement']);


        if(!$this->degustation->exist($hash_prelevement)) {

            throw new sfError404Exception(sprintf('No Prelevement found with the id "%s" and hash "%s".', $parameters['id'], $hash_prelevement));
        }

        $this->prelevement = $this->degustation->get($hash_prelevement);

        return $this->prelevement;
    }

    protected function doConvertObjectToArray($object) {
        $parameters = array("id" => $object->getDocument()->_id, "hash_prelevement" => $object->getHashForKey());

        return $parameters;
    }

    public function getPrelevement() {
        if (!$this->prelevement) {
            $this->getObject();
        }
        return $this->prelevement;
    }

    public function getDegustation() {
        if (!$this->degustation) {
            $this->getObject();
        }
        return $this->degustation;
    }

}
