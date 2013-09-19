#!/usr/bin/php -q
<?php
   require_once('lib/MiaNagiosPluginSNMPIndexed.inc.php');
   
   class MiaNagiosPlugin_CheckFilerVolPercentUsed extends MiaNagiosPluginSNMPIndexed{        

   	/**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
      
   	protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','FILER_VOL_PERCENTUSED');
         $this->setSpecialProperty('titre_aide','Plugins Nagios du pourcentage du volume du filer utilisé');
         $this->setSpecialProperty('commentaire_aide','');
   	}
      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */

      protected function setIndicators(){         
         $this->addIndicatorSnmpIndexed('filer_vol_percentused','.1.3.6.1.4.1.789.1.5.4.1.1','.1.3.6.1.4.1.789.1.5.4.1.6','.1.3.6.1.4.1.789.1.5.4.1.2');
         $this->setIndicatorMin('filer_vol_percentused',0);
         $this->setIndicatorUnit('filer_vol_percentused','%');
      }
     
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#nameFilter()
       */
      
      protected function nameFilter($name,$value){
         $name = str_replace('.snapshot' ,'.S',$name);
      return $name;  
      }
      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#StatusInformationFilter()
       */
      
      protected function StatusInformationFilter($name,$value){
        return $name.'='.$value.'%';  
      }
      
   }
   
   $check=new MiaNagiosPlugin_CheckFilerVolPercentUsed();
   $check->OutputResult();
