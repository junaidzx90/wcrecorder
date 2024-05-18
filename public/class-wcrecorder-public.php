<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://fiverr.com/junaidzx90
 * @since      1.0.0
 *
 * @package    Wcrecorder
 * @subpackage Wcrecorder/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wcrecorder
 * @subpackage Wcrecorder/public
 * @author     Devjoo <contact@easeare.com>
 */
class Wcrecorder_Public {

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

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wcrecorder_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wcrecorder_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wcrecorder-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wcrecorder_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wcrecorder_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name. "-recoderjs", plugin_dir_url( __FILE__ ) . 'js/recoder.min.js', array( ), $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wcrecorder-public.js', array( 'jquery', $this->plugin_name. "-recoderjs" ), $this->version, true );
		wp_localize_script( $this->plugin_name, "wcsobj", array(
			'ajaxurl' => admin_url("admin-ajax.php"),
			'sounds' => $this->get_all_user_records()
		) );
	}

	function get_all_user_records(){
		$orders = $this->get_all_order_sounds();

		$data = [];
		foreach($orders as $order_id){
			$soundUrl = get_post_meta($order_id, 'wcsound_record', true);
			if(!empty($soundUrl)){
				$data[] = [
					'text' => basename($soundUrl),
					'value' => $soundUrl
				];
			}
		}

		return json_encode($data);
	}

	function custom_checkout_field($checkout){
		woocommerce_form_field(
			'wcsound_record', 
			array(
				'type'        => 'hidden',
				'required'    => true,
				'label'       => 'Sound Response',
				'description' => '',
			),
			$checkout->get_value('wcsound_record')
		);

		echo '<div id="wcsoundbox"></div>';
	}

	function customised_checkout_field_process(){
		if (!$_POST['wcsound_record']) wc_add_notice(__('Sound Field is a Required Field!') , 'error');
	}

	function custom_checkout_field_update_order_meta($order_id){
		if (!empty($_POST['wcsound_record'])) {
			update_post_meta($order_id, 'wcsound_record',sanitize_text_field($_POST['wcsound_record']));
		}
	}

	function wc_sound_in_order_view_page($order){
		$soundUrl = get_post_meta($order->get_id(), 'wcsound_record', true);
		echo "<p><strong>Sound:</strong> <audio class='wcsaudio' controls src='$soundUrl'></audio></p>";
	}

	function display_custom_field_on_order_received($order_id) {
		$soundUrl = get_post_meta($order_id, 'wcsound_record', true);
		echo "<p><strong>Sound:</strong> <audio class='wcsaudio' controls src='$soundUrl'></audio></p>";
	}

	/**
	 * @package My account tab
	 */
	// Add custom endpoint
	function myrecords_tab_add_my_account_endpoint() {
		add_rewrite_endpoint('wcsrecords', EP_ROOT | EP_PAGES);
	}

	function insert_after_key($array, $key, $new_key, $new_value) {
		$new_array = [];
		foreach ($array as $k => $v) {
			$new_array[$k] = $v;
			if ($k === $key) {
				$new_array[$new_key] = $new_value;
			}
		}
		return $new_array;
	}

	// Add new tab to My Account menu
	function myrecords_my_account_menu_items($items) {
		// Insert new item after 'orders' key
		$items = $this->insert_after_key($items, 'orders', 'wcsrecords', __('My Records', 'your-textdomain'));
		return $items;
	}

	function get_all_order_sounds() {
		// Get all orders for the current user
		$customer_orders = wc_get_orders(array(
			'customer' => get_current_user_id(), // Get orders for the current user
			'status' => array('completed', 'processing', 'on-hold') // Filter by order status if needed
		));
	
		$order_ids = array();

		// Loop through each order and extract the order ID
		foreach ($customer_orders as $order) {
			$order_ids[] = $order->get_id();
		}
	
		return $order_ids;
	}	

	// Display content for the Records
	function myrecords_my_account_endpoint_content() {
		echo '<h3>' . __('Records Content', 'your-textdomain') . '</h3>';
		?>
		<table>
			<thead>
				<tr>
					<th>Order</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$ids = $this->get_all_order_sounds();
				foreach($ids as $order_id){
					$order_view_url = wc_get_endpoint_url( 'view-order', $order_id );

					$soundUrl = get_post_meta($order_id, 'wcsound_record', true);
					if(!empty($soundUrl)){
						$orderViewLink = "<a href='$order_view_url'>#$order_id</a>";
						?>
						<tr>
							<td><?php echo $orderViewLink; ?></td>
							<td>
								<div class="audioView">
									<audio class='wcsaudio' controls src="<?php echo $soundUrl; ?>"></audio>
									<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
										<input type="hidden" name="action" value="delete_sound">
										<input type="hidden" name="order_id" value="<?php echo $order_id ?>">
										<button type="submit" class="playSound"><i class="fas fa-trash"></i></button>
									</form>
								</div>
							</td>
						</tr>
						<?php
					}
				}
				?>
			</tbody>
		</table>
		<?php
	}

	function upload_from_blob() {
		// Check if the file was uploaded successfully
		if (isset($_FILES['audio']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
			$upload_dir = wp_upload_dir();
			$upload_path = $upload_dir['path'] . '/wcrecoder/';
	
			// Ensure the upload directory exists
			if (!file_exists($upload_path)) {
				wp_mkdir_p($upload_path);
			}
	
			// Set the correct permissions for the upload directory
			if (file_exists($upload_path)) {
				chmod($upload_path, 0755);
			} else {
				// Directory creation failed
				wp_send_json('Error: Failed to create upload directory.', 503);
			}
	
			$filename = 'record-' . get_current_user_id() . '-' . uniqid() . '.wav';
			$file_path = $upload_path . $filename;
	
			// Move the uploaded file to the destination directory
			if (move_uploaded_file($_FILES['audio']['tmp_name'], $file_path)) {
				// File moved successfully, generate file URL and send as response
				$file_url = $upload_dir['url'] . '/wcrecoder/' . $filename;
				wp_send_json(sanitize_url( $file_url ), 200);
			} else {
				// File move failed, send error response
				wp_send_json('Error: Failed to save Blob data as a file.', 503);
			}
		} else {
			// No file received or file upload error, send error response
			wp_send_json('Error: Blob data not received.', 503);
		}
		die;
	}

	function handle_delete_sound(){
		if(isset($_POST['order_id'])){
			$order_id = $_POST['order_id'];
			$soundUrl = get_post_meta($order_id, 'wcsound_record', true);
			$upload_dir = wp_upload_dir();
			$upload_path = $upload_dir['path'] . '/wcrecoder/';

			$filepath = $upload_path . '/'.basename($soundUrl);
			if(file_exists($filepath)){
				try {
					if(unlink($filepath)){
						delete_post_meta($order_id, 'wcsound_record');
					}
				} catch (\Throwable $th) {
					delete_post_meta($order_id, 'wcsound_record');
				}
			}else{
				delete_post_meta($order_id, 'wcsound_record');
			}
		}

		$referrer_url = get_permalink( get_option('woocommerce_myaccount_page_id') ).'/wcsrecords';
		wp_redirect($referrer_url);
		exit;
	}
	
}
