<?php

class DeclarationClient
{
    protected static $self = null;

    public static function getInstance() {
        if(is_null(self::$self)) {

            self::$self = new DeclarationClient();
        }

        return self::$self;
    }

    public function find($id, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT, $force_return_ls = false) {

        return acCouchdbManager::getClient()->find($id, $hydrate, $force_return_ls);
    }

    public function getExportCsvClassName($type) {
        if($type == DRevClient::TYPE_MODEL) {

            return 'ExportDRevCSV';
        }

        if($type == ParcellaireClient::TYPE_MODEL) {

            return 'ExportParcellaireCSV';
        }

        if($type == TirageClient::TYPE_MODEL) {

            return 'ExportTirageCSV';
        }

        if($type == DRevMarcClient::TYPE_MODEL) {

            return 'ExportDRevMarcCSV';
        }

        if($type == ConstatsClient::TYPE_MODEL) {

            return 'ExportConstatsCSV';
        }

        if($type == FactureClient::TYPE_MODEL) {

            return 'ExportFactureCSV';
        }

        throw new sfException(sprintf("Le type de document %s n'a pas de classe d'export correspondante", $type));
    }

    public function getExportCsvObject($doc, $header = true) {
        $className = $this->getExportCsvClassName($doc->type);

        return new $className($doc, $header);
    }

    public function getTypesAndCampagneForExport() {
        $typeAndCampagne = array();

        $rows = acCouchdbManager::getClient()
                    ->reduce(true)
                    ->group_level(2)
                    ->getView('declaration', 'export')->rows;

        foreach($rows as $row) {
            $item = new stdClass();
            $item->type = $row->key[DeclarationExportView::KEY_TYPE];
            $item->campagne = $row->key[DeclarationExportView::KEY_CAMPAGNE];
            $typeAndCampagne[$item->campagne."_".$item->type] = $item;
        }

        return $typeAndCampagne;
    }

    public function getIds($type, $campagne, $validation = true) {
        $ids = array();

        $rows = acCouchdbManager::getClient()
                    ->startkey(array($type, $campagne))
                    ->endkey(array($type, $campagne, array()))
                    ->reduce(false)
                    ->getView('declaration', 'export')->rows;

        foreach($rows as $row) {
            $ids[] = $row->id;
        }

        return $ids;
    }

    public function getIdsByIdentifiant($identifiant) {
        $ids = array();

        $rows = acCouchdbManager::getClient()
                    ->reduce(false)
                    ->getView('declaration', 'export')->rows;

        foreach($rows as $row) {
            if(str_replace("E", "", $row->key[DeclarationExportView::KEY_IDENTIFIANT]) == $identifiant) {
                $ids[] = $row->id;
            }
        }

        return $ids;
    }

    public function viewByIdentifiantCampagneAndType($identifiant, $campagne, $type) {

        $rows = acCouchdbManager::getClient()
                        ->startkey(array($identifiant, $campagne, $type))
                        ->endkey(array($identifiant, $campagne, $type, array()))
                        ->reduce(false)
                        ->getView("declaration", "identifiant")
                ->rows;

        $drms = array();

        foreach ($rows as $row) {
            $drms[$row->id] = $row->key;
        }

        krsort($drms);

        return $drms;
    }
}
