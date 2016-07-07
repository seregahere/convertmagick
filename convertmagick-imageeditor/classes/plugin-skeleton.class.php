<?php


require_once( dirname(__FILE__).'/plugin-updates/plugin-update-checker.php');
require_once( dirname(__FILE__).'/ConvertMagick/IConvertMagick.php');
require_once( dirname(__FILE__).'/ConvertMagick/ConvertMagick.php');
require_once( dirname(__FILE__).'/class-wp-image-editor-convert.php');

abstract class AConvertMagickPluginSkeleton {
	protected $optionName = 'convertmagick-skeleton';
	protected $options;
	private $updateUrl = '';
	protected $updateChecker = null;
	public $pluginName = 'skeleton';
	private $pluginFilename;
	
	public function __construct($pluginName, $updateUrl='', $pluginFilename='') {
		if (strlen($pluginName)>0) {
			$this->optionName = 'plugin-'.$pluginName.'-options';
			$this->pluginName = $pluginName;
		}

		if (strlen($updateUrl)) $this->updateUrl = $updateUrl;
		if (strlen($pluginFilename)) $this->pluginFilename = $pluginFilename;
		
		add_action('init', array(&$this, 'init'));
		register_activation_hook( __FILE__, array( $this, 'install' ) );
		
		$this->onCreate();
		
	}
	
	public function install() {
		$opt = get_option($this->optionName, array());
		update_option($this->optionName, $opt);
	}

	public function onCreate(){}
	 
	public function init() {
		$this->options = get_option($this->optionName, array());
		if ($this->updateUrl && $this->pluginFilename) :
		//error_log('call PucFactory::buildUpdateChecker("'.$this->updateUrl.'", "'. $this->pluginFilename .')');
			$this->updateChecker = PucFactory::buildUpdateChecker(
					$this->updateUrl,
					$this->pluginFilename
			);
		endif;
		
	}
	
	
	protected function getValueFromArray($key, &$array){
		return (is_array($array) && array_key_exists($key, $array)) ? $array[$key] : null;
	}
	
	public function getField($field, $postID = null) {
		if (!$postID) {$postID = get_the_ID(); }
	
		if (function_exists('get_field'))
			$res = get_field($field, $postID);
	
		if (!$res)
			$res = get_post_meta( $postID, $field, true );
	
		$res = apply_filters('get_field', $res);
		return $res;
	}
	
	
}
