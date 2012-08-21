#!/usr/local/bin/php5 -q
<?php
   require_once('lib/MiaNagiosPluginSimple.inc.php');
   
   class MiaNagiosPlugin_CheckCpu extends MiaNagiosPluginSimple{        
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','LINUX_CPU');
         $this->setSpecialProperty('titre_aide','Plugins Nagios de la charge cpu d\'un serveur linux');
         $this->setSpecialProperty('commentaire_aide','');
      }
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */
      protected function setIndicators(){   
         
         $this->addIndicatorSimple('process_running');
         $this->setIndicatorMin('process_running',0);
         $this->addIndicatorSimple('process_blocked');
         $this->setIndicatorMin('process_blocked',0);
         $this->addIndicatorSimple('context');
         $this->setIndicatorMin('context',0);
         $this->addIndicatorSimple('user');
         $this->setIndicatorUnit('user','%');
         $this->addIndicatorSimple('sys');
         $this->addIndicatorSimple('idle');
         $this->setIndicatorUnit('idle','%');         
         $this->addIndicatorSimple('wait');
         $this->addIndicatorSimple('load_1');
         $this->setIndicatorMin('load_1',0);

      }
      
      protected function coreFunction(){
         trigger_error("start",E_USER_NOTICE);
         exec('vmstat 1 2 | tail -1',$output1);
         
         $line=str_replace("\t","\s",$output1[0]);
            trigger_error("output = $line",E_USER_NOTICE);
            if (preg_match_all("/\s+([0-9\.]+)/",$line,$matche) > 0){
               trigger_error("Matched ",E_USER_NOTICE);

               $tab['process_running']=$matche[1][0];
               $tab['process_blocked']=$matche[1][1];
               $tab['context']=$matche[1][11];
               $tab['user']=$matche[1][12];
               $tab['sys']=$matche[1][13];
               $tab['idle']=$matche[1][14];
               $tab['wait']=$matche[1][15];              
               
            } else {
               trigger_error("RegExp not matched ".$line,E_USER_ERROR);
            }
         exec('uptime | sed \'s/.*load.*average..\([0-9\.]*\).*/\1/g\'',$output2);
         $line=str_replace(array(',',"\n"),array('',''),$output2[0]);
         trigger_error("Load ".$line,E_USER_NOTICE);
         $tab['load_1']=$line;   
         
         foreach ($tab as $nom => $val){
            $output[$nom]=$val;      
         }
         
         
         trigger_error("end",E_USER_NOTICE);
         return $output;
      }
      
     
      protected function StatusInformationFilter($name,$value){ 
         switch ($name){
            case 'wait':
            case 'sys':
            case 'idle':
            case 'user':
               return $name."=".$value."%";
            break;
            default:
               return $name."=".$value;
            
         }
         
      }
      
      protected function dataFilter($name,$value){    
         return MiaNagiosPluginFilters::dataFilterPrecision($name,$value,1);
      }
      
      

   }
   
   $check=new MiaNagiosPlugin_CheckCpu();
   $check->OutputResult();
   
   

   