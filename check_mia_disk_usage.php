#!/usr/local/bin/php5 -q
<?php
   require_once('lib/MiaNagiosPluginIndexed.inc.php');

   class MiaNagiosPlugin_CheckDiskUsage extends MiaNagiosPluginIndexed{
      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
   	
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','FS');
         $this->setSpecialProperty('titre_aide','Plugins Nagios de surveillance des remplissages FS');
         $this->setSpecialProperty('commentaire_aide','');
      }     

  
      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */
      protected function setIndicators(){
      	$this->addIndicatorIndexed('fs');
      	$this->setIndicatorUnit('fs',0);
      	$this->setIndicatorUnit('fs','%');
      }
      
      protected function coreFunction(){
         // On commence par recuperer la liste des FS
         
         trigger_error("start",E_USER_NOTICE);
         exec('df -Hl | grep -vE "^Filesystem|tmpfs|cdrom"',$output);
         foreach ($output as $line){
            if (preg_match("/^.*\s([0-9]+)%\s+(.*)$/",$line,$match)){
               $tab['fs'][$match[2]]=$match[1];
            }
         }
         trigger_error("end",E_USER_NOTICE);
         return $tab;
      }

   }
   
   $check=new MiaNagiosPlugin_CheckDiskUsage();
   $check->OutputResult();
