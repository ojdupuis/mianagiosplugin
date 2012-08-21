<?php

require_once('MiaNagiosPlugin.inc.php');
          
 /**
 * Fichier de définition de la classe MiaNagiosPluginMySql
 *
 * @package    systeme
 * @author     Raphaële Decussy
 * @author     $Author$
 * @version    $Revision$, $Date$
 */

   /**
    * Classe abstraite donnant accès à des primitives de base pour l'interrogation d'un base MySql
    * 
    * 
    *
    */   
   abstract class MiaNagiosPluginMySql extends MiaNagiosPlugin{
      
     /**
      * Constructeur de la classe abstraite
      * 
      * - ajoute les input nécessaires a une interrogation snmp
      * 
      * @author   Olivier Dupuis   
      * @return   void  
      */ 
      private $indicator_queries=array();
      
      /**
       * Handle de connection Oracle
       * @var resource
       */
      private $handle;            
      
      public function __construct(){   
         parent::__construct();
         trigger_error("start",E_USER_NOTICE);
         $this->defineSpecialProperty('login');
         $this->defineSpecialProperty('password');         
         $this->_parseconfiguration();   
         $this->_connect();                       
         trigger_error("end",E_USER_NOTICE);
      }
      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setInputs()
       */
      protected function setInputs(){
         trigger_error('start',E_USER_NOTICE);
         $this->addInput('hostname','/(\-H)\s+([^\s]+)/');
         trigger_error('end',E_USER_NOTICE);
      }
      
      /**
       * Retourne le mot de passe du schéma
       * 
       * @return object $this
       */
      final private function _parseconfiguration(){
         trigger_error('start',E_USER_NOTICE);
         $conf=parse_ini_file(ROOT.'/conf/'.__CLASS__.'.ini',true);
         trigger_error("Compte Supervision : mot de passe=".$this->getSpecialProperty('login'),E_USER_NOTICE);
         $this->setSpecialProperty('login',$conf['Compte Supervision']['login']);
         trigger_error("Compte Supervision : mot de passe=".$this->getSpecialProperty('password'),E_USER_NOTICE);         
         $this->setSpecialProperty('password',$conf['Compte Supervision']['password']);                 
         
         trigger_error('end',E_USER_NOTICE);
         return $this;                  
      }
      
      /**
       * Méthode de connection à la base mysql
       * 
       * @return void
       */      
      final private function _connect(){
         trigger_error('start',E_USER_NOTICE);
         trigger_error('hostname'.$this->getInput('hostname'),E_USER_NOTICE);
         if (!($this->handle=mysql_pconnect($this->getInput('hostname'),$this->getSpecialProperty('login'),$this->getSpecialProperty('password')))){
           trigger_error('Erreur connexion mysql '.mysql_error(),E_USER_ERROR);
         }
         trigger_error('end',E_USER_NOTICE);
      } 
      
      /**
       * Méthode d'exécution d'une requête mysql
       * 
       * @param   $query      requête mysql à exécuter 
       * @return array        
       */
      final protected function _executeQuery($query){
         trigger_error('start',E_USER_NOTICE);
         if (! mysql_query($query)) {
             trigger_error('Error execute de la requête '.$query,E_USER_ERROR);
         } else {
         	$data = mysql_query($query);
         }
         
         while ($results = mysql_fetch_assoc($data)) {        	
         	foreach ($results as $key => $value){
         		$output[$key][]=$value;
         	}
         	
         }
         
         trigger_error('query output : '.serialize($output),E_USER_NOTICE);
         trigger_error('end',E_USER_NOTICE);
         
         return $output;
      }                               
   }
   
