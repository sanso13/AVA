<?php

class FacturationDeclarantForm extends BaseForm {

    public function configure() {
    	$choices = $this->getModeles();

        $this->setWidgets(array(
                'modele'   => new sfWidgetFormChoice(array('choices' => $choices)),
                'date_facturation'   => new sfWidgetFormInput(array('default' => date('d/m/Y'))),
        ));

        $this->widgetSchema->setLabels(array(
                'modele'  => 'Template de facture',
                'date_facturation'  => 'Date de facturation',
        ));

        $this->setValidators(array(
                'modele' => new sfValidatorChoice(array('choices' => array_keys($choices), 'multiple' => false, 'required' => true)),
                'date_facturation' => new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)),
        ));

        $this->widgetSchema->setNameFormat('facturation_declarant[%s]');
    }

	public function getModeles() {

        return FacturationMassiveForm::getModelesByObject($this->getOption("modeles"));
    }

}
