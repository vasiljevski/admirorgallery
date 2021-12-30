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

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;

/**
 * AdmirorgalleryControllerResourcemanager
 *
 * @since 1.0.0
 */
class AdmirorgalleryControllerResourcemanager extends AdmirorgalleryController
{
	/**
	 * model
	 *
	 * @var mixed
	 *
	 * @since 1.0.0
	 */
	public $model;

	/**
	 * __construct
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		$this->registerTask('installResource', 'installResource');
		$this->registerTask('uninstallResource', 'uninstallResource');
		$this->registerTask('resetResource', 'resetResource');

		$this->model = $this->getModel('resourcemanager');
	}

	/**
	 * installResource
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function installResource(): void
	{
		$file = $this->input->getVar('AG_fileUpload', null, 'files');

		if (isset($file) && !empty($file['name']))
		{
			$resourceType = $this->model->input->getVar('resourceType');

			// Trim trailing directory separator
			$resourceType = substr($resourceType, 0, strlen($resourceType) - 1);

			$this->model->installResource($file, $resourceType, "zip", JFactory::getConfig()->get('tmp_path'));
		}
		else
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_ADMIRORGALLERY_NOTICE_MUST_SELECT_FILE'), 'notice');
		}

		parent::display();
	}

	/**
	 * uninstallResource
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function uninstallResource(): void
	{
		$idsToRemove = $this->input->getVar('cid');

		if (!empty($idsToRemove))
		{
			$this->model->uninstallResource($idsToRemove, $this->model->input->getVar('resourceType'));
		}

		parent::display();
	}

	/**
	 * resetResource
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function resetResource(): void
	{
		parent::display();
	}

}
