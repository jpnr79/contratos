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

class PluginContratosProfile extends Profile {
   public static string $rightname = 'profile';

   public function getTabNameForItem(CommonGLPI $item, int $withtemplate = 0): string {
      if ($item->getType() === 'Profile') {
         return PluginContratosContrato::getTypeName(2);
      }
      return '';
   }

   public static function displayTabContentForItem(CommonGLPI $item, int $tabnum = 1, int $withtemplate = 0): bool {
      global $CFG_GLPI;
      if ($item->getType() === 'Profile') {
         $ID = $item->getID();
         $prof = new self();
         self::addDefaultProfileInfos($ID, [
            'plugin_contratos' => 0,
            'plugin_contratos_facturacion' => 0,
            'plugin_contratos_licitacion' => 0
         ]);
         $prof->showForm($ID);
      }
      return true;
   }
   
   static function createFirstAccess($ID) {
      //85
      self::addDefaultProfileInfos($ID,
                                    array('plugin_contratos' => 23,
										  'plugin_contratos_facturacion' => 23,
										  'plugin_contratos_licitacion' => 23), true);
   }
   
    /**
    * @param $profile
   **/
   static function addDefaultProfileInfos($profiles_id, $rights, $drop_existing = false) {
      global $DB;
      
      $profileRight = new ProfileRight();
      foreach ($rights as $right => $value) {
		  
$criteria = [
"profiles_id" => $profiles_id,
"name" => $right,
];		  
		  
         if (countElementsInTable('glpi_profilerights', $criteria) && $drop_existing) {
            $profileRight->deleteByCriteria(array('profiles_id' => $profiles_id, 'name' => $right));
         }
         if (!countElementsInTable('glpi_profilerights', $criteria)) {
            $myright['profiles_id'] = $profiles_id;
            $myright['name']        = $right;
            $myright['rights']      = $value;
            $profileRight->add($myright);

            //Add right to the current session
            $_SESSION['glpiactiveprofile'][$right] = $value;
         }
      }
   }


   /**
    * Show profile form
    *
    * @param $items_id integer id of the profile
    * @param $target value url of target
    *
    * @return nothing
    **/
   function showForm($profiles_id=0, $openform=TRUE, $closeform=TRUE) {

      echo "<div class='firstbloc'>";
      if (($canedit = Session::haveRightsOr(self::$rightname, array(CREATE, UPDATE, PURGE)))
          && $openform) {
         $profile = new Profile();
         echo "<form method='post' action='".$profile->getFormURL()."'>";
      }

      $profile = new Profile();
      $profile->getFromDB($profiles_id);
      if ($profile->getField('interface') == 'central') {
         $rights = $this->getAllRights();	 
         $profile->displayRightsChoiceMatrix($rights, array('canedit'       => $canedit,
                                                         'default_class' => 'tab_bg_2',
                                                         'title'         => __('General')));
		
		 $rights = $this->getExtraRights();	 
		 $profile->displayRightsChoiceMatrix($rights, array('default_class' => 'tab_bg_2',
                                                    'title'         => __('Permisos adicionales')));

   	  }
       
      if ($canedit
          && $closeform) {
         echo "<div class='center'>";
         echo Html::hidden('id', array('value' => $profiles_id));
         echo Html::submit(_sx('button', 'Save'), array('name' => 'update'));
         echo "</div>\n";
         Html::closeForm();
      }
      echo "</div>";
   }

   static function getAllRights($all = false) {
      $rights = array(
          array('itemtype'  => 'PluginContratosContrato',
                'label'     => _n('Contratos', 'Contratos', 2, 'Contratos'),
                'field'     => 'plugin_contratos'
          ),
      );

      if ($all) {
						   
         $rights[] = array('itemtype' => 'PluginContratosContrato',
                           'label'    =>  __('Acceso a los costes de licitación'),
                           'field'    => 'plugin_contratos_licitacion');
						   					   
         $rights[] = array('itemtype' => 'PluginContratosContrato',
                           'label'    =>  __('Acceso a la facturación'),
						   'field'    => 'plugin_contratos_facturacion');						                
					   					   
	}
      
      return $rights;
   }
 static function getExtraRights($all = false) {

         $rights[] = array('itemtype' => 'PluginContratosContrato',
                           'label'    =>  __('Acceso a los costes de licitación'),
                           'field'    => 'plugin_contratos_licitacion');
						   					   
         $rights[] = array('itemtype' => 'PluginContratosContrato',
                           'label'    =>  __('Acceso a la facturación'),
						   'field'    => 'plugin_contratos_facturacion');						   
	
		return $rights;
   }   

   /**
    * Init profiles
    *
    **/
    
   static function translateARight($old_right) {
      switch ($old_right) {
         case '': 
            return 0;
         case 'r' :
            return READ;
         case 'w':
            return ALLSTANDARDRIGHT + READNOTE + UPDATENOTE;
         case '0':
         case '1':
            return $old_right;
            
         default :
            return 0;
      }
   }
     
   /**
   * Initialize profiles, and migrate it necessary
   */
   static function initProfile() {
      global $DB;
      $profile = new self();

      //Add new rights in glpi_profilerights table
      foreach ($profile->getAllRights(true) as $data) {

$criteria = [
"name" => $data['field'],
];			  
		  
         if (countElementsInTable("glpi_profilerights", $criteria) == 0) {
            ProfileRight::addProfileRights(array($data['field']));
         }
      }
   }

   
  static function removeRightsFromSession() {
      foreach (self::getAllRights(true) as $right) {
         if (isset($_SESSION['glpiactiveprofile'][$right['field']])) {
            unset($_SESSION['glpiactiveprofile'][$right['field']]);
         }
      }
   }
}


?>