<?php

/**
 * @version     6.0.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2020 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

/**
 * Script file of Admiror Gallery component
 *
 * @since 5.5.0
 */
class com_admirorgalleryInstallerScript
{

        private $plugin_status = array(
                'admirorgallery' => 0,
                'admirorbutton' => 0
        );

        /**
         * Install the component
         *
         * @param $parent
         *
         * @return void
         *
         * @since 5.5.0
         */
        function install($parent)
        {
                $manifest = $parent->getManifest();
                $parent = $parent->getParent();
                $source = $parent->getPath("source");

                $installer = new JInstaller();

                // Install plugins
                foreach ($manifest->plugins->plugin as $plugin) {
                        $attributes = $plugin->attributes();
                        $plg = $source . DIRECTORY_SEPARATOR . $attributes['folder'] . DIRECTORY_SEPARATOR . $attributes['plugin'];
                        $installer->install($plg);
                        //Enable plugin
                        $this->plugin_status[$attributes['plugin']] = $this->enablePlugin($attributes['plugin'], $attributes['group']);
                }
        }

        /**
         * Uninstall the component
         *
         * @param $parent
         *
         * @return void
         *
         * @since 5.5.0
         */
        function uninstall($parent)
        {

                $installer = new JInstaller();

                // Uninstall AdmirorGallery plugin
                $this->plugin_status['admirorgallery'] = $installer->uninstall('plugin', $this->getPluginId('admirorgallery', 'content'));
                // Uninstall AdmirorButton plugin
                $this->plugin_status['admirorbutton'] = $installer->uninstall('plugin', $this->getPluginId('admirorbutton', 'editors-xtd'));
        }

        /**
         * Update the component
         *
         * @param $parent
         *
         * @return void
         *
         * @since 5.5.0
         */
        function update($parent)
        {
                //On update we just call install, no special case for updating.
                $this->install($parent);
        }

        /**
         * Run before an install/update/uninstall method
         *
         * @param $type
         * @param $parent
         *
         * @return void
         *
         * @since 5.5.0
         */
        function preflight($type, $parent)
        {
        }

        /**
         * Run after an install/update/uninstall method
         *
         * @param $type
         * @param $parent
         *
         * @return void
         *
         * @since 5.5.0
         */
        function postflight($type, $parent)
        {
                // $parent is the class calling this method
                // $type is the type of change (install, update or discover_install)
                if ($type == 'install') {
                        $component_status = JText::_('Installed');
                        $gallery_status = ($this->plugin_status['admirorgallery']) ? JText::_('Not installed') : JText::_('Installed');
                        $button_status = ($this->plugin_status['admirorbutton']) ? JText::_('Not installed') : JText::_('Installed');
                } else if ($type == 'uninstall') {
                        $component_status = JText::_('Uninstall');
                        $gallery_status = ($this->plugin_status['admirorgallery']) ? JText::_('Removed') : JText::_('Error');
                        $button_status = ($this->plugin_status['admirorbutton']) ? JText::_('Removed') : JText::_('Error');
                } else {
                        return;
                }

                $html = '<h2>Admiror Gallery Status</h2>
                <table class="table table-striped">
                        <thead>
                                <tr>
                                        <th class="title" colspan="2">' . JText::_('Extension') . '</th>
                                        <th width="30%">' . JText::_('Status') . '</th>
                                </tr>
                        </thead>
                        <tfoot>
                                <tr>
                                        <td colspan="3"></td>
                                </tr>
                        </tfoot>
                        <tbody>
                                <tr class="row0">
                                        <td class="key" colspan="2">Admiror Gallery ' . JText::_('Component') . '</td>
                                        <td><strong>' . $component_status . '</strong></td>
                                </tr>
                                <tr class="row1">
                                        <th>' . JText::_('Plugin') . '</th>
                                        <th>' . JText::_('Group') . '</th>
                                        <th></th>
                                </tr>
                                <tr class="row0">
                                        <td class="key">' . ucfirst('Admiror Gallery Plugin') . '</td>
                                        <td class="key">' . ucfirst('content') . '</td>
                                        <td><strong>' . $gallery_status . '</strong></td>
                                </tr>
                                <tr class="row0">
                                        <td class="key">' . ucfirst('Admiror Button') . '</td>
                                        <td class="key">' . ucfirst('editors-xtd') . '</td>
                                        <td><strong>' . $button_status . '</strong></td>
                                </tr>
                        </tbody>
                </table>';
                echo $html;
        }
        private function getPluginId($name, $type)
        {
                $db = JFactory::getDbo();
                $tableExtensions = "#__extensions"; //$db->quote("#__extensions");
                $columnElement = "element"; //$db->quote("element");
                $columnType = "type"; //$db->quote("type");
                $columnFolder = "folder"; //$db->quote("folder");
                $db->setQuery(
                        "SELECT extension_id
				FROM
					$tableExtensions
				WHERE
					$columnElement='" . $name . "'
				AND
					$columnType='plugin'
				AND
					$columnFolder='" . $type . "'"
                );
                return $db->loadResult();
        }

        private function enablePlugin($name, $type)
        {
                $db = JFactory::getDbo();
                $tableExtensions = "#__extensions"; //$db->quote("#__extensions");
                $columnElement = "element"; //$db->quote("element");
                $columnType = "type"; //$db->quote("type");
                $columnFolder = "folder"; //$db->quote("folder");
                $columnEnabled = "enabled"; //$db->quote("enabled",false);

                // Enable plugins
                $db->setQuery(
                        "UPDATE
                    $tableExtensions
                SET
                    $columnEnabled=1
                WHERE
                    $columnElement='" . $name . "'
                AND
                    $columnType='plugin'
                AND
                    $columnFolder='" . $type . "'"
                );

                return $db->execute();
        }
}
