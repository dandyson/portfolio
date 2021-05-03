<?php 
if ( ! defined( 'ABSPATH' ) ) exit; 
$fontsData      = uaf_get_uploaded_font_data(); ?>

<?php 
if (!empty($fontsData)):
    $sn = 0;
    foreach ($fontsData as $key=>$fontData):
    $sn++
?>
    <div class="font_holder">
        <div class="font_meta">
                <div class="font_name"><?php echo ucfirst($fontData['font_name']); ?></div>
                <?php /* <div class="class_name"><?php echo $fontData['font_name'] ?></div> */ ?>
                <div class="delete_link"><a onclick="if (!confirm('Are you sure ?')){return false;}" href="admin.php?page=use-any-font&tab=font_upload&delete_font_key=<?php echo $key; ?>">Delete</a></div>
        </div>

        <div class="font_demo">
            <span class="<?php echo $fontData['font_name'] ?>">The quick brown fox jumps over the lazy dog</span>
        </div>

        <div class="fontclassname">Class to use this font : <em><strong><?php echo $fontData['font_name']; ?></strong></em></div>
        <br style="clear:both;" />
    </div>

<?php endforeach; ?>

<?php else: ?>
    <div class="dcinfo"><p>No font found. Please click on Add Fonts to upload font</p></div>
<?php endif; ?>