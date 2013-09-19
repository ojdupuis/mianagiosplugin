#!/usr/bin/php -q
<?php
   require_once('lib/MiaNagiosPluginSNMPSimple.inc.php');
   
   class MiaNagiosPlugin_CheckSquidByte extends MiaNagiosPluginSNMPSimple{        
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','SQUID_BYTE');
         $this->setSpecialProperty('titre_aide','Plugins Nagios indiquant le débit sur 1 minute squid ');
         $this->setSpecialProperty('commentaire_aide','');
      }
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */
      protected function setIndicators(){         
         $this->addIndicatorSnmpSimple('squid_byte','.1.3.6.1.4.1.3495.1.3.2.2.1.10.1'); 
         $this->setIndicatorUnit('squid_byte','%');             
      }
      
     
      protected function StatusInformationFilter($name,$value){ 
          return $name."=".$value."%";
      }

   }
   
   $check=new MiaNagiosPlugin_CheckSquidByte();
   $check->OutputResult();
