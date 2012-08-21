<?php
 require_once('MiaNagiosPluginMySql.inc.php');
          
 /**
 * Fichier de définition de la classe MiaNagiosPluginMySql
 *
 * @package    systeme
 * @author     Olivier Dupuis
 * @author     $Author$
 * @version    $Revision$, $Date$
 */

   /**
    * Classe abstraite donnant accès à des primitives de base pour l'interrogation d'une base MySql
    * 
    * 
    *
    */   
   abstract class MiaNagiosPluginMySqlSimple extends MiaNagiosPluginMySql{
      public $_query_output;
      public $query;                               
      /**
       * Méthode permettant l'ajout d'un indicateur mysql simple
       * 
       * A utiliser en lieu et place de addIndicator
       * 
       * @param   $name          mixed   Nom ou tableau de nom définissant l'indicateur et remonté à NAgios 
       * @param   $key           mixed   chaine ou tableau de chaine définissant la colonne retournee par MySql
       * @param   $query         string   requête SQL a exécuter
       * @param   $unit          string   Unité de l'indicateur cf http://nagiosplug.sourceforge.net/developer-guidelines.html#PLUGOUTPUT
       * @param   $min           float    optionnel : valeur minimale pouvant prendre l'indicateur
       * @param   $max           flat     optionnel : valeur maximale pourvant prendre l'indicateur   
       * @return  void  
       */
      final protected function addIndicatorMySqlSimple($name,$key,$query){
         trigger_error('start',E_USER_NOTICE);
            if (is_array($key)){
               foreach ($key as $i => $chaine){
                  $this->setIndicatorParameter($name[$i],'key',$chaine);                  
                  $this->setIndicatorParameter($name[$i],'name',$name[$i]);                                    
               }
            } else {
                  $this->setIndicatorParameter($name,'key',$key);
                  $this->setIndicatorParameter($name,'name',$name);
            }
            $this->query=$query;
            trigger_error('end',E_USER_NOTICE);
      }       
         
      protected function _preliminarySetUp(){
         trigger_error('start',E_USER_NOTICE);         
         $query_output=$this->_executeQuery($this->query);
         foreach ($query_output as $name => $tab_valeur){
            $query_output[$name]=$tab_valeur[0];
         }         
         trigger_error('end',E_USER_NOTICE);
         
         return $query_output;                  
      }
      
      protected function _dataNameIndicator($name){               
         return array($name);
      }
      
      protected function _dataValueIndicator($name){
         return array($this->getPreliminary($this->getIndicatorParameter($name,'key')));
      }      
      
        
   }
   