#!/usr/local/bin/php5 -q
<?php
   require_once('lib/MiaNagiosPluginSimple.inc.php');

   class MiaNagiosPlugin_CheckApachePhpfpm extends MiaNagiosPluginSimple{
      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */      
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','PHPFPM');
         $this->setSpecialProperty('titre_aide','Plugins Nagios supervision de /supervision/php/index.php5');
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
            $this->addIndicatorSimple('phpfpm');
      }
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */
      protected function coreFunction(){      
         trigger_error("start",E_USER_NOTICE);
         $output=file_get_contents("http://".$this->getInput('host')."/supervision/php/index.php5");
         $retour=1;
         if ($output === false) {            
            trigger_error("couldn't open http//:".$this->getInput('host')."supervision/php/index.php5",E_USER_ERROR);
         }
         else {
            $output = split("\n",$output);
            foreach ($output as $line){
               if (preg_match("/aucune erreur/",$line,$matche) > 0){
                  $retour=0;
               }
            }
         }
         trigger_error("end",E_USER_NOTICE);
         return array('phpfpm' => $retour);
         
      }

   }
   

   
   $check=new MiaNagiosPlugin_CheckApachePhpfpm();
   $check->OutputResult();
