<?php
/**
 * @version     6.0.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2021 https://www.admiror-design-studio.com All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

jimport( 'joomla.application.component.controller' );
jimport('joomla.html.parameter');

class AdmirorgalleryController extends JControllerLegacy
{
    /**
     * @param bool $cachable
     * @param bool $urlparams
     *
     * @since 5.5.0
     */
    function display($cachable = false, $urlparams = false)
    {
        parent::display();
    }
}