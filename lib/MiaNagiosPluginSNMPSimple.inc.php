<?php

 require_once('MiaNagiosPluginSNMP.inc.php');
 
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
   abstract class MiaNagiosPluginSNMPSimple extends MiaNagiosPluginSNMP{  
       
      
      protected function addIndicatorSNMPSimple($name,$oid,$utime_interval) {
         trigger_error('start',E_USER_NOTICE);
         trigger_error("name=$name",E_USER_NOTICE);
         $this->setIndicatorParameter($name,'name',$name);
         $this->setIndicatorParameter($name,'oid',$oid);
         trigger_error('end',E_USER_NOTICE);
      } 
      
      protected function _preliminarySetUp(){
         return null;
      }
                       
      protected function _preliminaryIndicator($name){
         trigger_error('start',E_USER_NOTICE);
         trigger_error('oid = '.$this->getIndicatorParameter($name,'oid')." name = $name",E_USER_NOTICE);
         $retour=$this->_snmpGet($this->getIndicatorParameter($name,'oid'),true,true);
         trigger_error('end',E_USER_NOTICE);
         return $retour;
      }
      
      protected function _dataNameIndicator($name){
         return array($name);
      }
      
      protected function _dataValueIndicator($dataname){
         return array($this->getPreliminary($dataname));
      }
            
   } 
