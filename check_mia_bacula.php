#!/usr/local/bin/php5 -q
<?php
   require_once('lib/MiaNagiosPluginSimple.inc.php');

   // INPORTANT nagios doit pouvoir executer bconsole en sudo sans passwd

   class MiaNagiosPlugin_CheckBaculaStatus extends MiaNagiosPluginSimple{        
       private $volumetrie;
       private $type;
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','BACULA_CLIENT_STATUS');
         $this->setSpecialProperty('titre_aide','Plugins Nagios du status de la sauvegarde d\'un serveur');
         $this->setSpecialProperty('commentaire_aide','');
      }
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */
      protected function setIndicators(){   
         
         $this->addIndicatorSimple('status');
      }

      protected function setInputs(){
         parent::setInputs();  
         $this->addInput('host','/(\-H)\s([^\s]+)/');
	 // différence max en pourcentage de volumetrie par rapport à la sauvegarde equivalente precedente
	 $this->addInput('max_delta','/(\-d)\s([^\s]+)/');
      }
      
      protected function coreFunction(){
         trigger_error("start",E_USER_NOTICE);
         exec("sudo /usr/sbin/bconsole <<EOF\nstatus client=".$this->getInput('host')."-fd\nEOF",$output);
         trigger_error("output bconsole ".serialize($output),E_USER_NOTICE); 
	 $retour=1;
	 foreach ($output as $line){
		if (preg_match ("/^[\s\t]*([0-9]+)[\s\t]*([a-zA-z]+)[\s\t]*[0-9\,]+[\s\t]*([0-9\.]+)\s*\t*([GMKT])[\s\t]*([a-zA-z]+)[\s\t]*([0-9]+\-([a-zA-Z]+)\-[0-9]+\s[0-9:]+)\s/",$line,$res) > 0){
			trigger_error("Expression matchee",E_USER_NOTICE);
			$job=$res[1];
			$type=$res[2];
			$size=$res[3];
			$unite=$res[4];
			switch($unite){
				case 'K': $size=$size*1024;
				break;
				case 'M': $size=$size*1024*1024;
				break;
				case 'G': $size=$size*1024*1024*1024;
				break;
				case 'T': $size=$size*1024*1024*1024*1024;
				break;
				default: trigger_error('Unite non reconnue pour la taille :'.$unite,E_USER_ERROR);
			}
			$status=$res[5];
			$date=strtotime($res[6]);
			trigger_error("job=$job type=$type size=$size status=$status date=$date",E_USER_NOTICE);
			// On cherhe la date de la journée en cours)
			trigger_error("status=$status date ".date('Ymd',$date)." compare to ".date('Ymd'),E_USER_NOTICE);
			if (date('Ymd',$date) == date('Ymd')&& ($status == 'OK')){
				trigger_error("Ok dernier backup : ".date('Ymd',$date),E_USER_NOTICE);
				$this->type=$type;
				$retour=0;
			}
			$this->volumetrie[$date]=$size;
		} else {
			trigger_error("Expression NON matchee",E_USER_NOTICE);
		}
	 }
	 // on compare la volumetrie de la derniere sauvegarde de meme type pour detecter des variatian
         trigger_error("end",E_USER_NOTICE);
         return array('status' => ok);
      }

      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#globalStatusFilter($status)
       */
      public function globalStatusFilter($status){
	    $dates=array_keys($this->volumetrie);
                         rsort($dates);

		if ((time()-$dates[0]) > 86400){
                                trigger_error("Dernière sauvegarde Err ".date('Ymd H:i:s',$dates[0]),E_USER_WARNING);
                                $status=critical;
                        }
                        else {
                                trigger_error("Dernière sauvegarde Ok ".date('Ymd H:i:s',$dates[0]));
                        }

	      return $status;
      }
   }
   
   $check=new MiaNagiosPlugin_CheckBaculaStatus();
   $check->OutputResult();
   
   

   
