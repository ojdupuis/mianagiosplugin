#!/usr/bin/php -q
<?php
   require_once('lib/MiaNagiosPluginSNMPSimple.inc.php');
   
   class MiaNagiosPlugin_CheckSquidHit extends MiaNagiosPluginSNMPSimple{        
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','SQUID_HIT');
         $this->setSpecialProperty('titre_aide','Plugins Nagios indiquant le ratio de hit squid sur 1 minute');
         $this->setSpecialProperty('commentaire_aide','');
      }
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */
      protected function setIndicators(){         
         $this->addIndicatorSnmpSimple('squid_hit','.1.3.6.1.4.1.3495.1.3.2.2.1.9.1'); 
         $this->setIndicatorUnit('squid_hit','%');             
      }
      
     
      protected function StatusInformationFilter($name,$value){ 
          return $name."=".$value."%";
      }

   }
   
   $check=new MiaNagiosPlugin_CheckSquidHit();
   $check->OutputResult();
