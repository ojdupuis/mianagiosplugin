#!/usr/local/bin/php5 -q
<?php
   require_once('lib/MiaNagiosPluginSimple.inc.php');

   class MiaNagiosPlugin_CheckVarnish extends MiaNagiosPluginSimple{
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','VARNISH');
         $this->setSpecialProperty('titre_aide','Plugins Nagios de supervision varnish');
         $this->setSpecialProperty('commentaire_aide','');
      }
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */
      protected function setIndicators(){
         $this->addIndicatorSimple('cache_hitratio');
         $this->addIndicatorSimple('client_conn');
	      $this->addIndicatorSimple('sm_bfree');
         $this->addIndicatorSimple('sm_balloc');

      }

      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setInputs()
       */
      protected function setInputs(){
         parent::setInputs();
         $this->addInput('instance','/(\-n)\s([^\s]+)/',true);
      }

      private function getValues($xml){
         foreach($xml->stat as $i=>$obj){
		var_dump($obj->name);
		$tab[$obj->name]=$obj->value;
	 }
	 return $tab;
      }


      protected function coreFunction(){
         trigger_error("start",E_USER_NOTICE);
          if ($this->getInput('instance') != ''){
              $instance="-n ".$this->getInput('instance');
          } else {
              $instance="";pu
          }
          exec('varnishstat -f cache_hit,cache_miss,client_conn,sm_bfree,sm_balloc -x '.$instance,$output);
        trigger_error("varnishstat -f cache_hit,cache_miss,client_conn,sm_bfree,sm_balloc -x ".$instance,E_USER_NOTICE);
	$val1=simplexml_load_string(implode("\n",$output));
	sleep(1);
	$output=null;
	exec('varnishstat -f cache_hit,cache_miss,client_conn,sm_bfree,sm_balloc -x '.$instance,$output);
        $val2=simplexml_load_string(implode("\n",$output));
	$retour['cache_hitratio']=($val2->stat[1]->value-$val1->stat[1]->value)/($val2->stat[1]->value-$val1->stat[1]->value+$val2->stat[2]->value-$val1->stat[2]->value);
	$retour['sm_bfree']=$val2->stat[3]->value;
   $retour['sm_balloc']=$val2->stat[4]->value;
	$retour['client_conn']=$val2->stat[0]->value-$val1->stat[0]->value;
         trigger_error("end",E_USER_NOTICE);
         return $retour;
      }

      protected function StatusInformationFilter($name,$value){
      	  switch($name){
      		case "cache_hitratio":
                          $value="%Hit=".$value;
      		    break;
      		case "sm_bfree":
      			$value=$value/1000000000;
                          $value="MemFree=".sprintf("%.1f",$value)."GB";
      		    break;
            case "sm_balloc":
               $value=$value/1000000000;
                          $value="MemUsed=".sprintf("%.1f",$value)."GB";
                break;
      		case "client_conn":
      		    $value="Conn=".$value;
      	  }
           return $value;
      }

      protected function dataFilter($name,$value){
         switch($name) {
            case 'cache_hitratio':
                  $value=sprintf("%.1f",$value*100);
                  break;
            case 'sm_bfree':
               $value=MiaNagiosPluginFilters::dataFilterPrecision(
                     $name,MiaNagiosPluginFilters::dataFilterUnitToGiga($name,$value),1);
            break;
            case 'sm_balloc':
               $value=MiaNagiosPluginFilters::dataFilterPrecision(
                     $name,MiaNagiosPluginFilters::dataFilterUnitToGiga($name,$value),1);
            break;
            Default:
         }
       return $value;
      }
   }

   $check=new MiaNagiosPlugin_CheckVarnish();
   $check->OutputResult();
