<?php 
/*
 * Plugin Name: ConvertMagick Image Editor Class
 * Version: 1.01
 * Author: elKore team
 * Description: Adds the WP_Image_Editor_Convert class to manipulate images using external ImageMagick "convert" utility. Suitable for linux-hostings that not supported PHP Imagick but have ImageMagick installed, such as 1and1
 */


$ConvertMagickImageEditorPluginDir = 'convertmagick-imageeditor';
$ConvertMagickImageEditorPluginURL = 'http://razuvalov.com/wordpress-plugins/'.$ConvertMagickImageEditorPluginDir.'/'.$ConvertMagickImageEditorPluginDir.'.json';

require_once(dirname(__FILE__).'/classes/plugin-skeleton.class.php');

class ConvertMagickImageEditorPlugin extends AConvertMagickPluginSkeleton {
	
    public function onCreate(){
		parent::onCreate();
		add_filter( 'wp_image_editors', array(&$this, 'addConvertMagickEditor'), 999, 1 );
    }

	public function addConvertMagickEditor($editors){
		$editor = 'WP_Image_Editor_Convert';
		$editors = array_diff( $editors, array( $editor ) );
		array_unshift( $editors, $editor );

		return $editors;
	}

}

$GLOBALS['ConvertMagickPlugin'] = new ConvertMagickImageEditorPlugin($ConvertMagickImageEditorPluginDir, $ConvertMagickImageEditorPluginURL, __FILE__);
