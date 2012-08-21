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
   abstract class MiaNagiosPluginSNMPIndexed extends MiaNagiosPluginSNMP{
        private $_index=null; 
        private $_index_oid=null; 
      
//      final protected function addIndicatorSNMPSimple($name,$oid,$time_derived=false,$counter=false) {
         final protected function addIndicatorSnmpIndexed($array_name,$oid_index,$array_oid_value,$oid_name=null){
         trigger_error('start',E_USER_NOTICE);
         
         if (!(is_array($array_name))){
            $array_name=array($array_name);
         }
         if (!(is_array($array_oid_value))){
            $array_oid_value=array($array_oid_value);
         }
         
         //@TODO Verifier les tailles tableau doivent être ==
         
         foreach ($array_name as $i => $name){
            trigger_error("name=$name",E_USER_NOTICE);
            $this->setIndicatorParameter($name,'name',$name);
            $this->setIndicatorParameter($name,'oid_value',$array_oid_value[$i]);
            //$this->setIndicatorParameter($name,'oid_index',$oid_index);
            $this->setIndicatorParameter($name,'oid_name',$oid_name);
         }              
         $this->_index_oid=$oid_index;    
         trigger_error('end',E_USER_NOTICE);
      } 
                       
      final protected function _preliminarySetUp(){
         trigger_error('start',E_USER_NOTICE);
         trigger_error('index oid='.$this->_index_oid,E_USER_NOTICE);
         $this->_index=$this->_snmpWalk($this->_index_oid,false);
         trigger_error('end',E_USER_NOTICE);
      }
      final protected function _preliminaryIndicator($name){
         trigger_error('start',E_USER_NOTICE);    
         
         foreach ($this->_index as $i => $index){            
              $valeur=$this->_snmpGet($this->getIndicatorParameter($name,'oid_value').".".$index,false);
            if ($this->getIndicatorParameter($name,'oid_name') !== null){
               $dataname=$this->_snmpGet($this->getIndicatorParameter($name,'oid_name').".".$index,false);
            } else {
               $dataname=$i;
            }
            $tab[$dataname]=$valeur;            
            trigger_error('name ='.$name." dataname=$dataname index=$index valeur=$valeur oid=".$this->getIndicatorParameter($name,'oid_value'),E_USER_NOTICE);
            
         }
         trigger_error('end',E_USER_NOTICE);
         return $tab;
      }
      
      protected function _dataNameIndicator($name){
         trigger_error("indicator name=$dataname",E_USER_NOTICE);
         return array_keys($this->getPreliminary($name));              
      }
      
      protected function _dataValueIndicator($name){
         trigger_error("dataname=$dataname",E_USER_NOTICE);
         return array_values($this->getPreliminary($name));
      }
            
   } 
