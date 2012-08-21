#!/usr/local/bin/php5 -q
<?php
   require_once('lib/MiaNagiosPluginSNMPSimple.inc.php');
   /*
    * voir : http://ipmsupport.solarwinds.com/mibs/DELL-RAC-MIB/item.aspx?id=drsGlobalSystemStatus
    * pour les valeurs des status
    */
   class MiaNagiosPlugin_CheckDellRacGlobalStatus extends MiaNagiosPluginSNMPSimple{        
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','DELL_RAC_HARDWARE_STATUS');
         $this->setSpecialProperty('titre_aide','Plugins Nagios indiquant le status hardware global remonte par la carte RAC (status renvoyé 0: ok, 1:warning, 2:critical)');
         $this->setSpecialProperty('commentaire_aide','voir : http://ipmsupport.solarwinds.com/mibs/DELL-RAC-MIB/item.aspx?id=drsGlobalSystemStatus');
      }
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */
      protected function setIndicators(){         
         $this->addIndicatorSnmpSimple('status','.1.3.6.1.4.1.674.10892.2.2.1.0'); 
      }
      /**
       * (non-PHPdoc)
       * @see miadm/bin/nagios/plugins/lib/MiaNagiosPlugin::setInputs()
       */
      protected function dataFilter($name,$value){  
         switch($value) {  
            case '1':
               // Status : other
               $value=2;                
            break;
            case '2':
            	// unknown
            	$value=2; 
            break;
            case '3':
               // ok  
               $value=0;
            break;
            case '4':
               // non critical
               $value=1; 
            break;
            case '5':
               // critical
               $value=2; 
            break;        
            case '6':
               // non recoverable
               $value=2; 
            break;                                        
            Default:
            	$value=2;
            	
         }         
	    return $value;      
      }
      
   }
   
   $check=new MiaNagiosPlugin_CheckDellRacGlobalStatus();
   $check->OutputResult();