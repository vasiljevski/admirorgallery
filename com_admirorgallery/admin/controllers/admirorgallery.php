<?php
/**
 * @version     6.0.0
 * @package     Admiror.Administrator
 * @subpackage  com_admirorgallery
 * @author      Igor Kekeljevic <igor@admiror.com>
 * @author      Nikola Vasiljevski <nikola83@gmail.com>
 * @copyright   Copyright (C) 2010 - 2021 https://www.admiror-design-studio.com All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();


/**
 * AdmirorgalleryControllerAdmirorgallery
 *
 * @since 1.0.0
 */
class AdmirorgalleryControllerAdmirorgallery extends AdmirorgalleryController
{
	/**
	 * __construct
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		$this->registerTask('agApply', 'agApply');
		$this->registerTask('agReset', 'agReset');
	}

	/**
	 * agApply
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function agApply(): void
	{
		$model = $this->getModel('admirorgallery');

		// UPDATE
		$model->update();

		parent::display();
	}

	/**
	 * agReset
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function agReset(): void
	{
		parent::display();
	}


}
