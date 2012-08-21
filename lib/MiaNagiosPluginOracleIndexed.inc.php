<?php
 require_once('MiaNagiosPluginOracle.inc.php');
          
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
   abstract class MiaNagiosPluginOracleIndexed extends MiaNagiosPluginOracle{
      public $_query_output;
      public $query;                               
      /**
       * Méthode permettant l'ajout d'un indicateur oracle simple
       * 
       * A utiliser en lieu et place de addIndicator
       * 
       * @param   $name          mixed   Nom ou tableau de nom définissant l'indicateur et remonté à NAgios 
       * @param   $key           mixed   chaine ou tableau de chaine définissant la colonne retournee par oracle
       * @param   $query         string   requête SQL a exécuter
       * @param   $unit          string   Unité de l'indicateur cf http://nagiosplug.sourceforge.net/developer-guidelines.html#PLUGOUTPUT
       * @param   $min           float    optionnel : valeur minimale pouvant prendre l'indicateur
       * @param   $max           flat     optionnel : valeur maximale pourvant prendre l'indicateur   
       * @return  void  
       */
      final protected function addIndicatorOracleIndexed($name,$key,$query){
         trigger_error('start',E_USER_NOTICE);
         $this->setIndicatorParameter($name,'key',$key);
         $this->setIndicatorParameter($name,'name',$name); 
         $this->query=$query;
         trigger_error('end',E_USER_NOTICE);
      }       
         
      protected function _preliminarySetUp(){
         trigger_error('start',E_USER_NOTICE);         
         $query_output=$this->_executeQuery($this->query);
         $keys=array_keys($query_output);
//         // La requête renvoie un résultat de la forme array_key;array_valeurs
         $col_key=$keys[0];
         $col_value=$keys[1];
         $output=array();
         foreach ($query_output[$col_key] as $index => $tab_valeur){
         	trigger_error($index." ".$query_output[$col_key][$index]."=".$query_output[$col_value][$index],E_USER_NOTICE);
            $output[$col_key][$query_output[$col_key][$index]]=$query_output[$col_value][$index];
            trigger_error('name ='.$col_key,E_USER_NOTICE);
         }         
         trigger_error('end',E_USER_NOTICE);
         return $output;                  
      }
      
      protected function _dataNameIndicator($name){
         $nametab=$this->getPreliminary($this->getIndicatorParameter($name,'key'));
         if (is_array($nametab)){
            return array_keys($nametab);
         } else {
            return array();
         }
      }
      
      protected function _dataValueIndicator($name){
         $nametab=$this->getPreliminary($this->getIndicatorParameter($name,'key'));       
         if (is_array($nametab)){
            return array_values($nametab);
         } else {
            return array();
         }             
      }  
      
        
   }
   