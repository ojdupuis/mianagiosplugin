#!/usr/local/bin/php5 -q
<?php
   require_once('lib/MiaNagiosPluginSNMPIndexed.inc.php');
   
   class MiaNagiosPlugin_CheckFilerVolState extends MiaNagiosPluginSNMPIndexed{        
      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
      
       */
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','FILER_VOL_STATE');
         $this->setSpecialProperty('titre_aide','Plugins Nagios du statut du volume du filer');
         $this->setSpecialProperty('commentaire_aide','RF : http://www.oidview.com/mibs/789/NETWORK-APPLIANCE-MIB.html');
      }
      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */

      protected function setIndicators(){         
         $this->addIndicatorSnmpIndexed('filer_vol_state','.1.3.6.1.4.1.789.1.5.4.1.1','.1.3.6.1.4.1.789.1.5.4.1.20','.1.3.6.1.4.1.789.1.5.4.1.2');
      }

      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#dataFilter()
       */
      
      protected function dataFilter($name,$value){
         switch($value)
            {
               case 2: $value=1; break;
               case 1: $value=2; break;
            }
         return $value;                 
      }   

      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#StatusInformationFilter()
       */
      
      protected function StatusInformationFilter($name,$value){ 
      switch($value)
         {
            case 1: $intitule="Ok";break;
            case 2: $intitule="unmounted";break;
            case 3: $intitule="frozen";break;
            case 4: $intitule="destroying";break;
            case 5: $intitule="creating";break;
            case 6: $intitule="mounting";break;
            case 7: $intitule="unmounting";break;
            case 8: $intitule="nosysinfo";break;
            case 9: $intitule="replaying";break;
            case 10: $intitule="replayed";break;
           Default:
          }
         return "$name=".$intitule;                 
      }

   }
   
   $check=new MiaNagiosPlugin_CheckFilerVolState();
   $check->OutputResult();