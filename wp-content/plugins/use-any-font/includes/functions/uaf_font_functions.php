<?php
if ( ! defined( 'ABSPATH' ) ) exit; 
function uaf_count_uploaded_fonts(){
	$count = 0;
	$fontsRawData   = get_option('uaf_font_data');
	if ((!empty($fontsRawData)) && ($fontsRawData != 'null')){
		$fontsData 		= json_decode($fontsRawData, true);	
		$count 			= count($fontsData);
	}
	return $count;
}

function uaf_get_uploaded_font_data(){
	$fontsRawData   = get_option('uaf_font_data');
	return json_decode($fontsRawData, true);
}

function uaf_get_font_families(){
	$fontsData		= uaf_get_uploaded_font_data();
	$fonts_uaf		= array();
	if (!empty($fontsData)):
		foreach ($fontsData as $key=>$fontData):
			$fonts_uaf[] = $fontData['font_name'];
		endforeach;
	endif;
	return $fonts_uaf;
}

function uaf_save_font_files($font_name, $convertResponse){
	uaf_create_folder(); // CREATE FOLDER IF DOESN"T EXISTS
	$uafPath 				= uaf_path_details();
	$fontNameToStore 		= sanitize_file_name(rand(0,9999).$font_name);
	
	$convertResponseArray = json_decode(stripslashes($convertResponse), true);
	if ($convertResponseArray['global']['status'] == 'ok'):
		$neededFontFormats = array('woff2','woff');
		foreach ($neededFontFormats as $neededFontFormat):
			if ($convertResponseArray[$neededFontFormat]['status'] == 'ok'):
				$fontFileContent = wp_safe_remote_get($convertResponseArray[$neededFontFormat]['filename'], array('timeout'=>'300'));

				if ( is_wp_error( $fontFileContent ) ) {
			        $fontUploadFinalResponse['status']   = 'error';
					$fontUploadFinalResponse['body']	 = $fontFileContent->get_error_message();
					return $fontUploadFinalResponse;
			    }	

			    if ( $fontFileContent['response']['code'] == '200') :			     	
			    	$fontFileContent = wp_remote_retrieve_body( $fontFileContent );
			    	if (!empty($fontFileContent)):
						$newFileName		= $fontNameToStore.'.'.$neededFontFormat;
						$newFilePath		= $uafPath['dir'].$newFileName;
						$fh = fopen($newFilePath, 'w') or die("can't open file. Make sure you have write permission to your upload folder");
						fwrite($fh, $fontFileContent);
						fclose($fh);
					else:
						$fontSaveMsg[$neededFontFormat]['status'] 	= 'error';
						$fontSaveMsg[$neededFontFormat]['body']		= "Couldn't receive $neededFontFormat file";
					endif;
			    else:
			    	$fontSaveMsg[$neededFontFormat]['status'] 	= 'error';
					$fontSaveMsg[$neededFontFormat]['body']		= $neededFontFormat.' : '.$fontFileContent['response']['code'].' '.$fontFileContent['response']['message'];		   
			    endif;
			else:
					$fontSaveMsg[$neededFontFormat]['status'] 	= 'error';
					$fontSaveMsg[$neededFontFormat]['body']		= $convertResponseArray[$neededFontFormat]['msg'];
			endif;
		endforeach;

		if (!empty($fontSaveMsg)):
			$fontUploadFinalResponse['body'] = '';
			foreach ($fontSaveMsg as $formatKey => $formatData):
				if ($fontSaveMsg[$formatKey]['status'] == 'error'):
					$fontUploadFinalResponse['status'] = 'error';
					$fontUploadFinalResponse['body']   .= $formatData['body'].'<br/>';
				endif;
			endforeach;
		else:
			uaf_save_font_entry_to_db($font_name, $fontNameToStore);
			$fontUploadFinalResponse['status']   = 'success';
			$fontUploadFinalResponse['body']	 = 'Font Uploaded';
		endif;
	else:
		$fontUploadFinalResponse['status']   = 'error';
		$fontUploadFinalResponse['body']	 = $convertResponseArray['global']['msg'];
	endif;

	return $fontUploadFinalResponse;
}

function uaf_upload_font_to_server(){
	$font_file_details 	= pathinfo($_FILES['font_file']['name']);
	$file_extension		= strtolower($font_file_details['extension']);	
	$font_size			= $_FILES['font_file']['size'];

	if ((in_array($file_extension, $GLOBALS['uaf_fix_settings']['allowedFontFormats'])) && ($font_size <= uaf_max_upload_size_for_php(true))){
		@set_time_limit(0);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $GLOBALS['uaf_user_settings']['uaf_server_url'].'/uaf_convertor/convert.php');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
		$post = array(
			'fontfile' 		=>  new CURLFile($_FILES['font_file']['tmp_name']),
			'fontfileext' 	=> pathinfo($_FILES['font_file']['name'], PATHINFO_EXTENSION),
			'api_key' 		=> $GLOBALS['uaf_user_settings']['uaf_api_key'],
			'url'			=> $_POST['url'],
			'font_count'	=> $_POST['font_count']
		);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		$convertResponse = curl_exec($ch);
		if(curl_errno($ch)) {
			$fontUploadResponse['status'] 		= 'error';
			$fontUploadResponse['body']		    = 'Error: ' . curl_error($ch);
		} else {
			$CrulStatinfo = curl_getinfo($ch);
			if ($CrulStatinfo['http_code'] == '200'):
				$convertResponseArray = json_decode($convertResponse, true);
				if ($convertResponseArray['global']['status'] == 'error'):
					$fontUploadResponse['status']    = 'error';
					$fontUploadResponse['body']   	 = $convertResponseArray['global']['msg'];
				else:					
					$fontUploadResponse['status']    = 'success';
					$fontUploadResponse['body']   	 = $convertResponse;
				endif;
			else:
				$fontUploadResponse['status']    = 'error';
				$fontUploadResponse['body'] 	 = $convertResponse;
			endif;
		}		
	} else {
		$fontUploadResponse['status']    = 'error';
		$fontUploadResponse['body'] 	 = 'Only '.join(", ",$GLOBALS['uaf_fix_settings']['allowedFontFormats']).' format and font less than '.uaf_max_upload_size_for_php().' MB accepted';
	}
	return $fontUploadResponse;
}

function uaf_path_details(){
	$uaf_upload 		= wp_upload_dir();
	$uaf_upload_dir		= $uaf_upload['basedir'];
	$uaf_upload_dir 	= $uaf_upload_dir . '/useanyfont/';
	$uaf_upload_url		= $uaf_upload['baseurl'];
	$uaf_upload_url 	= $uaf_upload_url . '/useanyfont/';

	$pathReturn['dir'] 	= $uaf_upload_dir;
	$pathReturn['url'] 	= $uaf_upload_url;
	return $pathReturn;
}

function uaf_create_folder() {
	$uaf_upload_path	= uaf_path_details();
	if (! is_dir($uaf_upload_path['dir'])) {
       mkdir( $uaf_upload_path['dir'], 0755 );
    }
}

function uaf_save_font_entry_to_db($font_name, $font_path){
	$fontsRawData 	= get_option('uaf_font_data');
	$fontsData		= json_decode($fontsRawData, true);
	if (empty($fontsData)):
		$fontsData = array();
	endif;
	
	$fontsData[date('ymdhis')]	= array('font_name' => sanitize_title($font_name), 'font_path' => $font_path);
	$updateFontData	= json_encode($fontsData);
	update_option('uaf_font_data',$updateFontData);
	uaf_write_css();
}

function uaf_write_css(){
	$uaf_use_absolute_font_path = $GLOBALS['uaf_user_settings']['uaf_use_absolute_font_path']; // Check if user want to use absolute font path.
	
	if (empty($uaf_use_absolute_font_path)){
		$uaf_use_absolute_font_path = 0;
	}
	
	$uaf_upload_path	= uaf_path_details();
	$uaf_upload_dir 	= $uaf_upload_path['dir'];
	$uaf_upload_url 	= $uaf_upload_path['url'];
	//$uaf_upload_url 	= preg_replace('#^https?:#', '', $uaf_upload_path['url']);
	

	if ($uaf_use_absolute_font_path == 0){ // If user use relative path
		$url_parts = parse_url($uaf_upload_url);
		$uaf_upload_url = "$url_parts[path]";
	}

	ob_start();
		$fontsData		= uaf_get_uploaded_font_data();
		if (!empty($fontsData)):
			foreach ($fontsData as $key=>$fontData): ?>
				@font-face {
					font-family: '<?php echo $fontData['font_name'] ?>';
					src: <?php if (file_exists($uaf_upload_dir.$fontData['font_path'].'.woff2')){ ?>url('<?php echo $uaf_upload_url.$fontData['font_path'] ?>.woff2') format('woff2'),
						<?php } ?>url('<?php echo $uaf_upload_url.$fontData['font_path'] ?>.woff') format('woff');
					font-display: <?php echo $GLOBALS['uaf_user_settings']['uaf_font_display_property']; ?>;
				}

				.<?php echo $fontData['font_name'] ?>{font-family: '<?php echo $fontData['font_name'] ?>' !important;}

		<?php
		endforeach;
		endif;	
			
		$fontsImplementRawData 	= get_option('uaf_font_implement');
		$fontsImplementData		= json_decode($fontsImplementRawData, true);
		if (!empty($fontsImplementData)):
			foreach ($fontsImplementData as $key=>$fontImplementData): ?>
				<?php echo $fontImplementData['font_elements']; ?>{
					font-family: '<?php echo $fontsData[$fontImplementData['font_key']]['font_name']; ?>' !important;
				}
		<?php
			endforeach;
		endif;	
		$uaf_style = ob_get_contents();
		$uafStyleSheetPath	= $uaf_upload_path['dir'].'/uaf.css';
		$fh = fopen($uafStyleSheetPath, 'w') or die("Can't open file");
		fwrite($fh, $uaf_style);
		fclose($fh);
	ob_end_clean();
	
	ob_start();
		$fontsData		= uaf_get_uploaded_font_data();
		if (!empty($fontsData)):
			foreach ($fontsData as $key=>$fontData): ?>
				@font-face {
					font-family: '<?php echo $fontData['font_name'] ?>';
					src: <?php if (file_exists($uaf_upload_dir.$fontData['font_path'].'.woff2')){ ?>url('<?php echo $uaf_upload_url.$fontData['font_path'] ?>.woff2') format('woff2'),
						<?php } ?>url('<?php echo $uaf_upload_url.$fontData['font_path'] ?>.woff') format('woff');
				}

				.<?php echo $fontData['font_name'] ?>{font-family: '<?php echo $fontData['font_name'] ?>' !important;}

				.et_gf_<?php echo $fontData['font_name'] ?>{background:none !important;font-family:<?php echo $fontData['font_name'] ?>;text-indent:0 !important;font-size:25px;}

		<?php
		endforeach;
		endif;
		$uaf_style = ob_get_contents();
		$uafStyleSheetPath	= $uaf_upload_path['dir'].'/admin-uaf.css';
		$fh = fopen($uafStyleSheetPath, 'w') or die("Can't open file");
		fwrite($fh, $uaf_style);
		fclose($fh);
		
		$uafStyleSheetPath	= $uaf_upload_path['dir'].'/admin-uaf-rtl.css';
		$fh = fopen($uafStyleSheetPath, 'w') or die("Can't open file");
		fwrite($fh, $uaf_style);
		fclose($fh);
	ob_end_clean();
	update_option('uaf_css_updated_timestamp', time()); // Time entry for stylesheet version
	update_option('uaf_site_url', base64_encode(home_url()));
}


function uaf_get_language_selector(){
	$enableMultiLang 	= '';
	$returnSelectHTML 	= '';
	if ($GLOBALS['uaf_user_settings']['uaf_enable_multi_lang_support'] == 1){
		$enableMultiLang = TRUE;
		$supported_multi_lang_plugins = $GLOBALS['uaf_fix_settings']['supported_multi_lang_plugins'];
		foreach ($supported_multi_lang_plugins as $key => $plugin_name) {
			if (is_plugin_active($plugin_name)){
				$active_multi_lang_plugin = $plugin_name;
			}
			//echo $active_multi_lang_plugin;
		}

		if (isset($active_multi_lang_plugin)){			
			switch ($active_multi_lang_plugin) {
				case 'polylang/polylang.php': // WHEN POLYLANG PLUGIN IS ACTIVATED.
						$active_languages = pll_languages_list(array('fields'=>''));
						foreach ($active_languages as $key => $active_language) {
							$lang_select_data[$active_language->w3c] = $active_language->name;
						}
					break;
				case 'polylang-pro/polylang.php': // WHEN POLYLANG PRO PLUGIN IS ACTIVATED.
						$active_languages = pll_languages_list(array('fields'=>''));
						foreach ($active_languages as $key => $active_language) {
							$lang_select_data[$active_language->w3c] = $active_language->name;
						}
					break;
				case 'sitepress-multilingual-cms/sitepress.php': // WHEN WPML PLUGIN IS ACTIVATED.
						$active_languages = icl_get_languages();
						foreach ($active_languages as $key => $active_language) {
							$lang_select_data[str_replace('_', '-',$active_language['default_locale'])] = $active_language['translated_name'].' ('.$active_language["native_name"].')';
						}
					break;
			}

			$returnSelectHTML = '<select style="width:200px;" class="uaf_required" name="language"><option selected="selected" value="">- Select - </option><option value="all_lang">All Languages</option>';
			foreach ($lang_select_data as $locale => $lang_name) {
				//$returnSelectHTML .= '<option value="body.language-'.$locale.'">'.$lang_name.'</option>';
				$returnSelectHTML .= '<option value="html:lang('.$locale.')">'.$lang_name.'</option>';		
			}
			$returnSelectHTML .= '</select>';
		} else {
			$returnSelectHTML = "You don't have multi lingual plugin active which is supported by Use Any Font.";
		}
	}
	
	
	$return['enableMultiLang'] 	= $enableMultiLang;
	$return['selectHTML'] 		= $returnSelectHTML;
	return $return;
}

function uaf_save_font_assign(){
	$fontsImplementRawData 	= get_option('uaf_font_implement');
	$fontsImplementData		= json_decode($fontsImplementRawData, true);
	if (empty($fontsImplementData)):
		$fontsImplementData = array();
	endif;
	
    $fontElements 	= array();
	$fontElements[] = @join(', ',$_POST['elements']);
	$fontElements[] = @join(', ',array_filter(array_map('trim',explode("\n", trim($_POST['custom_elements'])))));
	$fontElements 	= array_filter(array_map('trim',$fontElements));
	$finalElements  = join(', ', $fontElements);	
    $finalElements  = uaf_langutizse_elements($finalElements);

	if (!empty($finalElements) && !empty($_POST['font_key'])){
		$fontsImplementData[date('ymdhis')]	= array(
											'font_key' 		=> $_POST['font_key'], 
											'font_elements' => $finalElements
										);
		$updateFontsImplementData		= json_encode($fontsImplementData);
		update_option('uaf_font_implement',$updateFontsImplementData);
		uaf_write_css();
		$return['status']   = 'success';
		$return['body'] 	= 'Font Assigned';
	} else {
		$return['body']   = "Couldn't assign font. Please select font and atleast one element or add a custom element";
        $return['status']   = "error";
	}
	return $return;
}

function uaf_langutizse_elements($finalElements){
	if (isset($_POST['language']) && ($_POST['language'] != 'all_lang')){
          $finalElementArray = explode(',', $finalElements);
          $finalElementArray = array_map('trim', $finalElementArray);
          $prefixed_array    = preg_filter('/^/', $_POST['language'].' ', $finalElementArray);
          $finalElements  = join(', ', $prefixed_array);
    }
    return $finalElements;
}

function uaf_delete_font(){
	$uaf_paths 		= uaf_path_details();

	$fontsData		= uaf_get_uploaded_font_data();
	$key_to_delete	= $_GET['delete_font_key'];
	
	@unlink(realpath($uaf_paths['dir'].$fontsData[$key_to_delete]['font_path'].'.woff2'));
	@unlink(realpath($uaf_paths['dir'].$fontsData[$key_to_delete]['font_path'].'.woff'));
	@unlink(realpath($uaf_paths['dir'].$fontsData[$key_to_delete]['font_path'].'.eot'));
	unset($fontsData[$key_to_delete]);
	$updateFontData	= json_encode($fontsData);
	update_option('uaf_font_data',$updateFontData);
	
	$fontsImplementRawData 	= get_option('uaf_font_implement');
	$fontsImplementData		= json_decode($fontsImplementRawData, true);

	if (!empty($fontsImplementData)){
		foreach ($fontsImplementData as $implement_key => $font_assign_array) {
			if ($key_to_delete == $font_assign_array['font_key']){
				unset($fontsImplementData[$implement_key]);
			}
		}

		$updatefontsImplementData	= json_encode($fontsImplementData);
		update_option('uaf_font_implement',$updatefontsImplementData);
	}
	
	$return['status']   = 'success';
	$return['body'] 	= 'Font Deleted';
	uaf_write_css();
	return $return;
}

function uaf_delete_font_assign(){
	$fontsImplementRawData 	= get_option('uaf_font_implement');
	$fontsImplementData		= json_decode($fontsImplementRawData, true);
	$key_to_delete			= $_GET['delete_font_assign_key'];
	unset($fontsImplementData[$key_to_delete]);
	$updateFontsImplementData		= json_encode($fontsImplementData);
	update_option('uaf_font_implement',$updateFontsImplementData);
	uaf_write_css();
	$return['status']   = 'success';
	$return['body'] 	= 'Font assign removed';
	return $return;
}