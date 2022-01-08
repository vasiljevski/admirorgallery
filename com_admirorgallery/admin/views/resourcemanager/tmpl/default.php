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

use Joomla\CMS\Language\Text as JText;

?>
<div class="row-fluid">
	<?php echo JLayoutHelper::render('sidebar', array ("show", JFactory::getApplication()->isClient('administrator'))); ?>
	<div id="j-main-container" class="span12">
		<div class="well well-small">
			<form action="<?php echo JRoute::_('index.php?option=com_admirorgallery&task=' . $this->resourceType); ?>"
				  method="post"
				  name="adminForm"
				  id="adminForm"
				  enctype="multipart/form-data">

				<input type="hidden" name="option" value="com_admirorgallery"/>
				<input type="hidden" name="task" value=""/>
				<input type="hidden" name="boxchecked" value="0"/>
				<input type="hidden" name="view" value="resourcemanager"/>
				<input type="hidden" name="controller" value="resourcemanager"/>
				<input type="hidden" name="resourceType" value="<?php echo $this->resourceType; ?>"/>
				<?php echo JHTML::_('form.token'); ?>

				<script type="text/javascript">
					AG_jQuery(function () {
						AG_jQuery(".ag_title_link").click(function (e) {
							e.preventDefault();
							if (AG_jQuery(this).closest("tr").find('input:checkbox').attr("checked") == true) {
								AG_jQuery(this).closest("tr").find('input:checkbox').attr("checked", false);
							} else {
								AG_jQuery(this).closest("tr").find('input:checkbox').attr("checked", true);
							}
						});
					});//AG_jQuery
				</script>
				<div>
					<p><?php echo JText::_('AG_SELECT_TEMPLATE_TO_INSTALL'); ?>
						[ <b><?php echo JText::_('AG_MAX'); ?>
							<?php echo (JComponentHelper::getParams('com_media')->get('upload_maxsize', 0)) ?> MB</b> ]:
						<input type="file" name="AG_fileUpload"/></p>
				</div>
				<table class="table table-striped" id="categoryList" cellspacing="1">
					<thead>
					<tr>
						<th width="1%" class="nowrap center">#</th>
						<th class="center">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo JText::_('AG_TITLE'); ?></th>
						<th width="1%" class="nowrap center">
							<?php echo JText::_('AG_ID'); ?></th>
						<th class="center hidden-phone">
							<?php echo JText::_('AG_DESCRIPTION'); ?></th>
						<th class="center">
							<?php echo JText::_('AG_VERSION'); ?></th>
						<th class="center hidden-phone">
							<?php echo JText::_('AG_DATE'); ?></th>
						<th class="center hidden-phone">
							<?php echo JText::_('AG_AUTHOR'); ?></th>
					</tr>
					</thead>
					<tbody>

<?php
$total = count($this->resourceManagerInstalled);
$pageNav = new JPagination($total, $this->limitstart, $this->limit);

if ($this->limit == "all")
{
	$this->limit = $total;
}

if ($total > 0)
{
	foreach ($this->resourceManagerInstalled as $resourceManagerKey => $resourceManagerValue)
	{
		if ($resourceManagerKey >= $this->limitstart && $resourceManagerKey < ($this->limitstart + $this->limit))
		{
			// TEMPLATE DETAILS PARSING
			$id = $resourceManagerValue;
			$name = $id;
			$creationDate = JText::_("AG_UNDATED");
			$author = JText::_("AG_UNKNOWN_AUTHOR");
			$version = JText::_("AG_UNKNOWN_VERSION");
			$description = JText::_("AG_NO_DESCRIPTION");

			if (JFIle::exists(JPATH_SITE . '/plugins/content/admirorgallery/admirorgallery/' . $this->resourceType . '/' . $id . '/details.xml'))
			{
				$resourceManagerXmlObject = simplexml_load_file(
					JPATH_SITE .
					'/plugins/content/admirorgallery/admirorgallery/' .
					$this->resourceType . '/' . $id . '/details.xml'
				);
				$name = $resourceManagerXmlObject->name;
				$creationDate = $resourceManagerXmlObject->creationDate;
				$author = $resourceManagerXmlObject->author;
				$version = $resourceManagerXmlObject->version;
				$description = $resourceManagerXmlObject->description;
			}
			?>
				<tr>
					<td class="order nowrap center">
						<?php echo ($resourceManagerKey + 1); ?>
					</td>
					<td class="center">
						<?php echo JHtml::_('grid.id', ($resourceManagerKey + 1), $id); ?>
					</td>
					<td class="nowrap">

							<span class="editlinktip hasTip"
									title="<?php
											echo $name . '::<img border=&quot;1&quot; src=&quot;' .
											JURI::root() . 'plugins/content/admirorgallery/admirorgallery/' .
											$this->resourceType . '/' . $id .
											'/preview.jpg &quot; name=&quot;imagelib&quot; alt=&quot;&quot;
											width=&quot;206&quot; height=&quot;145&quot; />' ?>">
								<a href="#" class="ag_title_link">
									<?php echo $name; ?>
								</a>

							</span>
					</td>
					<td class="nowrap">
						<?php echo $id; ?>
					</td>
					<td class="hidden-phone">
						<?php echo $description; ?>
					</td>
					<td class="center nowrap">
						<?php echo $version; ?>
					</td>
					<td class="nowrap hidden-phone">
						<?php echo $creationDate; ?>
					</td>
					<td class="hidden-phone">
						<?php echo $author; ?>
					</td>
				</tr>
			<?php
		}
	}
}
?>
					<tfoot>
					<tr>
						<td colspan="9">
							<?php echo $pageNav->getListFooter(); ?>
						</td>
					</tr>
					</tfoot>
				</table>
			</form>
		</div>
	</div>
</div>
