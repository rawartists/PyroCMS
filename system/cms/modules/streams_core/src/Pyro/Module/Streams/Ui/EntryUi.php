<?php namespace Pyro\Module\Streams\Ui;

use Pyro\Module\Streams\Entry\EntryModel;
use Pyro\Module\Streams\Entry\EntryQueryBuilder;
use Pyro\Module\Streams\Entry\EntryQueryFilter;
use Pyro\Module\Streams\Entry\EntryViewOptions;
use Pyro\Module\Streams\Field\FieldCollection;
use Pyro\Module\Streams\Field\FieldGroupCollection;


class EntryUi extends UiAbstract
{
    /**
     * The filter events that have run
     *
     * @var array
     */
    public $fieldTypeFilterEventsRun = array();

    /**
     * Entries table
     *
     * @param      $stream_slug
     * @param null $stream_namespace
     * @return \Pyro\Module\Streams\Ui\UiAbstract
     */
    public function table($stream_slug, $stream_namespace = null)
    {

        $this->triggerMethod(__FUNCTION__);

        // If we are not passing the stream namespace we probably are passing an Entry model class
        if (!$stream_namespace) {
            $model = new $stream_slug;
        } else {
            $class = $this->getEntryModelClass($stream_slug, $stream_namespace);
            $model = new $class;
        }

        $this->format = 'string';

        return $this->model($model);


    }

    /**
     * Entries form
     *
     * @param      $streamSlugOrClassOrModel
     * @param null $streamNamespaceOrId
     * @param null $id
     * @return \Pyro\Module\Streams\Ui\UiAbstract
     */
    public function form($streamSlugOrClassOrModel, $streamNamespaceOrId = null, $id = null)
    {
        $this->triggerMethod(__FUNCTION__);

        $streamSlug      = null;
        $streamNamespace = null;

        if (is_numeric($streamNamespaceOrId)) {
            $id = $streamNamespaceOrId;
        } elseif (is_string($streamNamespaceOrId)) {
            $streamNamespace = $streamNamespaceOrId;
        }

        $model = $streamSlugOrClassOrModel;

        // Is this a model already?
        if ($streamSlugOrClassOrModel instanceof EntryModel) {
            $model = $streamSlugOrClassOrModel;
        } elseif (is_string($streamSlugOrClassOrModel) and is_string($streamNamespace)) {
            $streamSlug = $streamSlugOrClassOrModel;
            $class      = $this->getEntryModelClass($streamSlug, $streamNamespace);
            $model      = new $class;
        } elseif (is_string($model) and !$streamSlug and !$streamNamespace) {
            $model = new $model;
        }

        // If the model does not have an id and we passed one, query it
        if ($model and !$model->getKey() and is_numeric($id)) {
            if ($record = $model->find($id)) {
                $model = $record;
            }
        }

        return $this->model($model);
    }

    /**
     * Add form
     *
     * @param EntryUi $entryUi
     * @return $this
     */
    public function addForm(EntryUi $entryUi)
    {
        if ($stream = $entryUi->getStream()) {
            $entryUi->isNestedForm(true)->triggerForm();

            foreach ($entryUi->getFields() as $field_slug => $field) {
                $this->nested_fields[$stream->stream_slug . ':' . $stream->stream_namespace . ':' . $field_slug] = $field;
            }
        }

        return $this;
    }

    /**
     * Run field type filter events
     *
     * @return null
     */
    public function runFieldTypeFilterEvents()
    {
        if (!$this->assignments or (!is_array($this->assignments) and !is_object($this->assignments))) {
            return null;
        }

        foreach ($this->assignments as $field) {
            // We need the slug to go on.
            if (!$type = $field->getType($this->model)) {
                continue;
            }

            $type->setStream($this->model->getStream());

            if (!in_array($field->field_slug, $this->get('skips', array()))) {
                // If we haven't called it (for dupes),
                // then call it already.
                if (!in_array($field->field_type, $this->fieldTypeFilterEventsRun)) {
                    $type->filterEvent();
                    $this->fieldTypeFilterEventsRun[] = $field->field_type;
                }

                // Field filter events run per field regardless of it the type
                // event ran or not
                $type->filterFieldEvent();
            }
        }
    }

    /**
     * Trigger table
     *
     * @return $this
     */
    protected function triggerTable()
    {
        ci()->benchmark->mark('entry_ui_trigger_table_start');

        $this->fireOnTable();

        if ($this->getTitle() === null) {
            $this->title(
                lang(
                    $this->model->getStream()->stream_namespace
                    . '.stream.' . $this->model->getStream()->stream_slug
                    . '.name'
                )
            );
        }

        // Set a description if we have one in the lang file
        $description =  lang(
            $this->model->getStream()->stream_namespace
            . '.stream.' . $this->model->getStream()->stream_slug
            . '.description'
        );
        $this->description( ($description > '') ? $description : false );


        ci()->benchmark->mark('entry_ui_trigger_table_view_options_start');
        $viewOptions = EntryViewOptions::make($this->model, $this->getFields('string'), $this->format);
        ci()->benchmark->mark('entry_ui_trigger_table_view_options_end');

        ci()->benchmark->mark('entry_ui_trigger_table_field_type_filter_events_start');
        $this
            ->assignments($this->model->getAssignments())
            ->stream($this->model->getStream())
            ->viewOptions($viewOptions->getFieldSlugs())
            ->fieldNames($viewOptions->getFieldNames())
            ->runFieldTypeFilterEvents();
        ci()->benchmark->mark('entry_ui_trigger_table_field_type_filter_events_end');

        ci()->benchmark->mark('entry_ui_trigger_table_build_pagination_start');
        // Build pagination if it's up to us
        if ($this->offset == null) {
            $this->pagination($this->limit, $this->paginationUri);
        }
        ci()->benchmark->mark('entry_ui_trigger_table_build_pagination_end');

        $this->sortableColumns = $this->model->getAllColumns();

        ci()->benchmark->mark('entry_ui_trigger_table_query_builder_start');
        // Allow to modify the query before we execute it
        // We pass the model to get access to its methods but you also can run query builder methods against it
        // Whatever you do on your closure, it must return an EntryBuilder instance
        if ($query = $this->fireOnQuery($this->model) and $query instanceof EntryQueryBuilder) {
            $this->query = $query;
        }

        /**
         * We must filter the query by calling filterQuery()
         */
        /** @var  $this->query  EntryQueryBuilder */
        $this->query = $this->query->filterQuery();

        ci()->benchmark->mark('entry_ui_trigger_table_query_builder_end');

        /**
         * Auto eager load relations
         */
        ci()->benchmark->mark('entry_ui_trigger_table_eager_loads_start');
        $viewOptions->addEagerLoads($this->eager);

        $this->query->with($viewOptions->getEagerLoads());
        ci()->benchmark->mark('entry_ui_trigger_table_eager_loads_end');

        /**
         * Lets clone the query to a countQuery after doing ->onQuery()
         * and before we do ->take()->skip() so that the count does not break.
         * On the EntryQueryBuilder, ->filterQuery() will apply to both ->get() and ->count()
         * respectively, so that we have the relevant "wheres" applied
         */
        ci()->benchmark->mark('entry_ui_trigger_table_clone_query_start');
        $this->countQuery = clone $this->query;
        ci()->benchmark->mark('entry_ui_trigger_table_clone_query_end');

        /**
         * Order by or allow override here
         */
        if ($this->orderBy and !$this->isOrderOverride()) {
            $this->query->orderBy($this->orderBy, $this->sort);
        }

        /**
         * Get filters applied
         */
        ci()->benchmark->mark('entry_ui_trigger_table_entry_query_filter_start');
        $this->filterClass = $filter = new EntryQueryFilter($this->query);

        $this->appliedFilters = $filter->getAppliedFilters();
        ci()->benchmark->mark('entry_ui_trigger_table_entry_query_filter_end');


        // Override limit
        if ($limit = $filter->getLimit() and !$this->limit) {
            $this->limit($limit);
        } elseif (!$this->limit) {
            $this->limit(\Settings::get('records_per_page'));
        }

        ci()->benchmark->mark('entry_ui_trigger_table_limit_and_make_pagination_start');
        /**
         * Limit and make pagination
         */
        if ($this->limit > 0) {
            $this->query->take($this->limit)->skip($this->offset);
            $this->paginationTotalRecords($this->countQuery->count());
        }
        ci()->benchmark->mark('entry_ui_trigger_table_limit_and_make_pagination_end');

        /**
         * Get actual entries
         */
        ci()->benchmark->mark('entry_ui_trigger_table_get_entries_start');
        $this->entries = $this->query->get($this->select)->getPresenter($viewOptions);
        ci()->benchmark->mark('entry_ui_trigger_table_get_entries_end');


        ci()->benchmark->mark('entry_ui_trigger_table_sorting_start');
        /**
         * Check for custom sorting
         *
         * @todo - this probably needs to be touched on
         */
        if ($this->get('sorting', $this->stream->sorting) == 'custom') {
            $this->stream->sorting = 'custom';

            // As an added measure of obsurity, we are going to encrypt the
            // slug of the module so it isn't easily changed.
            ci()->load->library('encrypt');

            // We need some variables to use in the sort.
            ci()->template->append_metadata(
                '<script type="text/javascript" language="javascript">var stream_id='
                . $this->stream->id . '; var stream_offset=' . $offset
                . '; var streams_module="' . ci()->encrypt->encode(ci()->module_details['slug'])
                . '";</script>'
            );

            ci()->template->append_js('streams/entry_sorting.js');
        }
        ci()->benchmark->mark('entry_ui_trigger_table_sorting_end');

        ci()->benchmark->mark('entry_ui_trigger_table_load_view_streams_core_entries_table_start');

        $this->content = ci()->load->view($this->getViewOverride('streams_core/entries/table'), $this->attributes, true);
        ci()->benchmark->mark('entry_ui_trigger_table_load_view_streams_core_entries_table_end');

        ci()->benchmark->mark('entry_ui_trigger_table_end');

        return $this;


    }

    /**
     * Trigger form
     *
     * @return $this
     */
    protected function triggerForm()
    {
        $this->fireOnForm();

        if ($model = $this->fireOnSaving($this->model) and $model instanceof EntryModel) {
            $this->model = $model;
        }

        // Automatically index in search?
        if ($this->index) {
            $this->model->setSearchIndexTemplate($this->index);
        }

        $this->stream      = $this->model->getStream();
        $this->assignments = $this->model->getAssignments();
        $this->form        = $this->model->newFormBuilder($this->attributes);

        $this->buildFields = $this->form->buildForm() ? : new FieldCollection;

        if ($this->getIsMultiForm()) {

            $original_fields = $this->buildFields;

            $this->buildFields = array();

            foreach ($original_fields as $field_slug => $field) {
                $this->buildFields[$this->stream->stream_slug . ':' . $this->stream->stream_namespace . ':' . $field_slug] = $field;
            }

            $this->buildFields->merge($this->nested_fields);
        }

        if ($saved = $this->form->get('result') and $this->enableSave and !$this->isNestedForm) {
            $this->fireOnSaved($saved);

            $this->runRedirect($saved);
        }

        $this->formUrl = $_SERVER['QUERY_STRING'] ? uri_string() . '?' . $_SERVER['QUERY_STRING'] : uri_string();

        if ($this->getTitle() === null) {
            $this->title(
                lang(
                    $this->model->getStream()->stream_namespace
                    . '.stream.' . $this->model->getStream()->stream_slug
                    . '.' . ($this->model->getKey() ? 'edit' : 'create')
                )
            );
        }

        $fields = $this->getFields();
        if (!empty($fields)) {
            foreach($this->buildFields as $key => $field) {
                if (!in_array($field->field_slug, $fields)) {
                    $this->buildFields->forget($key);
                }
            }
        }

        if (empty($this->tabs)) {
            $this->content = ci()->load->view($this->view ? : 'streams_core/entries/form', $this->attributes, true);
        } else {


            $fieldGroupCollection = new FieldGroupCollection($this->tabs, $this->buildFields);

            $this->tabs = $fieldGroupCollection->distribute()->toArray();

            $this->content = ci()->load->view(
                $this->view ? : 'streams_core/entries/tabbed_form',
                $this->attributes,
                true
            );
        }

        return $this;
    }

    /**
     * Is order override
     *
     * @return bool
     */
    public function isOrderOverride()
    {
        $stream = $this->model->getStream();
        $key    = 'order-' . $stream->stream_namespace . '-' . $stream->stream_slug;

        return (ci()->input->get($key));
    }

    /**
     * View read only
     *
     * @param bool $viewReadonly
     * @return $this
     */
    public function viewReadOnly($viewReadonly = false)
    {
        $this->buttons(!$viewReadonly);
        $this->attributes['viewReadOnly'] = $viewReadonly;
        return $this;
    }
}
