<?php
/*------------------------------------------------------------------------
# com_admirorgallery - Admiror Gallery Component
# ------------------------------------------------------------------------
# author   Igor Kekeljevic & Nikola Vasiljevski
# copyright Copyright (C) 2014 admiror-design-studio.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.admiror-design-studio.com/joomla-extensions
# Technical Support:  Forum - http://www.vasiljevski.com/forum/index.php
# Version: 5.0.0
-------------------------------------------------------------------------*/
// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.html.toolbar');

class AdmirorgalleryHelperToolbar extends JObject
{
      public static function getToolbar() {


	      $bar = new JToolBar( 'toolbar' );

	      $bar->appendButton( 'Standard','unpublish','COM_ADMIRORGALLERY_RESET_DESC','AG_reset', false);
              $bar->appendButton( 'Standard','publish','COM_ADMIRORGALLERY_APPLY_DESC','AG_apply', false);

	      return $bar->render();

      }

}

?>