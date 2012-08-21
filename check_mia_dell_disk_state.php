#!/usr/local/bin/php5 -q
<?php
   require_once('lib/MiaNagiosPluginSNMPIndexed.inc.php');
   
   class MiaNagiosPlugin_CheckDiskState extends MiaNagiosPluginSNMPIndexed{  
   	      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
   	
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','DELL_DISK_STATE');
         $this->setSpecialProperty('titre_aide','Plugins Nagios de l\'etat d\'un RAID disk Dell');
         $this->setSpecialProperty('commentaire_aide','seuil critique à 1, warning à 0.5');
      }
      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */

      protected function setIndicators(){         
         $this->addIndicatorSnmpIndexed('dell_disk_state','.1.3.6.1.4.1.674.10893.1.20.130.4.1.1','.1.3.6.1.4.1.674.10893.1.20.130.4.1.4','.1.3.6.1.4.1.674.10893.1.20.130.4.1.2');
      }
      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#dataFilter()
       */      
      protected function dataFilter($name,$value){
         switch($value)
            {
               case 3: $value=0; break;
               case 11: $value=2; break;
               case 25: $value=3; break;
               case 26: $value=4; break;
               case 28: $value=6; break;
               case 0: $value=11; break;
               case 4: $value=25; break;
               case 6: $value=26; break;
               case 2: $value=28; break;
               case 24: $value=0.5;break;
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
            case 0: $intitule="Online - RAID configuration has been assigned."; break;
            case 0.5: $intitule="Rebuild"; break;
	         case 1: $intitule="Ready - but no RAID configuration assigned."; break;
            case 2: $intitule="Removed - Indicates that array disk has been removed."; break;
            case 3: $intitule="No Media - CD-ROM or removable disk has no media."; break;
            case 4: $intitule="Formatting - In the process of formatting."; break;
            case 6: $intitule="Recovering - Refers to state of recovering from bad blocks on disks."; break;
            case 7: $intitule="Diagnostics - Diagnostics are running."; break;
            case 11: $intitule="Unknown."; break;
            case 15: $intitule="Resynching - Transform Type or Reconfiguration, or Check Consistency."; break;
            
            case 25: $intitule="Offline - The drive is not available to the RAID controller."; break;
            case 26: $intitule="Degraded - Refers to a fault-tolerant array/virtual disk that has a failed disk."; break;
            case 28: $intitule="Failed."; break;
            case 34: $intitule="Predictive failure"; break;
            case 35: $intitule="Initializing: Applies only to virtual disks on PERC, PERC 2/SC, and PERC 2/DC controllers."; break;
           Default:
          }
         return "$name=".$intitule;                 
      }
      
   }
   
   $check=new MiaNagiosPlugin_CheckDiskState();
   $check->OutputResult();