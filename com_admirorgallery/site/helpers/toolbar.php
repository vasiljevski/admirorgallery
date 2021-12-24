<?php
/**
 * @version     6.0.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2021 https://www.admiror-design-studio.com All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();
jimport('joomla.html.toolbar');

class AdmirorgalleryHelperToolbar extends JObject
{
    /**
     *
     * @return string
     *
     * @since 5.5.0
     */
    public static function getToolbar(): string
      {
	      $bar = new JToolBar( 'toolbar' );

	      $bar->appendButton( 'Standard','unpublish','COM_ADMIRORGALLERY_RESET_DESC','AG_reset', false);
              $bar->appendButton( 'Standard','publish','COM_ADMIRORGALLERY_APPLY_DESC','AG_apply', false);

	      return $bar->render();

      }

}
