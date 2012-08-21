<?php
error_reporting(E_PARSE && E_ERROR);

define('ok','0');
define('notice','1.5');
define('warning','1');
define('critical','2');
define('unknown','3');

define('authorized_units','s;us;ms;;%;B;KB;MB;TB;c'); // cf  http://nagiosplug.sourceforge.net/developer-guidelines.html#PLUGOUTPUT
define('STATUSTEXTSEPARATOR',"<br>");
define('TMP_PATH','/tmp/nagiospluginscache');
define('ROOT',str_replace("/lib","",dirname(__FILE__)));
 
// On inclus les filtres
require_once('MiaNagiosPluginFilters.inc.php');
/**
 * Fichier de définition de la classe MiaNagiosPlugin
 *
 * @package    systeme
 * @author     Olivier Dupuis
 * @author     $Author$
 * @version    $Revision$, $Date$
 */

/**
 * Classe abstraite servant de fondation au développement d'un plugins Nagios pour la supervision au MIA
 *
 *
 *
 */
abstract Class MiaNagiosPlugin{

   private $special_property=array();

   /**
    * tableau associatif contenant les indicateurs remontes en perfdata  (celui de -w et -c)
    *
    * Il est construit comme suit
    * $this->_data['name' => $name]=array(
    *
    *                          'unit' => $unit,
    *                          'min'  => $min,
    *                          'max'  => $max,
    *                          'warning' => null,
    *                          'critical' => null,
    *                          'position' => $position,
    *                          'status'   => null,
    *                          'message'  => null
    *                  );
    *
    * @var   array
    */

   /**
    * Variable définissant la pause à effectuer en les deux mesures nécessaires au calcul d'un compteur
    *
    * @var integer
    */
   protected $_countersleep=2000000;

   protected $_data=array();

   protected $_preliminary=null;

   /**
    * tableau associatif contenant input parsés de la ligne de commande 
    *
    * sous la forme <nom de l input> => <regexp de parsing de l'input>|<true si il s'agit d un flag comme -v>
    * @var   array
    */
   private $_input;

   /**
    * Status de l'alarme Nagios calculé à l'aide des seuils
    * @var float
    */
   private $_status;

   public $_indicator_parameters;

   /**
    * Commentaire de Status de l'alarme Nagios destiné à décrire l'alerte dans la console Nagios
    * @var string
    */
   private $_text;

   /**
    * Niveau d'indentation pour le mode debug
    * @var integer
    */
   private $_tabulation=0;


   //**********************************************************************************************
   // Methode publiques non surchargeable ,
   //**********************************************************************************************

   /**
    * Méthode de rapatriement de la valeur d'une propriété speciale
    *
    * @param $name nom de la propriété
    * @return mixed valeur de la propriété
    */
   final protected function getSpecialProperty($name){
      return $this->special_property[$name];
   }
   /**
    * Méthode de définition de la valeur d'une propriété spéciale
    * @param $name string nom
    * @param $value mixed valeur
    *
    * @author   Olivier Dupuis
    * @return   void
    */
   final protected function setSpecialProperty($name,$value){
      trigger_error("start",E_USER_NOTICE);
      $this->special_property[$name]=$value;
      trigger_error("set special property $name = $value",E_USER_NOTICE);
      trigger_error("end",E_USER_NOTICE);
   }

   /**
    * Méthode de déclaration d'une propriété spéciale
    * @param $name string nom
    *
    * @author   Olivier Dupuis
    * @return   void
    */
   final protected function defineSpecialProperty($name){
      trigger_error("start",E_USER_NOTICE);
      trigger_error("special property $name définie",E_USER_NOTICE);
      $this->special_property[$name]=null;
      trigger_error("end",E_USER_NOTICE);
   }

   /**
    * Méthode de changement de l'intervalle entre les 2 mesures pour un compteur
    *
    * @param $microtime durée de la pause en 2 interrogation pour les compteurs
    * @return unknown_type
    */
   protected function setCounterSleep($microtime){
      $this->_countersleep=$microtime;
      return true;
   }

   /**
    * Méthode qui déclenche la collecte de donnée mais pas la vérification des seuils ni un quelconque output, pour utilisation dans une classe intermédiaire
    *
    *
    * @author   Olivier Dupuis
    * @return   void
    */
   final public function RunInternal(){
      $this->_checkSpecialProperties();
      $this->setIndicators();
      $this->calculateValues();
      trigger_error("start",E_USER_NOTICE);
      trigger_error("end",E_USER_NOTICE);
   }

   /**
    * Méthode qui déclenche la collecte de donnée 
    *
    *
    * @author   Olivier Dupuis
    * @return   void
    */
   final public function Run(){
      $this->_checkSpecialProperties();
      $this->setIndicators();
      // on ajoute l'option de debug
      trigger_error("start",E_USER_NOTICE);
       
      if ($this->_status !== unknown){
         $this->calculateValues();
         $this->setCriticity();
         $this->_calculateStatus();
      }
      trigger_error("end",E_USER_NOTICE);
   }

   /**
    * Method affichant sur le stderr et stdout le resultat de la collecte au format Nagios
    * @return unknown_type
    */
   final public function outputResult(){
      $this->Run();
      $this->_printOutput();
      $this->_exitCode();
   }

   /**
    * Constructeur de la classe abstraite
    *
    * - Il positionne le error_handler
    * - ajoute les options obligatoires de la ligne de commande (-c -w -h -v)
    * - Met en place la structure de donnée des indicateurs
    * - Met en place les seuils à partir des arguments de la ligne de commande
    *
    * @author   Olivier Dupuis
    * @return   void
    */
   public function __construct(){
      // Ce addInput, par construction, est le seul à ne pas apparaitre dans le mod verbeux 
      $this->addInput('debug','/(\-v)/',true);
      if (null !== set_error_handler(array($this,'_myErrorHandler'))){
         // Le gestionnaire d'erreur a déjà été modifié par un objet MiaNagiosPlugin
         // On le restaure donc (cela permet d'instancier des MiaNagiosPlugin au sein de MiaNagiosPlugin
         restore_error_handler();
      }
      trigger_error("start",E_USER_NOTICE);
      // on active immédiatement le mode debug en parsant les inputs
      $this->_ParseInput();
      $this->setInputs();
       
      // On ajoute les seuils obligatoires
      if (! isset($this->_input['warning'])){
         $this->addInput('warning','/(\-w)\s+([^\s]+)/');
      } else {
         trigger_error('il ne faut pas definir manuellement -w',E_USER_ERROR);
      }
      if (! isset($this->_input['critical'])){
         $this->addInput('critical','/(\-c)\s+([^\s]+)/');
      } else {
         trigger_error('il ne faut pas definir manuellement -c',E_USER_ERROR);
      }
      if (! isset($this->_input['help'])){
         $this->_ParseInput();
         $this->addInput('help','/(\-h)/',true);
      } else {
         trigger_error('il ne faut pas definir manuellement -h',E_USER_ERROR);
      }
      $this->_ParseInput();

      // propriete pour la remontee de l'alerte dans la console nagios, obligatoire
      $this->defineSpecialProperty('intituleStatus');
      $this->defineSpecialProperty('titre_aide');
      $this->defineSpecialProperty('commentaire_aide');
      $this->setSpecialProperties();
      $this->_checkSpecialProperties();
       
      // On traite le cas de l'aide
      if ($this->getInput('help') === true){
         $this->printHelp();
         exit;
      }
      trigger_error("end",E_USER_NOTICE);
   }
    
    
   //**********************************************************************************************
   // Methode Protected publique utilisables dans la classe derivee  mais non surchargeables
   //**********************************************************************************************


   /**
    * Méthode permettant de définir la valeur d'un indicateur (à utiliser dans $this->calculateValues)
    *
    * @author   Olivier Dupuis
    * @param    $name string Nom de l'indicateur
    * @param    $value string valeur de l'indicateur
    * @return   void
    */
   final protected function addData($name,$value){
      trigger_error("addData ".$name." ".$value,E_USER_NOTICE);
      $this->_data[$name]['data']=$value;
   }

   /** Méthode de filtrage de la valeur remontée par les calculs
    *  La méthode peut-être surchargee pour personnaliser l'affichage de la valeur (diviser par un facteur...)
    *
    * @author   Olivier Dupuis
    * @param $name string nom de la donnée à filtrer
    * @param $value any valeur à filtrer
    * @return   any
    */
   protected function dataFilter($name,$value){
      return $value;
   }

   /** Méthode de filtrage du nom de l'indicateur
    *  La méthode peut-être surchargee pour personnaliser l'affichage du nom notamment pour les indicateur snmp indexed
    *
    * @author   Olivier Dupuis
    * @param $name string nom de l'indicateur à filtrer
    * @param $value mixed valeur de l'indicateur à filtrer
    * @return   string
    */
   protected function nameFilter($name,$value){
      return $name;
   }

   /**
    * Methode permettant de definir les options de ligne de commande du script
    *
    * @author   Olivier Dupuis
    * @param    $name string Nom de l'indicateur
    * @param    $regexp string regexp pour le parsing elle doit contenir une option (type -h, ou -v) et une regexp entouree par une parsenthÃ¨se si il ne s'agit pas d'un flag
    * @param    $optional bool l'option est elle optionnelle ou non
    * @param    $defaultvalue string si l'option est optionnelle, quelle est sa valeur par défaut (si c'est applicable, -v par ex n'en a pas)
    * @return   void
    */
   final protected function addInput($nom,$regexp,$optional=false,$defaultvalue=null){
      trigger_error("start",E_USER_NOTICE);
      $this->_input[$nom]['regexp']=$regexp;
      // on récupère la syntaxe de l'option
      if (preg_match('/^\/\('.'\\\\'.'(\-.)\)/',$this->_input[$nom]['regexp'],$output) > 0) {
         $this->_input[$nom]['aide']=$output[1];
         $this->_input[$nom]['flag']=false;
      }
      //On cherche le nombre de parenthèse pour savoir si c'est un flag ou pas
      if (preg_match('/^\/\('.'\\\\'.'\-.\)\/$/',$this->_input[$nom]['regexp'],$output) > 0) {
         $this->_input[$nom]['flag']=true;
      }
      $this->_input[$nom]['optionnel']=$optional;
      $this->_input[$nom]['default']=$defaultvalue;
      trigger_error(serialize($this->_input[$nom]),E_USER_NOTICE);
      trigger_error("end",E_USER_NOTICE);
   }
    
   /**
    * Méthode permettant de récupérer un tableau indexé contenant la liste ordonnée des indicateurs paramétrés (à utiliser dans calculateValues)
    *
    * @author   Olivier Dupuis
    * @return   array  liste des indicateurs
    */
   final protected function getIndicatorsByName($indicator_name=null){
      if ($indicator_name === null){
         return array_keys($this->_data);
      } else {
         foreach ($this->_data as $name => $tab){
            if ($tab['indicator_name'] == $indicator_name){
               $retour[]=$name;
            }
         }
         return $retour;
      }
   }

   /**
    * Méthode permettant de récupérer la valeur d'un input de la ligne de commande par son no
    *
    * @author   Olivier Dupuis
    * @param    $name string Nom de l'input (cf addInput)
    * @return   string  valeur de l'input
    */
   final protected function getInput($name){
      trigger_error("start",E_USER_NOTICE);

      if (isset($this->_input[$name]['valeur'])){
         $retour=$this->_input[$name]['valeur'];
      } else {
         $retour=null;
      }
      trigger_error("$name = $retour",E_USER_NOTICE);
      trigger_error("end",E_USER_NOTICE);
      return $retour;
   }
   
   /**
    * Méthode permettant de modifier la valeur d'un input de la ligne de commande par son no
    *
    * @author   Olivier Dupuis
    * @param    $name string Nom de l'input (cf addInput)
    * @param    $$value string valeur de l'input 
    * @return   void
    */
   final protected function setInput($name,$value){
      trigger_error("start",E_USER_NOTICE);
      $this->_input[$name]['valeur']=$value;
      trigger_error("end",E_USER_NOTICE);
   }
   
   /**
    * Methode de filtre permettant de modifier la valeur d'un input, lorsqu'elle est executée la valeur initiale est connue (accessible via getInput)
    * 
    * @param string $name nom de l'input 
    * 
    */
   protected function inputFilter($name){   	
   }

   /**
    * Méthode permettant de définir un indicateur dont la valeur est calculée par le plugin
    *
    * @author   Olivier Dupuis
    * @param    $name    string Nom de l'indicateur, il sera utilisé dans la console et les graphes nagios
    * @param    $param   string type d'information concernant l'indicateur (name,unit,min,max,warning,critical)
    * @return   type     valeur de l'information concernant l'indicateur
    */
   final protected function getIndicator($name,$param){
      if (isset ($this->_data[$name][$param])){
         return $indicateur[$param];
      }

      trigger_error("L'information $param pour l'indicateur $name n'existe pas",E_USER_ERROR);
      return null;
   }

   /**
    * Méthode permettant de recuperer la valeur d'un indicateur
    *
    * @author   Olivier Dupuis
    * @param    $name    string Nom de l'indicateur, il sera utilisé dans la console et les graphes nagios
    * @return   type     valeur de l'indicateur
    */
   final protected function getData($name){
      return $this->dataFilter($name,$this->_data[$name]['data']);
   }
   /**
    * Ajout de caractère sur la chaine de status
    *
    * @param $text string chaine à ajouter
    * @return pointeur $this
    */
   final protected function addStatusText($text){
      $this->_text.=STATUSTEXTSEPARATOR.$text;
      return $this;
   }

   /**
    * Méthode de gestion des erreurs
    *
    *  Le niveau d'erreur E_USER_NOTICE est utilisé dans le mode verbeux
    *
    * @author   Olivier Dupuis
    * @param    $errno      integer  contient le niveau d'erreur, sous la forme d'un entier.
    * @param    $errstr     string   contient le message d'erreur, sous forme de chaîne.
    * @param    $errfile    string   contient le nom du fichier dans lequel l'erreur a été identifiée.
    * @param    $errline    integer  contient le numéro de ligne à laquelle l'erreur a été identifiée.
    * @param    $errcontext array    un tableau qui pointe sur la table des symboles actifs lors de l'erreur. En d'autres termes, errcontext  contient un tableau avec toutes les variables qui existaient lorsque l'erreur a été déclenchée
    * @return               bool     toujours true pour overrider la gestion des erreur de php
    */
   final public function _myErrorHandler($errno,$errstr,$errfile,$errline,$errcontext){

      switch($errno){
         case E_ERROR:
         case E_PARSE:
         case E_USER_ERROR:
            $status=critical;
            break;
         case E_WARNING:
         case E_USER_WARNING:
            $status=warning;
            break;
         case E_NOTICE:
         case E_USER_NOTICE:
            // On met le status à ok car les notices ne sont pas remontées à nagios
            $status=notice;
         default:

            break;
      }

      if ($this->getInput('debug') === true){
         $color=$this->_getStatusColor($status);
         if (preg_match('/(start|end)/',$errstr) > 0){
            $color="\033[0;33m";
         }
         if (preg_match('/end/',$errstr) > 0){
            $this->_tabulation--;
         }
         $this->_printDebugLine($color,$this->_getStatusDescription($status),$errstr,$errfile,$errline);
         if (preg_match('/start/',$errstr) > 0){
            $this->_tabulation++;
         }
          
      }
      // Un status a ete remonte il faut renvoyer cela a nagios
      if ($status === critical){
         $this->_status=unknown;
         if (isset($this->_text)){
            $this->_text.="<br>";
         }
         $this->_text=$this->_text."'".basename($errfile).":$errline - $errstr' ";
      }
      return true;
   }

   // **************************************************************************************
   // Methode protected a surcharger dans la classe derivee mais non utilisables
   // **************************************************************************************

   /**
    * Méthode permettant de définir un indicateur dont la valeur est calculée par le plugin
    *
    * @author   Olivier Dupuis
    * @param    $name string Nom de l'indicateur, il sera utilisé dans la console et les graphes nagios
    * @return   void
    */
   final protected function addIndicator($name){
      trigger_error("start",E_USER_NOTICE);
      $this->_indicator_parameters[$name]=array();
      trigger_error("end",E_USER_NOTICE);
   }

   /**
    * Setup préliminaire au calcul des valeurs des indicateurs 
    * destiné à recevoir par exemple l'exécution de la requête oracle 
    *
    * @return void
    */
   abstract protected function  _preliminarySetUp();
   /**
    * Calcul préliminaire pour un indicateur nécessaire pour le calcul des valeurs des nom des des valeurs des indicateurs
    *
    * @param $name nom de l'indicateur
    * @return mixed contient les données préliminaires au calcul des valeurs d'un indicateur
    */
   protected function  _preliminaryIndicator($name){
      return $this->getPreliminary($name);
   }
   /**
    * Pour un indicateur donné renvoi la liste des instances (datanames) de l'indicateurs
    * exemple: la liste des nom des filesystemes pour l'indicateur %occuppé d'un FS
    * @param $name nom de l'indicateur
    * @return array tableau indexé
    */
   abstract protected function  _dataNameIndicator($name);
   /**
    * Pour une instance (dataname) d'un l'indicateur
    * exemple: la liste des nom des filesystemes pour l'indicateur %occuppé d'un FS
    * @param $name nom de l'indicateur
    * @return array tableau indexé
    */
   abstract protected function  _dataValueIndicator($dataname);
   /** Méthode à surcharger dans le script final de définition des indicateurs remontés par le plugin
    *
    *  Elle est destinée principalement à accueillir les addIndicator de définition des différents indicateurs
    *
    * @author   Olivier Dupuis
    * @return   void
    */
   abstract protected function setIndicators();

   /**
    * Methode abstraite de définition des propriete speciales 
    * @return void
    */
   abstract protected function setSpecialProperties();

   function getPreliminary($name){
      return $this->_preliminary[$name];
   }


   final private function _setIndicatorUnitsMinMax(){
      trigger_error("start",E_USER_NOTICE);
      foreach ($this->getIndicatorsByName() as $i =>$indicator_name){
         trigger_error("name = $name",E_USER_NOTICE);
         if (preg_match("/^$name\-(.*)$/",$indicator_name,$output)){
            trigger_error("$indicator_name -> ".$output[1]." unit $unit",E_USER_NOTICE);
         }
      }
      trigger_error("end",E_USER_NOTICE);
   }

   /**
    * (non-PHPdoc)
    * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicatorUnit()
    */
   final protected function setIndicatorUnit($name,$unit){
      trigger_error("start",E_USER_NOTICE);
      $this->_indicator_parameters[$name]['unit']=$unit;
      trigger_error("end",E_USER_NOTICE);
      return true;
   }

   /**
    * (non-PHPdoc)
    * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicatorUnit()
    */
   final protected function setIndicatorCounter($name,$bits){
      trigger_error("start",E_USER_NOTICE);
      $this->_indicator_parameters[$name]['counter']=true;
      $this->_indicator_parameters[$name]['counter_bits']=$bits;
      trigger_error("end",E_USER_NOTICE);
      return true;
   }

   final protected function setIndicatorTimeDerived($name){
      trigger_error("start",E_USER_NOTICE);
      if (! $this->_indicator_parameters[$name]['counter']){
         trigger_error("time_derived without counter !",E_USER_NOTICE);
      } else {
         $this->_indicator_parameters[$name]['time_derived']=true;
      }
      trigger_error("end",E_USER_NOTICE);
      return true;
   }

   /**
    * (non-PHPdoc)
    * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicatorMax()
    */
   final protected function setIndicatorMax($name,$max){
      trigger_error("start",E_USER_NOTICE);
      $this->_indicator_parameters[$name]['max']=$max;
      trigger_error("end",E_USER_NOTICE);
      return true;
   }

   /**
    * (non-PHPdoc)
    * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicatorMin()
    */
   final protected function setIndicatorMin($name,$min){
      trigger_error("start",E_USER_NOTICE);
      $this->_indicator_parameters[$name]['min']=$min;
      return true;
   }

   /**
    * Méthode permettant d'inhiber l'affichage des perfdata pour l'indicateur
    *
    * @param $name nom de l'indicateur
    * @return void
    */

   final protected function setIndicatorNoPerfData($name){
      $this->_indicator_parameters[$name]['noperfdata']=true;
   }

   /**
    * Méthode permettant de spécifier l'indicateur comme étant une chaine, les seuils warning et critical s'appliquent alors au nombre de lignes remontées
    *
    * @param $name nom de l'indicateur
    * @return void
    */

   final protected function setIndicatorIsString($name){
      $this->_indicator_parameters[$name]['string']=true;
   }
   // **************************************************************************************
   // Methode protected a surcharger dans la classe derivee mais non utilisables
   // **************************************************************************************
   /**
   * Méthode à surcharger de définition des paramètres de la ligne de commande
   *
   *  Elle est destinée principalement à accueillir les addInputs de définition des différentes paramètres de ligne de commande
   *
   * @author   Olivier Dupuis
   * @return   void
   */
   protected function setInputs(){
   }


   /** Méthode d'affichage du message d'alerte pour un indicateur
    *  La méthode peut-être surcharger pour personnaliser l'affichage
    *  Pour certains indicateurs, la valeur peut être en partie calculée dans le setIjndicator (les données snmp indexées par exemple)
    * @author   Olivier Dupuis
    * @param $name string nom de l'indicateur
    * @param $value mixed valeur de l'indicateur
    * @return   void
    */
   protected function StatusInformationFilter($name,$value){
      return "$name=".$value;
   }



   // *************************************************************************************
   // Methodes privees
   // *************************************************************************************

   /**
    * Methode permettant de definir le parsing de ligne de commande du script
    *
    * @author   Raphaële Decussy 
    * @return   void
    */

   final protected function _ParseInput(){
      trigger_error("start",E_USER_NOTICE);
      $args=implode($_SERVER['argv']," ");
      foreach ($this->_input as $nom => $tmp){
         trigger_error("regexp ".$this->_input[$nom]['regexp'],E_USER_NOTICE);
         if (preg_match($this->_input[$nom]['regexp'],$args,$output) > 0){
            if ($this->_input[$nom]['flag'] !== true){
               trigger_error("Option $nom (pas flag)",E_USER_NOTICE);
               // Il s'agit d'un parametre de type -H <ip de l'host>
               trigger_error("parsing input de name=$nom",E_USER_NOTICE);
               if (! isset($output[2])){
                  trigger_error("Parsing de l'input incorrect, la regexp du addinput doit être de la forme /(\-H)\s([^\s]+)\s/ or on a ".$this->_input[$nom]['regexp'],E_USER_ERROR);
               }
               $this->_input[$nom]['valeur']=$output[2];
               trigger_error("parsing input de name=$nom value=".$output[2],E_USER_NOTICE);
            } else {
               // Il s'agit d'un flag
               $this->_input[$nom]['valeur']=true;
            }
            trigger_error($nom,E_USER_NOTICE);
         } elseif (! $this->_input[$nom]['optionnel']){
            trigger_error("input $nom manquant !",E_USER_ERROR);
         } else {
            trigger_error("input optionnel $nom manquant.",E_USER_NOTICE);
            if ($this->_input[$nom]['default'] !== null){
               $this->_input[$nom]['valeur']=$this->_input[$nom]['default'];
               trigger_error("input optionnel $nom valeur par defaut ".$this->_input[$nom]['default'],E_USER_NOTICE);
            }
         }
         $this->inputFilter($nom);
      }
      trigger_error("end",E_USER_NOTICE);
   }
   /**
    * Méthode de vérification des propriété speciales
    *
    * @return void
    */
   final private function _checkSpecialProperties(){
      foreach ($this->special_property as $name => $value){
         if ($value === null) {
            trigger_error("Propriete speciale $name non définie dans setSpecialProperties",E_USER_ERROR);
         }
      }
   }


   /** Méthode privee de mise a jours du status d'un indicateur de retour et de son commentaire
    *
    * @author   Olivier Dupuis
    * @param $errno  integer  contient la criticite Nagios, sous la forme d'un entier.
    * @param $name   string   nom de l'indicateur dont le status est mis à jour 
    * @return   void
    */
   public function _setIndicatorStatus($errno,$name){
      $this->_data[$name]['status']=$this->statusFilter($errno,$name);
   }

   /**
    * Méthode public surchargeable pour forcer le status d'un indicateur
    * @param integer $errno
    * @param string $name
    * @return void
    */
   public function statusFilter($errno,$name){
      return $errno;
   }

   /**
    * Méthode de calcul de la valeurs des datas d'un indicateurs
    * @param $name nom de l'indicateur
    * @return void
    */
   private function _calculateValue($name){
      trigger_error("start",E_USER_NOTICE);
      trigger_error("indicateur $name",E_USER_NOTICE);
      $this->_preliminary[$name]=$this->_preliminaryIndicator($name);
      $tab_name=$this->_dataNameIndicator($name);
      $tab_value=$this->_dataValueIndicator($name);
      trigger_error("$name indexé ",E_USER_NOTICE); 
      foreach ($tab_name as $key => $dataname){
         if ((count($this->_indicator_parameters) > 1) && (count($tab_name) > 1)){
            trigger_error("plusieurs indicateurs, on préfixe par $name",E_USER_NOTICE); 
            $perfdata_name=$name.".".$dataname;
         } else {
            $perfdata_name=$dataname;
         }
         trigger_error("le nom de la data est donc $perfdata_name ",E_USER_NOTICE);
         /*if (! isset($this->_data[$perfdata_name])){
          $this->addIndicator($perfdata_name);
          }*/
         foreach ($this->_indicator_parameters[$name] as $param_name => $param_value){
            $_data[$perfdata_name][$param_name]=$param_value;
         }
         $_data[$perfdata_name]['indicator_name']=$name;
         trigger_error("Data $perfdata_name key=$key valeur=".$this->dataFilter($perfdata_name,$tab_value[$key]));
         //ODU $_data[$perfdata_name]['data']=$this->dataFilter($perfdata_name,$tab_value[$key]);
         $_data[$perfdata_name]['data']=$tab_value[$key];
      }
       
      trigger_error("end",E_USER_NOTICE);
      return $_data;
   }

   /** Méthode de calcul des valeurs des indicateurs
    *
    * C'est l'intelligence du plugin proprement dite
    *
    * @author   Olivier Dupuis
    * @return   void
    */
   final private function calculateValues(){
      trigger_error("start",E_USER_NOTICE);
      $retour=$this->_preliminarySetUp();
      if ($retour !== null){
         $this->_preliminary=$retour;
      }

      foreach ($this->_indicator_parameters as $name => $tab){
         $end=microtime(true);

         $calculated=$this->_calculateValue($name);
         // Si l'indicateur est un counter
         if ($this->getIndicatorParameter($name,'counter') === true){
            $old=$calculated;
            foreach ($calculated as $dataname => $tab){
               // On rapatrie les valeur stockees
               $cache=$this->_getCounterCache($dataname);
               //
               if ($cache !== false){
                  $old[$dataname]['data']=$cache['value'];
                  trigger_error("name=$name old=".$old[$dataname]['data']." new=".$calculated[$dataname]['data'],E_USER_NOTICE);
                  // on stocke de suite la valeur nouvelle
                  $this->_storeCounterCache($dataname,$calculated[$dataname]['data']);
                  trigger_error("valeur de $dataname from cache ".$cache['value'],E_USER_NOTICE);
                  if ($calculated[$dataname]['data'] < $old[$dataname]['data']){
                     $calculated[$dataname]['data']=pow(2,$this->getIndicatorParameter($name,'counter_bits'))-$old[$dataname]['data']+$calculated[$dataname]['data'];
                  } else {
                     $calculated[$dataname]['data']=$calculated[$dataname]['data']-$old[$dataname]['data'];
                  }
                   
                  // Si le counter est time_derived
                  if ($this->getIndicatorParameter($name,'time_derived') === true){
                     trigger_error("indicateur $name est un compteur time derived end=$end start=".$cache['time'],E_USER_NOTICE);
                     $calculated[$dataname]['data']=$calculated[$dataname]['data']/($end - $cache['time']);
                  }
               } else {
                  // on stocke de suite la valeur nouvelle
                  $this->_storeCounterCache($dataname,$calculated[$dataname]['data']);
                  // Rien ne se trouvait dans le cache
                  $calculated[$dataname]['data']=null;
                   
               }

            }
         }
         //$this->_data=array_merge($this->_data,$calculated);
         // on n'utilise pas array_merge car les indexes numéroiques sont réindexés
         if (is_array($calculated)){
            foreach ($calculated as $key => $value){
               $this->_data[$key]=$value;
            }
         }
      }
      trigger_error("end",E_USER_NOTICE);
   }

   /**
    * Méthode de création du nom de fichier de cache pour les compteurs
    *
    * @param $dataname nom de la data a stocker
    * @return string
    */
   final private function _makeCounterHash($dataname){
      if (! is_dir (TMP_PATH)){
         if (mkdir(TMP_PATH) === false){
            trigger_error("Impossible de créer le repertoire ".TMP_PATH,E_USER_ERROR);
         }
      }
      return TMP_PATH."/".md5(serialize($_SERVER['argv']).$dataname);
   }

   /**
    * Méthode de stockage d'une valeur de counter
    *
    * @param $name nom de la data à stocker (instance d'un indicator)
    * @param $value valeur de la data
    * @return void
    */
   final private function _storeCounterCache($name,$value){
      trigger_error("start",E_USER_NOTICE);
      // Le hash pour reconnaire le compteur sera fait sur $argv qui identifie de façon unique le compteur
      $data['value']=$value;
      $data['time']=microtime(true);
      if (file_put_contents($this->_makeCounterHash($name),serialize($data)) === false){
         trigger_error("Impossible de créer le fichier de cache du compteur $name",E_USER_ERROR); 
      }
       
      trigger_error("end",E_USER_NOTICE);
   }

   /**
    * Méthode de stockage d'une valeur de counter
    *
    * @param $name nom de la data à stocker (instance d'un indicator)
    * @return array tableau 'value' => value, 'time' => timestamp
    */
   final private function _getCounterCache($name){
      trigger_error("start",E_USER_NOTICE);
      // Le hash pour reconnaire le compteur sera fait sur $argv qui identifie de façon unique le compteur
      $data=file_get_contents($this->_makeCounterHash($name));

      if ( $data === false ){
         trigger_error("Impossible de lire le fichier de cache du compteur $name",E_USER_WARNING);
      } else {
         $data=unserialize($data);
      }
       
      trigger_error("end",E_USER_NOTICE);
      return $data;
   }

   /** Méthode protected de parsing des input obligatoires criticity et warning
    *
    *  Elle met a jours le tableau $this->_input
    *
    * @author   Olivier Dupuis
    * @return   void
    */
   protected function setCriticity(){
      trigger_error("start",E_USER_NOTICE);
      if (($this->getInput('warning') !== null ) && ($this->getInput('critical') !== null )){

         // On verifie qu'il ne s'agit pas de seuils generiques
         if (preg_match('/^\*([\-\.0-9]+)$/',$this->getInput('warning'),$matche)){
            trigger_error("setCriticity warning seuils generiques",E_USER_NOTICE);
            $seuil=$matche[1];
            foreach($this->getIndicatorsByName() as $name){
               $tab['warning'][]=$seuil;
            }
         } else {
            $tab['warning']=split(",",$this->getInput('warning'));
         }
         // On verifie qu'il ne s'agit pas de seuils generiques
         if (preg_match('/^\*([\-\.0-9]+)$/',$this->getInput('critical'),$matche)){
            trigger_error("criticity seuils generiques",E_USER_NOTICE);
            $seuil=$matche[1];
            foreach($this->_data as $name => $indicateur){
               $tab['critical'][]=$seuil;
            }
         } else {
            $tab['critical']=split(",",$this->getInput('critical'));
         }

         // On verifie que les seuils sont determines pour tous les indicateurs

         foreach($this->getIndicatorsByName() as $position => $name){
            if (! isset($tab['warning'][$position])){
               trigger_error("Le seuil warning pour ".$indicateur['name']." (le #".($position+1).") non definis",E_USER_ERROR);
            } else {
               $this->_data[$name]['warning']=$tab['warning'][$position];
            }

            if (! isset($tab['critical'][$position])){
               trigger_error("Le seuil critical pour ".$name." (le #".($position+1).") non defini",E_USER_ERROR);
            } else {
               $this->_data[$name]['critical']=$tab['critical'][$position];
            }
         }
         trigger_error('setCriticity les seuils warning et criticity sont ok',E_USER_NOTICE);
      } else {
         trigger_error('Fatal. Il manque les seuils warning et criticity',E_USER_ERROR);
      }
      trigger_error("end",E_USER_NOTICE);
   }

   private function _calculateStatusStringIndicator($name){
      trigger_error("start",E_USER_NOTICE);
      $nb=0;
      foreach($this->_data as $data_name => $indicateur){
         if ( ($indicateur['indicator_name'] === $name) && ($data_name !== '')){
            if (!is_string($indicateur['data'])){
               trigger_error('indicateur '.$name.' ('.$data_name.') n est pas un chaine',E_USER_ERROR);
            }
            $nb++;
         }
      }
       
      trigger_error("$nb occurrences ou lignes ".serialize($indicateur['data']),E_USER_NOTICE);
      // Si aucune ligne n'a été remontée on va chercher la ligne à la clef "".
      if ($nb == 0){
         $warning=$this->_data['']['warning'];
         $critical=$this->_data['']['critical'];
      } else {
         $warning=$this->getIndicatorParameter($name,'warning');
         $critical=$this->getIndicatorParameter($name,'critical');
      }
      trigger_error("nb =$nb w=$warning c=$critical name=$name",E_USER_NOTICE);
      if ($warning <= $critical){
         if ($nb >= $critical){
            $status=critical;
         } elseif ($nb >= warning){
            $status=warning;
         } else {
            $status=ok;
         }
      } else {
         if ($nb <= $critical){
            $status=critical;
         } elseif ($nb <= warning){
            $status=warning;
         } else {
            $status=ok;
         }
      }
      foreach($this->_data as $data_name => $indicateur){
         if ($indicateur['indicator_name'] === $name){
            $this->_setIndicatorStatus($status,$data_name);
         }
      }
      trigger_error("end",E_USER_NOTICE);
   }

   private function _calculateStatusNumericalIndicator($name){
      foreach($this->_data as $data_name => $indicateur){
         trigger_error('indicateur '.$data_name.' ',E_USER_NOTICE);
         if ($indicateur['indicator_name'] === $name){
            //trigger_error('l\'indicateur '.$data_name." n'est pas une valeur numérique",E_USER_ERROR);
            if ($this->getData($data_name) !== null){
               if (!is_numeric($indicateur['data'])){

               }
               if ($indicateur['warning']<=$indicateur['critical']){
                  trigger_error($data_name." croissant",E_USER_NOTICE);
                  # Croissant
                  if ($this->getData($data_name) >=$indicateur['critical'])
                  {
                     #Critical
                     $this->_setIndicatorStatus(critical,$data_name);
                     trigger_error($data_name." critical ".$this->_data[$data_name]['data']." >= ".$indicateur['critical'],E_USER_NOTICE);

                  } else {
                     if ($this->getData($data_name) >=$indicateur['warning'])
                     {
                        # warning
                        $this->_setIndicatorStatus(warning,$data_name);
                        trigger_error($data_name." warning",E_USER_NOTICE);
                     }
                     else
                     {
                        #OK
                        $this->_setIndicatorStatus(ok,$data_name);
                     }
                  }
               } else {
                  #decroissant
                  trigger_error("".$data_name." decroissant",E_USER_NOTICE);
                  if ($this->getData($data_name) <= $indicateur['critical']){
                     #Critical
                     $this->_setIndicatorStatus(critical,$data_name);
                     trigger_error($data_name." critical",E_USER_NOTICE);
                  } else {
                     if ($this->getData($data_name) <= $indicateur['warning'])
                     {
                        # warning
                        $this->_setIndicatorStatus(warning,$data_name);
                        trigger_error($data_name." warning",E_USER_NOTICE);
                     }
                     else
                     {
                        #OK
                        $this->_setIndicatorStatus(ok,$data_name);
                     }
                  }
               }
            } else {
               trigger_error("".$data_name." unknown",E_USER_WARNING);
               $this->_setIndicatorStatus(unknown,$data_name);
            }
         }
      }
   }
   /** Méthode privee de calcul du status à partir des inputs et des data
    *
    *  Elle met a jours le tableau $this->_status et this->_text
    *
    * @author   Olivier Dupuis
    * @return   void
    */
   private function _calculateStatus(){
      $status=ok;
      $texte="";
      trigger_error("start",E_USER_NOTICE);
      foreach ($this->_indicator_parameters as $name => $parameters){
         if ($this->getIndicatorParameter($name,'string') === true){
            $this->_calculateStatusStringIndicator($name);
         } else {
            $this->_calculateStatusNumericalIndicator($name);
         }
      }
       
      // on prend en compte le status issu d'eventuelle erreur
      $data_presente=false;
      if (!isset($this->_status)){
         $this->_status=ok;
         // indicateurs numériques
         foreach ($this->_data as $name => $indicator){
            trigger_error("_outputStatus $name ".$indicator['status'],E_USER_NOTICE);
            if (isset($indicator['status'])){
               $data_presente=true;
            }
            if ($indicator['status'] >= $this->_status){
               trigger_error("_outputStatus superieur",E_USER_NOTICE);
               $this->_status=$indicator['status'];
            }
         }
         if (! $data_presente){
            $this->_status=unknown;
         }


         $this->_status=$this->globalStatusFilter($this->_status);
         
         foreach ($this->_data as $name => $indicator){
            trigger_error("$name ",E_USER_NOTICE);
            if ($indicator['status'] >= $this->_status){

               $this->_text.=" ".$this->StatusInformationFilter($this->nameFilter($name,$this->getData($name)),$this->getData($name));
            }
         }
          

      }
      trigger_error("end",E_USER_NOTICE);
   }
   

   /**
    * Méthode publique qurchargeable pour filtrer le status global
    * 
    * @param integer $status status issus du calcul
    * @return integer
    */
   public function globalStatusFilter($status){
      return $status;
   } 
   /** Méthode privee d'affichage au format Nagios des données PerfData
    *
    * @author   Olivier Dupuis
    * @return   void
    */
   private function _outputPerfdata(){
      trigger_error("start",E_USER_NOTICE);
      $output="";
      foreach ($this->_data as $name => $indicateur){
         if ($this->getIndicatorParameter($this->_data[$name]['indicator_name'],'noperfdata') !== true){
            trigger_error("name=$name",E_USER_NOTICE);
            if ($output !== ""){
               $output.=" ";
            }
             
            $output.=str_replace(' ','_',$this->nameFilter($name,$this->getData($name)))."=".$this->getData($name).$indicateur['unit'].";".$indicateur['warning'].";".$indicateur['critical'].";".$indicateur['min'].";".$indicateur['max'];
         }
      }
       
      trigger_error("start",E_USER_NOTICE);
      return $output;
   }

   /**
    * Méthode de définition d'un paramètre special pour un indicateur (oid,...)
    *
    * @param $name Nom de l'indicator
    * @param $parameter_name Nom du paramètre
    * @param $parameter_value Valeur du paramètre
    * @return true
    */
   final public function setIndicatorParameter($name,$parameter_name,$parameter_value){
      trigger_error("start",E_USER_NOTICE);
      trigger_error("name=$name parameter=$parameter_name value=$parameter_value",E_USER_NOTICE);
      $this->_indicator_parameters[$name][$parameter_name]=$parameter_value;
      trigger_error("end",E_USER_NOTICE);
      return true;
   }

   /**
    * Méthode de rapatriement de la valeur  d'un paramètre speciaux pour un indicateur (oid,...)
    *
    * @param $name Nom de l'indicator
    * @param $parameter_name Nom du paramètre
    * @return mixed valeur du paramète
    */
   final public function getIndicatorParameter($name,$parameter_name){
      if ( (array_key_exists($name,$this->_indicator_parameters))&& (array_key_exists($parameter_name,$this->_indicator_parameters[$name])) ){
         return $this->_indicator_parameters[$name][$parameter_name];
      } else {
         return null;
      }
       

   }

   /** Méthode privee d'affichage au format Nagios du status
    *
    * @author   Olivier Dupuis
    * @return   void
    */
   private function _outputStatus(){
      trigger_error("start",E_USER_NOTICE);
      $output=$this->getSpecialProperty('intituleStatus')." ".$this->_getStatusDescription($this->_status)." - ".$this->_text;
      trigger_error("end",E_USER_NOTICE);
      return $output;

   }
   /** Méthode privee qui retourne l'intitule d'un status au format nagios
    * @author   Olivier Dupuis
    * @param    $status  integer  status au format nagios (cf les define)
    * @return            string   intitule du status
    * @return   void
    */
   private function _getStatusDescription($status){
      switch($status){
         case critical:
            return "CRITICAL";
            break;
         case warning:
            return "WARNING";
            break;
         case notice:
            return "NOTICE";
         case ok:
            return "OK";
            break;
         default:
            return "UNKNOWN";
      }
   }

   /** Méthode privee qui retourne la couleur d'un status au format nagios
    * @author   Olivier Dupuis
    * @param    $status  integer  status au format nagios (cf les define)
    * @return            string   color (posix)
    * @return   void
    */
   private function _getStatusColor($status){
      switch($status){
         case critical:
            return "\033[31m";
            break;
         case warning:
            return "\033[35m";
            break;
         case notice:
         case ok:
            return "\033[0m";
            break;
      }
   }

   /** Méthode privee d'affichage de l'output du plugin sur la sortie standard
    * @author   Olivier Dupuis
    * @return   void
    */
   private function _printOutput(){

      if ($this->_status !== unknown){
         $output=$this->_outputStatus()." | ".$this->_outputPerfdata();
      } else {
         // Si un erreur a ete remontee par trigger_error, alors on ne remonte pas les perfdata
         $output=$this->_outputStatus()." | ";
      }
       
      if ($this->getInput('debug') === true){
         trigger_error("* OUTPUT * ".$output,E_USER_NOTICE);
      } else {
         print($output);
      }
   }

   /** Méthode privee de renvoi du code de retour (pour nagios)
    * @author   Olivier Dupuis
    * @return   void
    */
   private function _exitCode(){
       
      if ($this->_status >= warning){
         exit((int)$this->_status);
      } else {
         // On ne remonte pas le code notice qui est un mode debug
         exit((int)ok);
      }
   }
   /** Méthode pd'affichage d'une ligne de debuggage
    * @author   Olivier Dupuis
    * @param    $color               string   couleur de la ligne de debug (fonction de la criticite)
    * @param    $intitule_criticite  string   intitule de la criticite
    * @param    $errstr              string   contient le message d'erreur, sous forme de chaîne.
    * @param    $errfile             string   contient le nom du fichier dans lequel l'erreur a été identifiée.
    * @param    $errline             integer  contient le numéro de ligne à laquelle l'erreur a été identifiée.
    * @param    $errcontext          array    un tableau qui pointe sur la table des symboles actifs lors de l'erreur. En d'autres termes, errcontext  contient un tableau avec toutes les variables qui existaient lorsque l'erreur a été déclenchée   
    * @return   void
    */
   private function _printDebugLine($color,$intitule_criticite,$errstr,$errfile,$errline){
      $debug=debug_backtrace();
      // Le 3 vient des appels de __printDebugLine, _myErrorHandler,trigger_error
      $debug_last=$debug[3];
      $errstr=$debug_last['function']." - ".$errstr;
      print(date('Y/m/d h:i:s')." - $color".sprintf("%8s",$intitule_criticite)."\033[0m - ".sprintf("%30s",basename($errfile)).":".sprintf("%-4s",$errline)." - ".str_repeat("   ",$this->_tabulation)."$color".$errstr."\033[0m\n");
   }

   /**
    * Methode d'affichage de l'aide automatique
    *
    * @author   Raphaële Decussy
    * @return void
    */
   protected function printHelp(){
      $message='';
      foreach ($this->_input as $inputname => $line) {
          
         // on construit le message d'aide pour chacun des inputs
         if ($line['flag']===true){
            $msg='['.$line['aide'].' '.$inputname.']';
         } else {
            if ($line['optionnel']) {
               $msg='['.$line['aide'].' <'.$inputname.' default='.$line['default'].'>]';
            } else {
               $msg=$line['aide'].' '.'<'.$inputname.'>';
            }
         }

         // on met les commentaires optionnels à la fin, les autres au début
         if ($line['optionnel']) {
            $message=$message.' '.$msg;
         } else {
            $message=' '.$msg.$message;
         }
      }
      print("\n");
      print($this->getSpecialProperty('titre_aide')."\n\n".
            "Usage :\n".
            "-------\n". 
      basename($_SERVER['argv'][0]).$message."\n".
      $this->getSpecialProperty('commentaire_aide'))."\n";
   }
}
