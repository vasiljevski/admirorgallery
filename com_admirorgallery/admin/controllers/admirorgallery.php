<?php
/**
 * @version     6.0.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2021 https://www.admiror-design-studio.com All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

class AdmirorgalleryControllerAdmirorgallery extends AdmirorgalleryController
{
    function __construct()
    {
        parent::__construct();

        // Register Extra tasks
        $this->registerTask('AG_apply', 'AG_apply');
        $this->registerTask('AG_reset', 'AG_reset');
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
