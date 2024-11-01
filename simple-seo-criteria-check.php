<?php
/*
 * Plugin Name: Simple SEO Criteria Check
 * Description: The Simple SEO Criteria Check. Open the <a href="tools.php?page=sscc_permlinks">Tools</a> page to get your individual results.
 * Contributors: blapps
 * Version: 2.6
 * Tested up to: 6.3
 * Text Domain: simple-seo-criteria-check
 * Domain Path: /languages
*/

function sscc_load_plugin_textdomain()
{
	load_plugin_textdomain('simple-seo-criteria-check', FALSE, basename(dirname(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'sscc_load_plugin_textdomain');

add_action('admin_menu', 'sscc_seo_check_page');
function sscc_seo_check_page()
{
	if (function_exists('add_submenu_page')) {
		add_submenu_page(
			'tools.php',
			__('Simple SEO Criteria Check'),
			__('Simple SEO Criteria Check'),
			'manage_options',
			'sscc_permlinks',
			'sscc_permlinks'
		);
	}
}

require("sscc-permlinks-tab.php");
require("sscc-images-tab.php");
require("sscc-links-tab.php");


// based on http://wordpress.stackexchange.com/a/58826
function sscc_admin_tabs(){
	$tabs = apply_filters('simple-seo-criteria-check_filter_settingsscreen_tabs',array('sscc_permlinks' => __('Permalinks','simple-seo-criteria-check')));
	$tabContent="";
	if (count($tabs) >= 1) {
		$getpage = esc_attr($_GET['page']);
		if(isset($getpage)){
			$currentId = $getpage;
		} else {
			$currentId = "sscc_permlinks";
		}
		$tabContent .= "<h2 class=\"nav-tab-wrapper\">";
		foreach($tabs as $tabId => $tabName){
			if($currentId == $tabId){
				$class = " nav-tab-active";
			} else{
				$class = "";
			}
			$tabContent .= '<a class="nav-tab'.$class.'" href="?page='.$tabId.'">'.$tabName.'</a>';
		}
		$tabContent .= "</h2>";
	} else {
		$tabContent = "<hr/>";
	}

	return $tabContent;
}


function sscc_admin_style()
{
	global $pluginsURI;
	wp_enqueue_style('css', plugins_url('simple-seo-criteria-check/css/admin-style.css'), false, '1.0');
	//wp_enqueue_style('sscc_admin_css');
	wp_enqueue_script('script', plugins_url('simple-seo-criteria-check/js/sort-table.js'), array ( 'jquery' ), 1.1, true);
}
add_action('admin_enqueue_scripts', 'sscc_admin_style');


function sscc_info_progessbar($pc) {
	if ($pc > '100') $pc = 100;
	$infobar = ' <div style="background: #f1f1f1; width: 100%; height: 24px; margin-bottom: 15px;">
					<div style="background: #4CAF50; width: ' . $pc . '%; height: 24px;">
						<div style="text-align: right; color: #ffffff; padding: 2px;">' . round($pc, 1) . '% &nbsp;</div>
					</div>
				</div>';

	return $infobar;
}