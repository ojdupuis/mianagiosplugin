<?php

require_once('MiaNagiosPluginSimple.inc.php');
          
 /**
 * Fichier de définition de la classe MiaNagiosPluginRedis
 *
 * @package    systeme
 * @author     Raphaële Decussy
 * @author     $Author$
 * @version    $Revision$, $Date$
 */

   /**
    * Classe abstraite donnant accès à des primitives de base pour l'interrogation d'un base Redis
    * 
    * 
    *
    */   
   abstract class MiaNagiosPluginRedisSimple extends MiaNagiosPluginSimple{
      /**
       * Objet redis php
       * @var resource
       */
      protected $redis;            
      
      public function __construct(){   
         parent::__construct();
         trigger_error("start",E_USER_NOTICE);
         $this->_connect();                       
         trigger_error("end",E_USER_NOTICE);
      }
      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setInputs()
       */
      protected function setInputs(){
         trigger_error('start',E_USER_NOTICE);
         $this->addInput('host','/(\-H)\s+([^\s]+)/');
         $this->addInput('port','/(\-p)\s+([^\s]+)/',true,6379);
         $this->addInput('timeout','/(\-t)\s([^\s]+)/',true,2);
         trigger_error('end',E_USER_NOTICE);
      }
      
      /**
       * Méthode de connection à la base redis
       * 
       * @return void
       */      
      final private function _connect(){
         trigger_error('start',E_USER_NOTICE);
	 $this->redis=new Redis();
                try{
                        $this->redis->pconnect($this->getInput('host'),$this->getInput('port'),$this->getInput('timeout'));
                } catch(Exception $e){
			trigger_error('redis connection error',E_USER_ERROR);
                }

         trigger_error('end',E_USER_NOTICE);
      } 
   }
   
