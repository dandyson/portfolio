<?php if ( ! defined( 'ABSPATH' ) ) exit;  ?>
<div class="dcform">
	<form action="admin.php?page=use-any-font&tab=api" method="post" id="uaf_api_key_form" >
		<p>
				<label>API Key</label>

                <span class="field">
                    <?php if (empty($GLOBALS['uaf_user_settings']['uaf_api_key'])): ?>
                        <input name="uaf_api_key" id="uaf_api_key" type="text" class="large" style="max-width: 400px; width: 100%;" />
                        <br/><br/>
                        <input type="submit" name="ucf_api_key_activate" class="button-primary" value="Verify" />
                        <input type="button" name="uaf_api_key_generate" id="uaf_api_key_generate" class="button-primary" value="Generate Free Lite / Test API Key" onclick="uaf_lite_api_key_generate();" />
                        <a href="https://dineshkarki.com.np/use-any-font/api-key" target="_blank" class="button-primary">Get Premium Key</a>
                    <?php else: ?>
                        <span class="active_key"><?php echo $GLOBALS['uaf_user_settings']['uaf_api_key'];  ?> - Active</span>                           <input type="submit" name="ucf_api_key_deactivate" class="button-primary" value="Remove Key" onclick="if(!confirm('Are you sure ?')){return false;}" />
                    <?php endif;?>
                </span>				
		</p>
        

        <p>                      
                <br/><br/>
                <strong>Note</strong> : API key is needed to connect to our server for font conversion. Our server converts your fonts to required types and sends it back. You can get the premium key from <a href="https://dineshkarki.com.np/use-any-font/api-key" target="_blank">here</a>. You can also generate Lite / Test API key from button above. Lite / Test API only allow single font conversion. 
        </p>
	</form>
</div>