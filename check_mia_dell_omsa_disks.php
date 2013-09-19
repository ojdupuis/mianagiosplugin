#!/usr/bin/php -q
<?php
   require_once('lib/MiaNagiosPluginSNMPIndexed.inc.php');
   /*
    * voir : http://ipmsupport.solarwinds.com/mibs/DELL-RAC-MIB/item.aspx?id=drsGlobalSystemStatus
    * pour les valeurs des status
    */
   class MiaNagiosPlugin_CheckDellOMSADisk extends MiaNagiosPluginSNMPIndexed{
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','DISQUE_HS');
         $this->setSpecialProperty('titre_aide','Plugins Nagios indiquant le status hardware des disks remonté par OMSA (status renvoyé 0: ok, 1:warning, 2:critical)');
         $this->setSpecialProperty('commentaire_aide','voir :http://support.dell.com/support/edocs/software/svradmin/6.1/en/SNMP/PDF/SNMP.pdf');
      }
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */
      protected function setIndicators(){         
         $this->addIndicatorSnmpIndexed('disk_status','1.3.6.1.4.1.674.10893.1.20.130.4.1.1','1.3.6.1.4.1.674.10893.1.20.130.4.1.4','1.3.6.1.4.1.674.10893.1.20.130.4.1.2');
      }

     protected function dataFilter($name,$value){
         if ($value == 3){
            return -1;
         }
         return $value;
     }

    protected function StatusInformationFilter($name,$value){
      switch ($value){
         case "-1":
            $status="Online";
            break;
         case "0" : // other
            $status="unknown";
            break;
         case "2" : // unknown
            $status="failed";
            break;
         case "6" : //ok
            $status="degraded";
            break;
         case "7" :
            $status="recovering";
            break;
         case "11":
            $status="removed";
            break;
         case "15":
            $status="resynching";
            break;
         case "24":
            $status="rebuild";
            break;
         case "25":
            $status='nomedia';
            break;
         case "26":
            $status="formatting";
         break;
         case "28":
            $status="diagnostics";
            break;
         case "34":
            $status="predictive failure";
            break;
         case "35":
            $status="initializing";
            break;
         default:
            $status="Status inconnu";
      }
      return $name."=".$status;
   }
   }

   $check=new MiaNagiosPlugin_CheckDellOMSADisk();
   $check->OutputResult();
