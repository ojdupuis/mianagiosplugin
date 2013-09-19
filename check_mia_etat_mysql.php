#!/usr/bin/php -q
<?php
   require_once('lib/MiaNagiosPluginMySqlSimple.inc.php');
   
   class MiaNagiosPlugin_CheckEtatMySql extends MiaNagiosPluginMySqlSimple{      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','etat_mysql');
         $this->setSpecialProperty('titre_aide','Plugins Nagios vérifiant la connexion à une base mysql ');
         $this->setSpecialProperty('commentaire_aide','La valeur 0 indique que c\'est ok, sinon problème');
      }
      
         
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */
      protected function setIndicators(){ 
         $this->addIndicatorMySqlSimple(
            'etat_mysql',
            'etat_mysql',
            'SELECT VERSION()');
      }   
      
      
      protected function dataFilter($name,$value){    
      	if ($value===null){
      		$value=0;
      	} else {
      		$value=1;
      	}
         return $value; 
      }
      
      protected function StatusInformationFilter($name,$value){         
         return "$name=".$value;
      } 

   }
       
   
 $check=new MiaNagiosPlugin_CheckEtatMySql();   
 $check->OutputResult();
