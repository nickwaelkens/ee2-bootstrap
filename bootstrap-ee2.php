<?php
/**
 *  ExpressionEngine2 bootstrap for usage in other environments.
 *  Also allows to parse EE templates in a non-EE env using parse_template();
 *  Author: Nick Waelkens
 *  Author URI: http://www.nickwaelkens.be
 *
 *  Basic usage
 *      $system_path = APPLICATION_PATH . '/../ee2';
 *      include(APPLICATION_PATH . '/bootstrap-ee2.php');
 *      $EE = get_instance();
 *      // do your thing.
 */

/**
 * Assign extra things to config here
 */
$assign_to_config['enable_query_strings'] = true;
$assign_to_config['subclass_prefix'] = 'EE_';

/**
 * Setting $system_path is pretty important
 * Ensure there's a trailing slash for defining stuff below
 */

if (!isset($system_path)) {
    $system_path = "system";
}

if (realpath($system_path) !== FALSE) {
    $system_path = realpath($system_path) . '/';
}

$system_path = rtrim($system_path, '/') . '/';

/**
 * Making sure the actual defining only happens once
 */
if (!defined('SELF')) {
    define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
}
if (!defined('EXT')) {
    define('EXT', '.php');
}
if (!defined('BASEPATH')) {
    define('BASEPATH', str_replace("\\", "/", $system_path . 'codeigniter/system/'));
}
if (!defined('APPATH')) {
    define('APPPATH', $system_path . 'expressionengine/');
}
if (!defined('FCPATH')) {
    define('FCPATH', str_replace(SELF, '', __FILE__));
}
if (!defined('SYSDIR')) {
    define('SYSDIR', trim(strrchr(trim(str_replace("\\", "/", $system_path), '/'), '/'), '/'));
}
if (!defined('UTF8_ENABLED')) {
    define('UTF8_ENABLED', false);
}
if (!defined('CI_VERSION')) {
    define('CI_VERSION', '2.2');
}
if (!defined('DEBUG')) {
    define('DEBUG', false);
}

/**
 * Bootstrap essential EE classes
 */

require BASEPATH . 'core/Common' . EXT;
require APPPATH . 'config/constants' . EXT;

$CFG =& load_class('Config', 'core');
$URI =& load_class('URI', 'core');
$IN =& load_class('Input', 'core');
$OUT =& load_class('Output', 'core');
$LANG =& load_class('Lang', 'core');
$SEC =& load_class('Security', 'core');
$loader = load_class('Loader', 'core');

/**
 * Load the base controller class
 */
require BASEPATH . 'core/Controller' . EXT;

/**
 * Oldskool stuff going on here...
 */
function &get_instance() {
    return CI_Controller::get_instance();
}

class EE_Bootstrap extends CI_Controller {}

/**
 * Create instance of superglobal
 */
$EE = new EE_Bootstrap;

/**
 * Parse actual template file + custom EE {tag}s into HTML.
 *
 * @usage: echo parse_template('includes', 'header');
 *
 * @param $template_group ; name of the folder where the template is in, e.g. 'includes'
 * @param $template ; actual name of the template you want to render, e.g. 'header'
 * note: for some reason, hidden files (.header) do not work
 * @param $template_data ; pass data to the template, e.g. {... dynamic="true" ...}
 * @return string
 */
function parse_template($template_group, $template, $template_data = '')
{
    // EE_Template does all the magic for us, needs to be included
    if (!class_exists('EE_Template')) {
        require APPPATH . 'libraries/Template' . EXT;
    }

    // Get instance of $EE superglobal
    $EE = get_instance();

    // Create a new EE template and set properties EE requires
    $EE->TMPL = new EE_Template;
    $EE->TMPL->cache_status = 'NO_CACHE';
    $EE->TMPL->template = $EE->TMPL->fetch_template($template_group, $template, FALSE, '');

    //store current userdata in temp array to use recipient member data in parsing function
    $temp_userdata = $EE->session->userdata;
    $EE->session->userdata = array();
    $EE->session->userdata['group_id'] = '';

    // Parse EE {tag}s
    $EE->TMPL->template = $EE->TMPL->parse_globals($EE->TMPL->template);

    // Set EE session back like it should. (EE uses custom $_SESSION, just like ZF does)
    $EE->session->userdata = $temp_userdata;

    // Parse
    $EE->TMPL->template = $EE->TMPL->parse_variables_row($EE->TMPL->template, $template_data, FALSE);
    $EE->TMPL->parse($EE->TMPL->template, array(), array(), TRUE);

    return $EE->TMPL->final_template;
}
