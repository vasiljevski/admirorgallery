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
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.controller' );

class AdmirorgalleryControllerAdmirorgallery extends AdmirorgalleryController
{
	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		$this->registerTask( 'AG_apply', 'AG_apply' );
		$this->registerTask( 'AG_reset', 'AG_reset' );
	}

	function AG_apply()
	{
	       $model = $this->getModel('admirorgallery');

	       // UPDATE
	       $model->_update();
	       
	       parent::display();
	}

	function AG_reset()
	{
	    parent::display();
	}


}
