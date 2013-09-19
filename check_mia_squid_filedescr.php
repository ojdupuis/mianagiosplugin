#!/usr/bin/php -q
<?php
   require_once('lib/MiaNagiosPluginSNMPSimple.inc.php');
   
   class MiaNagiosPlugin_CheckSquidFileDescr extends MiaNagiosPluginSNMPSimple{        
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','SQUID_FILEDESCR');
         $this->setSpecialProperty('titre_aide','Plugins Nagios indiquant le nombre de file de scriptors ouvert par Squid');
         $this->setSpecialProperty('commentaire_aide','Le seuil de warning étant à 27900 et critical à 27930');
      }
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */
      protected function setIndicators(){         
         $this->addIndicatorSnmpSimple('squid_filedescr','.1.3.6.1.4.1.3495.1.3.1.10');             
      }
      
     
      protected function StatusInformationFilter($name,$value){ 
          return $name."=".$value;
      }

   }
   
   $check=new MiaNagiosPlugin_CheckSquidFileDescr
   ();
   $check->OutputResult();
