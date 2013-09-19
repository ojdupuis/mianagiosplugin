#!/usr/bin/php -q
<?php
   require_once('lib/MiaNagiosPluginSimple.inc.php');
   
   class MiaNagiosPlugin_CheckCustomWeatherStatus extends MiaNagiosPluginSimple{      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','CUSTOM_WEATHER');
         $this->setSpecialProperty('titre_aide','Plugins Nagios surveillant lètat de custom weather');
         $this->setSpecialProperty('commentaire_aide','Renvoi 0 quand tout est ok, 1 sinon');
      }
       
     
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */
      protected function setIndicators(){ 
            $this->addIndicator('custom_weather');
      }

      	
      protected function _preliminarySetUp(){
      	trigger_error("start",E_USER_NOTICE);
         $ctx = stream_context_create(array(
	         'http' => array(
	            'timeout' => 2)
             )
          );
         
          $tab['custom_weather']=0;
          if (file_get_contents("http://xml.customweather.com/",0,$ctx) === false) {
               $tab['custom_weather']=1;
         
          }
             
                
         trigger_error("end",E_USER_NOTICE); 
         
         return $tab;
      }   
      
      protected function StatusInformationFilter($name,$value){  
      	if ($value==0){       
            return "$name="."Ok";  
      	} else {
      		return "Alert:CustomWeather";
      	}               
      }
      
      
   } 
   
       
 $check=new MiaNagiosPlugin_CheckCustomWeatherStatus;   
 $check->OutputResult();
