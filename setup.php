<?php
/*
   ------------------------------------------------------------------------
   Autor: Grupo Inforges - Elena Martínez Ballesta
   Fecha: Enero 2016
   Plugin Contratos
   ------------------------------------------------------------------------
*/

// Init the hooks of the plugins -Needed
function plugin_init_contratos() {
   global $PLUGIN_HOOKS, $CFG_GLPI;
   
   $PLUGIN_HOOKS['csrf_compliant']['contratos'] = true;
   //load changeprofile function

   //[INICIO] [CRI] JMZ18G CAMBIOS CORE - Heredar los permisos del objeto contract   
   //$PLUGIN_HOOKS['change_profile']['contratos']   = array('PluginContratosProfile', 'initProfile');  
   //[FINAL] [CRI] JMZ18G CAMBIOS CORE - Heredar los permisos del objeto contract*/
   
   // Params : plugin name - string type - number - class - table - form page
   Plugin::registerClass('PluginContratosContrato');
   //Plugin::registerClass('PluginContratosProfile', array('addtabon' => array('Profile')));
   Plugin::registerClass('PluginContratosLicitacion', array('addtabon' => array('Contract', 'Budget')));
   Plugin::registerClass('PluginContratosFacturacion', array('addtabon' => array('Contract')));
  
   $PLUGIN_HOOKS['use_massive_action']['contratos']=1;
   
   // End init, when all types are registered
   $PLUGIN_HOOKS['post_init']['contratos'] = 'plugin_contratos_postinit';
   
   if (strpos($_SERVER["SCRIPT_FILENAME"], 'contract.form.php') !== false) {

   $PLUGIN_HOOKS['add_css']['contratos'][] = 'css/dataTables.jqueryui.min.css';
   $PLUGIN_HOOKS['add_css']['contratos'][] = 'css/responsive.dataTables.min.css';
   $PLUGIN_HOOKS['add_javascript']['contratos'][] = 'scripts/jquery.dataTables.min.js';
   $PLUGIN_HOOKS['add_javascript']['contratos'][] = 'scripts/dataTables.responsive.min.js';
   $PLUGIN_HOOKS['add_javascript']['contratos'][] = 'scripts/dataTables.jqueryui.min.js';

}
   
} 


// Get the name and the version of the plugin - Needed
function plugin_version_contratos() {

   return array('name'          => _n('Contratos-Extra' , 'Contratos-Extra' ,2, 'Contratos'),
                'version'        => '1.0.3',
                'license'        => 'AGPL3',
                'author'         => '<a href="http://www.carm.es">CARM</a>',
                'homepage'       => 'http://www.carm.es',
                'requirements'   => ['glpi' => ['min' => '11.0', 'max' => '12.0']]);
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_contratos_check_prerequisites() {
	
   if (version_compare(GLPI_VERSION,'9.4','lt')) {
      echo "This plugin requires GLPI >= 9.4";
      return false;
   }	
	
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_contratos_check_config() {
   return true;
}

?>