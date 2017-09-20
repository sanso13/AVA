<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

sfContext::createInstance($configuration);

$t = new lime_test(53);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des DRev précédentes
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
  $drev = DRevClient::getInstance()->find($k);
  $drev->delete(false);
}

$campagne = (date('Y')-1)."";


$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $campagne);
$drev->save();

$t->comment("Récupération des données à partir de la DR");

$csv = new DRDouaneCsvFile(dirname(__FILE__).'/../data/dr_douane.csv');
$csvContent = $csv->convert();
file_put_contents("/tmp/dr.csv", $csvContent);
$csv = new DRCsvFile("/tmp/dr.csv");

$drev->importCSVDouane($csv->getCsvAcheteur("7523700100"));
$drev->save();

$t->is(count($drev->getProduits()), 5, "La DRev a repris 5 produits du csv de la DR");

$i = 0;
$produits2Delete = array();
foreach($drev->getProduits() as $produit) {
    $i++;
    if($i > 2) {
        $produits2Delete[$produit->getHash()] = $produit->getHash();
    }
}

foreach($produits2Delete as $hash) {
    $drev->remove($hash);
}

$produits = $drev->getProduits();

$produit1 = current($produits);
$produit_hash1 = $produit1->getHash();
$produit1->vci_stock_initial = 3;
next($produits);
$produit2 = current($produits);
$produit_hash2 = $produit2->getHash();

$drev->save();

$t->is($produit1->getLibelleComplet(), "Saint Joseph Rouge", "Le libelle du produit est Saint Joseph");
$t->is($produit1->detail->superficie_total, 247.86, "La superficie total de la DR pour le produit est de 333.87");
$t->is($produit1->detail->volume_sur_place, 105.18, "Le volume sur place pour ce produit est de 108.94");
$t->is($produit1->detail->usages_industriels_total, 3.03, "Les usages industriels la DR pour ce produit sont de 4.32");
$t->is($produit1->detail->recolte_nette, 104.1, "La récolte nette de la DR pour ce produit est de 104.1");
$t->is($produit1->detail->volume_total, 105.18, "Le volume total de la DR pour ce produit est de 169.25");
$t->is($produit1->detail->vci, 2, "Le vci de la DR pour ce produit est de 2");
$t->is($produit1->vci, 2, "Le vci de l'année de la DR pour ce produit est de 2");
$t->is($produit2->getLibelleComplet(), "Collines Rhodaniennes Blanc", "Le libelle du produit est Collines Rhodaniennes Blanc");

$t->comment("Formulaire de revendication");

$form = new DRevRevendicationForm($drev);

$defaults = $form->getDefaults();

$t->is(count($form['produits']), count($drev->getProduits()), "La form à le même nombre de produit que dans la drev");
$t->is($form['produits'][$produit_hash1]['detail']['superficie_total']->getValue(), $produit1->detail->superficie_total, "La superficie totale de la DR est initialisé dans le form");
$t->is($form['produits'][$produit_hash1]['detail']['volume_total']->getValue(), $produit1->detail->volume_total, "La volume totale de la DR est initialisé dans le form");
$t->is($form['produits'][$produit_hash1]['detail']['recolte_nette']->getValue(), $produit1->detail->recolte_nette, "La récolté nette de la DR sont initialisé dans le form");
$t->is($form['produits'][$produit_hash1]['detail']['volume_sur_place']->getValue(), $produit1->detail->volume_sur_place, "Le volume sur place est initialisé dans le form");
$t->is($form['produits'][$produit_hash1]['superficie_revendique']->getValue(), $produit1->superficie_revendique, "La superficie revendique est initialisé dans le form");
$t->is($form['produits'][$produit_hash1]['volume_revendique_sans_vci']->getValue(), $produit1->volume_revendique_sans_vci, "Le volume revendique avec vci est initialisé dans le form");
$t->is($form['produits'][$produit_hash1]['vci_complement_dr']->getValue(), $produit1->vci_complement_dr, "Le volume de vci  en complément de récolte est initialisé dans le form");

$valuesRev = array(
    'produits' => $form['produits']->getValue(),
    '_revision' => $drev->_rev,
);

$valuesRev['produits'][$produit_hash1]['superficie_revendique'] = 10;
$valuesRev['produits'][$produit_hash1]['volume_revendique_sans_vci'] = 100;
$valuesRev['produits'][$produit_hash1]['vci_complement_dr'] = 2;
$valuesRev['produits'][$produit_hash1]['detail']['superficie_total'] = 10;
$valuesRev['produits'][$produit_hash2]['detail']['superficie_total'] = 300;

$form->bind($valuesRev);

$t->ok($form->isValid(), "Le formulaire est valide");
$form->save();

$t->is($produit1->detail->superficie_total, $valuesRev['produits'][$produit_hash1]['detail']['superficie_total'], "La superficie total de la DR est enregistré");
$t->is($produit1->detail->volume_total, $valuesRev['produits'][$produit_hash1]['detail']['volume_total'], "Le volume total de la DR est enregistré");
$t->is($produit1->detail->recolte_nette, $valuesRev['produits'][$produit_hash1]['detail']['recolte_nette'], "La récolte nette de la DR a été enregistrée");
$t->is($produit1->superficie_revendique, $valuesRev['produits'][$produit_hash1]['superficie_revendique'], "La superficie revendique est enregistré");
$t->is($produit1->volume_revendique_sans_vci, $valuesRev['produits'][$produit_hash1]['volume_revendique_sans_vci'], "Le volume revendiqué sans VCI est enregistré");
$t->is($produit1->vci_complement_dr, $valuesRev['produits'][$produit_hash1]['vci_complement_dr'], "Le vci complement DR est enregistré");

$t->is($produit1->volume_revendique_avec_vci, $produit1->volume_revendique_sans_vci + $produit1->vci_complement_dr, "Le volume revendique avec vci est bien calcule à partir du complément DR");

$t->comment("Formulaire du VCI");

$form = new DRevVciForm($drev);

$defaults = $form->getDefaults();

$t->is(count($form['produits']), 1, "La form a 1 seul produit");
$t->is($form['produits'][$produit_hash1]['vci_stock_initial']->getValue(), 3, "Le stock VCI avant récolte du formulaire est de 3");
$t->is($form['produits'][$produit_hash1]['vci']->getValue(), 2, "Le VCI du formulaire est de 0");
$t->is($form['produits'][$produit_hash1]['vci_destruction']->getValue(), null, "Le VCI desctruction est nul");
$t->is($form['produits'][$produit_hash1]['vci_substitution']->getValue(), null, "Le VCI en substitution est nul");
$t->is($form['produits'][$produit_hash1]['vci_rafraichi']->getValue(), null, "Le VCI rafraichi est nul");

$valuesVCI = array(
    'produits' => array(
        $produit_hash1 => array("vci_stock_initial" => 3, "vci" => 12, "vci_destruction" => 0, "vci_substitution" => 0, "vci_rafraichi" => null),
    ),
    '_revision' => $drev->_rev,
);

$form->bind($valuesVCI);

$t->ok($form->isValid(), "Le formulaire est valide");

$form->save();

$produit1 = $drev->get($produit_hash1);
$t->is($produit1->vci_stock_initial, 3, "Le stock VCI avant récolte du produit du doc est de 3");
$t->is($produit1->vci, 12, "Le VCI du produit du doc est de 12");
$t->is($produit1->vci_destruction, null, "Le VCI en destruction du produit du doc est null");
$t->is($produit1->vci_complement_dr, $valuesRev['produits'][$produit_hash1]['vci_complement_dr'], "Le VCI en complément de la DR du produit du doc est de 2");
$t->is($produit1->vci_substitution, 0, "Le VCI en substitution de la DR du produit du doc est de 0");
$t->is($produit1->vci_rafraichi, null, "Le VCI rafraichi du produit du doc est nul");
$t->is($produit1->vci_stock_final, 12, "Le VCI stock après récolte du produit du doc est 12");


$vci_stock_final_calc = $produit1->vci + $produit1->vci_rafraichi;

$t->is($produit1->vci_stock_final, $vci_stock_final_calc, "Le VCI stock après récolte du produit du doc est le même que le calculé");

$t->comment("Validation");
$drev->cleanDoc();
$validation = new DRevValidation($drev);

$erreurs = $validation->getPointsByCodes('erreur');

$t->ok(isset($erreurs['revendication_incomplete']) && count($erreurs['revendication_incomplete']) == 1 && $erreurs['revendication_incomplete'][0]->getInfo() == $produit2->getLibelleComplet(), "Un point bloquant est levé car les infos de revendications n'ont pas été saisi");

$t->ok(isset($erreurs['dr_rendement']) && count($erreurs['dr_rendement']) == 1 && $erreurs['dr_rendement'][0]->getInfo() == 'Saint Joseph Rouge' , "Un point bloquant est levé car le rendement sur la DR n'est pas respecté");

$t->ok(isset($erreurs['revendication_rendement']) && count($erreurs['revendication_rendement']) == 1 && $erreurs['revendication_rendement'][0]->getInfo() == $produit1->getLibelleComplet() , "Un point bloquant est levé car le rendement sur le revendiqué n'est pas respecté");

$t->ok(isset($erreurs['vci_stock_utilise']) && count($erreurs['vci_stock_utilise']) == 1 && $erreurs['vci_stock_utilise'][0]->getInfo() == $produit1->getLibelleComplet() , "Un point bloquant est levé car le vci utilisé n'a pas été correctement réparti");

$t->ok(isset($erreurs['vci_rendement_annee']) && count($erreurs['vci_rendement_annee']) == 1 && $erreurs['vci_rendement_annee'][0]->getInfo() == $produit1->getLibelleComplet() , "Un point bloquant est levé car le vci déclaré de l'année ne respecte pas le rendement de l'annee");

$t->ok(isset($erreurs['vci_rendement_total']) && count($erreurs['vci_rendement_total']) == 1 && $erreurs['vci_rendement_total'][0]->getInfo() == $produit1->getLibelleComplet() , "Un point bloquant est levé car le stock vci final déclaré ne respecte pas le rendement total");


$produit1->superficie_revendique = 500;
$produit1->detail->superficie_total = 500;
$produit1->vci_rafraichi = 1;

$produit2->superficie_revendique = 100;
$produit2->volume_revendique_sans_vci = 20;
$produit2->volume_revendique_avec_vci = 20;

$validation = new DRevValidation($drev);
$validation->controle();
$erreurs = $validation->getPointsByCodes('erreur');

$t->ok(!isset($erreurs['revendication_incomplete']), "Après correction dans la drev plus de point blocant sur le remplissage des données de revendication");
$t->ok(!isset($erreurs['revendication_rendement']), "Après correction dans la drev plus de point blocant sur le rendement de la revendication");
$t->ok(!isset($erreurs['dr_rendement']), "Après correction dans la drev plus de point blocant sur le rendement DR");
$t->ok(!isset($erreurs['vci_stock_utilise']), "Après correction dans la drev plus de point blocant sur la repartition du vci");
$t->ok(!isset($erreurs['vci_rendement_annee']), "Après correction dans la drev plus de point blocant sur le rendement à l'année du vci");
$t->ok(!isset($erreurs['vci_rendement_total']), "Après correction dans la drev plus de point blocant sur le rendement total du vci");
