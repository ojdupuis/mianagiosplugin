#!/usr/local/bin/php5 -q
<?php
   require_once('lib/MiaNagiosPluginSimple.inc.php');
   
   class MiaNagiosPlugin_CheckUpTime extends MiaNagiosPluginSimple{      
   	  
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
   	
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','UPTIME');
         $this->setSpecialProperty('titre_aide','Plugins Nagios qui v�rifie le uptime');
         $this->setSpecialProperty('commentaire_aide','');
      }
      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */
      protected function setIndicators(){   
         $this->addIndicatorSimple('uptime');
         $this->setIndicatorMin('uptime',0 );
      }
      
      /**
       * (non-PHPdoc)
       * @see miadm/bin/nagios/plugins/lib/MiaNagiosPluginSNMPSimple#coreFunction()
       */
      protected function coreFunction(){
         trigger_error("start",E_USER_NOTICE);
         exec('uptime',$output);
        
         if (preg_match("/\s([0-9]+)\sdays?/",$output[0],$matche) > 0 ){
            $output['uptime']=$matche[1];
            trigger_error("uptime ".$matche[1],E_USER_NOTICE);
         }
	trigger_error("/\s([0-9]+:[0-9]+:[0-9]+)\sup\s/".$output[0],E_USER_NOTICE);
         if (preg_match("/\s([0-9]+:[0-9]+:[0-9]+)\sup\s+/",$output[0],$matche) > 0 ){
            $output['uptime']=0;
            trigger_error("uptime ".$matche[1],E_USER_NOTICE);
         }


         trigger_error("end",E_USER_NOTICE);
         return $output;
      }
      
   }
   
   $check=new MiaNagiosPlugin_CheckUptime();
   $check->OutputResult();
