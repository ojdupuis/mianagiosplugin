<?php


   /**
    * Classe servant à définir plusieurs types de filtres récurrents
    * 
    * 
    *
    */   
   class MiaNagiosPluginFilters {

	   /** Méthode de filtrage de la valeur remontée par les calculs pour convertir des Bits en Mbits
	   * 
	   * @author   Raphaële Decussy   
	   * @param $name string nom de la donnée à filtrer
	   * @param $value any valeur à filtrer
	   * @return   any
	   */
	   
	   static function dataFilterUnitToMega($name,$valeur){	
			return $valeur/1024/1024;		
		}
		
		static function dataFilterUnitToKilo($name,$valeur){
			return $valeur/1024;
		}
      
      static function dataFilterUnitToGiga($name,$valeur){  
         return $valeur/1024/1024/1024;     
      }
		
	   /** Méthode de filtrage de la valeur remontée par les calculs dans d'obtenir le format voulu (nombre après la virgule etc...)
	   * 
	   * @author   Raphaële Decussy   
	   * @param $name string nom de la donnée à filtrer
	   * @param $value any valeur à filtrer
	   * @param $precision integer nombre de chiffre avant la virgule
	   * @param $digits integer 
	   * @return   any
	   */
		
		static function dataFilterPrecision($name,$valeur,$precision,$digits=null){	
			return sprintf("%".$digits.".".$precision."f",$valeur);
		}		
}