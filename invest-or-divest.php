<?php if(!defined('ABSPATH')) die('Fatal Error');
/*
Plugin Name: Invest or Divest
Plugin URI: #
Description: Divestmedia plugin for Invest or Divest
Author: ljopleda@gmail.com
Version: 1.0
Author URI:
*/
define( 'INVEST_DIVEST_VERSION', '1.0' );
define( 'INVEST_DIVEST_MIN_WP_VERSION', '4.4' );
define( 'INVEST_DIVEST_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'INVEST_DIVEST_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'INVEST_DIVEST_DEBUG' , true );

require_once(INVEST_DIVEST_PLUGIN_DIR . 'vendor/autoload.php');
require_once(INVEST_DIVEST_PLUGIN_DIR . 'lib/class-invest-or-divest.php');
require_once(INVEST_DIVEST_PLUGIN_DIR . 'lib/iod-custom-template.php');
require_once(INVEST_DIVEST_PLUGIN_DIR . 'lib/iod-generate-widget.php');
require_once(INVEST_DIVEST_PLUGIN_DIR . 'lib/class-more-videos.php');

if(class_exists('InvestOrDivest'))
{
  register_activation_hook(__FILE__, array('InvestOrDivest', 'activate'));
  register_deactivation_hook(__FILE__, array('InvestOrDivest', 'deactivate'));
  $InvestOrDivest = new InvestOrDivest();
}

if(class_exists('IODCustomTemplate'))
{
  $IODCustomTemplate = new IODCustomTemplate();
}

if(class_exists('InvestOrDivestWidget'))
{
  $InvestOrDivestWidget = new InvestOrDivestWidget();
}
