<?php namespace Pyro\FieldType;

use Carbon\Carbon;
use Pyro\Module\Streams\FieldType\FieldTypeAbstract;

class Datetime extends FieldTypeAbstract
{
    /**
     * Field type slug
     *
     * @var string
     */
    public $field_type_slug = 'datetime';

    /**
     * Database column type
     *
     * @var string
     */
    public $db_col_type = 'datetime';

    /**
     * Custom parameters
     *
     * @var array
     */
    public $custom_parameters = array(
        'use_time',
        'start_date',
        'end_date',
        'input_type',
        'date_format',
    );

    /**
     * Version
     *
     * @var string
     */
    public $version = '2.0.0';

    /**
     * Author
     *
     * @var array
     */
    public $author = array(
        'name' => 'Ryan Thompson - PyroCMS',
        'url'  => 'http://pyrocms.com/'
    );

    /**
     * Storage format
     *
     * @var string
     */
    protected $storageFormat = 'Y-m-d H:i:s';

    /**
     * Zero 2 digit
     *
     * @var string
     */
    protected $zero2Digit = '00';

    /**
     * Zero date
     *
     * @var string
     */
    protected $zeroDate = '0000-00-00';

    /**
     * Zero datetime
     *
     * @var string
     */
    protected $zeroDatetime = '0000-00-00 00:00:00';

    /**
     * Zero time
     *
     * @var string
     */
    protected $zeroTime = '00:00:00';

    /**
     * Blank time
     *
     * @var string
     */
    protected $blankTime = '12:00 AM';

    /**
     * Default display datetime format
     *
     * @var string
     */
    protected $defaultDisplayDatetimeFormat = 'M j Y g:i a';

    /**
     * Default display date format
     *
     * @var string
     */
    protected $defaultDisplayDateFormat = 'M j Y';

    /**
     * Half hours per day
     *
     * @var int
     */
    protected $halfHoursPerDay = 12;

    /**
     * Datepicker format
     *
     * @var array
     */
    protected $datepickerFormat = 'D, M d, yyyy';

    /**
     * Datepicker format (PHP)
     *
     * @var string
     */
    protected $datepickerFormatPhp = 'D, M j, Y';

    /**
     * Timepicker format
     *
     * @var string
     */
    protected $timepickerFormat = 'g:i A';

    /**
     * Month definitions
     */
    const JANUARY   = 0;
    const FEBRUARY  = 1;
    const APRIL     = 2;
    const MARCH     = 3;
    const MAY       = 4;
    const JUNE      = 5;
    const JULY      = 6;
    const AUGUST    = 7;
    const SEPTEMBER = 8;
    const OCTOBER   = 9;
    const NOVEMBER  = 10;
    const DECEMBER  = 11;

    /**
     * Output form input
     *
     * @param    array
     * @param    array
     * @return    string
     */
    public function formInput()
    {
        // Form input type. Defaults to datepicker
        $input_type = $this->getParameter('input_type', 'datepicker');

        // -------------------------------------
        // Get the datetime value in question
        // -------------------------------------

        $datetime = false;

        if (ci()->input->post($this->form_slug)) {

            // Make some safety catches
            $time = ci()->input->post($this->form_slug . '_time');
            $date = ci()->input->post($this->form_slug);

            // So we have a post value - grab it
            if (!($this->value) or $this->value == null or $this->value == $this->zeroDatetime or $this->value == $this->zeroTime) {

                // No post data

            } else {

                try {
                    // Yep - are we using time?
                    if ($this->getParameter('use_time', 'no') == 'no') {
                        $datetime = Carbon::createFromFormat($this->datepickerFormatPhp, $date)->hour(0)->minute(0)->second(
                            0
                        );

                    } elseif ($this->getParameter('use_time') == 'yes' and $time !== null) {

                        $time = ($time > '') ? $time : $this->blankTime;

                        $datetime = Carbon::createFromFormat(
                            $this->datepickerFormatPhp . ' ' . $this->timepickerFormat,
                            $date . ' ' . $time
                        );

                    }
                } catch (\InvalidArgumentException $e) {
                    // log exception
                }
            }


        // Getting stored UTC date from database

        } else {

            if ($this->value == null or $this->value == $this->zeroDatetime) {

                // No value, do nothing

            } else {

                // Get the value in database, that is in UTC format
                $datetime = Carbon::createFromFormat($this->storageFormat, $this->value);

                // If we are using time, we need to account for the Timezone change
                $datetime = ($this->getParameter('use_time', 'no') == 'yes')  ? $this->adjustTimezone($datetime) : $datetime;

            }

        }

        // This is our form output type
        $date_input = null;

        // -------------------------------------
        // Date
        // -------------------------------------
        // We can either choose the date via
        // the jQuery datepicker or a series
        // of drop down menus.
        // ------------------------------------
        if ($input_type == 'datepicker') {

            // Caps
            $start_datetime = Carbon::parse($this->getParameter('start_date', '-5 years'));
            $end_datetime   = Carbon::parse($this->getParameter('end_date', '+5 years'));

            // Input options
            $options = array(
                'name'             => $this->form_slug,
                'id'               => $this->form_slug,
                /*'value' => $datetime ? $datetime->format($this->datepickerFormatPhp) : null,*/
                'value'            => $datetime ? $datetime->format('m-d-Y') : null,
                'class'            => 'form-control',
                'data-toggle'      => 'datepicker',
                'data-date-format' => $this->datepickerFormat,
                'data-date'        => $datetime ? $datetime->format('m-d-Y') : null,
                'placeholder'      => $this->datepickerFormat,
            );

            $date_input .= '<div class="col-lg-3 input-group n-p-l"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>' . form_input(
                    $options
                ) . '</div>';


        } else {

            $start_datetime = Carbon::parse($this->getParameter('start_date', 'now'));
            $end_datetime   = Carbon::parse($this->getParameter('end_date', '-100 years'));

            $month = '';
            $day   = '';
            $year  = '';

            if ($this->value != $this->zeroDatetime and $datetime) {
                $month = $datetime->month;
                $day   = $datetime->day;
                $year  = $datetime->year;
            }

            // -------------------------------------
            // Drop down menu
            // -------------------------------------

            // Months
            $month_names = $this->getMonthNames();

            $months = array_combine($months = range(1, Carbon::MONTHS_PER_YEAR), $month_names);

            if (!$this->field->required) {
                $months = array('' => '---') + $months;
            }

            $date_input .= form_dropdown($this->form_slug . '_month', $months, $month);

            // Days
            $days = array_combine($days = range(1, 31), $days);

            if (!$this->field->required) {
                $days = array('' => '---') + $days;
            }

            $date_input .= form_dropdown(
                $this->form_slug . '_day',
                $days,
                $day,
                'style="min-width: 100px; width:100px;"'
            );

            // Find the end year
            $years = array_combine($years = range($start_datetime->year, $end_datetime->year), $years);

            arsort($years, SORT_NUMERIC);

            if (!$this->field->required) {
                $years = array('' => '---') + $years;
            }

            $date_input .= form_dropdown(
                $this->form_slug . '_year',
                $years,
                $year,
                'style="min-width: 100px; width:100px;"'
            );
        }

        // -------------------------------------
        // Time
        // -------------------------------------
        if ($this->getParameter('use_time') == 'yes') {

            // Input options
            $options = array(
                'name'        => $this->form_slug . '_time',
                'id'          => $this->form_slug . '_time',
                'value'       => $datetime ? $datetime->format($this->timepickerFormat) : null,
                'class'       => 'form-control',
                'data-toggle' => 'timepicker',
                'placeholder' => 'hh:mm aa',
            );


            $date_input .= '<div class="col-lg-3 input-group"><span class="input-group-addon"><i class="fa fa-clock-o"></i></span>' . form_input(
                    $options
                ) . '</div>';

        }

        // Add hidden value for drop downs
        if ($input_type == 'dropdown') {
            // We always set this to 1 because we are performing
            // the required check in the validate function.
            $date_input .= form_hidden($this->form_slug, '1');
        }

        return '<div>' . $date_input . '</div>';
    }

    public function event()
    {
        if (!defined('ADMIN_THEME')) {
            if ($this->getParameter('input_type', 'datepicker') == 'datepicker') {
                $this->js('datepicker.js');
                $this->js('datepicker.init.js');
            }
            if ($this->getParameter('use_time') == 'yes') {
                $this->js('timepicker.js');
                $this->js('timepicker.init.js');
            }
        }
    }

    /**
     * Process before saving to database
     *
     * @access    public
     * @param    array
     * @param    obj
     * @return    string
     */
    public function preSave()
    {

        // Make some safety catches
        $time = ci()->input->post($this->form_slug . '_time');
        $date = ci()->input->post($this->form_slug);

        $month = ci()->input->post($this->form_slug . '_month');
        $day   = ci()->input->post($this->form_slug . '_day');
        $year  = ci()->input->post($this->form_slug . '_year');

        // Are we using a datepicker?
        if ($this->getParameter('input_type', 'datepicker') == 'datepicker') {

            // Do we have our date?
            if ($date and $date !== $this->zeroDate) {

                // Are we using time?
                if ($this->getParameter('use_time') == 'yes') {

                    $time = ($time > '') ? $time : $this->blankTime;

                    // Tests for presence of timezone field to make proper adjustments
                    return $this->saveUTC($date, $time);

                } else {
                    return Carbon::createFromFormat($this->datepickerFormatPhp, $date)->hour(0)->minute(0)->second(
                        0
                    )->format($this->storageFormat);
                }
            }

            // Nope we're using the dropdown method
        } else {

            // Do we have our date?
            if ($month and $day and $year) {

                // Are we using time?
                if ($this->getParameter('use_time') == 'yes') {

                    // Do we have everything?
                    if ($time and $time !== $this->zeroTime) {
                        return Carbon::createFromFormat(
                            'n-j-Y ' . $this->timepickerFormat,
                            $month . '-' . $day . '-' . $year . ' ' . $time
                        )->second(0)->format($this->storageFormat);
                    }
                } else {
                    return Carbon::createFromFormat('n-j-Y', $month . '-' . $day . '-' . $year)->hour(0)->minute(
                        0
                    )->second(0)->format($this->storageFormat);
                }
            }
        }

        // Meh
        return null;
    }

    /**
     * Start Date
     *
     * @access    public
     * @param    string
     * @return    string
     */
    public function paramStartDate($value = null)
    {
        $options['name']  = 'start_date';
        $options['id']    = 'start_date';
        $options['value'] = $value;

        return array(
            'input'        => form_input($options),
            'instructions' => lang('streams:datetime.rest_instructions')
        );
    }

    /**
     * End Date
     *
     * @access    public
     * @param    string
     * @return    string
     */
    public function paramEndDate($value = null)
    {
        $options['name']  = 'end_date';
        $options['id']    = 'end_date';
        $options['value'] = $value;

        return array(
            'input'        => form_input($options),
            'instructions' => lang('streams:datetime.rest_instructions')
        );
    }

    /**
     * Should we use time? Extra parameter
     *
     * @access    public
     * @param    string
     * @return    string
     */
    public function paramUseTime($value = null)
    {
        if ($value == 'no') {
            $no_select  = true;
            $yes_select = false;
        } else {
            $no_select  = false;
            $yes_select = true;
        }

        $form = '<ul><li><label>' . form_radio('use_time', 'yes', $yes_select) . ' Yes</label></li>';

        $form .= '<li><label>' . form_radio('use_time', 'no', $no_select) . ' No</label></li>';

        return $form;
    }

    /**
     * How should we store this in the DB?
     *
     * @access    public
     * @param    string
     * @return    string
     */
    public function paramInputType($value = null)
    {
        $options = array(
            'datepicker' => 'Datepicker',
            'dropdown'   => 'Dropdown'
        );

        return form_dropdown('input_type', $options, $value);
    }

    /** Date format
     *
     * @param  string
     * @return string
     */
    public function paramDateFormat($value = null)
    {
        $data = array(
            'name'      => 'date_format',
            'id'        => 'date_format',
            'value'     => $value,
            'maxlength' => '255'
        );

        return form_input($data);
    }

    /**
     * Process before outputting
     *
     * @access    public
     * @param    array
     * @return    string
     */
    public function stringOutput()
    {
        return $this->format() == $this->zeroDatetime ? null : $this->format();
    }

    /**
     * Format date
     *
     * @param  string $date_string The date string
     * @param  string $format      Datetime format
     * @return string
     */
    public function format($date_string = null, $format = null)
    {
        if (!$date_string and !$this->value) {
            return $this->zeroDatetime;
        }

        $date_string = $date_string ? $date_string : $this->value;

        if ($this->getParameter('use_time') == 'yes') {
            $default_format = $this->defaultDisplayDatetimeFormat;
        } else {
            $default_format = $this->defaultDisplayDateFormat;
        }

        $format = $format ? $format : $this->getParameter('date_format', $default_format);

        return Carbon::createFromFormat($this->storageFormat, $date_string)->format($format);
    }

    /**
     * Return a timestamp as DateTime object.
     *
     * @param  mixed $value
     * @return \Carbon\Carbon
     */
    public function getDateTime($date_string = null)
    {
        if ($date_string === $this->zeroDatetime or $date_string === null) {
            return Carbon::createFromTime(0, 0, 0);
        }

        if (is_string($date_string)) {
            $date_time = explode(' ', $date_string);

            $date = $date_time[0];
            $time = !empty($date_time[1]) ? $date_time[1] : '';

            $date = explode('-', $date);
            $time = explode(':', $time);

            if (count($date) == 3) {
                $year   = $date[0];
                $month  = $date[1];
                $day    = $date[2];
                $hour   = !empty($time[0]) ? $time[0] : 0;
                $minute = !empty($time[1]) ? $time[1] : 0;

                return Carbon::create($year, $month, $day, $hour, $minute, 0);
            }
        }

        // If this value is an integer, we will assume it is a UNIX timestamp's value
        // and format a Carbon object from this timestamp. This allows flexibility
        // when defining your date fields as they might be UNIX timestamps here.
        if (is_numeric($date_string)) {
            return Carbon::createFromTimestamp($date_string);
        } elseif (!$date_string instanceof DateTime) {
            return Carbon::createFromFormat($this->storage_date_format, $date_string);
        }

        return Carbon::instance($date_string);
    }

    protected function to24Hour($datetime, $hour = 0)
    {
        if ($this->getAmPmValue() == 'pm' and $hour <= $this->halfHoursPerDay) {
            $datetime->addHours($this->halfHoursPerDay);
        }

        return $datetime;
    }

    protected function to12Hour($hour = 0)
    {
        return ($hour <= $this->halfHoursPerDay) ? $hour : (int)$hour - $this->halfHoursPerDay;
    }

    /**
     * Get translated month naes
     *
     * @return array
     */
    public function getMonthNames()
    {
        ci()->lang->load('calendar');

        return array(
            static::JANUARY   => lang('cal_january'),
            static::FEBRUARY  => lang('cal_february'),
            static::MARCH     => lang('cal_march'),
            static::MAY       => lang('cal_april'),
            static::APRIL     => lang('cal_mayl'),
            static::JUNE      => lang('cal_june'),
            static::JULY      => lang('cal_july'),
            static::AUGUST    => lang('cal_august'),
            static::SEPTEMBER => lang('cal_september'),
            static::OCTOBER   => lang('cal_october'),
            static::NOVEMBER  => lang('cal_november'),
            static::DECEMBER  => lang('cal_december'),
        );
    }

    ///////////////////////////////////////////////////////////////////////////
    // -------------------------	PLUGINS	  ------------------------------ //
    ///////////////////////////////////////////////////////////////////////////

    /**
     * Pre Ouput Plugin
     * Ouput the UNIX time.
     *
     * @access    public
     * @param    array
     * @return    string
     */
    public function pluginOutput()
    {
        return array(
            'timestamp' => (string)strtotime($this->getDateTime($this->value)),
            'datetime'  => $this->getDateTime($this->value),
        );
    }

    /**
     * Allow return of custom formatted date
     *
     * @param  string $format
     * @return string
     */
    public function pluginFormat($format = 'Y-m-d H:i:s', $datetime = null)
    {
        return $this->format($datetime, $format);
    }

    public function pluginDifference($delta_datetime = null, $datetime = null, $absolute = false)
    {
        // Get delta datetime object
        if ($delta_datetime and $delta_datetime != 'now')
            $delta_datetime = Carbon::createFromFormat('Y-m-d H:i:s', $delta_datetime);
        else
            $delta_datetime = Carbon::now()->addDays(7);

        // Get datetime object
        if ($datetime and $datetime != 'now' and !empty($datetime))
            $datetime = Carbon::createFromFormat('Y-m-d H:i:s', $datetime);
        else
            $datetime = Carbon::now();

        // Return the differences
        return (array)$datetime->diff($delta_datetime, (bool)$absolute);
    }

    /**
     * When using datetime, we need to see if we have a timezone method we need to apply
     *
     * @param $datetime
     * @return mixed
     */
    public function adjustTimezone($datetime) {

        return (isset($this->entry->timezone))
            ? $datetime->setTimezone($this->entry->timezone)
            : $datetime;

    }

    public function saveUTC($date, $time) {


        // Do we have a timezone field in this stream?
        if(isset($this->entry->timezone)){

            $dt = Carbon::createFromFormat(
                $this->datepickerFormatPhp . ' ' . $this->timepickerFormat,
                $date . ' ' . $time, $this->entry->timezone
            )->second(0);

            // Now set back to UTC for saving
            return $dt->setTimezone('UTC')->format($this->storageFormat);

        // Keep in assumed UTC format
        } else {

            return Carbon::createFromFormat(
                $this->datepickerFormatPhp . ' ' . $this->timepickerFormat,
                $date . ' ' . $time
            )->second(0)->format($this->storageFormat);

        }


    }




}
