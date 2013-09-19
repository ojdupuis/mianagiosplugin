#!/usr/bin/php -q
<?php
   require_once('lib/MiaNagiosPluginIndexed.inc.php');

   
   class MiaNagiosPlugin_CheckPingPingloss extends MiaNagiosPluginIndexed{      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','ping_pingloss');
         $this->setSpecialProperty('titre_aide','ping divers');
         $this->setSpecialProperty('commentaire_aide','pour la commande -H le séparateur est ,.');
      }
      
         protected function setInputs(){
         parent::setInputs();  
         $this-> addInput('hostnames',"/(\-H)\s([^\s]+)\s*/",false);
            
      }
      
         
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */
      protected function setIndicators(){ 
           $this->addIndicatorIndexed('ping_pingloss');

      }
      	
      protected function _preliminarySetUp(){
      	trigger_error("start",E_USER_NOTICE);
      	$hostlist=split(",",$this->getInput('hostnames'));
      	$nbping=5;
      	foreach ($hostlist as $case){
	         exec('ping -c '.$nbping.' -i 0.2 -t 100 '.$case.' | grep loss',$output);
	         foreach ($output as $line){
	         	preg_match("/.*([0-9]+)%/",$line,$match);  
	         	$tab['ping_pingloss'][$case]=$match[1];
	         }
      	}
         trigger_error("end",E_USER_NOTICE);  
         return $tab;
      }   

   }
       
   
 $check=new MiaNagiosPlugin_CheckPingPingloss();   
 $check->OutputResult();
