<?php

class DRevDeclaration extends BaseDRevDeclaration 
{

	public function getChildrenNode() 
    {
        return $this->getCertifications();
    }

    public function getCertifications() 
    {
        return $this->filter('^certification');
    }
    
    public function getAppellations() 
    {
        if(!$this->exist('certification')) {
        	return array();	
        }
        return $this->getChildrenNodeDeep(2)->getAppellations();
    }

}