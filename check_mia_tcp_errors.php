#!/usr/bin/php -q
<?php
   require_once('lib/MiaNagiosPluginSNMPSimpleCompteur.inc.php');

   class MiaNagiosPlugin_CheckTCP extends MiaNagiosPluginSNMPSimpleCompteur{
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','TCP');
         $this->setSpecialProperty('titre_aide','Plugins Nagios de debit sortant du FireWall');
         $this->setSpecialProperty('commentaire_aide','');
      }
      protected function setInputs(){
         parent::setInputs();
         // différence max en pourcentage de volumetrie par rapport à la sauvegarde equivalente precedente
         $this->addInput('metrique','/(\-d)\s([^\s]+)/');
      }
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */
      protected function setIndicators(){
	switch ($this->getInput('metrique')){
		case 'activeopen':
			$oid='5.0';
		break;
		case 'passiveopen':
			$oid='6.0';
		break;
                case 'attemptfails':
			$oid='7.0';
		break;
		case 'estabresets':
			$oid='8.0';
		break;
		case 'currestab':
			$oid='9.0';
		break;
		case 'insegs':
			$oid='10.0';
		break;
		case 'outsegs':
			$oid='11.0';
		break;
		case 'retranssegs':
			$oid='12.0';
		break;
		default:
			trigger_error('Metrique non reconnue',E_USER_ERROR);
	}
         	$this->addIndicatorSnmpSimpleCompteur('tcp','.1.3.6.1.2.1.6.'.$oid,true,$this->setCounterSleep(10000000));
         $this->setIndicatorMin('tcp',0);
         }

      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#dataFilter()
       */

      protected function dataFilter($name,$value){
         $value=$value/10;
         $value=sprintf("%.1f",$value);
         return $value;
         }

      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#StatusInformationFilter()
       */

      protected function StatusInformationFilter($name,$value){
         return $name."=".$value." ".$this->getInput('metrique')."/s";
      }

   }

   $check=new MiaNagiosPlugin_CheckTCP();
   $check->OutputResult();
