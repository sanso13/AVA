<?php 

class DRevPrelevementsView extends acCouchdbView
{
    const KEY_APPELLATION = 0;
    const KEY_DATE = 1;
    const KEY_IDENTIFIANT = 2;
    const KEY_RAISON_SOCIALE = 3;
    const KEY_COMMUNE = 4;
    const KEY_PRODUIT_HASH = 5;
    const KEY_PRODUIT_LIBELLE = 6;

    public static function getInstance() {

        return acCouchdbManager::getView('drev', 'prelevements', 'DRev');
    }

    public function getPrelevements($date_from, $date_to) {
        
        return $this->viewToJson($this->client->startkey(array("cuve_ALSACE", $date_from))
                            ->endkey(array("cuve_ALSACE", $date_to, array()))
                            ->reduce(false)
                            ->getView($this->design, $this->view)->rows);;
    }

    public function viewToJson($rows) {
        $items = array();

        foreach($rows as $row) {
            $key = $row->key[self::KEY_APPELLATION].$row->key[self::KEY_DATE].$row->key[self::KEY_IDENTIFIANT];

            if(array_key_exists($key, $items)) {
                $item = $items[$key];
            } else {
                $item = new stdClass();
                $item->lots = array();
            }

            $item->date = $row->key[self::KEY_DATE];
            $item->appellation = $row->key[self::KEY_APPELLATION];
            $item->identifiant = $row->key[self::KEY_IDENTIFIANT];
            $item->raison_sociale = $row->key[self::KEY_RAISON_SOCIALE];
            $item->commune = $row->key[self::KEY_COMMUNE];
            $key_hash = str_replace("/", "-", $row->key[self::KEY_PRODUIT_HASH]);
            $item->lots[$key_hash] = new stdClass();
            $item->lots[$key_hash]->hash_produit = $row->key[self::KEY_PRODUIT_HASH];
            $item->lots[$key_hash]->libelle = $row->key[self::KEY_PRODUIT_LIBELLE];
            $item->lots[$key_hash]->nb = $row->value;

            $items[$key] = $item;
        }

        return $items;
    }

}