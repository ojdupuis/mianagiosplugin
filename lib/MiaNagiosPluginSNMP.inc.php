<?php
 require_once('MiaNagiosPlugin.inc.php');

 /**
 * Fichier de définition de la classe MiaNagiosPluginSNMP
 *
 * @package    systeme
 * @author     Olivier Dupuis
 * @author     $Author$
 * @version    $Revision$, $Date$
 */

   /**
    * Classe abstraite donnant accès à des primitives de base pour l'interrogation SNMP
    * 
    * 
    *
    */   
   abstract class MiaNagiosPluginSNMP extends MiaNagiosPlugin{            
      /**
       * Separateur pour la nom des indicateur stockés en clef de $data
       * @const string
       */
      const INDEXEDSEPARATOR=".";
           
      /**
      *  Tableau associatif intermédiaire. Sa raison d'êtr est la suivante :
      * Dans le cas de données SNMP indexées, la liste des Indicateurs n'est pas connue à l'avance, seule le snmpwalk sur l'oid peut nous la donner.
      * Le addIndicatorSNMP empiète donc sur les prérogatives du _calculateValues.  
      * Les données collectées sont donc transmise au _calculateValues dont le seul rôle (sauf surcharge) est de réaliser le addData
      * 
      * @var array
      */
      var $snmp_def=array();
      
      
      
      /**
       * Le framework ne supporte pas l'ajout de plusieurs donnees indexées
       * On memorise donc l'utilisation de addIndicatorSnmpIndexed
       * @var bool
       */
      var $_donnee_indexed_presente=false;
      
      /**
       * Tableau contenant les propriété d'un indicateur SNMP (indexé ou pas, multivalue...)
       * @var array
       */
      var $_snmp_indicator_properties=array();
      
     /**
      * Constructeur de la classe abstraite
      * 
      * - ajoute les input nécessaires a une interrogation snmp
      * 
      * @author   Olivier Dupuis   
      * @return   void  
      */ 
      public function __construct(){            
         trigger_error("start",E_USER_NOTICE);
         if (! isset($this->_input['host'])){
                                $this->addInput('host','/(\-H)\s+([^\s]+)/',false);
         } else {
                                trigger_error('il ne faut pas definir manuellement -h',E_USER_ERROR);
         }
         if (! isset($this->_input['port'])){
                                $this->addInput('port','/(\-p)\s+([^\s]+)/',true,161);
         } else {
                                trigger_error('il ne faut pas definir manuellement -p',E_USER_ERROR);
         }
         if (! isset($this->_input['community'])){
                                $this->addInput('community','/(\-C)\s+([^\s]+)/',true,"public");
         } else {
                                trigger_error('il ne faut pas definir manuellement -C',E_USER_ERROR);
         }
         if (! isset($this->_input['timeout'])){
                                $this->addInput('timeout','/(\-t)\s+([^\s]+)/',true,"10");
         } else {
                                trigger_error('il ne faut pas definir manuellement -t',E_USER_ERROR);
         }
         if (! isset($this->_input['retries'])){
                                $this->addInput('retries','/(\-r)\s+([^\s]+)/',true,"3");
         } else {
                                trigger_error('il ne faut pas definir manuellement -r',E_USER_ERROR);
         }
         if (! isset($this->_input['version'])){
                                $this->addInput('version','/(\-V)\s+([^\s]+)/',true,"auto");
         } else {
                                trigger_error('il ne faut pas definir manuellement -V',E_USER_ERROR);
         }
         parent::__construct();
      }                  
      
                   
      /**
       * Méthode permettant de nettoyer l'output de snmpget  car il est de la forme INTEGER: $valeur
       * @param   $value      string valeur à nettoyer issue 
       * @return unknown_type false en cas d'erreur, sinon retourne une chaine
       */
      final private function _makeValueClean($value){
         trigger_error('start',E_USER_NOTICE);
         trigger_error("Parsing de la valeur= $value pregmatch=".preg_match("/^([^:]+):\s(.*)$/",$value,$output),E_USER_NOTICE);    
              
         if (1 == preg_match("/^([^:]+):\s(.*)$/",$value,$output)){
            $value=$output[2];
            trigger_error('Parsing de la valeur pour recherche du type ok  valeur= '.$value,E_USER_NOTICE);
         } else {
            trigger_error('Parsing de la valeur pour recherche du type a echoue valeur= '.$value,E_USER_WARNING);
            $value=$value;
         }
         $value=str_replace('"','',$value);
         trigger_error(' end',E_USER_NOTICE);
         return $value;
      }
      
      
      

      /**
       * Méthode de détermination du type de donnée d'une valeur remontée par SNMP
       * 
       * @param   $value   string   valeur dont on doit déterminer le type
       * @return string
       */
      final private function _getSnmpType($value){
         trigger_error('_getSnmpType start',E_USER_NOTICE);
         $tmp=split(":",$value);
         // snmpget retourne une chaine de type INTEGER: valeur
         if ($tmp !== false){
            if (count($tmp) == 1){
               trigger_error('_getSnmpType type=null',E_USER_NOTICE);                           
               $value=null;
            } else {
               trigger_error('_getSnmpType type='.$tmp[0],E_USER_NOTICE);
               $value=trim($tmp[0]," ");
            }
         } else {
            trigger_error('Split a echoue pour l indicateur '.$name." valeur= ".$this->snmp_def[$name],E_USER_ERROR);
            $value=false;
         }
         trigger_error('_getSnmpType end',E_USER_NOTICE);
         return $value;
      }
      
      /**
       * Méthode permettant de nettoyer l'ouput d'un snmpwalk
       *  
       * @param $array_value array tableau contenant un résultat issu d'un snmpwalk
       * @return array  résultat nettoyé
       */           
      final private function _makeAllValuesClean($array_value){
         trigger_error('_makeAllValuesClean start',E_USER_NOTICE);
         foreach ($array_value as $i => $value){
            $retour[$i]=$this->_makeValueClean($value);
         }
         trigger_error('_makeAllValuesClean end',E_USER_NOTICE);
         return $retour;
      }  
      
      /**
       * Méthode d'interrogation snmp.
       * Comparée au snmpget elle gère les type compteurs
       *  
       * @param   $oid  string oid de la donnée
       * @param    $time_derived boolean optionnel : false par défaut, le compteur doit il être divisé par le temp 
       * @return string 
       */
      final protected function _snmpGet($oid){       
         trigger_error('start',E_USER_NOTICE);
         trigger_error("oid=$oid",E_USER_NOTICE);  
         switch($this->getInput('version')){
            case 'auto':
               $valeur=$this->_makeValueClean(snmpget($this->getInput('host').":".$this->getInput('port'),$this->getInput('community'),$oid,$this->getInput('timeout')*1000000,$this->getInput('retries')));
            break;
            case '2' :
               $valeur=$this->_makeValueClean(snmp2_get($this->getInput('host').":".$this->getInput('port'),$this->getInput('community'),$oid,$this->getInput('timeout')*1000000,$this->getInput('retries')));
            break;
            case '3' :
               $valeur=$this->_makeValueClean(snmp3_get($this->getInput('host').":".$this->getInput('port'),$this->getInput('community'),$oid,$this->getInput('timeout')*1000000,$this->getInput('retries')));
            break;
         }
         trigger_error('end',E_USER_NOTICE);
         
         return $valeur;        
      } 
      
      /**
       * Méthode d'interrogation snmp.
       * Comparée au snmpget elle gère les type compteurs
       *  
       * @param   $oid  string oid de la donnée
       * @param    $time_derived boolean optionnel : false par défaut, le compteur doit il être divisé par le temp 
       * @return string 
       */
      final protected function _snmpWalk($oid){       
         trigger_error('start',E_USER_NOTICE);
         trigger_error("oid=$oid",E_USER_NOTICE);  
         switch($this->getInput('version')){
            case 'auto':
               $retour=snmpwalk($this->getInput('host').":".$this->getInput('port'),$this->getInput('community'),$oid,$this->getInput('timeout')*1000000,$this->getInput('retries'));
            break;
            case '2' :
               $retour=snmp2_walk($this->getInput('host').":".$this->getInput('port'),$this->getInput('community'),$oid,$this->getInput('timeout')*1000000,$this->getInput('retries'));
            break;
            case '3' :
               $retour=snmp3_walk($this->getInput('host').":".$this->getInput('port'),$this->getInput('community'),$oid,$this->getInput('timeout')*1000000,$this->getInput('retries'));
            break;
         }
         $retour=snmpwalk($this->getInput('host').":".$this->getInput('port'),$this->getInput('community'),$oid,$this->getInput('timeout')*1000000,$this->getInput('retries'));
         foreach ($retour as $i => $valeur){
            $retour[$i]=$this->_makeValueClean($valeur);
         }
         
         return $retour;        
      } 
      /**
       * Méthode d'interrogation snmp.
       * Comparée au snmpget elle gère les type compteurs
       *  
       * @param   $oid  string oid de la donnée
       * @param    $time_derived boolean optionnel : false par défaut, le compteur doit il être divisé par le temp 
       * @return string 
       */
      final protected function _snmpWalkOid($oid){       
         trigger_error('start',E_USER_NOTICE);
         trigger_error("oid=$oid",E_USER_NOTICE);  
         $retour=snmpwalkoid($this->getInput('host').":".$this->getInput('port'),$this->getInput('community'),$oid,$this->getInput('timeout')*1000000,$this->getInput('retries'));
         foreach ($retour as $i => $valeur){
            $retour[$i]=$this->_makeValueClean($valeur);
         }
         
         return $retour;        
      }
  
   }
    
