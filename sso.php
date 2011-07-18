<?php
//When Saving Profile, These Actions Sync Data to FoxyCart
add_action('profile_update', 'foxyshop_profile_update');
add_action('user_register', 'foxyshop_profile_add');
function foxyshop_profile_update($user_id) {
	
	//Get User Info
	$foxycart_customer_id = get_user_meta($user_id, 'foxycart_customer_id', true);
	
	//Send Updated Info to FoxyCart
	$foxy_data = array("api_action" => "customer_save", "customer_id" => $foxycart_customer_id, "customer_email" => $_POST['email']);
	if (isset($_POST['pass1'])) $foxy_data['customer_password'] = $_POST['pass1'];
	if (isset($_POST['first_name'])) $foxy_data['customer_first_name'] = $_POST['first_name'];
	if (isset($_POST['last_name'])) $foxy_data['customer_last_name'] = $_POST['last_name'];
	$foxy_response = foxyshop_get_foxycart_data($foxy_data);
	$xml = simplexml_load_string($foxy_response, NULL, LIBXML_NOCDATA);
	$foxycart_customer_id = (string)$xml->customer_id;
	if ($foxycart_customer_id) add_user_meta($user_id, 'foxycart_customer_id', $foxycart_customer_id, true);
}
function foxyshop_profile_add($user_id) {
	if (isset($_POST['pass1'])) {
		$foxy_data = array("api_action" => "customer_save", "customer_email" => $_POST['email'], "customer_password" => $_POST['pass1']);
		if (isset($_POST['first_name'])) $foxy_data['customer_first_name'] = $_POST['first_name'];
		if (isset($_POST['last_name'])) $foxy_data['customer_last_name'] = $_POST['last_name'];
		$foxy_response = foxyshop_get_foxycart_data($foxy_data);
		$xml = simplexml_load_string($foxy_response, NULL, LIBXML_NOCDATA);
		$foxycart_customer_id = (string)$xml->customer_id;
		if ($foxycart_customer_id) add_user_meta($user_id, 'foxycart_customer_id', $foxycart_customer_id, true);
	}
}


//Adds a Login Message When Prompting Users to Login Before Checking Out
if (isset($_GET['foxycart_checkout'])) {
	add_filter('login_message', 'foxyshop_login_message');
	add_action('login_head','foxyshop_login_head');
}
function foxyshop_login_head() { ?>
<style type="text/css">
#login_error, .message { display:none; }
.custom-message {
	-moz-border-radius:3px 3px 3px 3px;
	border-style:solid;
	border-width:1px;
	margin:0 0 16px 8px;
	padding:12px;
}
.login .custom-message {
	background-color:#FFFFE0;
	border-color:#E6DB55;
}
</style><?php
}

function foxyshop_login_message() {
	$message = 'Please login before checking out. <a href="' . get_bloginfo("wpurl") . '/wp-login.php?action=register">Click here to register.</a>';
	return '<p class="custom-message">' . $message . '</p><br />';
}


//Setup Actions
add_action('admin_init', 'foxyshop_user_init');
function foxyshop_user_init() {
	add_action('show_user_profile', 'action_show_user_profile');
	add_action('edit_user_profile', 'action_show_user_profile');
	add_action('personal_options_update', 'action_process_option_update');
	add_action('edit_user_profile_update', 'action_process_option_update');
}

function action_show_user_profile($user) {
	global $foxyshop_settings;
	if (!current_user_can('administrator')) return;
	?>
	<h3><?php _e('FoxyCart User Data') ?></h3>
	<table class="form-table">
	<tr>
	<th><label for="foxycart_customer_id"><?php _e('FoxyCart Customer ID'); ?></label></th>
	<td><input type="text" name="foxycart_customer_id" id="foxycart_customer_id" value="<?php echo esc_attr(get_the_author_meta('foxycart_customer_id', $user->ID) ); ?>" /> <span class="description">Editing is not recommended</span></td>
	</tr>
	</table>

	<?php
	
	//Get User's Subscription Array
	$foxyshop_subscription = unserialize(get_user_meta($user->ID, 'foxyshop_subscription', true));
	if (!is_array($foxyshop_subscription)) $foxyshop_subscription = array();
	
	if (count($foxyshop_subscription) > 0) {
	?>
<h3><?php _e('FoxyCart Subscriptions') ?></h3>
<table class="widefat" cellspacing="0">
    <thead>
    <tr>
        <tr>
            <th class="manage-column column-columnname" scope="col">Product Code</th>
            <th class="manage-column column-columnname" scope="col">Active</th>
            <th class="manage-column column-columnname" scope="col">Actions</th>
        </tr>
    </tr>
    </thead>
    <tbody>
	<?php
	foreach ($foxyshop_subscription as $key => $val) {
		$sub_token = str_replace('https://'.$foxyshop_settings['domain'].'/cart?sub_token=', "", $val['sub_token_url']);
	?>
        <tr class="alternate">
            <td class="column-columnname"><?php echo $key; ?></td>
            <td class="column-columnname"><?php echo ($val['is_active'] == 1 ? "Yes" : "No"); ?></td>
            <td class="column-columnname"><a href="<?php echo $val['sub_token_url']; ?>&amp;cart=checkout" target="_blank">Update Info</a> | <a href="<?php echo $val['sub_token_url']; ?>&amp;sub_cancel=true&amp;cart=checkout" target="_blank">Cancel</a></td>
        </tr>
     <?php
     }
     ?>
    </tbody>
</table>
	<?php
	
	
	}
}

function action_process_option_update($user_id) {
	if (!current_user_can('administrator')) return;
	update_user_meta($user_id, 'foxycart_customer_id', (isset($_POST['foxycart_customer_id']) ? $_POST['foxycart_customer_id'] : ''));
}
?>