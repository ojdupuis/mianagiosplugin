#!/usr/local/bin/php5 -q
<?php
   require_once('lib/MiaNagiosPluginRedisSimple.inc.php');
   
   class MiaNagiosPlugin_CheckRedis extends MiaNagiosPluginRedisSimple{        
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','REDIS');
         $this->setSpecialProperty('titre_aide','Plugins Nagios de supervision serveur Redis');
         $this->setSpecialProperty('commentaire_aide','');
      }

      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */
      protected function setIndicators(){         
         $this->addIndicatorSimple('connected_clients'); 
         $this->addIndicatorSimple('used_memory');
         $this->addIndicatorSimple('connected_slaves');
	 $this->addIndicatorSimple('blocked_clients');
    
      }

      protected function coreFunction(){
         trigger_error("start",E_USER_NOTICE);
		try{
			$info=$this->redis->info();
			trigger_error(serialize($info),E_USER_NOTICE);
			if ($info['role'] != 'master'){
				$info['connected_slaves']=1;
			}
		} catch(Exception $e){
			$info=array();
		}
         trigger_error("end",E_USER_NOTICE);
         return $info;
      } 

      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#globalStatusFilter($status)
       */
      public function globalStatusFilter($status){
         if ($status == unknown){
            return critical;
         } else {
            return $status;
         }
      }

      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#globalStatusFilter($status)
       */
      public function dataFilter($name,$value){
	switch($name){
		case 'used_memory': $value=sprintf("%.0f",$value/1024/1024);
		break;
	}
	return $value;
      }
      
   }
   
   $check=new MiaNagiosPlugin_CheckRedis();
   $check->OutputResult();
