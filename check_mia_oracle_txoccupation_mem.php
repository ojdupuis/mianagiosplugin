#!/usr/bin/php -q
<?php
   require_once('lib/MiaNagiosPluginOracleSimple.inc.php');
   
   class MiaNagiosPlugin_CheckTxOccupationMemUsed extends MiaNagiosPluginOracleSimple{      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','TXOCCUPATION_MEM');
         $this->setSpecialProperty('titre_aide','Plugins Nagios : Taux d\'occupation global de la mémoire oracle');
         $this->setSpecialProperty('commentaire_aide','');
      }
      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */
      protected function setIndicators(){   
         trigger_error('start',E_USER_NOTICE);         
         $this->addIndicator('memoryspaceused_pourcent');         
         $this->setIndicatorUnit('memoryspaceused_pourcent','%');
         $this->setIndicatorMin('memoryspaceused_pourcent',0);                 
         trigger_error('end',E_USER_NOTICE);   
      }

      protected function _preliminarySetUp(){
         trigger_error('start',E_USER_NOTICE);
         $query="SELECT free.val free_mb, tot.val tot_mb
FROM (SELECT ROUND (SUM (BYTES) / 1048576, 2) val
FROM v\$sgastat st1
WHERE st1.NAME = 'free memory') free,
(SELECT ROUND (SUM (BYTES) / 1048576, 2) val
FROM v\$sgastat st2) tot";          
         $query_output=$this->_executeQuery($query);
         
         foreach ($query_output as $name => $tab_valeur){
            $query_output[$name]=$tab_valeur[0];
         }
         $output['memoryspaceused_pourcent']=($query_output['TOT_MB']-$query_output['FREE_MB'])/($query_output['TOT_MB']);
         return $output;
        
         trigger_error('end',E_USER_NOTICE);      
            
      }
      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#StatusInformationFilter($name)
       */
      protected function StatusInformationFilter($name,$value){         
         return "$name=".$value."%";                 
      }
      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#dataFilter($name, $value)
       */
      protected function dataFilter($name,$value){         
      	return MiaNagiosPluginFilters::dataFilterPrecision($name,$value*100,1);                
      }
      protected function _dataNameIndicator($name){
         return array($name);
      }
      
      protected function _dataValueIndicator($name){         
         return array($this->getPreliminary($name));
      } 
   }
          
   $check=new MiaNagiosPlugin_CheckTxOccupationMemUsed();   
   $check->OutputResult();
