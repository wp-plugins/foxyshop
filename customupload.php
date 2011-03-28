<?php
if (!$writeUploadInclude) {
	$writeUploadInclude = 1;
	echo '<link rel="stylesheet" href="' . FOXYSHOP_DIR . '/js/uploadify/uploadify.css" type="text/css" media="screen" />'."\n";
	echo '<script type="text/javascript" src="' . FOXYSHOP_DIR . '/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>'."\n";
	echo '<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>'."\n";
	$imagefilename = "file-" . substr(MD5(rand(1000, 99999)."{img}" . date("H:i:s")),1,8);
	$upload_dir = wp_upload_dir();
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($){
		$('.foxyshop_file_upload').each(function() {
			var variationID = $(this).attr("rel");
			$(this).uploadify({
				uploader  : '<?php  echo FOXYSHOP_DIR; ?>/js/uploadify/uploadify.swf',
				script    : '<?php  echo FOXYSHOP_DIR; ?>/js/uploadify/uploadify.php',
				cancelImg : '<?php  echo FOXYSHOP_DIR; ?>/js/uploadify/cancel.png',
				auto      : true,
				width     : '130',
				height    : '29',
				folder    : '<?php echo str_replace("http" . ($_SERVER['SERVER_PORT'] == 443 ? 's' : '') . "://" . $_SERVER['SERVER_NAME'],"",$upload_dir['baseurl']); ?>/customuploads',
				sizeLimit : '800000',
				scriptData: { 'newfilename': '<?php echo $imagefilename; ?>_' + $(this).attr("rel") },
				onComplete: function(event,queueID,fileObj,response,data) {
						if (response == "unsupported file type") {
							$("#uploadedFile_" + variationID).html('<span style="color: red;"><?php _e('Invalid File Type'); ?></span>').show();
						} else {
							$("#FileNameHolder_"+variationID).val(response);
							if (response.indexOf("jpg") >= 0 || response.indexOf("gif") >= 0 || response.indexOf("png") >= 0 || response.indexOf("jpeg") >= 0) {
								$("#uploadedFile_" + variationID).html('<img src="/wp-content/uploads/customuploads/' + response + '?rand=<?php echo rand(35450, 97534); ?>" alt="" />').show();
							} else {
								$("#uploadedFile_" + variationID).html('<?php _e('File Uploaded Successfuly.'); ?> <a href="/wp-content/uploads/customuploads/' + response + '?rand=<?php echo rand(35450, 97534); ?>"><?php _e('Click here to view.'); ?></a>').show();
							}
						}
					}
			});
		});
	});	
	</script>
	<?php
}

$write .= '<div class="foxyshop_custom_upload_container' . $dkeyclass . '"'. $dkey . '>';

$write .= '<label for="' . esc_attr($product['code']) . '_' . $i . '">' . esc_attr(str_replace('_',' ',$variationName)) . '</label>'."\n";

$write .= '<input type="file" class="foxyshop_file_upload" rel="' . $i . '" id="' . esc_attr($product['code']) . '_' . $i . '">'."\n";
if ($variationValue) $write .= '<p>' . $variationValue . '</p>'."\n";
$write .= '<div id="uploadedFile_' . $i . '" class="foxyshop_uploaded_file" style="display: none;"></div>'."\n";
$write .= '<input type="hidden" name="' . esc_attr($variationName) . foxyshop_get_verification($variationName,'--OPEN--') . '" id="FileNameHolder_' . $i . '" value="" class="' . $dkeyclass . '"'. $dkey . ' />'."\n";
$write .= '<div class="clr"></div>';
$write .= '</div>';

?>