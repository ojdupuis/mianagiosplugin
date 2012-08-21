#!/usr/local/bin/php5 -q
<?php
   require_once('lib/MiaNagiosPluginSNMPSimple.inc.php');
   
   class MiaNagiosPlugin_CheckSquidMem extends MiaNagiosPluginSNMPSimple{        
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','SQUID_DISK');
         $this->setSpecialProperty('titre_aide','Plugins Nagios indiquant l\'espace mémoire a loué à squid dans sa conf et à l\'espace mémoire effectivement utilisé par squid');
         $this->setSpecialProperty('commentaire_aide','');
      }
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */
      protected function setIndicators(){         
         $this->addIndicatorSnmpSimple('squid_mem_conf','.1.3.6.1.4.1.3495.1.2.5.1'); 
         $this->addIndicatorSnmpSimple('squid_mem_val','.1.3.6.1.4.1.3495.1.1.1');
         $this->setIndicatorUnit('squid_mem_conf','GB');   
         $this->setIndicatorUnit('squid_mem_val','GB');                    
      }
      
      
      protected function dataFilter($name,$value){  
         switch($name) {  
            case 'squid_mem_conf':
               $value=MiaNagiosPluginFilters::dataFilterPrecision(
                     $name,MiaNagiosPluginFilters::dataFilterUnitToKilo($name,$value),1); 
            break;
            case 'squid_mem_val': 
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
   
   $check=new MiaNagiosPlugin_CheckSquidMem();
   $check->OutputResult();