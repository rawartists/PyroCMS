<?php namespace Pyro\Validation;

class Validator
{
    /**
     * Rules
     *
     * @var array
     */
    protected $rules = array();

    protected $errorDelimiterOpen = '<div class="alert alert-danger">';

    protected $errorDelimiterClose = '</div>';

    public function __construct()
    {
        ci()->load->library('form_validation');
        ci()->form_validation->set_error_delimiters($this->errorDelimiterOpen, $this->errorDelimiterClose);
    }

    public function validate($data = array())
    {
        ci()->form_validation->reset_validation();
        ci()->form_validation->set_data($data);
        ci()->form_validation->set_rules($this->rules);

        if (!empty($this->rules)) {
            return ci()->form_validation->run();
        } else {
            return true;
        }
    }

    public function disableRule($key)
    {
        if (isset($this->rules[$key])) {
            unset($this->rules[$key]);
        }
    }

    public function addRule(array $rule)
    {
        $this->rules[] = $rule;
    }

}
