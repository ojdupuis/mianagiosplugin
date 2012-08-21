<?php
 require_once('MiaNagiosPlugin.inc.php');
          
 /**
 * Fichier de définition de la classe MiaNagiosPluginSimple
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
   abstract class MiaNagiosPluginSimple extends MiaNagiosPlugin{
                              
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
      final protected function addIndicatorSimple($name){
         trigger_error('start',E_USER_NOTICE);
         $this->addIndicator($name);
         trigger_error('end',E_USER_NOTICE);
      }       
         
      protected function _preliminarySetUp(){
         return "some assoc array";
      }
      
      
      protected function _dataNameIndicator($name){
         return array($name);
      }
      
      protected function _dataValueIndicator($name){         
         return array($this->getPreliminary($name));
      }         
   }
   