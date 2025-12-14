<?php
/*
   ------------------------------------------------------------------------
   Autor: Grupo Inforges - Elena Martínez Ballesta
   Fecha: Enero 2016
   Plugin Contratos
   ------------------------------------------------------------------------
*/

//[INICIO] [CRI] JMZ18G CAMBIOS CORE - Heredar los permisos del objeto contract   
//include_once (GLPI_ROOT."/plugins/contratos/inc/profile.class.php");
//[FINAL] [CRI] JMZ18G CAMBIOS CORE - Heredar los permisos del objeto contract   

function plugin_contratos_install() {
   global $DB;

  if (!$DB->TableExists("glpi_plugin_contratos_facturacions")) { 
		$nombre_fichero = GLPI_ROOT . '/plugins/contratos/sql/scripts-install-085.sql';
		if (file_exists($nombre_fichero)){
			Session::addMessageAfterRedirect("Ejecutando fichero ".$nombre_fichero,true);
			$DB->runFile($nombre_fichero);
			Session::addMessageAfterRedirect("Scripts ejecutados",true);
		}else{
			Session::addMessageAfterRedirect("No existe el fichero ".$nombre_fichero,true);
		}
   }
   

      //from 1.0.2' version
   if ($DB->tableExists("glpi_plugin_contratos_licitacions")
      && !$DB->fieldExists("glpi_plugin_contratos_licitacions", "project")) {
         $DB->doQuery("
         ALTER TABLE `glpi_plugin_contratos_licitacions` ADD COLUMN `project` text COLLATE utf8mb4_unicode_ci AFTER `comment`;
         ");
   }

   //[INICIO] [CRI] JMZ18G CAMBIOS CORE - Heredar los permisos del objeto contract   
   //include_once GLPI_ROOT.'/plugins/contratos/inc/profile.class.php';
   //PluginContratosProfile::initProfile();
   //PluginContratosProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);	
   //[FINAL] [CRI] JMZ18G CAMBIOS CORE - Heredar los permisos del objeto contract

   return true;
}


function plugin_contratos_uninstall() {
   global $DB;
    //Delete rights associated with the plugin
   $profileRight = new ProfileRight();
   foreach (PluginContratosProfile::getAllRights() as $right) {
      $profileRight->deleteByCriteria(array('name' => $right['field']));
   }
    PluginContratosProfile::removeRightsFromSession(); 
/* //DESINSTALACIÓN DE TABLAS COMENTADAS POR SEGURIDAD
   $tables = array("glpi_plugin_contratos_licitacions",
                   "glpi_plugin_contratos_facturacions"				   
				   );

   foreach($tables as $table) {
      $DB->query("DROP TABLE IF EXISTS `$table`;");
   }
	*/
    return true;
}


function plugin_contratos_getAddSearchOptions($itemtype) {

  $sopt = array();

  if ($itemtype == 'Contract'){	 	   
	if (Session::haveRight("plugin_contratos", UPDATE)) {
		  
		 $sopt['contratos'] = 'Facturación';
		 
      $sopt[111]['table']              = 'glpi_plugin_contratos_facturacions';
      $sopt[111]['field']              = 'name';
      $sopt[111]['name']               =  __('Nº factura');
      $sopt[111]['datatype']           = 'itemlink';
      $sopt[111]['massiveaction']      = false;
      $sopt[111]['linkfield']      = 'id';
      $sopt[111]['joinparams']     = array('jointype'   => 'child');	  

      $sopt[112]['table']              = 'glpi_plugin_contratos_facturacions';
      $sopt[112]['field']              = 'id';
      $sopt[112]['name']               = __('ID');
      $sopt[112]['massiveaction']      = false;
      $sopt[112]['datatype']           = 'number';
      $sopt[112]['linkfield']      = 'id';
      $sopt[112]['joinparams']     = array('jointype'   => 'child');	  

      $sopt[113]['table']             = 'glpi_plugin_contratos_facturacions';
      $sopt[113]['field']             = 'date';
      $sopt[113]['name']              = __('Fecha factura');
      $sopt[113]['datatype']          = 'datetime';	 
      $sopt[113]['linkfield']      = 'id';
      $sopt[113]['joinparams']     = array('jointype'   => 'child');	  
	  
      $sopt[114]['table']             = 'glpi_plugin_contratos_facturacions';
      $sopt[114]['field']             = 'reg_date';
      $sopt[114]['name']              = __('Fecha de registro');
      $sopt[114]['datatype']          = 'datetime';
      $sopt[114]['linkfield']      = 'id';
      $sopt[114]['joinparams']     = array('jointype'   => 'child');	  
	  
      $sopt[115]['table']             = 'glpi_plugin_contratos_facturacions';
      $sopt[115]['field']             = 'cont_date';
      $sopt[115]['name']              = __('Fecha de contabilización');
      $sopt[115]['datatype']          = 'datetime';
      $sopt[115]['linkfield']      = 'id';
      $sopt[115]['joinparams']     = array('jointype'   => 'child');	  
	  
      $sopt[116]['table']             = 'glpi_plugin_contratos_facturacions';
      $sopt[116]['field']             = 'pay_date';
      $sopt[116]['name']              = __('Fecha de pago');
      $sopt[116]['datatype']          = 'datetime';
      $sopt[116]['linkfield']      = 'id';
      $sopt[116]['joinparams']     = array('jointype'   => 'child');
	  
      $sopt[117]['table']             = 'glpi_plugin_contratos_facturacions';
      $sopt[117]['field']             = 'comment';
      $sopt[117]['name']              = __('Comments');
      $sopt[117]['datatype']          = 'text';
      $sopt[117]['linkfield']      = 'id';
      $sopt[117]['joinparams']     = array('jointype'   => 'child');	  

      $sopt[118]['table']             = 'glpi_plugin_contratos_facturacions';
      $sopt[118]['field']             = 'begin_date';
      $sopt[118]['name']              = __('Fecha inicio periodo facturación');
      $sopt[118]['datatype']          = 'datetime';
      $sopt[118]['linkfield']      = 'id';
      $sopt[118]['joinparams']     = array('jointype'   => 'child');	  

      $sopt[119]['table']             = 'glpi_plugin_contratos_facturacions';
      $sopt[119]['field']             = 'end_date';
      $sopt[119]['name']              = __('Fecha fin periodo facturación');
      $sopt[119]['datatype']          = 'datetime';
	  $sopt[119]['linkfield']      = 'id';
      $sopt[119]['joinparams']     = array('jointype'   => 'child');

      $sopt[120]['table']              = 'glpi_plugin_contratos_facturacions';
      $sopt[120]['field']              = 'cost';
      $sopt[120]['name']               = __('Importe');
      $sopt[120]['datatype']           = 'decimal';
      $sopt[120]['linkfield']      = 'id';
      $sopt[120]['joinparams']     = array('jointype'   => 'child');	  

      $sopt[121]['table']             = 'glpi_contractcosts';
      $sopt[121]['field']             = 'name';
      $sopt[121]['name']              = _n('Importe de adjudicación', 'Importes de adjudicación', 1);
      $sopt[121]['datatype']          = 'dropdown';
      $sopt[121]['linkfield']      = 'id';
      $sopt[121]['joinparams']     = array('jointype'   => 'child');	 
		 
		 
	}
  }
   return $sopt;
}
/*
function plugin_contratos_postinit(){

  var_dump($_REQUEST);
 
/*
array(15) { ["entities_id"]=> string(1) "1" ["is_recursive"]=> string(1) "0" ["contracts_id"]=> string(3) "528" ["name"]=> string(14) "Anualidad 2017" ["cost"]=> string(9) "893330.90" ["_begin_date"]=> string(0) "" ["begin_date"]=> string(0) "" ["comment"]=> string(0) "" ["_end_date"]=> string(0) "" ["end_date"]=> string(0) "" ["budgets_id"]=> string(3) "120" ["project"]=> string(36) "xxx sfsdfsfdsss sscasdvfffddfsdfdddd" ["update"]=> string(7) "Guardar" ["id"]=> string(3) "612" ["_glpi_csrf_token"]=> string(64) "9f84ecfefd96bd4a602b7481f00f17646b3822d0bec57d08dc0bfbfdbe82ff5a" } 
*/
//}

?>