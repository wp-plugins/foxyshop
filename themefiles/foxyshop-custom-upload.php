<?php
if (!$writeUploadInclude) {
	$writeUploadInclude = 1;
	echo '<link rel="stylesheet" href="' . FOXYSHOP_DIR . '/js/uploadify/uploadify.css" type="text/css" media="screen" />'."\n";
	echo '<script type="text/javascript" src="' . FOXYSHOP_DIR . '/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>'."\n";
	echo '<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>'."\n";
	$imagefilename = "file-" . substr(MD5(rand(1000, 99999)."{img}" . date("H:i:s")),1,8);
	$upload_dir = wp_upload_dir();

	//Get Max Upload Limit
	$max_upload = (int)(ini_get('upload_max_filesize'));
	$max_post = (int)(ini_get('post_max_size'));
	$memory_limit = (int)(ini_get('memory_limit'));
	$upload_mb = min($max_upload, $max_post, $memory_limit);
	$foxyshop_max_upload = $upload_mb * 1048576;
	if ($foxyshop_max_upload == 0) $foxyshop_max_upload = "8000000";

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
				sizeLimit : '<?php echo $foxyshop_max_upload; ?>',
				scriptData: { 'newfilename': '<?php echo $imagefilename; ?>_' + $(this).attr("rel") },
				onComplete: function(event,queueID,fileObj,response,data) {
						if (response == "unsupported file type") {
							$("#uploadedFile_" + variationID).html('<span style="color: red;"><?php _e('Invalid File Type'); ?></span>').show();
						} else {
							if (response.indexOf("move_uploaded_file") >= 0) {
								$("#uploadedFile_" + variationID).html('There was an error uploading your image: ' + response);
							} else if (response.indexOf("jpg") >= 0 || response.indexOf("gif") >= 0 || response.indexOf("png") >= 0 || response.indexOf("jpeg") >= 0) {
								$("#uploadedFile_" + variationID).html('<img src="<?php echo $upload_dir['baseurl']; ?>/customuploads/' + response + '?rand=<?php echo rand(35450, 97534); ?>" alt="" />').show();
								$("#FileNameHolder_"+variationID).val(response);
							} else {
								$("#uploadedFile_" + variationID).html('<?php _e('File Uploaded Successfuly.'); ?> <a href="<?php echo $upload_dir['baseurl']; ?>/customuploads/' + response + '?rand=<?php echo rand(35450, 97534); ?>"><?php _e('Click here to view.'); ?></a>').show();
								$("#FileNameHolder_"+variationID).val(response);
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

$uploadRequiredClassName = ($variationRequired ? ' foxyshop_required' : '');

$write .= '<input type="file" class="foxyshop_file_upload" rel="' . $i . '" id="' . esc_attr($product['code']) . '_' . $i . '">'."\n";
if ($variationValue) $write .= '<p>' . $variationValue . '</p>'."\n";
$write .= '<div id="uploadedFile_' . $i . '" class="foxyshop_uploaded_file" style="display: none;"></div>'."\n";
$write .= '<input type="hidden" name="' . esc_attr($variationName) . foxyshop_get_verification($variationName,'--OPEN--') . '" id="FileNameHolder_' . $i . '" value="" class="hiddenimageholder ' . $uploadRequiredClassName . $dkeyclass . '"'. $dkey . ' />'."\n";
$write .= '<div class="clr"></div>';
$write .= '</div>';

?>