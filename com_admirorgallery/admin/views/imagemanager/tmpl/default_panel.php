<?php
/**
 * @version     6.0.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic <igor@admiror.com>
 * @author      Nikola Vasiljevski <nikola83@gmail.com>
 * @copyright   Copyright (C) 2010 - 2021 https://www.admiror-design-studio.com All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Admiror\Plugin\Content\AdmirorGallery\Helper;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Uri\Uri as JURI;

?>
<div class="AG_body_wrapper">
    <!--FORMAT SCREEN-->
    <table border="0" cellspacing="0" cellpadding="0" width="100%">
        <tbody>
        <tr>
            <td class="AG_bookmarks_wrapper" style="display:none;">

                <h1>
                    <img src="<?php echo JURI::root() . 'administrator/components/com_admirorgallery/templates/' . $this->ag_template_id . '/images/bookmark.png'; ?>"
                         style="float:left;"/>&nbsp;<?php echo JText::_('AG_GALLERIES'); ?>
                </h1>
                <?php
                $ag_bookmarks_xml = simplexml_load_file($this->ag_get_bookmark_path());
                if (isset($ag_bookmarks_xml->bookmark)) {
                    foreach ($ag_bookmarks_xml->bookmark as $key => $value) {
                        ?>

                        <table border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr>
                                <td>
                                    <img src="<?php echo JURI::root() . 'administrator/components/com_admirorgallery/templates/' . $this->ag_template_id . '/images/bookmarkRemove.png'; ?>"
                                         style="float:left;"/></td>
                                <td><input type="checkbox" value="<?php echo $value; ?>"
                                           name="AG_cbox_bookmarkRemove[]"></td>
                                <td><span class="AG_border_color AG_border_width AG_separator">&nbsp;</span></td>
                                <td>
                                    <a href="' . $value . '" class="AG_folderLink AG_common_button"
                                       title="' . $value . '">
                                                <span><span>
                                                        <?php echo Helper::ag_shrinkString(basename($value), 20); ?>
                                                    </span></span>
                                    </a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <?php
                    }
                }
                ?>

                <div style="clear:both" class="AG_margin_bottom"></div>
                <hr/>
                <div class="AG_legend">
                    <h2><?php echo JText::_('AG_LEGEND') ?></h2>
                    <table>
                        <tbody>
                        <tr>
                            <td>
                                <img src="<?php echo JURI::root() . 'administrator/components/com_admirorgallery/templates/' . $this->ag_template_id . '/images/bookmarkRemove.png' ?>"
                                     style="float:left;"/></td>
                            <td><?php echo JText::_('AG_SELECT_TO_REMOVE_BOOKMARK') ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </td>
            <td id="AG_main">
                <a class="AG_common_button" href=""
                   id="AG_bookmarks_showHide"><span><span><?php echo JText::_('AG_SHOW_SIDEBAR') ?></span></span></a>
                <?php echo $ag_preview_content; ?>
            </td>
        </tr>
        </tbody>
    </table>

</div>