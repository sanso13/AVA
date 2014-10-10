<?php

class DRevRevendicationProduitForm extends acCouchdbObjectForm {

    public function configure() {
        $this->setWidgets(array(
            'superficie_revendique' => new sfWidgetFormInputFloat(),
            'volume_revendique' => new sfWidgetFormInputFloat()
        ));
        $this->widgetSchema->setLabels(array(
            'superficie_revendique' => 'Superficie totale (ares):',
            'volume_revendique' => 'Volume revendiqué (hl):',
        ));
        $this->setValidators(array(
            'superficie_revendique' => new sfValidatorNumber(array('required' => false)),
            'volume_revendique' => new sfValidatorNumber(array('required' => false))
        ));

        if ($this->getObject()->getDocument()->isNonRecoltant()) {
            unset($this->widgetSchema['superficie_revendique']);
            unset($this->validatorSchema['superficie_revendique']);
        } else {

            if ($this->getObject()->detail->superficie_total) {
                unset($this->widgetSchema['superficie_revendique']);
                unset($this->validatorSchema['superficie_revendique']);
            }
        }
        
        $this->widgetSchema->setNameFormat('[%s]');
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }

}
