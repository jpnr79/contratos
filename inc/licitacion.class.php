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

class PluginContratosLicitacion extends CommonDBTM {
   public bool $dohistory = true;
   public string $table = 'glpi_plugin_contratos_licitacions';
   public string $type  = 'PluginContratosLicitacion';
   // Heredar los permisos del objeto contract
   public static string $rightname = 'contract';

   public static function getTypeName($nb = 0) {
      return __('Importes licitación', 'contratos');
   }

   public static function canView(): bool {
      return Session::haveRight(self::$rightname, READ);
   }

   public static function canCreate(): bool {
      return Session::haveRight(self::$rightname, UPDATE);
   }

   public function canViewItem(): bool {
      if (!Session::haveAccessToEntity($this->getEntityID())) {
         return false;
      }
      return Session::haveRight(self::$rightname, READ);
   }

   public function canCreateItem(): bool {
      if (!Session::haveAccessToEntity($this->getEntityID())) {
         return false;
      }
      return Session::haveRight(self::$rightname, UPDATE);
   }

   public function canUpdateItem(): bool {
      if (!Session::haveAccessToEntity($this->getEntityID())) {
         return false;
      }
      return Session::haveRight(self::$rightname, UPDATE);
   }

   function canDeleteItem(): bool {

      if (!Session::haveAccessToEntity($this->getEntityID())) {
         return false;
      }
      return (Session::haveRight(self::$rightname, CREATE));
   } 

   function canPurgeItem(): bool {

      if (!Session::haveAccessToEntity($this->getEntityID())) {
         return false;
      }
      return (Session::haveRight(self::$rightname, CREATE));
   }    

  
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      // can exists for template
      if (($item->getType() == 'Contract')
          && Contract::canView()) {

         if ($_SESSION['glpishow_count_on_tabs']) {
			 
            $criteria = [
            "contracts_id" => $item->getID(),
            ];					 
			 
            return self::createTabEntry(self::getTypeName(Session::getPluralNumber()),
                                        countElementsInTable($this->getTable(),$criteria));
         }
         return self::getTypeName(Session::getPluralNumber());
      }

      if (($item->getType() == 'Budget')
          && Budget::canView()) {

         if ($_SESSION['glpishow_count_on_tabs']) {
			 
            $criteria = [
            "budgets_id" => $item->getID(),
            ];					 
			 
            return self::createTabEntry(self::getTypeName(Session::getPluralNumber()),
                                        countElementsInTable($this->getTable(),$criteria));
         }
         return self::getTypeName(Session::getPluralNumber());
      }      

      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      switch ($item->getType()) {
         case 'Contract' :
            self::showForContract($item, $withtemplate);
            break;
         case 'Budget' :
               self::showValuesByEntity($item);
               self::showItems($item);
               break;            
      }
      return true;
   }
   
   function defineTabs($options=array()) {

      $ong = array();
      $this->addDefaultFormTab($ong);
      $this->addStandardTab('PluginContratosLicitacion', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);
      return $ong;
   }
   
    function rawSearchOptions() {
      global $CFG_GLPI, $LANG;

      $tab = array();
      
      $tab[] = [
         'id'                 => 'common',
         'name'               => 'Licitación',
      ];
   
      $tab[] = [
         'id'            => 1,
         'table'         => $this->getTable(),
         'field'         => 'name',
         'name'          => __("Anualidad"), 
		 'searchtype'    => 'contains', 
		 'datatype'    => 'itemlink', 		 
         'itemlink_type' => $this->getType(),
         'massiveaction' => false, // implicit field is id
      ];

      $tab[] = [
         'id'            => 2,
         'table'         => $this->getTable(),
         'field'         => 'id',
         'name'          => __("ID"), 
		 'datatype'    => 'number', 		 
         'massiveaction' => false, // implicit field is id
      ];

      $tab[] = [
         'id'            => 16,
         'table'         => $this->getTable(),
         'field'         => 'comment',
         'name'          => __("Comments"), 
		 'datatype'    => 'text', 		 
         'massiveaction' => false, // implicit field is id
      ];
	  
      $tab[] = [
         'id'            => 12,
         'table'         => $this->getTable(),
         'field'         => 'begin_date',
         'name'          => __("Begin date"), 
		 'datatype'    => 'datetime', 		 
      ];

      $tab[] = [
         'id'            => 10,
         'table'         => $this->getTable(),
         'field'         => 'end_date',
         'name'          => __("End date"), 
		 'datatype'    => 'datetime', 		 
      ];
	  
      $tab[] = [
         'id'            => 14,
         'table'         => $this->getTable(),
         'field'         => 'cost',
         'name'          => __("Importe"), 
		 'datatype'    => 'decimal', 		 
      ];	  

      $tab[] = [
         'id'            => 18,
         'table'         => 'glpi_budgets',
         'field'         => 'name',
         'name'          => _n('Budget', 'Budgets', 1), 
		 'datatype'    => 'dropdown', 		 
      ];
	  
      $tab[] = [
         'id'            => 80,
         'table'         => 'glpi_entities',
         'field'         => 'completename',
         'name'          => __('Entity'), 
		 'datatype'    => 'dropdown', 
		 'massiveaction' => false, // implicit field is id		 
      ];


      return $tab;
   }   
   
   static function showForContract(\CommonGLPI $contract, $withtemplate='') {
      global $DB, $CFG_GLPI;

      $ID = $contract->fields['id'];

      if (!$contract->getFromDB($ID)
          || !$contract->can($ID, READ)) {
         return false;
      }
      $canedit = $contract->can($ID, UPDATE);

      echo "<div class='center'>";

      $query = "SELECT *
                FROM `glpi_plugin_contratos_licitacions`
                WHERE `contracts_id` = '$ID'
                ORDER BY `begin_date`";

      $rand   = mt_rand();

      if ($canedit) {
         echo "<div id='viewlicitacion".$ID."_$rand'></div>\n";
         echo "<script type='text/javascript' >\n";
         echo "function viewAddLic".$ID."_$rand() {\n";
         $params = array('type'         => __CLASS__,
                         'parenttype'   => 'Contract',
                         'contracts_id' => $ID,
                         'id'           => -1);
         Ajax::updateItemJsCode("viewlicitacion".$ID."_$rand",
                                $CFG_GLPI["root_doc"]."/ajax/viewsubitem.php", $params);
         echo "};";
         echo "</script>\n";
         echo "<div class='center firstbloc'>".
               "<a class='vsubmit' href='javascript:viewAddLic".$ID."_$rand();'>";
         echo __('Nuevo coste de licitación')."</a></div>\n";
      }

      if ($result = $DB->query($query)) {
         echo "<table class='tab_cadre_fixehov'>";
         echo "<tr><th colspan='6'>".self::getTypeName($DB->numrows($result))."</th></tr>";

         if ($DB->numrows($result)) {
            echo "<tr><th>".__('Anualidad')."</th>";
            echo "<th>".__('Begin date')."</th>";
            echo "<th>".__('End date')."</th>";
            echo "<th>".__('Partida')."</th>";
            //[INICIO] [CRI] JMZ18G CAMBIOS CORE -	Incluir campo “Proyecto”
            echo "<th>".__('Project')."</th>"; 
            //[FINAL] [CRI] JMZ18G CAMBIOS CORE -	Incluir campo “Proyecto”              
            echo "<th>".__('Importe')."</th>";
            echo "</tr>";

         Session::initNavigateListItems(__CLASS__,
                              //TRANS : %1$s is the itemtype name,
                              //        %2$s is the name of the item (used for headings of a list)
                                        sprintf(__('%1$s = %2$s'),
                                                Contract::getTypeName(1), $contract->getName()));

            $total = 0;
            while ($data = $DB->fetchAssoc($result)) {
               echo "<tr class='tab_bg_2' ".
                     ($canedit
                      ? "style='cursor:pointer' onClick=\"viewEditCost".$data['contracts_id']."_".
                        $data['id']."_$rand();\"": '') .">";
               $name = (empty($data['name'])? sprintf(__('%1$s (%2$s)'),
                                                      $data['name'], $data['id'])
                                            : $data['name']);
               echo "<td>";
               printf(__('%1$s %2$s'), $name,
                        Html::showToolTip($data['comment'], array('display' => false)));
               if ($canedit) {
                  echo "\n<script type='text/javascript' >\n";
                  echo "function viewEditCost" .$data['contracts_id']."_". $data["id"]. "_$rand() {\n";
                  $params = array('type'         => __CLASS__,
                                  'parenttype'   => 'Contract',
                                  'contracts_id' => $data["contracts_id"],
                                  'id'           => $data["id"]);
                  Ajax::updateItemJsCode("viewlicitacion".$ID."_$rand",
                                         $CFG_GLPI["root_doc"]."/ajax/viewsubitem.php", $params);
                  echo "};";
                  echo "</script>\n";
               }
               echo "</td>";
               echo "<td>".Html::convDate($data['begin_date'])."</td>";
               echo "<td>".Html::convDate($data['end_date'])."</td>";
               echo "<td>".Dropdown::getDropdownName('glpi_budgets', $data['budgets_id'])."</td>";
               //[INICIO] [CRI] JMZ18G CAMBIOS CORE -	Incluir campo “Proyecto”
               echo "<td class='center'>".$data['project']."</td>";
               //[FINAL] [CRI] JMZ18G CAMBIOS CORE -	Incluir campo “Proyecto”                
               echo "<td class='numeric'>".Html::formatNumber($data['cost'])."</td>";                
               $total += $data['cost'];
               echo "</tr>";
               Session::addToNavigateListItems(__CLASS__, $data['id']);
            }
            echo "<tr class='b noHover'><td colspan='4'>&nbsp;</td>";
            echo "<td class='right'>".__('Total cost').'</td>';
            echo "<td class='numeric'>".Html::formatNumber($total).'</td></tr>';
         } else {
            echo "<tr><th colspan='6'>".__('No item found')."</th></tr>";
         }
         echo "</table>";
      }
      echo "</div><br>";
   }   

   function showForm($ID, $options=array()) {
	global $CFG_GLPI, $DB;
	
	  if ($ID > 0) {
         $this->check($ID, READ);
      } else {
         
         // Create item
         $options['contracts_id'] = $options['parent']->getField('id');
         //$this->check(CREATE, $options);
      }
	  
	  $this->initForm($ID, $options);
      $this->showFormHeader($options);
 
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Anualidad')."</td>";
      echo "<td>";
      echo "<input type='hidden' name='contracts_id' value='".$this->fields['contracts_id']."'>";
    //Html::autocompletionTextField($this,'name');
      echo Html::input('name', ['value' => $this->fields['name']]);
      echo "</td>";
      echo "<td>".__('Importe')."</td>";
      echo "<td>";
      echo "<input type='text' name='cost' value='".Html::formatNumber($this->fields["cost"], true)."'
             size='14'>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>".__('Begin date')."</td>";
      echo "<td>";
	  Html::showDateField("begin_date", array('value' => $this->fields['begin_date']));	 
      echo "</td>";
      //[INICIO] [CRI] JMZ18G CAMBIOS CORE -	centrado de la caja comentarios 
      //$rowspan = 3;
      $rowspan = 4;
      //[FINAL] [CRI] JMZ18G CAMBIOS CORE -	centrado de la caja comentarios 
      echo "<td rowspan='$rowspan'>".__('Comments')."</td>";
      echo "<td rowspan='$rowspan' class='middle'>";
      echo "<textarea cols='45' style='width: 99%;' rows='".($rowspan+3)."' name='comment' >".$this->fields["comment"].
           "</textarea>";
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'><td>".__('End date')."</td>";
      echo "<td>";
	  Html::showDateField("end_date", array('value' => $this->fields['end_date']));	 
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>".__('Partida')."</td>";
      echo "<td>";
      Budget::dropdown(array('value' => $this->fields["budgets_id"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>".__('Project')."</td>";
      echo "<td colspan='3'>";
       //Html::autocompletionTextField($this, 'project');
         echo Html::input('project', ['value' => $this->fields['project']]);
      echo "</td></tr>\n";

      $this->showFormButtons($options);

      return true;
   }


   /**
    * Print the HTML array of value consumed for a budget
    *
    * @return void
   **/
   static function showValuesByEntity($item) {
   global $DB;

   $budgets_id = $item->fields['id'];

   if (!$item->can($budgets_id, READ)) {
      return;
   }

   $licitacion = new PluginContratosLicitacion();
   $total = 0;

   $sum = new QueryExpression(
      "SUM(`cost`) AS sumvalue"
   );


   $iterator=$DB->request([
      'SELECT' => [
         'entities_id',
         $sum
      ],
      'FROM' => $licitacion->getTable(),
      'WHERE' => [
         'budgets_id' => $budgets_id
      ],
      'GROUPBY' => [
         'entities_id',
      ],
      'ORDER' => 'entities_id',
      ]);   

   $colspan = 2;
   echo "<div class='spaced'><table class='tab_cadre_fixehov'>";
   echo "<tr class='noHover'><th colspan='$colspan'>".__('Total estimado en licitación')."</th></tr>";
   echo "<tr><th>".__('Entity')."</th>";   
   //echo "<th>".__('Contract')."</th>";
   echo "<th>".__('Total')."</th>";
   echo "</tr>";

   // get all entities ordered by names
   //[INICIO] [CRI] JMZ18G 30/09/2022 PHP User deprecated function (16384): Order should be defined in criteria! in /var/www/html/glpi957/inc/toolbox.class.php at line 653
   //$allentities = getAllDatasFromTable('glpi_entities', [], true, 'completename');
   $allentities = getAllDataFromTable('glpi_entities', ['ORDER' => 'completename'], true);
   //[FINAL] [CRI] JMZ18G 30/09/2022 PHP User deprecated function (16384): Order should be defined in criteria! in /var/www/html/glpi957/inc/toolbox.class.php at line 653
   while ($data = $iterator->next()) {       
      $total += $data['sumvalue'];
         echo "<tr class='tab_bg_1'>";
         echo "<td class='b'>".Dropdown::getDropdownName('glpi_entities', $data['entities_id'])."</td>";
/*
         echo "<td class='numeric'>";               
         echo Html::formatNumber($data['sumvalue']);
         echo "</td>";
*/          
         echo "<td class='right b'>".Html::formatNumber($data['sumvalue'])."</td>";
         echo "</tr>";
      
   }
      
      echo "<tr class='tab_bg_1'>";
      echo "<td class='right b'>".__('Total')."</td>";

      echo "<td class='numeric b'>".Html::formatNumber($total)."</td>";
      echo "</tr>";

   echo "<tr class='tab_bg_1 noHover'><th colspan='$colspan'><br></th></tr>";
   echo "<tr class='tab_bg_1 noHover'>";
   echo "<td class='right' colspan='".($colspan-1)."'>".__('Total estimado gastado en el presupuesto')."</td>";
   echo "<td class='numeric b'>".Html::formatNumber($total)."</td></tr>";
   if ($_SESSION['glpiactive_entity'] == $item->fields['entities_id']) {
      echo "<tr class='tab_bg_1 noHover'>";
      echo "<td class='right' colspan='".($colspan-1)."'>".__('Total estimado restante en el presupuesto').
            "</td>";
      echo "<td class='numeric b'>".Html::formatNumber($item->fields['value'] - $total).
            "</td></tr>";
   }
   echo "</table></div>";
   echo "<br><br><br>";
}

   /**
    * Print the HTML array of Items on a budget
    *
    * @return void
   **/
   static function showItems($item) {
   global $DB;

   $budgets_id = $item->fields['id'];

   if (!$item->can($budgets_id, READ)) {
      return;
   }

   $licitacion = new PluginContratosLicitacion();
   $contract   = new Contract();
   $params     = ["budgets_id" => $budgets_id];
   $all_cost   = $licitacion->find($params,['id DESC']);


 
   $nb = count($all_cost);

   echo "<div class='spaced'><table class='tab_cadre_fixe'>";
   echo "<tr><th colspan='2'>";
   Html::printPagerForm();
   echo "</th><th colspan='4'>";
   if ($nb == 0) {
      echo __('No associated item');
   } else {
      echo _n('Associated item', 'Associated items', $nb);
   }
   echo "</th></tr>";

      echo "<tr><th>".Contract::getTypeName($nb)."</th>
      <th>".__('Anualidad')."</th>";
      echo "<th>".__('Begin date')."</th>";
      echo "<th>".__('End date')."</th>";
      //[INICIO] [CRI] JMZ18G CAMBIOS CORE -	Incluir campo “Proyecto”
      echo "<th>".__('Project')."</th>"; 
      //[FINAL] [CRI] JMZ18G CAMBIOS CORE -	Incluir campo “Proyecto”            
      echo "<th>".__('Importe')."</th>";
      echo "</tr>";

   $num       = 0;

   foreach ($all_cost as $cost_id => $data) {

         if ($nb) {
  
               echo "<tr class='tab_bg_1'>";
               $name = NOT_AVAILABLE;
               if ($contract->getFromDB($data["contracts_id"])) {
                      $name = $contract->getLink(['additional' => true]);
                  }

               echo "<td class='center";
               echo (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
               echo ">".$name."</td>";                  
             
               echo "<td class='center'>".$data['name'];
               echo "</td><td class='center'>". $data['begin_date']."</td>";
               echo "</td><td class='center'>". $data['end_date']."</td>";
               //[INICIO] [CRI] JMZ18G CAMBIOS CORE -	Incluir campo “Proyecto”
               echo "<td class='center'>".$data['project']."</td>";
               //[FINAL] [CRI] JMZ18G CAMBIOS CORE -	Incluir campo “Proyecto”               
               echo "<td class='numeric b'>".
                        (isset($data["cost"]) ? Html::formatNumber($data["cost"])
                                             :"-");
               echo "</td></tr>";               

         }
         $num += $nb;
      }

   if ($num>0) {
      echo "<tr class='tab_bg_2'>";
      echo "<td class='center b'>".sprintf(__('%1$s = %2$s'), __('Total'), $num)."</td>";
      echo "<td colspan='5'>&nbsp;</td></tr> ";
   }
   echo "</table></div>";
}

   
} 


?>