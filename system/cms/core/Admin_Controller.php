<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Pyro\Module\Addons\ThemeOptionModel;

/**
 * This is the basis for the Admin class that is used throughout PyroCMS.
 * Code here is run before admin controllers
 *
 * @copyright   Copyright (c) 2012, PyroCMS LLC
 * @package     PyroCMS\Core\Controllers
 */
class Admin_Controller extends MY_Controller
{
    /**
     * Admin controllers can have sections, normally an arbitrary string
     *
     * @var string
     */
    protected $section = null;

    /**
     * Load language, check flashdata, define https, load and setup the data
     * for the admin theme
     */
    public function __construct()
    {
        // Start Benchmarking
        ci()->benchmark->mark('admin_controller_start');

        ci()->benchmark->mark('admin_controller_construct_my_controller_start');
        parent::__construct();
        ci()->benchmark->mark('admin_controller_construct_my_controller_end');

        // Load the Language files ready for output
        $this->lang->load('admin');
        $this->lang->load('buttons');

        ci()->benchmark->mark('admin_controller_check_access_start');

        // Show error and exit if the user does not have sufficient permissions
        if (!self::checkAccess()) {
            $this->session->set_flashdata('error', lang('cp:access_denied'));
            redirect();
        }

        ci()->benchmark->mark('admin_controller_check_access_end');

        // If the setting is enabled redirect request to HTTPS
        if (Settings::get('admin_force_https') and strtolower(substr(current_url(), 4, 1)) != 's') {
            redirect(str_replace('http:', 'https:', current_url()) . '?session=' . session_id());
        }

        ci()->benchmark->mark('admin_controller_admin_theme_start');
            $this->load->helper('admin_theme');
        ci()->benchmark->mark('admin_controller_admin_theme_end');

        ci()->benchmark->mark('admin_controller_admin_theme_manager_start');
            $theme = $this->themeManager->locate(Settings::get('admin_theme'));
        ci()->benchmark->mark('admin_controller_admin_theme_manager_end');

        // Using a bad slug? Weak
        if (is_null($theme)) {
            show_error('This site has been set to use an admin theme that does not exist.');
        }

        $this->theme = ci()->theme = $theme;

        // make a constant as this is used in a lot of places
        defined('ADMIN_THEME') or define('ADMIN_THEME', $this->theme->model->slug);

        // Set the location of assets
        Asset::add_path('theme', $this->theme->web_path . '/');
        Asset::set_path('theme');

        ci()->benchmark->mark('admin_controller_register_widget_controllers_start');
        $this->registerWidgetLocations();
        ci()->benchmark->mark('admin_controller_register_widget_controllers_end');

        // Active Admin Section (might be null, but who cares)
        $this->template->active_section = $this->section;


        ci()->benchmark->mark('admin_controller_trigger_events_start');
        Events::trigger('admin_controller');
        ci()->benchmark->mark('admin_controller_trigger_events_end');



        // Admin Menu
        ci()->benchmark->mark('admin_controller_building_navigation_start');
        if (is_logged_in()) {
            $this->buildAdminMenu();
        ci()->benchmark->mark('admin_controller_building_navigation_end');
        }

        // Template configuration
        ci()->benchmark->mark('admin_controller_template_config_start');
        $this->template
            ->enable_parser(false)
            ->set('theme_options', (object)$this->theme->model->getOptionValues())
            ->set_theme(ADMIN_THEME)
            ->set_layout('default', 'admin');
        ci()->benchmark->mark('admin_controller_template_config_end');


        // trigger the run() method in the selected admin theme
        ci()->benchmark->mark('admin_controller_run_method_start');
        $class = 'Theme_' . ucfirst($this->theme->model->slug);
        call_user_func(array(new $class, 'run'));
        ci()->benchmark->mark('admin_controller_run_method_end');

        // End Benchmarkfor Admin Controller
        ci()->benchmark->mark('admin_controller_end');
    }

    /**
     * Checks to see if a user object has access rights to the admin area.
     *
     * @return boolean
     */
    private function checkAccess()
    {
        // These pages get past permission checks
        $ignored_pages = array('admin/login', 'admin/logout', 'admin/help');

        // Check if the current page is to be ignored
        $current_page = $this->uri->segment(1, '') . '/' . $this->uri->segment(2, 'index');

        // Dont need to log in, this is an open page
        if (in_array($current_page, $ignored_pages)) {
            return true;
        }

        if (!$this->current_user) {

            // save the location they were trying to get to
            $this->session->set_userdata('admin_redirect', $this->uri->uri_string());
            redirect('admin/login');

            // Well they at least better have permissions!
        }
        if ($this->current_user) {

            if ($this->current_user->isSuperUser()) {
                return true;

                // We are looking at the index page. Show it if they have ANY admin access at all
            } elseif ($this->current_user->is_blocked) {
                $this->sentry->logout($this->current_user);
                throw new \Exception('Your account has been blocked.');
            } elseif ($current_page === 'admin/index' && $this->current_user->hasAccess('admin.general')) {
                return true;
            }

            // Check if the current user can view that page
            return $this->current_user->hasAccess("{$this->module}.*");
        }

        // god knows what this is... erm...
        return false;
    }

    /**
     * Let the Frontend know where Widgets are hiding
     */
    protected function registerWidgetLocations()
    {
        $this->widgetManager->setLocations(
            array(
                SHARED_ADDONPATH . 'themes/' . ADMIN_THEME . '/widgets/',
                APPPATH . 'themes/' . ADMIN_THEME . '/widgets/',
                ADDONPATH . 'themes/' . ADMIN_THEME . '/widgets/',
                APPPATH . 'widgets/',
                ADDONPATH . 'widgets/',
                SHARED_ADDONPATH . 'widgets/',
            )
        );
    }

    protected function buildAdminMenu()
    {

        // Cached menu key for each user
        $menu_key = 'my_admin_menu_' . ci()->current_user->id;

        // If not cached menu, build it
        if (!$my_menu = ci()->cache->get($menu_key)) {
        //if (1 == 1) {

            // -------------------------------------
            // Build Admin Navigation
            // -------------------------------------
            // We'll get all of the backend modules
            // from the DB and run their module items.
            // -------------------------------------


            // Here's our menu array.
            $menu_items = array();

            // This array controls the order of the admin items.
            $this->template->menu_order = array(
                array(
                    'before' => '<i class="fa fa-book"></i>',
                    'title'  => 'lang:cp:nav_content',
                    'items'  => array(),
                ),
                array(
                    'before' => '<i class="fa fa-sitemap"></i>',
                    'title'  => 'lang:cp:nav_structure',
                    'items'  => array(),
                ),
                array(
                    'before' => '<i class="fa fa-hdd-o"></i>',
                    'title'  => 'lang:cp:nav_data',
                    'items'  => array(),
                ),
                array(
                    'before' => '<i class="fa fa-group"></i>',
                    'title'  => 'lang:cp:nav_users',
                    'items'  => array(),
                ),
            );

            $modules = $this->moduleManager->getAllEnabled(
                array(
                    'is_backend' => true,
                )
            );

            foreach ($modules as $module) {

                // Only enabled ones
                if (!module_enabled($module['slug'])) {
                    continue;
                }

                // If we do not have an admin_menu function, we use the
                // regular way of checking out the details.php data.
                if ($module['menu'] and ($this->current_user->hasAccess($module['slug'] . '.*'))) {

                    // Legacy module routing. This is just a rough
                    // re-route and modules should change using their
                    // upgrade() details.php functions.
                    if ($module['menu'] == 'utilities') {
                        $module['menu'] = 'data';
                    }
                    if ($module['menu'] == 'design') {
                        $module['menu'] = 'structure';
                    }

                    $menu_items['lang:cp:nav_' . $module['menu']][$module['name']] = 'admin/' . $module['slug'];
                }

                // If a module has an admin_menu function, then
                // we simply run that and allow it to manipulate the
                // menu array.
                if (method_exists($module['module'], 'admin_menu')) {
                    $module['module']->admin_menu($menu_items);
                }
            }

            // Order the menu items. We go by our menu_order array.
            $ordered_menu = array();

            foreach ($this->template->menu_order as $order) {

                // We need to follow standards
                if (isset($order['title']) and isset($menu_items[$order['title']])) {

                    // Add our menu starter
                    $ordered_menu[lang_label($order['title'])] = $order;

                    // Do we have items or a URI?
                    if (is_array($menu_items[$order['title']])) {
                        $ordered_menu[lang_label($order['title'])]['items'] = $menu_items[$order['title']];
                    } elseif (is_string($menu_items[$order['title']])) {
                        $ordered_menu[lang_label($order['title'])]['uri'] = $menu_items[$order['title']];
                        unset($ordered_menu[lang_label($order['title'])]['items']);
                    }

                    // Bai
                    unset($menu_items[$order['title']]);
                }
            }

            // Any stragglers?
            if ($menu_items) {
                $translated_menu_items = array();

                // translate any additional top level menu keys so the array_merge works
                foreach ($menu_items as $key => $menu_item) {
                    $translated_menu_items[lang_label($key)] = $menu_item;
                }

                $ordered_menu = array_merge_recursive($ordered_menu, $translated_menu_items);
            }

            ksort($ordered_menu);

            // Trigger an event so modules can mess with the
            // menu items array via the events structure.
            $event_output = Events::trigger('admin_menu', $ordered_menu, 'array');

            // If we get an array, we assume they have altered the menu items
            // and are returning them to us to use.
            if (isset($event_output[0]) and is_array($event_output[0])) {
                $ordered_menu = $event_output[0];
            }

            $my_menu = $ordered_menu;

            // Save to cache
            ci()->cache->put($menu_key, $ordered_menu, 3000);
        }

        // And there we go! These are the admin menu items.
        $this->template->menu_items = $my_menu;

    }

}