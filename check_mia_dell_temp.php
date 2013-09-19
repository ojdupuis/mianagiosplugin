#!/usr/bin/php -q
<?php
   require_once('lib/MiaNagiosPluginSNMPIndexed.inc.php');
   
   class MiaNagiosPlugin_CheckDellServTemp extends MiaNagiosPluginSNMPIndexed{  
   	      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
   	
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','DELL_SERV_TEMP');
         $this->setSpecialProperty('titre_aide','Plugins Nagios indiquant la temperature du serveur');
         $this->setSpecialProperty('commentaire_aide','');
      }
      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */

      protected function setIndicators(){         
         $this->addIndicatorSnmpIndexed('dell_serv_temp', 
	         '.1.3.6.1.4.1.674.10892.1.700.20.1.2.1',
	         '.1.3.6.1.4.1.674.10892.1.700.20.1.6.1',
	         '.1.3.6.1.4.1.674.10892.1.700.20.1.8.1');
      }
      
      
      final protected function _dataValueIndicator($name){
      	foreach (array_values($this->getPreliminary($name)) as $key => $value){      		
            if ($value == null){
         	  $retour[]=-10;
            } else {
         	  $retour[]=$value;
            }
      	}
         return $retour;
      } 
      
      protected function dataFilter ($name, $value){
      	return MiaNagiosPluginFilters::dataFilterPrecision($name,$value/10,0);
      }
      
      
      protected function StatusInformationFilter($name,$value){ 
         return "$name=".$value; 
      }
   }
   
   $check=new MiaNagiosPlugin_CheckDellServTemp();
   $check->OutputResult();
