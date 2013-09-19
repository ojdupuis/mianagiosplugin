#!/usr/bin/php -q
<?php
   require_once('lib/MiaNagiosPluginSNMPIndexed.inc.php');
   
   class MiaNagiosPlugin_CheckWindowNetworkBwout extends MiaNagiosPluginSNMPIndexed{        
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','WINDOWS_NETWORK_BWOUT');
         $this->setSpecialProperty('titre_aide','Plugins Nagios de débit sortant d\'un serveur windows');
                  $this->setSpecialProperty('commentaire_aide','');
      }
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */

      protected function setIndicators(){         
         $this->addIndicatorSnmpIndexed('iftable_network_bwout','.1.3.6.1.2.1.2.2.1.1','.1.3.6.1.2.1.2.2.1.16','.1.3.6.1.2.1.2.2.1.1');
         $this->setIndicatorMin('iftable_network_bwout',0);
         $this->setIndicatorCounter('iftable_network_bwout',32);
         $this->setIndicatorTimeDerived('iftable_network_bwout');
         
      }
      
      protected function statusInformationFilter($name,$value){         
         return $name."=".$value."Mbits";
      }      
      
      protected function dataFilter($name,$value){
         return MiaNagiosPluginFilters::dataFilterPrecision($name,MiaNagiosPluginFilters::dataFilterUnitToMega($name,$value)*8,1);
      }
      
      protected function _dataNameIndicator($name){
         trigger_error("indicator name=$dataname",E_USER_NOTICE);
         // on récupère les @ip correspondant à chaque interface
         $ip_liste_index=$this->_snmpWalk(".1.3.6.1.2.1.4.20.1.1");
         $ip_liste_name=$this->_snmpWalk(".1.3.6.1.2.1.4.20.1.2");
         foreach ($ip_liste_index as $index=>$ip){
            $convert[$ip_liste_name[$index]]=$ip;
         }
         // on converti le name en ip         
         foreach (array_keys($this->getPreliminary($name)) as $index => $perfdata_name){
            $retour[$index]=$convert[$perfdata_name];
         }
         return $retour;              
      }

   }
   
   $check=new MiaNagiosPlugin_CheckWindowNetworkBwout();
   $check->OutputResult();
