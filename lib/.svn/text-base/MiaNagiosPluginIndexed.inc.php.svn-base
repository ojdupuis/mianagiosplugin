<?php
 require_once('MiaNagiosPlugin.inc.php');
          
 /**
 * Fichier de définition de la classe MiaNagiosPluginIndexed
 *
 * @package    systeme
 * @author     Olivier Dupuis
 * @author     $Author$
 * @version    $Revision$, $Date$
 */

   /**
    * Classe abstraite pour les inndicateurs simples construits manuellement
    * 
    * 
    *
    */   
   abstract class MiaNagiosPluginIndexed extends MiaNagiosPlugin{
                              
      /**
       * Méthode permettant l'ajout d'un indicateur indexe
       * 
       * A utiliser en lieu et place de addIndicator
       * 
       * @param   $name          mixed   Nom ou tableau de nom définissant l'indicateur et remonté à NAgios        
       * @return  void  
       */
      final protected function addIndicatorIndexed($name){
         trigger_error('start',E_USER_NOTICE);
         $this->addIndicator($name);
         trigger_error('end',E_USER_NOTICE);
      }       
         
      protected function _preliminarySetUp(){
         return "some assoc array";
      }
      
      
      protected function _dataNameIndicator($name){
         return array_keys($this->getPreliminary($name));
      }
      
      protected function _dataValueIndicator($name){	         
         return array_values($this->getPreliminary($name));
      }         
   }
   