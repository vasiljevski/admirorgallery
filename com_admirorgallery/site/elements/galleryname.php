<?php
/**
 * @version     6.0.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2021 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

class JFormFieldGalleryName extends JFormField {

    //var   $_name = 'galleryName'; 
    public $type = 'galleryName';

    /**
     *
     * @return string
     *
     * @since 5.5.0
     */
    protected function getInput(): string
    {
        JHTML::_('behavior.modal');

        // Initialize some field attributes.
        $size = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
        $maxLength = $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';
        $class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
        $readonly = ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
        $disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';

        // Initialize JavaScript field attributes.
        $onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

        $content = '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'
                . htmlspecialchars($this->value, ENT_COMPAT) . '"' . $class . $size . $disabled . $readonly . $onchange . $maxLength . '/>';
        $link = JRoute::_('index.php?option=com_admirorgallery&amp;view=galleryname&amp;tmpl=component&amp;e_name=' . $this->name);
        $content.= '
	  <a href="' . $link . '" rel="{handler: \'iframe\', size: {x: 500, y: 400}}" class="modal" style="text-decoration:none;">
		<button type="button" class="btn">' . JText::_('AG_SELECT_GALLERY') . '</button>
	  </a>
      ';

        return $content;
    }

}
