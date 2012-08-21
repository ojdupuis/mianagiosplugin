#!/usr/local/bin/php5 -q
<?php
   require_once('lib/MiaNagiosPluginSNMPSimple.inc.php');
   
   class MiaNagiosPlugin_CheckSquidDisk extends MiaNagiosPluginSNMPSimple{        
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','SQUID_DISK');
         $this->setSpecialProperty('titre_aide','Plugins Nagios indiquant l\'espace disque a loué à squid dans sa conf et à l\'espace disque effectivement utilisé par squid');
         $this->setSpecialProperty('commentaire_aide','');
      }
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */
      protected function setIndicators(){         
         $this->addIndicatorSnmpSimple('squid_disk_conf','.1.3.6.1.4.1.3495.1.2.5.2'); 
         $this->addIndicatorSnmpSimple('squid_disk_val','.1.3.6.1.4.1.3495.1.1.2');        
         $this->setIndicatorUnit('squid_disk_conf','GB');   
         $this->setIndicatorUnit('squid_disk_val','GB');           
      }
      
      
      protected function dataFilter($name,$value){  
	      switch($name) {  
	      	case 'squid_disk_conf':
	      		$value=MiaNagiosPluginFilters::dataFilterPrecision(
	      		      $name,MiaNagiosPluginFilters::dataFilterUnitToKilo($name,$value),1); 
	      	break;
	         case 'squid_disk_val': 
               $value=MiaNagiosPluginFilters::dataFilterPrecision(
                     $name,MiaNagiosPluginFilters::dataFilterUnitToMega($name,$value),1); 
	         break;
	         Default:
	      }          
	    return $value;      
      }
      
      protected function StatusInformationFilter($name,$value){ 
          return $name."=".$value."GB";
      }

   }
   
   $check=new MiaNagiosPlugin_CheckSquidDisk();
   $check->OutputResult();