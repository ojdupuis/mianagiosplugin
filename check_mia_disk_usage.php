#!/usr/bin/php -q
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
      
      protected function _preliminarySetUp(){
         // On commence par recuperer la liste des FS
         
         trigger_error("start",E_USER_NOTICE);
         exec('df -Hl | tail -n +2 | grep -vE "^Filesystem|tmpfs|cdrom"',$output);
         foreach ($output as $line){
            if (preg_match("/^.*\s([0-9]+)%\s+(.*)$/",$line,$match)){
               $tab['fs'][$match[2]]=$match[1];
            }
         }
error_log(var_export($tab,true));
         trigger_error("end",E_USER_NOTICE);
         return $tab;
      }

   }
   
   $check=new MiaNagiosPlugin_CheckDiskUsage();
   $check->OutputResult();
