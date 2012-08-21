#!/usr/local/bin/php5 -q
<?php
   require_once('lib/MiaNagiosPluginSNMPSimple.inc.php');
   
   class MiaNagiosPlugin_CheckNvramState extends MiaNagiosPluginSNMPSimple{
   	        
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
      
   	protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','NVRAM_STATE');
         $this->setSpecialProperty('titre_aide','Plugins Nagios de vérification de la pile de la nvram d\'un filer NetApp');
         $this->setSpecialProperty('commentaire_aide',"\n".'RF : http://www.oidview.com/mibs/789/NETWORK-APPLIANCE-MIB.html');
      }
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */

      protected function setIndicators(){         
         $this->addIndicatorSnmpSimple('nvram_state','.1.3.6.1.4.1.789.1.2.5.1.0');
         $this->setSpecialProperty('titre_aide','Plugins Nagios de l\'etat de la nvram du filer');
         $this->setSpecialProperty('commentaire_aide',"\n".'Seuil de warning à partir de 2 et seuil de critique à partir de 3'."\n".'REF : http://support.ipmonitor.com/mibs/NETWORK-APPLIANCE-MIB/item.aspx?id=nvramBatteryStatus&overflow=0');
      }

      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#StatusInformationFilter()
       */
      
      protected function StatusInformationFilter($name,$value){ 
         switch($value)
         {
            case 1: $intitule="Ok."; break;
            case 2: $intitule="Partially discharged."; break;
            case 3: $intitule="Fully discharged."; break;
            case 4: $intitule="Not present."; break;
            case 5: $intitule="Near end of life."; break;
            case 6: $intitule="At end of life."; break;
            case 7: $intitule="Unknown."; break;
            Default:
          }
         return $name."=".$intitule;               
      }

   }
   
   $check=new MiaNagiosPlugin_CheckNvramState();
   $check->OutputResult();