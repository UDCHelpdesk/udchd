<?php
/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2021 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

/**
 * Equipments refused from inventory
 */
class RefusedEquipment extends CommonDBTM {
   use Glpi\Features\Inventoriable;

   // From CommonDBTM
   public $dohistory                   = true;
   static $rightname                   = 'config';

   static function getTypeName($nb = 0) {
      return _n('Equipment refused by rules log', 'Equipments refused by rules log', $nb);
   }

   public function rawSearchOptions() {
      $tab = parent::rawSearchOptions();

      $tab[] = [
         'id'            => '2',
         'table'         => RuleImportAsset::getTable(),
         'field'         => 'id',
         'real_type'     => RuleImportAsset::getType(),
         'name'          => Rule::getTypeName(1),
         'datatype'      => 'specific',
         'massiveaction' => false,
      ];

      $tab[] = [
         'id'            => '3',
         'table'         => $this->getTable(),
         'field'         => 'date_creation',
         'name'          => _n('Date', 'Dates', 1),
         'datatype'      => 'datetime',
         'massiveaction' => false,
      ];

      $tab[] = [
         'id'            => '4',
         'table'         => $this->getTable(),
         'field'         => 'itemtype',
         'name'          => __('Item type'),
         'massiveaction' => false,
         'datatype'      => 'itemtypename',
      ];

      $tab[] = [
         'id'            => '5',
         'table'         => Entity::getTable(),
         'field'         => 'completename',
         'name'          => Entity::getTypeName(1),
         'massiveaction' => false,
         'datatype'      => 'dropdown',
      ];

      $tab[] = [
         'id'            => '6',
         'table'         => $this->getTable(),
         'field'         => 'serial',
         'name'          => __('Serial number'),
         'datatype'      => 'string',
         'massiveaction' => false,
      ];

      $tab[] = [
         'id'            => '7',
         'table'         => $this->getTable(),
         'field'         => 'uuid',
         'name'          => __('UUID'),
         'datatype'      => 'string',
         'massiveaction' => false,
      ];

      $tab[] = [
         'id'            => '8',
         'table'         => $this->getTable(),
         'field'         => 'ip',
         'name'          => __('IP'),
         'datatype'      => 'string',
         'massiveaction' => false,
      ];

      $tab[] = [
         'id'            => '9',
         'table'         => $this->getTable(),
         'field'         => 'mac',
         'name'          => __('MAC'),
         'datatype'      => 'string',
         'massiveaction' => false,
      ];

      $tab[] = [
         'id'            => '10',
         'table'         => $this->getTable(),
         'field'         => 'method',
         'name'          => __('Method'),
         'datatype'      => 'string',
         'massiveaction' => false,
      ];

      $tab[] = [
         'id'            => '11',
         'table'         => Agent::getTable(),
         'field'         => 'name',
         'name'          => Agent::getTypeName(1),
         'datatype'      => 'itemlink',
         'massiveaction' => false,
         'itemlink_type' => 'Agent',
      ];

      return $tab;
   }

   /**
    * Get search parameters for default search / display list
    *
    * @return array
    */
   public static function getDefaultSearchRequest() {
      return [
         'sort'  => 3, //date SO
         'order' => 'DESC'
      ];
   }

   public static function getIcon() {
      return "fas fa-times";
   }

   public function showForm($ID, $options = []) {
      global $CFG_GLPI;

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";

      $itemtype = $this->fields['itemtype'];
      echo "<th>" .  __('Item type') . "</th>";
      echo "<td>" . $itemtype::getTypeName(1)  . "</td>";

      echo "<th>" . __('Name') . "</th>";
      echo "<td>" . $this->getName()  . "</td>";

      echo "</tr>";
      echo "<tr class='tab_bg_1'>";

      echo "<th>" .  __('Serial') . "</th>";
      echo "<td>" . $this->fields['serial']  . "</td>";

      echo "<th>" .  __('UUID') . "</th>";
      echo "<td>" . $this->fields['uuid']  . "</td>";

      echo "</tr>";
      echo "<tr class='tab_bg_1'>";

      $rule = new RuleImportAsset();
      $rule->getFromDB($this->fields['rules_id']);
      echo "<th>" .  Rule::getTypeName(1) . "</th>";
      echo "<td>";
      echo $rule->getLink();

      $rand = mt_rand();
      echo sprintf(
         "<a class='vsubmit' style='float:right;' href='#' onClick=\"%s\">%s</a>",
         Html::jsGetElementbyID('allruletest'.$rand).".dialog('open'); return false;",
         __('Test rules engine')
      );
      Ajax::createIframeModalWindow(
         'allruletest'.$rand,
         $CFG_GLPI['root_doc']."/front/rulesengine.test.php?"."sub_type=".RuleImportAsset::getType()."&refusedequipments_id=".$this->fields['id'],
         ['title' => __('Test rules engine')]
      );

      echo "</td>";

      $entity = new Entity();
      $entity->getFromDB($this->fields['entities_id']);
      echo "<th>" .  Entity::getTypeName(1) . "</th>";
      echo "<td>" . $entity->getLink() . "</td>";

      echo "</tr>";
      echo "<tr class='tab_bg_1'>";

      echo "<th>" .  IPAddress::getTypeName(1) . "</th>";
      echo "<td>" . implode(', ', importArrayFromDB($this->fields['ip'])) . "</td>";

      echo "<th>" .  __('MAC address') . "</th>";
      echo "<td>" . implode(', ', importArrayFromDB($this->fields['mac'])) . "</td>";

      echo "</tr>";
      $this->showInventoryInfo();

      $this->showFormButtons($options);

      return true;
   }

   public function isDynamic() {
      return true;
   }
}