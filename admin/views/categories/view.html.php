<?php
/*------------------------------------------------------------------------

# TZ Pinboard Extension

# ------------------------------------------------------------------------

# author    TuNguyenTemPlaza

# copyright Copyright (C) 2013 templaza.com. All Rights Reserved.

# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Websites: http://www.templaza.com

# Technical Support:  Forum - http://templaza.com/Forum

-------------------------------------------------------------------------*/

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Categories view class for the Category package.
 */
class TZ_PinboardViewCategories extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
        JFactory::getLanguage()->load('com_categories');

		$this->state		= $this->get('State');
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Preprocess the list of items to find ordering divisions.
		foreach ($this->items as &$item) {
			$this->ordering[$item->parent_id][] = $item->id;
		}

		// Levels filter.
		$options	= array();
		$options[]	= JHtml::_('select.option', '1', JText::_('J1'));
		$options[]	= JHtml::_('select.option', '2', JText::_('J2'));
		$options[]	= JHtml::_('select.option', '3', JText::_('J3'));
		$options[]	= JHtml::_('select.option', '4', JText::_('J4'));
		$options[]	= JHtml::_('select.option', '5', JText::_('J5'));
		$options[]	= JHtml::_('select.option', '6', JText::_('J6'));
		$options[]	= JHtml::_('select.option', '7', JText::_('J7'));
		$options[]	= JHtml::_('select.option', '8', JText::_('J8'));
		$options[]	= JHtml::_('select.option', '9', JText::_('J9'));
		$options[]	= JHtml::_('select.option', '10', JText::_('J10'));

		$this->assign('f_levels', $options);
        $this -> assign('listsGroup',$this -> get('FieldsGroup'));

		$this->addToolbar();
        $this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		// Initialise variables.
        TZ_PinboardHelper::addSubmenu('categories');

		$categoryId	= $this->state->get('filter.category_id');
		$component	= $this->state->get('filter.component');

		$section	= $this->state->get('filter.section');

		$canDo		= null;
		$user		= JFactory::getUser();

        // Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');

		// Avoid nonsense situation.
		if ($component != 'com_tz_pinboard') {
			return;
		}

		// Need to load the menu language file as mod_menu hasn't been loaded yet.
		$lang = JFactory::getLanguage();
			$lang->load($component, JPATH_BASE, null, false, false)
		||	$lang->load($component, JPATH_ADMINISTRATOR.'/components/'.$component, null, false, false)
		||	$lang->load($component, JPATH_BASE, $lang->getDefault(), false, false)
		||	$lang->load($component, JPATH_ADMINISTRATOR.'/components/'.$component, $lang->getDefault(), false, false);

 		// Load the category helper.
		require_once JPATH_COMPONENT.'/helpers/categories.php';

		// Get the results for each action.
		$canDo = TZ_PinboardHelper::getActions($component, $categoryId);

		// If a component categories title string is present, let's use it.
		if ($lang->hasKey($component_title_key = strtoupper($component.($section?"_$section":'')).'_CATEGORIES_TITLE')) {
			$title = JText::_($component_title_key);
		}
		// Else if the component section string exits, let's use it
		elseif ($lang->hasKey($component_section_key = strtoupper($component.($section?"_$section":'')))) {
			$title = JText::sprintf( 'COM_CATEGORIES_CATEGORIES_TITLE', $this->escape(JText::_($component_section_key)));
		}
		// Else use the base title
		else {
			$title = JText::_('COM_CATEGORIES_CATEGORIES_BASE_TITLE');
		}

		// Load specific css component
		JHtml::_('stylesheet', $component.'/administrator/categories.css', array(), true);

		// Prepare the toolbar.
		JToolBarHelper::title($title, 'categories '.substr($component, 4).($section?"-$section":'').'-categories');

		if ($canDo->get('core.create') || (count($user->getAuthorisedCategories($component, 'core.create'))) > 0 ) {
			 JToolBarHelper::addNew('category.add');
		}

		if ($canDo->get('core.edit' ) || $canDo->get('core.edit.own')) {
			JToolBarHelper::editList('category.edit');
			JToolBarHelper::divider();
		}

		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::publish('categories.publish', 'JTOOLBAR_PUBLISH', true);
			JToolBarHelper::unpublish('categories.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			//JToolBarHelper::divider();
			//JToolBarHelper::archiveList('categories.archive');
		}

//		if (JFactory::getUser()->authorise('core.admin')) {
//			JToolBarHelper::checkin('categories.checkin');
//		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete', $component)) {
			JToolBarHelper::deleteList('', 'categories.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state')) {
			JToolBarHelper::trash('categories.trash');
			JToolBarHelper::divider();
		}
        // Add a batch button
		if ($canDo->get('core.edit'))
		{
			JHtml::_('bootstrap.modal', 'collapseModal');
			$title = JText::_('JTOOLBAR_BATCH');
			$dhtml = "<button data-toggle=\"modal\" data-target=\"#collapseModal\" class=\"btn btn-small\">
						<i class=\"icon-checkbox-partial\" title=\"$title\"></i>
						$title</button>";
			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		if ($canDo->get('core.admin')) {
			//JToolBarHelper::custom('categories.rebuild', 'refresh.png', 'refresh_f2.png', 'JTOOLBAR_REBUILD', false);
//			JToolBarHelper::preferences($component);
			JToolBarHelper::preferences('com_tz_pinboard');
			JToolBarHelper::divider();
		}

		// Compute the ref_key if it does exist in the component
		if (!$lang->hasKey($ref_key = strtoupper($component.($section?"_$section":'')).'_CATEGORIES_HELP_KEY')) {
			$ref_key = 'JHELP_COMPONENTS_'.strtoupper(substr($component, 4).($section?"_$section":'')).'_CATEGORIES';
		}

		// Get help for the categories view for the component by
		// -remotely searching in a language defined dedicated URL: *component*_HELP_URL
		// -locally  searching in a component help file if helpURL param exists in the component and is set to ''
		// -remotely searching in a component URL if helpURL param exists in the component and is NOT set to ''
		if ($lang->hasKey($lang_help_url = strtoupper($component).'_HELP_URL')) {
			$debug = $lang->setDebug(false);
			$url = JText::_($lang_help_url);
			$lang->setDebug($debug);
		}
		else {
			$url = null;
		}

		JToolBarHelper::help("JHELP_COMPONENTS_CONTENT_CATEGORIES", JComponentHelper::getParams( $component )->exists('helpURL'), $url);
        JHtmlSidebar::setAction('index.php?option=com_tz_pinboard&view=categories');

        $doc    = &JFactory::getDocument();
        $doc -> addStyleSheet(JURI::base(true).'/components/com_tz_pinboard/assets/style.css');
        // Special HTML workaround to get send popup working




		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_MAX_LEVELS'),
			'filter_level',
			JHtml::_('select.options', $this->f_levels, 'value', 'text', $this->state->get('filter.level'))
		);

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_PUBLISHED'),
			'filter_published',
			JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true)
		);

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_ACCESS'),
			'filter_access',
			JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'))
		);

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_LANGUAGE'),
			'filter_language',
			JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language'))
		);
	}

    /**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
        protected function getSortFields()
        {
            return array(
                'a.lft' => JText::_('JGRID_HEADING_ORDERING'),
                'a.state' => JText::_('JSTATUS'),
                'a.title' => JText::_('JGLOBAL_TITLE'),
                'a.access' => JText::_('JGRID_HEADING_ACCESS'),
                'language' => JText::_('JGRID_HEADING_LANGUAGE'),
                'a.id' => JText::_('JGRID_HEADING_ID')
            );
        }
}
