<?php
 require_once('MiaNagiosPlugin.inc.php');
          
 /**
 * Fichier de définition de la classe MiaNagiosPluginOracle
 *
 * @package    systeme
 * @author     Olivier Dupuis
 * @author     $Author$
 * @version    $Revision$, $Date$
 */

   /**
    * Classe abstraite donnant accès à des primitives de base pour l'interrogation d'un base Oracle
    * 
    * 
    *
    */   
   abstract class MiaNagiosPluginOracle extends MiaNagiosPlugin{
      
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
         $this->addInput('servicename','/(\-s)\s+([^\s]+)/');
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
         
         if (isset($conf['Environnement'])){
            foreach ($conf['Environnement'] as $nom => $valeur){
               putenv("$nom=$valeur");
               trigger_error("Environnement  : $nom = $valeur",E_USER_NOTICE);
            }
         }
         trigger_error('end',E_USER_NOTICE);
         return $this;                  
      }
      
      /**
       * Méthode de connection à la base Oracle
       * 
       * @return void
       */      
      final private function _connect(){
         trigger_error('start',E_USER_NOTICE);
         trigger_error('service '.$this->getInput('servicename'),E_USER_NOTICE);
         if (!($this->handle=oci_connect($this->getSpecialProperty('login'),$this->getSpecialProperty('password'),$this->getInput('servicename')))){
            trigger_error('Erreur connexion oracle '.serialize(oci_error()),E_USER_ERROR);
         }
         trigger_error('end',E_USER_NOTICE);
      } 
      
      /**
       * Méthode d'exécution d'une requête oracle
       * 
       * @param   $query      requête Oracle à exécuter 
       * @return array        
       */
      final protected function _executeQuery($query){
         trigger_error('start',E_USER_NOTICE);
         trigger_error('query = '.$query,E_USER_NOTICE);
         if (! ($stmt = oci_parse($this->handle, $query))) {
            trigger_error('Error parsing de la requête '.$query,E_USER_ERROR);
         }
         if (! oci_execute($stmt)) {
            trigger_error('Error execute de la requête '.$query,E_USER_ERROR);
         }
         if (oci_fetch_all($stmt, $results) === false){
            trigger_error('Error fetchall de la requête '.$query,E_USER_ERROR);
         }
         trigger_error('query output : '.serialize($results),E_USER_NOTICE);
         trigger_error('end',E_USER_NOTICE);
         
         return $results;
      }                               
   }
   