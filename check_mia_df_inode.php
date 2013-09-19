#!/usr/bin/php -q
<?php
   require_once('lib/MiaNagiosPluginIndexed.inc.php');

   class MiaNagiosPlugin_CheckInodeUsage extends MiaNagiosPluginIndexed{
   	
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
   	
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','INODE_USAGE');
         $this->setSpecialProperty('titre_aide','Plugins Nagios de surveillance des remplissages des tables des inodes des FS');
         $this->setSpecialProperty('commentaire_aide','');
      }          
      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */
      
      protected function setIndicators(){   
         $this->addIndicatorIndexed('inodes');
         $this->setIndicatorMin('inodes',0 );
         $this->setIndicatorUnit('inodes','%');
      }
      
      protected function _preliminarySetUp(){
         trigger_error("start",E_USER_NOTICE);
         exec('df -i | grep -vE "^Filesystem" | grep -v "/mnt/" | egrep -v "^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+"',$output);
         foreach ($output as $line){
         	if (preg_match("/\s([0-9]+)%\s+(.+)$/",$line,$matche)){
               $output['inodes'][$matche[2]]=$matche[1];
               trigger_error("df $nom trouve $percentused%",E_USER_NOTICE);
            }
         }
         trigger_error("end",E_USER_NOTICE);
         return $output;
      }
      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#StatusInformationFilter()
       */
      
      protected function StatusInformationFilter($name,$value){
        return $name.'='.$value.'%';  
      }
    }
   
   $check=new MiaNagiosPlugin_CheckInodeUsage();
   $check->OutputResult();
