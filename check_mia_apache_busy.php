#!/usr/bin/php -q
<?php
   require_once('lib/MiaNagiosPluginSimple.inc.php');

   class MiaNagiosPlugin_CheckApacheBusy extends MiaNagiosPluginSimple{
      
      private $liste=array('ReqPerSec','BytesPerSec','BytesPerReq','BusyWorkers','IdleWorkers');
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */      
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','APACHE_BUSY');
         $this->setSpecialProperty('titre_aide','Plugins Nagios de rapportant le nombre de process apache busy/idle apache2');
         $this->setSpecialProperty('commentaire_aide','');
      }          
      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setInputs()
       */
      
      public function setInputs(){    
         trigger_error("start",E_USER_NOTICE);
         $this->addInput('host','/(\-H)\s+([^\s]+)/',false);         
         trigger_error("end",E_USER_NOTICE);
      }
      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */
      
      public function setIndicators(){
         foreach ($this->liste as $indic){
            $this->addIndicatorSimple($indic);
         }
      }
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */
      protected function _preliminarySetUp(){      
         trigger_error("start",E_USER_NOTICE);
         $output=file_get_contents("http://".$this->getInput('host')."/x-httpd-server-status?auto");
         if ($output === false) {            
            trigger_error("couldn't open http//:".$this->getInput('host')."/x-httpd-server-status?auto",E_USER_ERROR); 
         }
         else {
            $output = split("\n",$output);
            foreach ($output as $line){
               if (preg_match("/^(".implode($this->liste,"|")."):\s+([0-9\.]+)\s*$/",$line,$matche)){
                  $nom=$matche[1];
                  $value=$matche[2];
                  trigger_error("$nom: $value",E_USER_NOTICE);
                  $temp[$nom]=$value;
               }
            }
         }
         trigger_error("end",E_USER_NOTICE);
         return $temp;
         
      }
       /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#StatusInformationFilter()
       */

      protected function dataFilter($name,$value){ 

         return MiaNagiosPluginFilters::dataFilterPrecision($name,$value,0);               
      }                 
   }
   

   
   $check=new MiaNagiosPlugin_CheckApacheBusy();
   $check->OutputResult();
