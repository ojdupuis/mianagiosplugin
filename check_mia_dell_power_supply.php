#!/usr/bin/php -q
<?php
   require_once('lib/MiaNagiosPluginSNMPIndexed.inc.php');
   
   class MiaNagiosPlugin_CheckDellPowerSupply extends MiaNagiosPluginSNMPIndexed{  
            
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
      
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','DELL_POWER_SUPPLY');
         $this->setSpecialProperty('titre_aide','Plugins Nagios indiquant le status des alimentations des serveurs dell');
         $this->setSpecialProperty('commentaire_aide','cf http://support.ipmonitor.com/tutorials/b6c02f3532554affa79bb523c6c28f2b.aspx\n 0 -> ok\n 1 -> warning\n à partir de 2 critical');
      }
      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */

      protected function setIndicators(){         
         $this->addIndicatorSnmpIndexed('dell_power', 
            '.1.3.6.1.4.1.674.10892.1.600.12.1.2.1',
            '.1.3.6.1.4.1.674.10892.1.600.12.1.5.1',
            '.1.3.6.1.4.1.674.10892.1.600.12.1.8.1');
      }
      
      protected function dataFilter($name,$value){
         switch ($value){
            case "3" : //ok
               return "0";
            case "1" :
               return "2";
            case "2" : 
               return "3";
            case "4" :
               return "1";
               break;
            default :
               return $value;
         }
      }
      protected function StatusInformationFilter($name,$value){ 
         switch ($value){
            case "2" : // other
                $status="other";
               break;
            case "3" : // unknown
                $status="unknown";
               break;
            case "0" : //ok
               $status="ok";
               break;
            case "1" : 
               $status="warning";
               break;
            case "5":
               $status="critical";
            case "6":
               $status="nonrecoverable";
            default:
               $status="Status inconnu";
         }
         return $name."=".$status;
      }
   }
   
   $check=new MiaNagiosPlugin_CheckDellPowerSupply();
   $check->OutputResult();
