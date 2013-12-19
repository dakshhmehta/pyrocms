<?php namespace Pyro\Module\Streams_core;

// The CP driver is broken down into more logical classes

use Closure;

class EntryUi extends AbstractUi
{
	/**
	 * Search index params or false
	 * @var mixed
	 */
	protected $index = false;

	/**
	 * The filter events that have run
	 * @var array
	 */
	public $field_type_filter_events_run = array();

	/**
	 * Set the auto index params or false
	 * @param  mixed $params $params array or false
	 * @return object          [description]
	 */
	public function index($params = false)
	{
		$this->index = $params;

		return $this;
	}

	/**
	 * Entries Table
	 *
	 * Creates a table of entries.
 	 *
	 * @param	string - the stream slug
	 * @param	string - the stream namespace slug
	 * @param	[mixed - pagination, either null for no pagination or a number for per page]
	 * @param	[null - pagination uri without offset]
	 * @param	[bool - setting this to true will take care of the $this->template business
	 * @param	[array - extra params (see below)]
	 * @return	mixed - void or string
	 *
	 * Extra parameters to pass in $extra array:
	 *
	 * title	- Title of the page header (if using view override)
	 *			$extra['title'] = 'Streams Sample';
	 * 
	 * buttons	- an array of buttons (if using view override)
	 *			$extra['buttons'] = array(
	 *				'label' 	=> 'Delete',
	 *				'url'		=> 'admin/streams_sample/delete/-entry_id-',
	 *				'confirm'	= true
	 *			);
	 * columns  - an array of field slugs to display. This overrides view options.
	 * 			$extra['columns'] = array('field_one', 'field_two');
	 *
 	 * sorting  - bool. Whether or not to turn on the drag/drop sorting of entries. This defaults
 	 * 			to the sorting option of the stream.
	 *
	 * see docs for more explanation
	 */
	public static function table($stream_slug, $stream_namespace = null, $pagination = null, $pagination_uri = null, $extra = array())
	{	
		// Prepare the stream, model and trigger method
		$instance = static::instance(__FUNCTION__);

		$instance->model = EntryModel::stream($stream_slug, $stream_namespace);

		$instance->query = $instance->model->newQuery();

		$instance->data->stream = $instance->model->getStream();

  		$instance->data->stream_fields = $instance->model->getAssignments();

  		$instance->field_slugs = $instance->data->stream_fields->getFieldSlugs();

  		// -------------------------------------
		// Sorting
		// @since 2.1.5
		// -------------------------------------

		if ($instance->data->stream->sorting == 'custom' or (isset($extra['sorting']) and $extra['sorting'] === true))
		{
			$instance->data->stream->sorting = 'custom';

			// As an added measure of obsurity, we are going to encrypt the
			// slug of the module so it isn't easily changed.
			ci()->load->library('encrypt');

			// We need some variables to use in the sort.
			ci()->template->append_metadata('<script type="text/javascript" language="javascript">var stream_id='
				.$instance->data->stream->id.'; var stream_offset='.$offset
				.'; var streams_module="'.ci()->encrypt->encode(ci()->module_details['slug'])
				.'";</script>');

			ci()->template->append_js('streams/entry_sorting.js');
		}
 
		$limit = ($instance->pagination) ? $pagination : null;

		return $instance;
	}


	/**
	 * trigger table
	 * @return void
	 */
	protected function triggerTable()
	{
		$this->data->buttons		= $this->buttons;

		$this->data->filters 		= isset($extra['filters']) ? $extra['filters'] : $this->filters;

		$this->data->search_id 		= isset($_COOKIE['streams_core_filters']) ? $_COOKIE['streams_core_filters'] : null;

		$this->runFieldTypeFilterEvents();

		// Allow to modify the query before we execute it
		// We pass the model to get access to its methods but you also can run query builder methods against it
		// Whatever you do on your closure, it must return an EntryBuilder instance
		if ($query = $this->fireOnQuery($this->model) and $query instanceof EntryQueryBuilder)
		{
			$this->query = $query;
		}

		$this->model->setViewOptions($this->view_options);

  		$this->data->entries = $this->query
			->enableAutoEagerLoading(true)
			->take($this->limit)
			->skip($this->offset)
			->remember(10)
			->get($this->select, $this->exclude);

		$this->data->view_options =	$this->model->getViewOptionsFields();

  		$this->data->field_names 	= $this->model->getViewOptionsFieldNames();

  		if ( ! empty($this->headers))
  		{
  			$this->data->field_names = array_merge($this->data->field_names, $this->headers);
  		}

  		$this->data->pagination = ! ($this->limit > 0) ?: $this->getPagination($this->model->count());
		
		$this->data->content = ci()->load->view('streams_core/entries/table', $this->data, true);
	}

	/**
	 * [form description]
	 * @param  string|Pyro\Module\Streams_core\EntryModel $mixed            [description]
	 * @param  [type] $stream_namespace [description]
	 * @param  [type] $id               [description]
	 * @return [type]                   [description]
	 */
	public static function form($mixed, $stream_namespace = null, $id = null)
	{	
		// Load up things we'll need for the form
		ci()->load->library(array('form_validation'));

		// Prepare the stream, model and trigger method
		$instance = static::instance(__FUNCTION__);

		if ($instance->isSubclassOfEntryModel($mixed))
		{
			$instance->entry = new $mixed;

			if (is_numeric($stream_namespace))
			{
				$id = $stream_namespace;

				$instance->entry = $instance->entry->find($id);
			}
		}
		elseif ($mixed instanceof EntryModel and $mixed->getKey())
		{
			$instance->entry = $mixed;
		}
		else
		{
			$instance->entry = EntryModel::stream($mixed, $stream_namespace);

			if ($id)
			{
				$instance->entry = $instance->entry->select('*')->find($id);
			}
		}

		$instance->stream = $instance->entry->getStream();

		$instance->entry->asEloquent();

		$stream_namespace = $instance->stream->stream_namespace;

		return $instance;	
	}

	/**
	 * trigger the form
	 * @return string The triggered HTML
	 */
	protected function triggerForm()
	{
		$this->fireOnSaving($this->entry);
		
		// Automatically index in search?
		if ($this->index)
		{
			$this->entry->setSearchIndexTemplate($this->index);
		}

		$this->form = $this->entry->newFormBuilder()
			->setDefaults($this->defaults)
			->setSkips($this->skips)
			->setHidden($this->hidden)
			->enableSave($this->enable_save)
			->successMessage($this->success_message)
			->redirect($this->redirect)
			->exitRedirect($this->exit_redirect)
			->continueRedirect($this->continue_redirect)
			->createRedirect($this->create_redirect)
			->cancelUri($this->cancel_uri);

		$this->data->stream 	= $this->entry->getStream();
		$this->data->tabs		= $this->tabs;
		$this->data->hidden 	= $this->hidden;
		$this->data->defaults	= $this->defaults;
		$this->data->entry		= $this->entry;
		$this->data->mode		= $this->mode;
		$this->data->fields		= $this->form->buildForm();

		$this->data->form_override 		= $this->form_override;
		
		if ($this->getIsMultiForm()) {

			$original_fields = $this->data->fields;
			
			$this->data->fields = array();

			foreach ($original_fields as $field_slug => $field) {
				$this->data->fields[$this->data->stream->stream_slug.':'.$this->data->stream->stream_namespace.':'.$field_slug] = $field;
			}

			$this->data->fields->merge($this->nested_fields);
		}
		
		if ($saved = $this->form->result() and $this->enable_save and ! $this->is_nested_form)
		{
			$this->fireOnSaved($saved);
			
			// Ooohh where to go..
			switch (ci()->input->post('btnAction')) {

				// Boring.
				case 'save':
					$url = site_url(ci()->parser->parse_string($this->data->redirect, $saved->toArray(), true));
					break;

				// Exit
				case 'save_exit':
					$url = site_url(ci()->parser->parse_string($this->data->exit_redirect, $saved->toArray(), true));
					break;

				// Create
				case 'save_create':
					$url = site_url(ci()->parser->parse_string($this->data->create_redirect, $saved->toArray(), true));
					break;

				// Continue
				case 'save_continue':
					$url = site_url(ci()->parser->parse_string($this->data->continue_redirect, $saved->toArray(), true));
					break;
				
				// Donknow
				default:
					$url = site_url(uri_string());
					break;
			}

			redirect($url);
		}

    	$this->data->form_url  = $_SERVER['QUERY_STRING'] ? uri_string().'?'.$_SERVER['QUERY_STRING'] : uri_string();

		if (empty($this->data->tabs))
		{
			$this->data->content  = ci()->load->view($this->view ?: 'streams_core/entries/form', $this->data, true);
		}
		else
		{
			$this->data->tabs = $this->distributeFields($this->data->tabs, $this->data->fields->getFieldSlugs());

			$this->data->content  = ci()->load->view($this->view ?: 'streams_core/entries/tabbed_form', $this->data, true);
		}

		return $this;
	}

	/**
	 * Distribute fields across tabs
	 * @param  array  $tabs             
	 * @param  array  $available_fields 
	 * @return array
	 */
	protected function distributeFields($tabs = array(), $available_fields = array())
	{
		foreach ($tabs as &$tab)
		{
			if ( ! empty($tab['fields']) and is_array($tab['fields']))
			{
				foreach ($tab['fields'] as $field)
				{
					if (isset($available_fields[$field])) unset($available_fields[$field]);
				}
			}
		}

		foreach ($tabs as &$tab)
		{
			if ( ! empty($tab['fields']) and $tab['fields'] === '*')
			{
				$tab['fields'] = $available_fields;

				break;
			}
		}

		return $tabs;
	}

	/**
	 * Run Field Filter Events
	 *
	 * Runs all the filterEvent() functions for some
	 * stream fields. The filterEvent() functions usually
	 * have field asset loads.
	 *
	 * @access 	public
	 * @param 	obj - stream fields
	 * @param 	[array - skips]
	 * @return 	array
	 */
	// $stream_fields, $skips = array(), $values = array()
	public function runFieldTypeFilterEvents()
	{
		if ( ! $this->data->stream_fields or ( ! is_array($this->data->stream_fields) and ! is_object($this->data->stream_fields))) return null;

		foreach ($this->data->stream_fields as $field)
		{
			// We need the slug to go on.
			if ( ! $type = $field->getType($this->entry))
			{
				continue;
			}

			if ( ! in_array($field->field_slug, $this->skips))
			{
				// If we haven't called it (for dupes),
				// then call it already.
				if ( ! in_array($field->field_type, $this->field_type_filter_events_run))
				{
					$type->filterEvent();

					$this->field_type_filter_events_run[] = $field->field_type;
				}

				// Field filter events run per field regardless of it the type
				// event ran or not
				$type->filterFieldEvent();
			}
		}
	}
}
