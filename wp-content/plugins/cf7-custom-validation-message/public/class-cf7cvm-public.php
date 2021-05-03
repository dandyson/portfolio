<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://digitaldyna.com
 * @since      1.0.0
 *
 * @package    CF7_CVM
 * @subpackage CF7_CVM/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version
 *
 * @package    CF7_CVM
 * @subpackage CF7_CVM/public
 * @author     Support <support@digitaldyna.com>
 */
class CF7_CVM_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	
	public function cf7cv_custom_form_validation($result,$tag) {
		$type = $tag['type'];
		$name = $tag['name'];

		$is_required = $tag->is_required() || 'radio' == $tag->type || 'quiz' == $tag->type;
		
		$allowed_tags = array('text*','email*','textarea*','tel*','url*','checkbox*','number*','date*','select*','radio','file','quiz');
		$minmax_supported_tags = array('text*','email*','textarea*','tel*','quiz','captchar');

		$cf7_form_id = (int)sanitize_text_field($_POST['_wpcf7']);
		$arr_values = get_post_meta( $cf7_form_id, '_wpcf7_cv_validation_messages', true );
		$validatation_msg = isset($arr_values[$name]) ? esc_attr(sanitize_text_field($arr_values[$name])) : wpcf7_get_message( $name );
		
		//check if activation
		$is_active = isset($arr_values['activate']) && $arr_values['activate'] === 1 ? 1 : 0;
		if( $is_active === 0 || $is_required == false || !in_array($type,$allowed_tags)){
			return $result;
		}
		if($type == 'text*' && sanitize_text_field($_POST[$name]) == '' && $is_required){
			$result->invalidate( $name, $validatation_msg );
		}

		if($type == 'email*' && sanitize_text_field($_POST[$name]) == ''  && $is_required){   
			$result->invalidate( $name, $validatation_msg );		
		}

		//for email confirmation
		if($type == 'email*' && sanitize_text_field($_POST[$name]) != ''  && $is_required) {
			if(substr(sanitize_text_field($_POST[$name]), 0, 1) == '.' || !preg_match('/^([*+!.&#$¦\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i', sanitize_text_field($_POST[$name]))) {  
				$confirm_field = $name.'_invalid';
				$validatation_msg = isset($arr_values[$confirm_field]) ? esc_attr(sanitize_text_field($arr_values[$confirm_field])) : wpcf7_get_message( $name );
				$result->invalidate( $name, $validatation_msg );
			} 
		}

		if($type == 'textarea*' && sanitize_text_field($_POST[$name]) == '' && $is_required){   
			$result->invalidate( $name, $validatation_msg );
		}
		
		//for minlength and maxlength
		$value = isset( $_POST[$name] ) ? (string) $_POST[$name] : '';
		if ( '' !== $value && in_array($type,$minmax_supported_tags) ) {
			$maxlength = $tag->get_maxlength_option();
			$minlength = $tag->get_minlength_option();

			if ( $maxlength and $minlength
			and $maxlength < $minlength ) {
				$maxlength = $minlength = null;
			}

			$code_units = wpcf7_count_code_units( stripslashes( $value ) );

			if ( false !== $code_units ) {
				if ( $maxlength and $maxlength < $code_units ) {
					$textarea_maxlength = $name.'_maxlength';
					$validatation_msg = isset($arr_values[$textarea_maxlength]) ? esc_attr(sanitize_text_field($arr_values[$textarea_maxlength])) : wpcf7_get_message( $name );
					$result->invalidate( $name, $validatation_msg );
				} elseif ( $minlength and $code_units < $minlength ) {
					$textarea_minlength = $name.'_minlength';
					$validatation_msg = isset($arr_values[$textarea_minlength]) ? esc_attr(sanitize_text_field($arr_values[$textarea_minlength])) : wpcf7_get_message( $name );
					$result->invalidate( $name, $validatation_msg );
				}
			}
		}

		if($type == 'tel*' && sanitize_text_field($_POST[$name]) == '' && $is_required){   
			$result->invalidate( $name, $validatation_msg );
		}

		if($type == 'url*' && sanitize_text_field($_POST[$name]) == '' && $is_required){   
			$result->invalidate( $name, $validatation_msg );
		}
		
		$checkbox_vals = array();
		if( $type == 'checkbox*' && isset($_POST[$name]) ){
			$checkbox_vals = $this->recursive_sanitize_text_field($_POST[$name]);
		}		
		if($type == 'checkbox*' && $is_required && empty($checkbox_vals) ){
			$result->invalidate( $name, $validatation_msg );
		}

		if($type == 'number*' && sanitize_text_field($_POST[$name]) == '' && $is_required){   
			$result->invalidate( $name, $validatation_msg );
		}

		if($type == 'date*' && sanitize_text_field($_POST[$name]) == '' && $is_required){   
			$result->invalidate( $name, $validatation_msg );
		}

		if($type == 'select*' && sanitize_text_field($_POST[$name]) == '' && $is_required){   
			$result->invalidate( $name, $validatation_msg );
		}

		if($type == 'radio' && sanitize_text_field($_POST[$name]) == '' && $is_required){   
			$result->invalidate( $name, $validatation_msg );
		}

		if($type == 'file*' && sanitize_text_field($_POST[$name]) == '' && $is_required){   
			$result->invalidate( $name, $validatation_msg );
		}

		if($type == 'quiz' && sanitize_text_field($_POST[$name]) == '' && $is_required){   
			$result->invalidate( $name, $validatation_msg );
		}
		
		return $result;
	}

	/**
	 * Recursive sanitation for an array
	 * @since    1.2.1
	 * @param $array
	 * @return mixed
	 */
	function recursive_sanitize_text_field($array) {
		foreach ( $array as $key => &$value ) {
			if ( is_array( $value ) ) {
				$value = $this->recursive_sanitize_text_field($value);
			}
			else {
				$value = sanitize_text_field( $value );
			}
		}

		return $array;
	}


}
