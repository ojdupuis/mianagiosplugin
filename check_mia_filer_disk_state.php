#!/usr/bin/php -q
<?php
   require_once('lib/MiaNagiosPluginSNMPIndexed.inc.php');
   
   class MiaNagiosPlugin_CheckFilerState extends MiaNagiosPluginSNMPIndexed{     
   	   
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
   	
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','FILER_DISK_STATE');
         $this->setSpecialProperty('titre_aide','Plugins Nagios de l\'etat du filer disk');
         $this->setSpecialProperty('commentaire_aide',"\n".'Seuil de warning à partir de 3 et seuil de critique à partir de 7'."\n".'RF : http://support.ipmonitor.com/mibs/NETWORK-APPLIANCE-MIB/item.aspx?id=raidStatus');
      }
      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */

      protected function setIndicators(){         
         $this->addIndicatorSnmpIndexed('filer_disk_state','.1.3.6.1.4.1.789.1.6.1.1.1','.1.3.6.1.4.1.789.1.6.1.1.3','.1.3.6.1.4.1.789.1.6.1.1.2');
         $this->setIndicatorNoPerfData('filer_disk_state');
      }
      

      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#StatusInformationFilter()
       */
      
      protected function StatusInformationFilter($name,$value){ 
      switch($value)
         {
               case 1: $intitule="Active";break;
               case 2: $intitule="Spare";break;
               case 3: $intitule="Reconstructing";break;
               case 4: $intitule="Reconstructing Parity";break;
               case 5: $intitule="Verificating Parity";break;
               case 6: $intitule="Scrubbing";break;
               case 7: $intitule="Failed";break;
               case 8: $intitule="Adding Spare";break;
               case 9: $intitule="Prefailed";break;
               case 10: $intitule="Offline";break;
          }
         return str_replace('"','',$name).'='.$intitule;                 
      }
      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#DataFilter()
       */
      
      protected function dataFilter($name,$value){
         switch($value)
            {
               case 2 : $valeur=3;break;
               case 3 : $valeur=4;break;
               case 4 : $valeur=5;break;
	            case 5 : $valeur=6;break;
	            case 6 : $valeur=7;break;
	            case 7 : $valeur=8;break;
	            case 8: $valeur=2;
            break;
         default:
            }
         return $value;                 
      }   

   }
   
   $check=new MiaNagiosPlugin_CheckFilerState();
   $check->OutputResult();
