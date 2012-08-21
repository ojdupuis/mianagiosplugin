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
   abstract class MiaNagiosPluginSNMPSimpleCompteur extends MiaNagiosPluginSNMP{  
       
      
      protected function addIndicatorSNMPSimpleCompteur($name,$oid,$utime_interval) {
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
         $retour1=$this->_snmpGet($this->getIndicatorParameter($name,'oid'),true,true);
	 $time1=microtime(true);
         usleep($this->_countersleep);
	 $retour2=$this->_snmpGet($this->getIndicatorParameter($name,'oid'),true,true);
	 $time2=microtime(true);
         trigger_error('end',E_USER_NOTICE);
	 if ($retour2 < $retour1){
		return 4294967296-$retour1+$retour2;
	 } else {
                return ($retour2-$retour1)/($time2-$time1);
	}
      }
      
      protected function _dataNameIndicator($name){
         return array($name);
      }
      
      protected function _dataValueIndicator($dataname){
         return array($this->getPreliminary($dataname));
      }
            
   } 
