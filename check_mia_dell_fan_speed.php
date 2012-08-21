#!/usr/local/bin/php5 -q
<?php
   require_once('lib/MiaNagiosPluginSNMPIndexed.inc.php');
   
   class MiaNagiosPlugin_CheckDellFanSpeed extends MiaNagiosPluginSNMPIndexed{  
   	      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
   	
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','DELL_FAN_SPEED');
         $this->setSpecialProperty('titre_aide','Plugins Nagios indiquant la vitesse de rotation des ventilateurs du serveur en tour par minutes');
         $this->setSpecialProperty('commentaire_aide','');
      }
      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */

      protected function setIndicators(){         
         $this->addIndicatorSnmpIndexed('dell_serv_temp', 
	         '.1.3.6.1.4.1.674.10892.1.700.12.1.2.1',
	         '.1.3.6.1.4.1.674.10892.1.700.12.1.6.1',
	         '.1.3.6.1.4.1.674.10892.1.700.12.1.8.1');
      }
      
      
      protected function StatusInformationFilter($name,$value){ 
         return "$name=".$value."tours/min"; 
      }
      
      protected function nameFilter($name,$value){
         return str_replace(array('System_','_RPM'),array('',''),$name);
      }
   }
   
   $check=new MiaNagiosPlugin_CheckDellFanSpeed();
   $check->OutputResult();