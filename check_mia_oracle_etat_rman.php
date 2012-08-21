#!/usr/local/bin/php5 -q
<?php
   require_once('lib/MiaNagiosPluginOracleIndexed.inc.php');
   
   class MiaNagiosPlugin_CheckOracleEtatRman extends MiaNagiosPluginOracleIndexed{      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','ETATRMAN');
         $this->setSpecialProperty('titre_aide','Supervision backup RMAN (running, failed...)');
         $this->setSpecialProperty('commentaire_aide',' //Seuil de warning à 2 et seuil critical à 4');
      }
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */
      protected function setIndicators(){
      	$query="SELECT DISTINCT object_type,CASE status
                   WHEN 'COMPLETED'               THEN '0'
                   WHEN 'COMPLETED WITH WARNINGS' THEN '1'
                   WHEN 'RUNNING'                 THEN '2'
                   WHEN 'RUNNING WITH WARNINGS'   THEN '3'
                   WHEN 'COMPLETED WITH ERRORS'   THEN '4'
                   WHEN 'RUNNING WITH ERRORS'     THEN '5'
                   WHEN 'FAILED'                  THEN '6'
                END as STATUS
  FROM v\$rman_status
 WHERE start_time >= TRUNC(SYSDATE)
       AND row_type = 'COMMAND'
       AND operation in ('BACKUP','CONTROL FILE AND SPFILE AUTOBACK','DELETE')
       AND object_type <> ' '"; 
      	     
         $this->addIndicatorOracleIndexed('rman_status', 'rman_status', $query);
            
      }
      
      protected function coreFunction(){
         trigger_error('start',E_USER_NOTICE);         
         $query_output=$this->_executeQuery($this->query);
         foreach ($query_output['OBJECT_TYPE'] as $i => $name){
            trigger_error($name,E_USER_NOTICE);
            $query_output['rman_status'][$name]=$query_output['STATUS'][$i];
         }         
         trigger_error('end',E_USER_NOTICE);
         return $query_output;                  
      }
      
     protected function StatusInformationFilter($name,$value){ 
     	
      switch($value)
         {
            case '0': $intitule="COMPLETED"; break;
            case '1': $intitule="COMPLETED WITH WARNINGS"; break;
            case '2': $intitule="RUNNING"; break;
            case '3': $intitule="RUNNING WITH WARNINGS"; break;
            case '4': $intitule="COMPLETED WITH ERRORS"; break;
            case '5': $intitule="RUNNING WITH ERRORS"; break;
            case '6': $intitule="FAILED"; break;
           Default:
          }
         return "$name=".$intitule; 
     }                      
   }
       
 $check=new MiaNagiosPlugin_CheckOracleEtatRman();   
 $check->OutputResult();
 

