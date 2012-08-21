<?php
 require_once('MiaNagiosPluginIndexed.inc.php');
 
 /**
 * Fichier de définition de la classe MiaNagiosPluginSNMP
 *
 * @package    systeme
 * @author     Olivier Dupuis
 * @author     $Author$
 * @version    $Revision$, $Date$
 */

   /**
    * Classe abstraite donnant accès à des primitives de base pour tester l'age d'un fichier (sémaphore...)
    * 
    * 
    *
    */   
   abstract class MiaNagiosPluginFileAge extends MiaNagiosPluginIndexed{
    
      /**
       * Tableau associatif contenant une liste de chemins (* autorisés) à tester
       * 
       * @var array
       */
      public $_path_list=array();
      
      protected function setindicators(){
         $this->addIndicatorIndexed('age');         
         $this->setIndicatorMin('age',0);
   }

      
      public function _preliminarySetUp(){         
        
         foreach ($this->getSpecialProperty('path_list') as $completepath){
            trigger_error('Path '.$completepath,E_USER_NOTICE);
            
            // On extrait le path et le nom/masque de fichier
            preg_match("/^(.*)\/([^\/]+)$/",$completepath,$res);
            $path=$res[1];
            $masque=$res[2];

            $handledir=opendir($path);
            $trouve=false;
            if ($handledir === false){
               trigger_error('Erreur chemin '.$path,E_USER_ERROR);
            } else {
               while ($file = readdir($handledir)){
                  trigger_error("fichier $path/$file",E_USER_NOTICE);
                  if (preg_match("/^$masque$/",$file) > 0){
                     trigger_error("fichier $path/$file matches the mask",E_USER_NOTICE);
                     $temp['age']["$path/$file"]=$this->_getFileAge("$path/$file");            
                     $trouve=true;
                  }
               }
               closedir($handledir);
               //trigger_error('Fin readdir',E_NOTICE);
            }
            if (! $trouve){
               trigger_error("fichier $path/$file non trouvé",E_USER_WARNING);
               $temp['age'][$completepath]=null;
            }
            //trigger_error('Fin foreach',E_USER_NOTICE);
         }
         //trigger_error("Temp = ".serialize($temp),E_USER_NOTICE);
         return $temp;
      }                                
      
      
      final private function _getFileAge($path){
         $age=time()-filemtime($path);
         trigger_error("$path=$age",E_USER_NOTICE);            
         return $age;
      }
      
  
            
   }
   