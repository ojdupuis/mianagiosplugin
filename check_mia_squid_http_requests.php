#!/usr/bin/php -q
<?php
   require_once('lib/MiaNagiosPluginSNMPSimple.inc.php');
   
   class MiaNagiosPlugin_CheckSquidHttpRequests extends MiaNagiosPluginSNMPSimple{        
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','SQUID_HTTP_REQUESTS');
         $this->setSpecialProperty('titre_aide','Plugins Nagios indiquant le nombre de requetes par secondes traitees par Squid');
         $this->setSpecialProperty('commentaire_aide','Le seuil de warning étant à 1 et critical à 4');
      }
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */
      protected function setIndicators(){         
         $this->addIndicatorSnmpSimple('squid_http_requests','.1.3.6.1.4.1.3495.1.3.2.1.1');
         $this->setIndicatorCounter('squid_http_requests',32);
         $this->setIndicatorTimeDerived('squid_http_requests');
      }
      
     
      protected function DataFilter($name,$value){ 
      	$value=MiaNagiosPluginFilters::dataFilterPrecision($name,$value,1);
          return $value;
      }
      
      
      protected function StatusInformationFilter($name,$value){ 
          return $name."=".$value."req/s";
      }

   }
   
   $check=new MiaNagiosPlugin_CheckSquidHttpRequests();
   $check->OutputResult();
