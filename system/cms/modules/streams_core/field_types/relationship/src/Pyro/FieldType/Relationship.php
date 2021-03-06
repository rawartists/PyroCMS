<?php namespace Pyro\FieldType;

use Illuminate\Support\Str;

use Pyro\Model\Eloquent;
use Pyro\Module\Streams\FieldType\FieldTypeAbstract;
use Pyro\Module\Streams\Entry\EntryModel;
use Pyro\Module\Streams\Field\FieldModel;
use Pyro\Module\Streams\Stream\StreamModel;

/**
 * Class Relationship
 *
 * @package Pyro\FieldType
 * @author  PyroCMS - Ryan Thompson
 */
class Relationship extends FieldTypeAbstract
{
    /**
     * Field type slug
     *
     * @var string
     */
    public $field_type_slug = 'relationship';

    /**
     * DB column type
     *
     * @var string
     */
    public $db_col_type = 'string';

    /**
     * Custom parameters
     *
     * @var array
     */
    public $custom_parameters = array(
        'stream',
        'input_method',
        'relation_class',
    );

    /**
     * Version
     *
     * @var string
     */
    public $version = '2.0';

    /**
     * Author
     *
     * @var  array
     */
    public $author = array(
        'name' => 'Ryan Thompson - PyroCMS',
        'url'  => 'https://www.pyrocms.com/about/the-team'
    );

    /**
     * Relation
     *
     * @return null|\Pyro\Module\Streams\FieldType\Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function relation()
    {
        return $this->belongsTo($this->getRelationClass());
    }

    /**
     * Field event
     */
    public function fieldEvent()
    {
        if ($this->getParameter('use_ajax')) {
            $class = $this->getRelationClass();
            $model = new $class;

            // It appears we need to get the record as a model

            // If we are using a filter, get the value, otherwise, get it from entry
            $defaultValue = ($this->isFilter) ? $this->getFilterValue('is') : $this->value;

            $result = $model->find($defaultValue);

            // If it is a filter we have a a different slug
            $jquerySelector = ($this->isFilter) ? 'f-'.$this->form_slug.'-is' : $this->form_slug.'-selectize';

            $data = array(
                'value'          => $result,
                'jquerySelector' => $jquerySelector,
                'valueField'     => $model->getFieldTypeRelationshipValueField(),
                'searchFields'   => $model->getFieldTypeRelationshipSearchFields(),
                'itemTemplate'   => $model->getPresenter()->getFieldTypeRelationshipItemTemplate(),
                'optionTemplate' => $model->getPresenter()->getFieldTypeRelationshipOptionTemplate(),
                'relationClass'  => $this->getRelationClass(),
                'form_slug'      => $this->form_slug
            );

            $this->appendMetadata($this->view('fragments/relationship.js.php', $data));
        }
    }

    /**
     * Output form input
     *
     * @access     public
     * @return    string
     */
    public function formInput()
    {

        $this->isFilter = false;

        $options = array(null => lang_label($this->getPlaceholder())) + $this->getOptions();

        if (!$this->getParameter('use_ajax')) {
            $attributes = '';
        } else {
            $attributes = 'class="' . $this->form_slug . '-selectize skip"';
        }

        return form_dropdown($this->form_slug, $options, $this->value, $attributes);

    }

    /**
     * Output the form input for frontend use
     *
     * @return string
     */
    public function publicFormInput()
    {
        $this->isFilter = false;

        return form_dropdown($this->form_slug, $this->getOptions(), $this->value);
    }

    /**
     * Output filter input
     *
     * @access     public
     * @return    string
     */
    public function filterInput()
    {
        $this->isFilter = true;

        $this->fieldEvent();

        $options = $this->getOptions();

        if (!$this->getParameter('use_ajax')) {
            $attributes = '';
        } else {
            $attributes = 'class="' . $this->getFilterSlug('is') . ' skip"';
        }

        return form_dropdown($this->getFilterSlug('is'), $options, $this->getFilterValue('is'), $attributes);
    }

    /**
     * String output
     *
     * @return  mixed   null or string
     */
    public function stringOutput()
    {
        if ($relatedModel = $this->getRelationResult()) {
            if (!$relatedModel instanceof RelationshipInterface) {
                throw new ClassNotInstanceOfRelationshipInterfaceException;
            }

            return $relatedModel->getFieldTypeRelationshipTitle();
        }

        return null;
    }

    /**
     * Plugin output
     *
     * @return array
     */
    public function pluginOutput()
    {
        if ($relatedModel = $this->getRelationResult()) {
            return $relatedModel;
        }

        return null;
    }

    /**
     * Data output
     *
     * @return RelationClassModel
     */
    public function dataOutput()
    {
        return $this->pluginOutput();
    }

    /**
     * Choose a stream to relate to.. or remote source
     *
     * @param  mixed $value
     * @return string
     */
    public function paramStream($value = null)
    {
        $options = StreamModel::getStreamAssociativeOptions();

        return form_dropdown('stream', $options, $value);
    }

    /**
     * Options
     *
     * @return array
     */
    public function getOptions()
    {
        if (!$this->getParameter('use_ajax')) {

            if ($relatedClass = $this->getRelationClass()) {

                $relatedModel = new $relatedClass;

                if (!$relatedModel instanceof RelationshipInterface) {
                    throw new ClassNotInstanceOfRelationshipInterfaceException;
                }

                return $relatedModel->getFieldTypeRelationshipOptions($this);
            }
        } else {
            // We are using Ajax
            return array(null => lang_label($this->getPlaceholder()));

        }

    }

    /**
     * Get column name
     *
     * @return string
     */
    public function getColumnName()
    {
        return parent::getColumnName() . '_id';
    }

    /**
     * Search
     *
     * @return string
     */
    public function ajaxSearch()
    {
        $class = ci()->input->post('relation_class');
        $model = new $class;
        $term  = urldecode(ci()->input->post('term'));
        $form_slug = ci()->input->post('form_slug');

        echo $model->getFieldTypeRelationshipResults($term, $form_slug);
    }
}
