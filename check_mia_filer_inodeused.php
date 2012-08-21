#!/usr/local/bin/php5 -q
<?php
   require_once('lib/MiaNagiosPluginSNMPIndexed.inc.php');
   require_once('lib/MiaNagiosPluginIndexed.inc.php');
   
   /**
    * Classe intermédiaire pour récupérer le nom d'inode used et free
    * @author dupuis
    *
    */
   
   class MiaNagiosPlugin_CheckFilerInodeUsedFree extends MiaNagiosPluginSNMPIndexed{   

      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
   	
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','FILER_INODE_USED');
         $this->setSpecialProperty('titre_aide','Plugins Nagios : taux d\'occupation en % des tables des inodes des volumes d\'un filer NetApp');
         $this->setSpecialProperty('commentaire_aide','ERREUR : classe intermédiaire non destinée à être utilisée telle quelle');
      }  
      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */
      
      protected function setIndicators(){         
         $this->addIndicatorSnmpIndexed(
            array(
               'inode_used' => 'inode_used', 
               'inode_free' => 'inode_free'
            ),
            '.1.3.6.1.4.1.789.1.5.4.1.1',
            array(
               'inode_used' => '1.3.6.1.4.1.789.1.5.4.1.7',
               'inode_free' => '1.3.6.1.4.1.789.1.5.4.1.8'
            ),
            '.1.3.6.1.4.1.789.1.5.4.1.2'            
         );
      }
         
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPluginSNMP#print_help()
       */
      
   }
   
class MiaNagiosPlugin_CheckFilerInodePercentUsed extends MiaNagiosPluginIndexed{
   /**
    * Objet intermediaire servant à récupérer les indicateurs utilisés pour construire l'indicateur %inodeused
    * @var unknown_type
    */   
   private $objet_usedfree;
   
   /**
    * (non-PHPdoc)
    * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
    */
   protected function setSpecialProperties(){
      $this->setSpecialProperty('intituleStatus','FILER_INODEUSED');
      $this->setSpecialProperty('titre_aide','Plugins Nagios : taux d\'occupation en % des tables des inodes des volumes d\'un filer NetApp');         
      $this->setSpecialProperty('commentaire_aide','ERREUR : classe intermédiaire non destinée à être utilisée telle quelle');
   }  
   
   protected function setindicators(){
   	$this->addIndicator('inode_used');
   	$this->setIndicatorUnit('inode_used','%');
      $this->setIndicatorMin('inode_used',0);
   }
   
   /**
    * (non-PHPdoc)
    * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
    */
   protected function coreFunction(){   
      trigger_error('start',E_USER_NOTICE);
      
      $this->objet_usedfree=new MiaNagiosPlugin_CheckFilerInodeUsedFree();
      $this->objet_usedfree->RunInternal();
      foreach ($this->objet_usedfree->getIndicatorsByName('inode_used') as $i => $name){
         $array['inode_used'][$name]=
         $inode_used=$this->objet_usedfree->getData($name);
         $inode_free=$this->objet_usedfree->getData(str_replace('inode_used','inode_free',$name));
         if ($inode_free+$inode_used != 0){         
            $array['inode_used'][$name]=$inode_used/($inode_free+$inode_used);
         } else {
            $array['inode_used'][$name]=0;
         }
         
      }
                    
      trigger_error('end',E_USER_NOTICE);
      return $array;   
   }
      
   /**
    * (non-PHPdoc)
    * @see nagios/plugins/lib/MiaNagiosPlugin#StatusInformationFilter($name)
    */
   protected function StatusInformationFilter($name,$value){         
      return "$name=".$value."%";                 
   }
   
   /**
    * (non-PHPdoc)
    * @see nagios/plugins/lib/MiaNagiosPlugin#dataFilter($name, $value)
    */
   protected function dataFilter($name,$value){       
      return MiaNagiosPluginFilters::dataFilterPrecision($name,$value*100,0);                 
   }

   /**
    * (non-PHPdoc)
    * @see nagios/plugins/lib/MiaNagiosPlugin#nameFilter($name)
    */
   protected function nameFilter($name){
      return str_replace('.snapshot','.S',$name);                 
   }

}
       
   $check=new MiaNagiosPlugin_CheckFilerInodePercentUsed();
   $check->OutputResult();

