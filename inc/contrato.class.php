<?php
declare(strict_types=1);
/*
   ------------------------------------------------------------------------
   Autor: Grupo Inforges - Elena Martínez Ballesta
   Fecha: Enero 2016
   Plugin Contratos
   ------------------------------------------------------------------------
*/

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginContratosContrato extends CommonDBTM {
   public bool $dohistory = true;

   // Heredar los permisos del objeto contract
   public static string $rightname = 'contract';
   protected static bool $notable = true;

   /**
    * Should return the localized name of the type
    */
   public static function getTypeName($nb = 0) {
      return __('Contratos - Extra', 'contratos');
   }

   public static function canCreate(): bool {
      return Session::haveRight('plugin_contratos', UPDATE);
   }

   public static function canView(): bool {
      return Session::haveRight('plugin_contratos', READ);
   }
}
?>