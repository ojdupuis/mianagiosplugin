#!/usr/bin/php -q
<?php
   require_once('lib/MiaNagiosPluginOracleSimple.inc.php');
   
   class MiaNagiosPlugin_CheckOracleSession extends MiaNagiosPluginOracleSimple{      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','SESSION');
         $this->setSpecialProperty('titre_aide','Plugins Nagios monitorant Nombre de sessions actives (sans exclure les sessions SYSMAN et DBSNMP) / Nombre de sessions bloquantes / Nombre de sessions bloquées');
         $this->setSpecialProperty('commentaire_aide','');
      }
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */
      protected function setIndicators(){
         $this->addIndicator(strtoupper('nb_sessions_actives'));
	 $this->setIndicatorParameter(strtoupper('nb_sessions_actives'),'key',strtoupper('nb_sessions_actives'));
	 $this->addIndicator(strtoupper('nb_sessions_bloquees'));
	$this->setIndicatorParameter(strtoupper('nb_sessions_bloquees'),'key',strtoupper('nb_sessions_bloquees'));
	 $this->addIndicator(strtoupper('nb_sessions_bloquantes'));
 	 $this->setIndicatorParameter(strtoupper('nb_sessions_bloquantes'),'key',strtoupper('nb_sessions_bloquantes'));

            
      }

	protected function _preliminarySetUp(){
         trigger_error('start',E_USER_NOTICE);
	 $query='select * from (SELECT count(*) nb_sessions_actives FROM v$session WHERE status=\'ACTIVE\' AND username IS NOT NULL) actives, (SELECT count(distinct blocking_session) nb_sessions_bloquantes FROM v$session WHERE blocking_session IS NOT NULL) blocking, (SELECT count(*) nb_sessions_bloquees FROM v$session WHERE blocking_session IS NOT NULL) blocked';
         $query_output=$this->_executeQuery($query);
         
         foreach ($query_output as $name => $tab_valeur){
            $output[$name]=$tab_valeur[0];
	    trigger_error("$name = $tab_valeur[0]",E_USER_NOTICE);
         }
        
         trigger_error('end',E_USER_NOTICE);      
	 return $output;
      }
      
   }
       
 $check=new MiaNagiosPlugin_CheckOracleSession();   
 $check->OutputResult();
