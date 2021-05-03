<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://digitaldyna.com
 * @since      1.0.0
 *
 * @package    CF7_CVM
 * @subpackage CF7_CVM/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version
 *
 * @package    CF7_CVM
 * @subpackage CF7_CVM/admin
 * @author     Support <support@digitaldyna.com>
 */
class CF7_CVM_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	function cf7cvm_add_panel( $panels ) {
		$panels['custom-validation'] = array(
			'title'    => __( 'Custom Validation Messages', 'cf7-custom-validation-message' ),
			'callback' => array( $this, 'cf7cvm_panel_callback_fn' ),
		);
		return $panels;
	}
	
	function cf7cvm_panel_callback_fn( $post ) {
		wp_nonce_field( 'wpcf7_custom_validation_security', 'wpcf7_custom_validation_nonce' );
		?>
		<h2><?php _e( 'Custom Validation', 'cf7-custom-validation-message' ); ?></h2>
		<p><?php _e( '<b>Note:</b>', 'cf7-custom-validation-message' ); ?>
		<?php _e( 'You need to save form to reflect new tag(s).', 'cf7-custom-validation-message' ); ?></p>
		
		<fieldset>
			<?php
			$form_fields = array();
			$form_ID     = $post->id; # get CF7 form ID
			if( $form_ID != null){
				$ContactForm = WPCF7_ContactForm::get_instance( $form_ID );
				$form_fields = $ContactForm->scan_form_tags();
			}else{
				$form_fields = $post->scan_form_tags();
			}
			$arr_values = get_post_meta( $form_ID, '_wpcf7_cv_validation_messages', true );
			// Good idea to make sure things are set before using them
			$arr_values = isset( $arr_values ) ? (array) $arr_values : array();
			// Any of the WordPress data sanitization functions can be used here
			//$arr_values = array_map( 'esc_attr', $arr_values );
			$arr_values = $this->recursive_sanitize_text_field( $arr_values );
			$is_active = isset($arr_values['activate']) && $arr_values['activate'] === "1" ? 1 : 0;
			?>
			<table class="form-table"><tbody>
				<tr>
					<th scope="row"><?php _e( 'Activate', 'cf7-custom-validation-message' ); ?></th>
					<td><input type="checkbox" <?php echo isset($is_active) && $is_active === 1 ? 'checked' : ''; ?> name="wpcf7-cv[activate]"></td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'Your field', 'cf7-custom-validation-message' ); ?></th>
					<td><?php _e( 'Your custom validation message', 'cf7-custom-validation-message' ); ?></td>
				</tr>
				<?php
				foreach ($form_fields as $field) {
					if($field->type === 'submit' || $field->type === 'acceptance'){ continue; }
					$custom_message = isset($arr_values[$field->name]) ? sanitize_text_field($arr_values[$field->name]) : '';
					?>
					<tr>
						<th scope="row">
							<label for="field-<?php echo $field->name; ?>"><?php echo $field->name; ?></label>
						</th>
						<td>
							<input type="text" id="field-<?php echo $field->name; ?>" name="wpcf7-cv[<?php echo $field->name; ?>]" class="regular-text" size="70" value="<?php echo $custom_message; ?>">
						</td>
					</tr>
					<?php
					//confirmation email
					if($field->type === 'email*'){
						$custom_message_confirm = isset($arr_values[$field->name.'_invalid']) ? sanitize_text_field($arr_values[$field->name.'_invalid']) : '';
					?>
					<tr>
						<th scope="row">
							<label for="field-<?php echo $field->name.'_invalid'; ?>"><?php echo $field->name.' (Wrong Email)'; ?></label>
						</th>
						<td>
							<input type="text" id="field-<?php echo $field->name.'_invalid'; ?>" name="wpcf7-cv[<?php echo $field->name.'_invalid'; ?>]" class="regular-text" size="70" value="<?php echo $custom_message_confirm; ?>">
						</td>
					</tr>
					<?php
					}
					//confirmation email end

					//textarea min length
					if( ($field->type === 'text*' || $field->type === 'textarea*') && $this->array_contain_string('minlength:',$field->options) == true){
						$custom_message_confirm = isset($arr_values[$field->name.'_minlength']) ? sanitize_text_field($arr_values[$field->name.'_minlength']) : '';
					?>
					<tr>
						<th scope="row">
							<label for="field-<?php echo $field->name.'_minlength'; ?>"><?php echo $field->name.' (Min Length)'; ?></label>
						</th>
						<td>
							<input type="text" id="field-<?php echo $field->name.'_minlength'; ?>" name="wpcf7-cv[<?php echo $field->name.'_minlength'; ?>]" class="regular-text" size="70" value="<?php echo $custom_message_confirm; ?>">
						</td>
					</tr>
					<?php
					}
					//textarea min length end

					//textarea max length
					if( ($field->type === 'text*' || $field->type === 'textarea*') && $this->array_contain_string('maxlength:',$field->options) == true){
						$custom_message_confirm = isset($arr_values[$field->name.'_maxlength']) ? sanitize_text_field($arr_values[$field->name.'_maxlength']) : '';
					?>
					<tr>
						<th scope="row">
							<label for="field-<?php echo $field->name.'_maxlength'; ?>"><?php echo $field->name.' (Max Length)'; ?></label>
						</th>
						<td>
							<input type="text" id="field-<?php echo $field->name.'_maxlength'; ?>" name="wpcf7-cv[<?php echo $field->name.'_maxlength'; ?>]" class="regular-text" size="70" value="<?php echo $custom_message_confirm; ?>">
						</td>
					</tr>
					<?php
					}
					//textarea max length end

				}
			echo '</tbody></table>'; 
		?>
		</fieldset>

		<?php
	}
	
	/**
	 * storing the custom validation messages in database from backend
	 * @param $contact_form id
	 */
	function cf7cvm_store_messages( $contact_form ) {
		if ( ! isset( $_POST ) || empty( $_POST ) ) {
			return;
		} else {
			if ( ! wp_verify_nonce( sanitize_text_field($_POST['wpcf7_custom_validation_nonce']), 'wpcf7_custom_validation_security' ) ) {
				return;
			}
	
			$form_id = $contact_form->id();
			//$fields  = $this->get_plugin_fields( $form_id );
			if( isset($_POST['wpcf7-cv']) ){
				$fields    = $_POST['wpcf7-cv'];
				$validation_messages = array();
				foreach ( $fields as $name => $value ) {
					if(sanitize_text_field($_POST['wpcf7-cv'][$name]) ==''){ continue; }
					$val = sanitize_text_field($_POST['wpcf7-cv'][$name]);
					if( $name == 'activate' && $val == 'on'){ $val = 1; }
					//if(sanitize_text_field($_POST['wpcf7-cv']['activate']) !='' && sanitize_text_field($_POST['wpcf7-cv']['activate']) =='on'){ $val = 1; }
					//update_post_meta( $form_id, '_wpcf7_cv_' . $name, $val );
					$validation_messages[$name] = $val;
				}
				update_post_meta( $form_id, '_wpcf7_cv_validation_messages', $validation_messages );
			}	
		}
	}

	/**
	 * Recursive sanitation for an array
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
	
	function array_contain_string($str, array $arr)
	{
		foreach($arr as $a) {
			if (strpos($a,$str) !== false) {
				return true;
 			}
			//return false;
		}
	}

}
