#!/usr/local/bin/php5 -q
<?php
   require_once('lib/MiaNagiosPluginRedisSimple.inc.php');
   
   class MiaNagiosPlugin_CheckQueue extends MiaNagiosPluginRedisSimple{        
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','REDIS');
         $this->setSpecialProperty('titre_aide','Plugins Nagios de supervision du nb d element dans une file d attente redis');
         $this->setSpecialProperty('commentaire_aide','');
      }

      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setInputs()
       */
      protected function setInputs(){
         parent::setInputs();
         $this->addInput('queue','/(\-q)\s([^\s]+)/');
      }  

      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */
      protected function setIndicators(){         
         $this->addIndicatorSimple('queue_size'); 
      }


      protected function coreFunction(){
         trigger_error("start",E_USER_NOTICE);
		$redis=new Redis();
		try{
			$info=$this->redis->lSize('queue:'.$this->getInput('queue'));
                        trigger_error('lsize queue:'.$this->getInput('queue').' = '.$info);
		} catch(Exception $e){
			trigger_error('Redis error',E_USER_ERROR);
			$info=array();
		}
         trigger_error("end",E_USER_NOTICE);
         return array('queue_size' => $info);
      } 
   }
   
   $check=new MiaNagiosPlugin_CheckQueue();
   $check->OutputResult();
