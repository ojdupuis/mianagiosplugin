#!/usr/local/bin/php5 -q
<?php
   require_once('lib/MiaNagiosPluginSimple.inc.php');
   
   class MiaNagiosPlugin_CheckMem extends MiaNagiosPluginSimple{        
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
         
         $this->addIndicatorSimple('swap_used');
         $this->setIndicatorMin('swap_used',0);
         $this->addIndicatorSimple('mem_free');
         $this->setIndicatorMin('mem_free',0);
         $this->addIndicatorSimple('buffer');
         $this->setIndicatorMin('buffer',0);
         $this->addIndicatorSimple('cache');
         $this->setIndicatorMin('cache',0);
         $this->addIndicatorSimple('swap_in');
         $this->addIndicatorSimple('swap_out');
      }
      
      protected function coreFunction(){
         trigger_error("start",E_USER_NOTICE);
         exec('vmstat 1 2 | tail -1',$output1);
         
         $line=str_replace("\t","\s",$output1[0]);
            trigger_error("output = $line",E_USER_NOTICE);
            if (preg_match_all("/\s+([0-9\.]+)/",$line,$matche) > 0){
               trigger_error("Matched ",E_USER_NOTICE);

               $tab['swap_used']=$matche[1][2];
               $tab['mem_free']=$matche[1][3];
               $tab['buffer']=$matche[1][4];
               $tab['cache']=$matche[1][5];
               $tab['swap_in']=$matche[1][6];
               $tab['swap_out']=$matche[1][7];            
               
            } else {
               trigger_error("RegExp not matched ".$line,E_USER_ERROR);
            }
         exec("uptime | awk '{print $10}'",$output2);
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
   
   $check=new MiaNagiosPlugin_CheckMem();
   $check->OutputResult();
   
   

   