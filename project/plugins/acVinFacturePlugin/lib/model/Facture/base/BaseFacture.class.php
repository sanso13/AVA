<?php
/**
 * BaseFacture
 * 
 * Base model for Facture
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $identifiant
 * @property string $code_comptable_client
 * @property string $numero_facture
 * @property string $numero_ava
 * @property string $numero_adherent
 * @property string $date_emission
 * @property string $date_facturation
 * @property string $date_paiement
 * @property string $date_echeance
 * @property string $reglement_paiement
 * @property string $montant_paiement
 * @property string $campagne
 * @property string $numero_archive
 * @property string $statut
 * @property string $avoir
 * @property string $taux_tva
 * @property string $region
 * @property integer $versement_comptable
 * @property integer $versement_comptable_paiement
 * @property acCouchdbJson $arguments
 * @property string $message_communication
 * @property acCouchdbJson $emetteur
 * @property acCouchdbJson $declarant
 * @property float $total_ht
 * @property float $total_ttc
 * @property float $total_taxe
 * @property FactureLignes $lignes
 * @property acCouchdbJson $echeances
 * @property acCouchdbJson $origines
 * @property acCouchdbJson $templates
 * @property acCouchdbJson $pieces

 * @method string getId()
 * @method string setId()
 * @method string getRev()
 * @method string setRev()
 * @method string getType()
 * @method string setType()
 * @method string getIdentifiant()
 * @method string setIdentifiant()
 * @method string getCodeComptableClient()
 * @method string setCodeComptableClient()
 * @method string getNumeroFacture()
 * @method string setNumeroFacture()
 * @method string getNumeroAva()
 * @method string setNumeroAva()
 * @method string getNumeroAdherent()
 * @method string setNumeroAdherent()
 * @method string getDateEmission()
 * @method string setDateEmission()
 * @method string getDateFacturation()
 * @method string setDateFacturation()
 * @method string getDatePaiement()
 * @method string setDatePaiement()
 * @method string getDateEcheance()
 * @method string setDateEcheance()
 * @method string getReglementPaiement()
 * @method string setReglementPaiement()
 * @method string getMontantPaiement()
 * @method string setMontantPaiement()
 * @method string getCampagne()
 * @method string setCampagne()
 * @method string getNumeroArchive()
 * @method string setNumeroArchive()
 * @method string getStatut()
 * @method string setStatut()
 * @method string getAvoir()
 * @method string setAvoir()
 * @method string getTauxTva()
 * @method string setTauxTva()
 * @method string getRegion()
 * @method string setRegion()
 * @method integer getVersementComptable()
 * @method integer setVersementComptable()
 * @method integer getVersementComptablePaiement()
 * @method integer setVersementComptablePaiement()
 * @method acCouchdbJson getArguments()
 * @method acCouchdbJson setArguments()
 * @method string getMessageCommunication()
 * @method string setMessageCommunication()
 * @method acCouchdbJson getEmetteur()
 * @method acCouchdbJson setEmetteur()
 * @method acCouchdbJson getDeclarant()
 * @method acCouchdbJson setDeclarant()
 * @method float getTotalHt()
 * @method float setTotalHt()
 * @method float getTotalTtc()
 * @method float setTotalTtc()
 * @method float getTotalTaxe()
 * @method float setTotalTaxe()
 * @method FactureLignes getLignes()
 * @method FactureLignes setLignes()
 * @method acCouchdbJson getEcheances()
 * @method acCouchdbJson setEcheances()
 * @method acCouchdbJson getOrigines()
 * @method acCouchdbJson setOrigines()
 * @method acCouchdbJson getTemplates()
 * @method acCouchdbJson setTemplates()
 * @method acCouchdbJson getPieces()
 * @method acCouchdbJson setPieces()
 
 */
 
abstract class BaseFacture extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'Facture';
    }
    
}