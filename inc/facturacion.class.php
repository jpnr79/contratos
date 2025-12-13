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

class PluginContratosFacturacion extends CommonDBTM {
   public bool $dohistory = true;
   public string $table = 'glpi_plugin_contratos_facturacions';
   public string $type  = 'PluginContratosFacturacion';

   // Heredar los permisos del objeto contract
   public static string $rightname = 'contract';

   public static function getTypeName($nb = 0) {
      return __('Facturación', 'contratos');
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
	
		if (Session::haveRight(self::$rightname,READ)) { // Permiso adicional dado al perfil "Administracion > perfiles"
			if ($_SESSION['glpishow_count_on_tabs']) {

            $criteria = [
            "contracts_id" => $item->getID(),
            ];				
				
				return self::createTabEntry(self::getTypeName(Session::getPluralNumber()),
                                        countElementsInTable($this->getTable(),$criteria));
			}
			return self::getTypeName(Session::getPluralNumber());
		}
	 }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      switch ($item->getType()) {
         case 'Contract' :
            self::showForContract($item, $withtemplate);
            break;
      }
      return true;
   }
   
   function defineTabs($options=array()) {

      $ong = array();
      $this->addDefaultFormTab($ong);
      $this->addStandardTab('PluginContratosFacturacion', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);
      return $ong;
   }

    function rawSearchOptions() {
      global $CFG_GLPI, $LANG;

      $tab = array();
      
      $tab[] = [
         'id'                 => 'common',
         'name'               => 'Facturación',
      ];
   
      $tab[] = [
         'id'            => 1,
         'table'         => $this->getTable(),
         'field'         => 'name',
         'name'          => __("Title"), 
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
         'id'            => 3,
         'table'         => $this->getTable(),
         'field'         => 'date',
         'name'          => __("Fecha"), 
		 'datatype'    => 'datetime', 		          
         'massiveaction' => false, // implicit field is id		 
      ];

      $tab[] = [
         'id'            => 4,
         'table'         => $this->getTable(),
         'field'         => 'reg_date',
         'name'          => __("Fecha de registro"), 
		 'datatype'    => 'datetime', 		          
         'massiveaction' => false, // implicit field is id		 
      ];	  
	  
      $tab[] = [
         'id'            => 5,
         'table'         => $this->getTable(),
         'field'         => 'cont_date',
         'name'          => __("Fecha de contabilización"), 
		 'datatype'    => 'datetime', 		          
         'massiveaction' => false, // implicit field is id		 
      ];	  
	  
      $tab[] = [
         'id'            => 6,
         'table'         => $this->getTable(),
         'field'         => 'pay_date',
         'name'          => __("Fecha de pago"), 
		 'datatype'    => 'datetime', 		          
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
         'name'          => __("Fecha inicio periodo facturación"), 
		 'datatype'    => 'datetime', 		          
         'massiveaction' => false, // implicit field is id		 
      ];


      $tab[] = [
         'id'            => 10,
         'table'         => $this->getTable(),
         'field'         => 'end_date',
         'name'          => __("Fecha fin periodo facturación"), 
		 'datatype'    => 'datetime', 		          
         'massiveaction' => false, // implicit field is id		 
      ];
	  
      $tab[] = [
         'id'            => 14,
         'table'         => $this->getTable(),
         'field'         => 'cost',
         'name'          => __("Importe"), 
		 'datatype'    => 'decimal', 		          
         'massiveaction' => false, // implicit field is id		 
      ];	  

      $tab[] = [
         'id'            => 18,
         'table'         => 'glpi_contractcosts',
         'field'         => 'name',
         'name'          => _n('Coste de adjudicación', 'Costes de adjudicación', 1), 
		 'datatype'    => 'dropdown', 		                   	 
      ];	 

      $tab[] = [
         'id'            => 80,
         'table'         => 'glpi_entities',
         'field'         => 'completename',
         'name'          => __('Entity'), 
		 'datatype'    => 'dropdown', 		                   	 
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
                FROM `glpi_plugin_contratos_facturacions`
                WHERE `contracts_id` = '$ID'
                ORDER BY `begin_date`";

      $rand   = mt_rand();

      if ($canedit) {
         echo "<div id='viewfacturacion".$ID."_$rand'></div>\n";
         echo "<script type='text/javascript' >\n";
         echo "function viewAddFact".$ID."_$rand() {\n";
         $params = array('type'         => __CLASS__,
                         'parenttype'   => 'Contract',
                         'contracts_id' => $ID,
                         'id'           => -1);
         Ajax::updateItemJsCode("viewfacturacion".$ID."_$rand",
                                $CFG_GLPI["root_doc"]."/ajax/viewsubitem.php", $params);
         echo "};";
         echo "</script>\n";
         echo "<div class='center firstbloc'>".
               "<a class='vsubmit' href='javascript:viewAddFact".$ID."_$rand();'>";
         echo __('Nueva Facturación')."</a></div>\n";
      }


      if ($result = $DB->query($query)) {
 echo "<table class='tab_cadre_fixehov'>";
         echo "<tr><th>".self::getTypeName($DB->numrows($result))."</th></tr>";		  
		 
         if ($DB->numrows($result)) {

         echo "<tr><td><table id='facturacion' class='display' style='width:100%'>";
         echo "        <thead>";			 
			 
		 echo "<tr>
                <th>".__('Nº Factura')."</th>
                <th>".__('Fecha inicio facturación')."</th>
                <th>".__('Fecha fin facturación')."</th>
                <th>".__('Presupuesto')."</th>
                <th>".__('Importe')."</th>
            </tr>
        </thead>
        <tbody>";

         Session::initNavigateListItems(__CLASS__,
                              //TRANS : %1$s is the itemtype name,
                              //        %2$s is the name of the item (used for headings of a list)
                                        sprintf(__('%1$s = %2$s'),
                                                Contract::getTypeName(1), $contract->getName()));

            $total = 0;
			$disponible = 0;
			$presupuestos = array();
			
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
                  Ajax::updateItemJsCode("viewfacturacion".$ID."_$rand",
                                         $CFG_GLPI["root_doc"]."/ajax/viewsubitem.php", $params);
                  echo "};";
                  echo "</script>\n";
               }
               echo "</td>";
              echo "<td>".Html::convDate($data['begin_date'])."</td>";
               echo "<td>".Html::convDate($data['end_date'])."</td>";
               echo "<td>".Dropdown::getDropdownName('glpi_contractcosts', $data['contractcosts_id'])."</td>";
               echo "<td class='numeric'>".Html::formatNumber($data['cost'])."</td>";
               $total += $data['cost'];
	
			   if (isset($data['contractcosts_id'])){
					array_push($presupuestos, $data['contractcosts_id']);
			   }
			   echo "</tr>";
               Session::addToNavigateListItems(__CLASS__, $data['id']);
			   
            }

			echo "</tbody>
        <tfoot>
            <tr>
                <th>".__('Nº Factura')."</th>
                <th>".__('Fecha inicio facturación')."</th>
                <th>".__('Fecha fin facturación')."</th>
                <th>".__('Presupuesto')."</th>
                <th>".__('Importe')."</th>
            </tr>
		<tr><th colspan='5'>Total Facturación ".Html::formatNumber($total)."</th></tr>			
        </tfoot>
			</table>";
			
		 echo "</table></td></tr></table><br>";	
			
			// Array con los presupuestos de las distintas facturas del contrato
			$presupuestos = array_unique($presupuestos);
			
			
			// Contruyo el conjunto para la select
			$longitud = count($presupuestos);
			$conjunto = "(";
			for($i=0; $i<$longitud; $i++)
			{
				$elemento = array_shift($presupuestos);
				$conjunto = $conjunto.$elemento;
				if ($i!=($longitud-1)){
						$conjunto = $conjunto.",";
				}

			}		
			$conjunto = $conjunto.")";
	
			// Select que saca presupuesto y suma de importe de facturas de un contrato
			$query2 = "SELECT sum(f.cost), f.contracts_id, cc.id, cc.cost, cc.name, cc.comment
					   from glpi_plugin_contratos_facturacions f
					   left join glpi_contractcosts as cc on (f.contractcosts_id = cc.id)
					   WHERE cc.id in".$conjunto." group by cc.id;";
					   			
$totales = [
"total" => 0,
"facturado" => 0,
"pendiente" => 0,
];
								
			if ($result2 = $DB->query($query2)) {
				echo "<table class='tab_cadre_fixehov'>";
				echo "<tr><th colspan='4'> Resumen Datos Facturación</th></tr>";
							

				if ($DB->numrows($result2)) {
					
         echo "<tr><td><table id='resumen_facturacion' class='display' style='width:100%'>";
         echo "        <thead>";			 
			 
		 echo "<tr>
                <th>".__('Presupuesto')."</th>
                <th>".__('Importe Total')."</th>
                <th>".__('Importe Facturado')."</th>
                <th>".__('Importe Pendiente')."</th>                
            </tr>
        </thead>
        <tbody>";	


					while ($data2 = $DB->fetchAssoc($result2)) {
						echo "<tr class='tab_bg_2' >";
						$name = (empty($data2['name'])? sprintf(__('%1$s (%2$s)'),
                                                      $data2['name'], $data2['id'])
                                            : $data2['name']);
						echo "<td>";
						printf(__('%1$s %2$s'), $name,
									Html::showToolTip($data2['comment'], array('display' => false)));
																								
						echo "<td class='numeric'>".Html::formatNumber($data2['cost'])."</td>";
						$totales["total"]=$data2['cost']+$totales["total"];
						echo "<td class='numeric'>".Html::formatNumber($data2['sum(f.cost)'])."</td>";
						$totales["facturado"]=$totales["pendiente"]+$data2['sum(f.cost)'];
						$pendiente = $data2['cost'] - $data2['sum(f.cost)'];						
						echo "<td class='numeric'>".Html::formatNumber($pendiente)."</td>";
						$totales["pendiente"]=$totales["pendiente"]+$pendiente;					
						
						echo "</tr>";
					}
					echo "<div id='viewpresupuesto".$ID."_$rand'></div>\n";

				}

			echo "</tbody>
        <tfoot>
            <tr>
                <th>".__('Presupuesto')."</th>
                <th>".__('Importe Total')."</th>
                <th>".__('Importe Facturado')."</th>
                <th>".__('Importe Pendiente')."</th>   
            </tr>
		<tr>
		<th></th>
        <th>".Html::formatNumber($totales["total"])."</th>
        <th>".Html::formatNumber($totales["facturado"])."</th>
        <th>".Html::formatNumber($totales["pendiente"])."</th>   		
		</tr>			
        </tfoot>
			</table>";				
				
			}
			echo "</table>";	 
		 
		 
		 
		 
         } else {
            echo "<tr><th colspan='5'>".__('No item found')."</th></tr></table>";
         }
        
      }

/*
      if ($result = $DB->query($query)) {
         echo "<table class='tab_cadre_fixehov'>";
         echo "<tr><th colspan='5'>".self::getTypeName($DB->numrows($result))."</th></tr>";

         if ($DB->numrows($result)) {
            echo "<tr><th>".__('Nº Factura')."</th>";
            echo "<th>".__('Fecha inicio facturación')."</th>";
            echo "<th>".__('Fecha fin facturación')."</th>";
            echo "<th>".__('Presupuesto')."</th>";
            echo "<th>".__('Importe')."</th>";
            echo "</tr>";

         Session::initNavigateListItems(__CLASS__,
                              //TRANS : %1$s is the itemtype name,
                              //        %2$s is the name of the item (used for headings of a list)
                                        sprintf(__('%1$s = %2$s'),
                                                Contract::getTypeName(1), $contract->getName()));

            $total = 0;
			$disponible = 0;
			$presupuestos = array();
			
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
                  Ajax::updateItemJsCode("viewfacturacion".$ID."_$rand",
                                         $CFG_GLPI["root_doc"]."/ajax/viewsubitem.php", $params);
                  echo "};";
                  echo "</script>\n";
               }
               echo "</td>";
              echo "<td>".Html::convDate($data['begin_date'])."</td>";
               echo "<td>".Html::convDate($data['end_date'])."</td>";
               echo "<td>".Dropdown::getDropdownName('glpi_contractcosts', $data['contractcosts_id'])."</td>";
               echo "<td class='numeric'>".Html::formatNumber($data['cost'])."</td>";
               $total += $data['cost'];
	
			   if (isset($data['contractcosts_id'])){
					array_push($presupuestos, $data['contractcosts_id']);
			   }
			   echo "</tr>";
               Session::addToNavigateListItems(__CLASS__, $data['id']);
			   
            }
            echo "<tr class='b noHover'><td colspan='3'>&nbsp;</td>";
            echo "<td class='right'>".__('Total facturado').'</td>';
            echo "<td class='numeric'>".Html::formatNumber($total).'</td></tr>';
			
			// Array con los presupuestos de las distintas facturas del contrato
			$presupuestos = array_unique($presupuestos);
			
			
			// Contruyo el conjunto para la select
			$longitud = count($presupuestos);
			$conjunto = "(";
			for($i=0; $i<$longitud; $i++)
			{
				$elemento = array_shift($presupuestos);
				$conjunto = $conjunto.$elemento;
				if ($i!=($longitud-1)){
						$conjunto = $conjunto.",";
				}

			}		
			$conjunto = $conjunto.")";
	
			// Select que saca presupuesto y suma de importe de facturas de un contrato
			$query2 = "SELECT sum(f.cost), f.contracts_id, cc.id, cc.cost, cc.name, cc.comment
					   from glpi_plugin_contratos_facturacions f
					   left join glpi_contractcosts as cc on (f.contractcosts_id = cc.id)
					   WHERE cc.id in".$conjunto." group by cc.id;";
					   			
			if ($result2 = $DB->query($query2)) {
				echo "<table class='tab_cadre_fixehov'>";
				echo "<tr><th colspan='4'> Resumen Datos Facturación</th></tr>";

				if ($DB->numrows($result2)) {
					echo "<tr><th>".__('Presupuesto')."</th>";
					echo "<th>".__('Importe Total')."</th>";
					echo "<th>".__('Importe Facturado')."</th>";
					echo "<th>".__('Importe Pendiente')."</th>";
					echo "</tr>";
					
					while ($data2 = $DB->fetchAssoc($result2)) {
						echo "<tr class='tab_bg_2' >";
						$name = (empty($data2['name'])? sprintf(__('%1$s (%2$s)'),
                                                      $data2['name'], $data2['id'])
                                            : $data2['name']);
						echo "<td>";
						printf(__('%1$s %2$s'), $name,
									Html::showToolTip($data2['comment'], array('display' => false)));
																								
						echo "<td class='numeric'>".Html::formatNumber($data2['cost'])."</td>";
						echo "<td class='numeric'>".Html::formatNumber($data2['sum(f.cost)'])."</td>";
						$pendiente = $data2['cost'] - $data2['sum(f.cost)'];
						echo "<td class='numeric'>".Html::formatNumber($pendiente)."</td>";					
						echo "</tr>";
					}
					echo "<div id='viewpresupuesto".$ID."_$rand'></div>\n";

				}
			}
			echo "</table>";
			
			
         } else {
            echo "<tr><th colspan='5'>".__('No item found')."</th></tr>";
         }
         echo "</table>";
      }
      echo "</div><br>";
	  */
	  ?>
	  <script>	  
$(document).ready(function() {

///////////////////////////////////////////////
// DataTable facturacion  /////////////////////	
///////////////////////////////////////////////	
	    // DataTable
    var table =  $('#facturacion').DataTable( {
		    select: {
        style: 'single'
    },
		responsive: true,
        columnDefs: [ {
            targets: [ 0 ],
            orderData: [ 3, 0, 4 ]
        }]
    } );
	
	
		var nColumnas = $("#facturacion tfoot tr:first th").length-1;
    // Setup - add a text input to each footer cell
    $('#facturacion tfoot th').each( function (index, value) {
        var title = $(this).text();
		if (index>nColumnas){
		 
		$(this).css({
		'text-align': 'right',
		'font-weight': 'bold',
		'padding-right': '10px',
		'color': 'blue',
		});
		 
		 
		} else {
        $(this).html( '<input type="text" placeholder="Buscar:  '+title+'" />' );
		}
    }, nColumnas );
 

 
    // Apply the search
    table.columns().every( function () {
        var that = this;
 
        $( 'input', this.footer() ).on( 'keyup change clear', function () {
            if ( that.search() !== this.value ) {
                that
                    .search( this.value )
                    .draw();
            }
        } );
    } );
///////////////////////////////////////////////
// DataTable resumen_facturacion  /////////////	
///////////////////////////////////////////////	
	    // DataTable
    var tabla =  $('#resumen_facturacion').DataTable( {
		    select: {
        style: 'single'
    },
		responsive: true,
        columnDefs: [ {
            targets: [ 0 ],
            orderData: [ 0 ]
        }]
    } );
	
	var nColumnas = $("#resumen_facturacion tfoot tr:first th").length-1;
	
    // Setup - add a text input to each footer cell
    $('#resumen_facturacion tfoot th').each( function (index, value) {
       
		if (index>nColumnas){
		 
		$(this).css({
		'text-align': 'right',
		'font-weight': 'bold',
		'padding-right': '10px',
		});
		
		if (index==5){
			$(this).css({'color': 'blue'});	
		}	

		if (index==6){
			$(this).css({'color': 'green'});	
		}		
		
		if (index==7){
			$(this).css({'color': 'red'});	
		}		
		 
		 
		} else {
		var title = $(this).text();
        $(this).html( '<input type="text" placeholder="Buscar:  '+title+'" />' );
		}

    }, nColumnas);
 

 
    // Apply the search
    tabla.columns().every( function () {
        var that = this;
 
        $( 'input', this.footer() ).on( 'keyup change clear', function () {
            if ( that.search() !== this.value ) {
                that
                    .search( this.value )
                    .draw();
            }
        } );
    } );

} );
	  </script>
	  
	  <?php
	  
   }   

   function showForm($ID, $options=array()) {
	global $CFG_GLPI, $DB;
	
	  if ($ID > 0) {
         $this->check($ID, READ);
      } else {
         // Create item
         $options['contracts_id'] = $options['parent']->getField('id');
         //$this->check(-1, CREATE, $options);
      }
	  
	   $this->initForm($ID, $options);
      $this->showFormHeader($options);
 
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Nº factura')."</td>";
      echo "<td>";
      echo "<input type='hidden' name='contracts_id' value='".$this->fields['contracts_id']."'>";
//    Html::autocompletionTextField($this,'name');
      echo Html::input('name', ['value' => $this->fields['name']]);
      echo "</td>";
      echo "<td>".__('Importe')."</td>";
      echo "<td>";
      echo "<input type='text' name='cost' value='".Html::formatNumber($this->fields["cost"], true)."'
             size='14'>";
      echo "</td></tr>";
     
  	  echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Fecha de facturación')."</td>";
      echo "<td>";
	  Html::showDateField("date", array('value' => $this->fields['date']));
      echo "</td>";
      echo "<td>".__('Fecha de contabilización')."</td>";
      echo "<td>";
	  Html::showDateField("cont_date", array('value' => $this->fields['cont_date']));
      echo "</td></tr>";
	  
  	  echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Fecha de registro')."</td>";
      echo "<td>";
	  Html::showDateField("reg_date", array('value' => $this->fields['reg_date']));
      echo "</td>";
      echo "<td>".__('Fecha de pago')."</td>";
      echo "<td>";
	  Html::showDateField("pay_date", array('value' => $this->fields['pay_date']));
      echo "</td></tr>";	  
	  
	  
      echo "<tr class='tab_bg_1'><td>".__('Inicio periodo facturación')."</td>";
      echo "<td>";
	  Html::showDateField("begin_date", array('value' => $this->fields['begin_date']));	  
      echo "</td>";
      $rowspan = 3;
      echo "<td rowspan='$rowspan'>".__('Comments')."</td>";
      echo "<td rowspan='$rowspan' class='middle'>";
      echo "<textarea cols='45' rows='".($rowspan+3)."' name='comment' >".$this->fields["comment"].
           "</textarea>";
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'><td>".__('Fin periodo facturación')."</td>";
      echo "<td>";
	  Html::showDateField("end_date", array('value' => $this->fields['end_date']));	 
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>".__('Presupuesto')."</td>";
      echo "<td>";
	  
	  
      Contractcost::dropdown(array('condition' => [ "contracts_id" => $this->fields["contracts_id"] ],
								   'value' => $this->fields["contractcosts_id"]));
      echo "</td></tr>";

      $this->showFormButtons($options);

      return true;
   }

   
} 



?>