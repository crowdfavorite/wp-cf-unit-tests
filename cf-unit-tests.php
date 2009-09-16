<?php
/*
Plugin Name: WordPress CMS Unit Tests 
Plugin URI: http://crowdfavorite.com/wordpress/plugins/cms-unit-tests 
Description: Unit testing framework for WordPress CMS web sites. 
Version: 1.0dev 
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
*/

// ini_set('display_errors', '1'); ini_set('error_reporting', E_ALL);

if (!defined('PLUGINDIR')) {
	define('PLUGINDIR','wp-content/plugins');
}

load_plugin_textdomain('cms-unit-tests');

function cfut_init() {
// TODO
}
add_action('init', 'cfut_init');


function cfut_request_handler() {
	if (!empty($_GET['cf_action'])) {
		switch ($_GET['cf_action']) {
			case 'cfut_admin_js':
				cfut_admin_js();
				break;
			case 'cfut_admin_css':
				cfut_admin_css();
				die();
				break;
		}
	}
	if (!empty($_POST['cf_action'])) {
		switch ($_POST['cf_action']) {
			case 'cfut_update_settings':
				cfut_save_settings();
				wp_redirect(trailingslashit(get_bloginfo('wpurl')).'wp-admin/options-general.php?page='.basename(__FILE__).'&updated=true');
				die();
				break;
		}
	}
}
add_action('init', 'cfut_request_handler');

function cfut_admin_js() {
	header('Content-type: text/javascript');
// TODO
	die();
}

wp_enqueue_script('cfut_admin_js', trailingslashit(get_bloginfo('url')).'?cf_action=cfut_admin_js', array('jquery'));


function cfut_admin_css() {
	header('Content-type: text/css');
?>
fieldset.options div.option {
	background: #EAF3FA;
	margin-bottom: 8px;
	padding: 10px;
}
fieldset.options div.option label {
	display: block;
	float: left;
	font-weight: bold;
	margin-right: 10px;
	width: 150px;
}
fieldset.options div.option span.help {
	color: #666;
	font-size: 11px;
	margin-left: 8px;
}
<?php
	die();
}

function cfut_admin_head() {
	echo '<link rel="stylesheet" type="text/css" href="'.trailingslashit(get_bloginfo('url')).'?cf_action=cfut_admin_css" />';
}
add_action('admin_head', 'cfut_admin_head');


/*
$example_settings = array(
	'key' => array(
		'type' => 'int',
		'label' => 'Label',
		'default' => 5,
		'help' => 'Some help text here',
	),
	'key' => array(
		'type' => 'select',
		'label' => 'Label',
		'default' => 'val',
		'help' => 'Some help text here',
		'options' => array(
			'value' => 'Display'
		),
	),
);
*/
$cfut_settings = array(
	'cfut_' => array(
		'type' => 'string',
		'label' => '',
		'default' => '',
		'help' => '',
	),
	'cfut_' => array(
		'type' => 'int',
		'label' => '',
		'default' => 5,
		'help' => '',
	),
	'cfut_' => array(
		'type' => 'select',
		'label' => '',
		'default' => '',
		'help' => '',
		'options' => array(
			'' => ''
		),
	),

);

function cfut_setting($option) {
	$value = get_option($option);
	if (empty($value)) {
		global $cfut_settings;
		$value = $cfut_settings[$option]['default'];
	}
	return $value;
}

function cfut_admin_menu() {
	if (current_user_can('manage_options')) {
		add_options_page(
			__('CMS Unit Tests', 'cms-unit-tests')
			, __('CMS Unit Tests', 'cms-unit-tests')
			, 10
			, basename(__FILE__)
			, 'cfut_settings_form'
		);
	}
}
add_action('admin_menu', 'cfut_admin_menu');

function cfut_plugin_action_links($links, $file) {
	$plugin_file = basename(__FILE__);
	if (basename($file) == $plugin_file) {
		$settings_link = '<a href="options-general.php?page='.$plugin_file.'">'.__('Settings', 'cms-unit-tests').'</a>';
		array_unshift($links, $settings_link);
	}
	return $links;
}
add_filter('plugin_action_links', 'cfut_plugin_action_links', 10, 2);

if (!function_exists('cf_settings_field')) {
	function cf_settings_field($key, $config) {
		$option = get_option($key);
		if (empty($option) && !empty($config['default'])) {
			$option = $config['default'];
		}
		$label = '<label for="'.$key.'">'.$config['label'].'</label>';
		$help = '<span class="help">'.$config['help'].'</span>';
		switch ($config['type']) {
			case 'select':
				$output = $label.'<select name="'.$key.'" id="'.$key.'">';
				foreach ($config['options'] as $val => $display) {
					$option == $val ? $sel = ' selected="selected"' : $sel = '';
					$output .= '<option value="'.$val.'"'.$sel.'>'.htmlspecialchars($display).'</option>';
				}
				$output .= '</select>'.$help;
				break;
			case 'textarea':
				$output = $label.'<textarea name="'.$key.'" id="'.$key.'">'.htmlspecialchars($option).'</textarea>'.$help;
				break;
			case 'string':
			case 'int':
			default:
				$output = $label.'<input name="'.$key.'" id="'.$key.'" value="'.htmlspecialchars($option).'" />'.$help;
				break;
		}
		return '<div class="option">'.$output.'<div class="clear"></div></div>';
	}
}

function cfut_settings_form() {
	global $cfut_settings;

	print('
<div class="wrap">
	<h2>'.__('CMS Unit Tests', 'cms-unit-tests').'</h2>
	<form id="cfut_settings_form" name="cfut_settings_form" action="'.get_bloginfo('wpurl').'/wp-admin/options-general.php" method="post">
		<input type="hidden" name="cf_action" value="cfut_update_settings" />
		<fieldset class="options">
	');
	foreach ($cfut_settings as $key => $config) {
		echo cf_settings_field($key, $config);
	}
	print('
		</fieldset>
		<p class="submit">
			<input type="submit" name="submit" value="'.__('Save Settings', 'cms-unit-tests').'" class="button-primary" />
		</p>
	</form>
</div>
	');
}

function cfut_save_settings() {
	if (!current_user_can('manage_options')) {
		return;
	}
	global $cfut_settings;
	foreach ($cfut_settings as $key => $option) {
		$value = '';
		switch ($option['type']) {
			case 'int':
				$value = intval($_POST[$key]);
				break;
			case 'select':
				$test = stripslashes($_POST[$key]);
				if (isset($option['options'][$test])) {
					$value = $test;
				}
				break;
			case 'string':
			case 'textarea':
			default:
				$value = stripslashes($_POST[$key]);
				break;
		}
		update_option($key, $value);
	}
}


if (!function_exists('get_snoopy')) {
	function get_snoopy() {
		include_once(ABSPATH.'/wp-includes/class-snoopy.php');
		return new Snoopy;
	}
}

//a:22:{s:11:"plugin_name";s:24:"WordPress CMS Unit Tests";s:10:"plugin_uri";s:57:"http://crowdfavorite.com/wordpress/plugins/cms-unit-tests";s:18:"plugin_description";s:50:"Unit testing framework for WordPress CMS web sites";s:14:"plugin_version";s:6:"1.0dev";s:6:"prefix";s:4:"cfut";s:12:"localization";s:14:"cms-unit-tests";s:14:"settings_title";s:14:"CMS Unit Tests";s:13:"settings_link";s:14:"CMS Unit Tests";s:4:"init";s:1:"1";s:7:"install";b:0;s:9:"post_edit";b:0;s:12:"comment_edit";b:0;s:6:"jquery";b:0;s:6:"wp_css";b:0;s:5:"wp_js";b:0;s:9:"admin_css";s:1:"1";s:8:"admin_js";s:1:"1";s:15:"request_handler";s:1:"1";s:6:"snoopy";s:1:"1";s:11:"setting_cat";b:0;s:14:"setting_author";b:0;s:11:"custom_urls";b:0;}

?>