#!/usr/local/bin/php5 -q
<?php
   require_once('lib/MiaNagiosPluginSimple.inc.php');

   class MiaNagiosPlugin_CheckApacheCache extends MiaNagiosPluginSimple{
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','APACHE_CACHE');
         $this->setSpecialProperty('titre_aide','Plugins Nagios de pourcentage d utililisation du cache PHP');
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
      protected function setIndicators(){
         $this->addIndicatorSimple('cache');
         $this->setindicatorUnit('cache','%');
         $this->setIndicatorMin('cache',0);  
      }

      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#coreFunction()
       */      
      protected function coreFunction(){      
         trigger_error("start",E_USER_NOTICE);
         $output=file_get_contents("http://".$this->getInput('host')."/supervision/apc/apc_cache.php5");
         if ($output === false) {            
            trigger_error("couldn't open http://".$this->getInput('host')."/supervision/apc/apc_cache.php5",E_USER_ERROR); 
         }
         else {
               $value=preg_match('/^\s*([0-9\.]+)\s*$/',$output,$matche); 
               $value=$matche[1];
               trigger_error("Le pourcentage de ".$nom." utilis� est : ".$value,E_USER_NOTICE);               
               
                $temp['cache']=$value; 
         }
         trigger_error("end",E_USER_NOTICE);         
         return $temp;
      }   

     /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#StatusInformationFiler()
       */     
      protected function StatusInformationFilter($name,$value){
         return $name."=".$value."%";
      }
   }
   
   $check=new MiaNagiosPlugin_CheckApacheCache();
   $check->OutputResult();
