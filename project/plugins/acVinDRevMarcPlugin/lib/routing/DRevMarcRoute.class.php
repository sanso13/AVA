<?php
class DRevMarcRoute extends sfObjectRoute {

    protected $drevMarc = null;

    protected function getObjectForParameters($parameters) {

        $this->drevMarc = DRevMarcClient::getInstance()->find($parameters['id']);
        if (!$this->drevMarc) {

            throw new sfError404Exception(sprintf('No DRev found with the id "%s".', $parameters['id']));
        }
        return $this->drevMarc;
    }

    protected function doConvertObjectToArray($object) {  
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getDRevMarc() {
        if (!$this->drevMarc) {
            $this->getObject();
        }
        return $this->drevMarc;
    }

}