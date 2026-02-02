<?php
/**
 * Plugin Name: Risto Kitchen Board - Real-Time Kitchen Display
 * Plugin URI: https://viaggi.cloud
 * Description: Real-time kitchen display system with voice control and audio notifications for restaurant orders. Integrates seamlessly with Risto Base.
 * Version: 1.0.0
 * Author: Viaggi Cloud
 * Author URI: https://viaggi.cloud
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: risto-kitchen-board
 * Requires at least: 5.0
 * Requires PHP: 7.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class Risto_Kitchen_Board {
    
    private static $instance = null;
    private $dependency_met = false;
    
    const DEMO_ORDER_INTERVAL = 30;
    const DEMO_ORDER_TRANSIENT_EXPIRY = 300;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('plugins_loaded', array($this, 'check_dependencies'));
        add_action('init', array($this, 'init'));
    }
    
    public function check_dependencies() {
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        $risto_base_active = false;
        
        // Check if Risto Base plugin is active
        if (class_exists('Risto_Base') || 
            is_plugin_active('risto-base/risto-base.php') || 
            is_plugin_active('risto-base.php') ||
            defined('RISTO_BASE_VERSION')) {
            $risto_base_active = true;
        }
        
        if (!$risto_base_active) {
            add_action('admin_notices', array($this, 'dependency_notice'));
            $this->dependency_met = false;
        } else {
            $this->dependency_met = true;
        }
    }
    
    public function dependency_notice() {
        ?>
        <div class="notice notice-error">
            <p><strong>Risto Kitchen Board</strong> requires the <strong>Risto Base</strong> plugin to be installed and activated.</p>
        </div>
        <?php
    }
    
    public function init() {
        if (!$this->dependency_met) {
            return;
        }
        
        add_shortcode('risto_kitchen_board', array($this, 'render_kitchen_board'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_rkb_get_orders', array($this, 'ajax_get_orders'));
        add_action('wp_ajax_nopriv_rkb_get_orders', array($this, 'ajax_get_orders'));
        add_action('wp_ajax_rkb_mark_ready', array($this, 'ajax_mark_ready'));
        add_action('wp_ajax_rkb_print_order', array($this, 'ajax_print_order'));
    }
    
    public function enqueue_scripts() {
        if (!is_singular() && !is_page()) {
            return;
        }
        
        global $post;
        if (is_a($post, 'WP_Post') && !has_shortcode($post->post_content, 'risto_kitchen_board')) {
            return;
        }
        
        wp_enqueue_style('risto-kitchen-board', false, array(), '1.0.0');
        wp_add_inline_style('risto-kitchen-board', $this->get_inline_css());
        
        wp_enqueue_script('risto-kitchen-board', false, array('jquery'), '1.0.0', true);
        wp_add_inline_script('risto-kitchen-board', $this->get_inline_js());
        
        wp_localize_script('risto-kitchen-board', 'rkbConfig', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rkb_nonce'),
            'refreshInterval' => 5000,
            'soundUrl' => $this->get_notification_sound_data()
        ));
    }
    
    public function render_kitchen_board($atts) {
        ob_start();
        ?>
        <div id="risto-kitchen-board" class="rkb-container">
            <div class="rkb-header">
                <h1 class="rkb-title">
                    <span class="rkb-icon">🍽️</span>
                    Kitchen Display System
                    <span class="rkb-status" id="rkb-connection-status">
                        <span class="rkb-status-indicator"></span>
                        <span class="rkb-status-text">Connected</span>
                    </span>
                </h1>
                <div class="rkb-controls">
                    <button id="rkb-voice-toggle" class="rkb-btn rkb-btn-voice" title="Toggle Voice Control">
                        🎤 <span id="rkb-voice-status">Voice: Off</span>
                    </button>
                    <button id="rkb-sound-toggle" class="rkb-btn rkb-btn-sound" title="Toggle Sound">
                        🔊 <span id="rkb-sound-status">Sound: On</span>
                    </button>
                </div>
            </div>
            
            <div id="rkb-ready-flash" class="rkb-ready-flash" style="display: none;">
                <div class="rkb-ready-message"></div>
            </div>
            
            <div id="rkb-orders-container" class="rkb-orders-container">
                <div class="rkb-loading">
                    <div class="rkb-spinner"></div>
                    <p>Loading orders...</p>
                </div>
            </div>
            
            <div class="rkb-footer">
                <p class="rkb-footer-text">
                    Last updated: <span id="rkb-last-update">--:--:--</span>
                    &nbsp;|&nbsp;
                    <span id="rkb-voice-hint" style="display: none;">
                        Say: "ordine numero [X] pronto" to mark order ready
                    </span>
                </p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function ajax_get_orders() {
        check_ajax_referer('rkb_nonce', 'nonce');
        
        // Get orders from Risto Base or simulate
        $orders = $this->get_kitchen_orders();
        
        wp_send_json_success(array(
            'orders' => $orders,
            'timestamp' => current_time('H:i:s')
        ));
    }
    
    public function ajax_mark_ready() {
        check_ajax_referer('rkb_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Unauthorized');
        }
        
        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        
        if (!$order_id) {
            wp_send_json_error('Invalid order ID');
        }
        
        // Update order status
        $updated = $this->update_order_status($order_id, 'ready');
        
        if ($updated) {
            wp_send_json_success(array(
                'message' => 'Order marked as ready',
                'order_id' => $order_id
            ));
        } else {
            wp_send_json_error('Failed to update order');
        }
    }
    
    public function ajax_print_order() {
        check_ajax_referer('rkb_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Unauthorized');
        }
        
        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        
        if (!$order_id) {
            wp_send_json_error('Invalid order ID');
        }
        
        $order = $this->get_order_by_id($order_id);
        
        if (!$order) {
            wp_send_json_error('Order not found');
        }
        
        $receipt = $this->generate_receipt($order);
        
        wp_send_json_success(array(
            'message' => 'Order sent to printer',
            'receipt' => $receipt,
            'order_id' => $order_id
        ));
    }
    
    private function get_kitchen_orders() {
        // Try to get orders from Risto Base
        if (class_exists('Risto_Base') && method_exists('Risto_Base', 'get_orders')) {
            return Risto_Base::get_orders(array('status' => array('new', 'preparing', 'ready')));
        }
        
        // Fallback: get from WooCommerce if available
        if (class_exists('WC_Order')) {
            return $this->get_woocommerce_orders();
        }
        
        // Demo data for testing
        return $this->get_demo_orders();
    }
    
    private function get_woocommerce_orders() {
        $orders_data = array();
        
        $orders = wc_get_orders(array(
            'limit' => 20,
            'status' => array('processing', 'pending'),
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        foreach ($orders as $order) {
            $items = array();
            foreach ($order->get_items() as $item) {
                $items[] = array(
                    'name' => $item->get_name(),
                    'quantity' => $item->get_quantity(),
                    'notes' => $item->get_meta('_notes', true)
                );
            }
            
            $status = 'new';
            $order_status = $order->get_status();
            if ($order_status === 'processing') {
                $status = 'preparing';
            }
            
            $orders_data[] = array(
                'id' => $order->get_id(),
                'number' => $order->get_order_number(),
                'items' => $items,
                'status' => $status,
                'time' => $order->get_date_created()->date('H:i'),
                'notes' => $order->get_customer_note()
            );
        }
        
        return $orders_data;
    }
    
    private function get_demo_orders() {
        $last_check = get_transient('rkb_last_order_check');
        $current_time = time();
        
        // Simulate new order every 30 seconds
        if (!$last_check || ($current_time - $last_check) > self::DEMO_ORDER_INTERVAL) {
            set_transient('rkb_last_order_check', $current_time, self::DEMO_ORDER_TRANSIENT_EXPIRY);
            $this->add_demo_order();
        }
        
        $demo_orders = get_option('rkb_demo_orders', array());
        
        // Clean up old ready orders (using timestamp if available)
        foreach ($demo_orders as $key => $order) {
            if ($order['status'] === 'ready') {
                $order_timestamp = isset($order['timestamp']) ? $order['timestamp'] : 0;
                if ($order_timestamp && (time() - $order_timestamp) > 600) {
                    unset($demo_orders[$key]);
                }
            }
        }
        
        update_option('rkb_demo_orders', array_values($demo_orders));
        
        return array_values($demo_orders);
    }
    
    private function add_demo_order() {
        $demo_orders = get_option('rkb_demo_orders', array());
        
        $next_number = count($demo_orders) + 1;
        
        $items_pool = array(
            array('name' => 'Pizza Margherita', 'quantity' => 2, 'notes' => 'Extra mozzarella'),
            array('name' => 'Spaghetti Carbonara', 'quantity' => 1, 'notes' => 'No pepper'),
            array('name' => 'Lasagne al Forno', 'quantity' => 1, 'notes' => ''),
            array('name' => 'Risotto ai Funghi', 'quantity' => 1, 'notes' => 'Gluten free'),
            array('name' => 'Bistecca Fiorentina', 'quantity' => 1, 'notes' => 'Medium rare'),
            array('name' => 'Insalata Caprese', 'quantity' => 1, 'notes' => ''),
            array('name' => 'Tiramisu', 'quantity' => 2, 'notes' => ''),
        );
        
        $num_items = rand(1, 3);
        $selected_items = array();
        $keys = array_rand($items_pool, min($num_items, count($items_pool)));
        
        if (!is_array($keys)) {
            $keys = array($keys);
        }
        
        foreach ($keys as $key) {
            $selected_items[] = $items_pool[$key];
        }
        
        $timestamp = time();
        
        $demo_orders[] = array(
            'id' => ($timestamp * 1000) + $next_number,
            'number' => $next_number,
            'items' => $selected_items,
            'status' => 'new',
            'time' => current_time('H:i'),
            'timestamp' => $timestamp,
            'notes' => ''
        );
        
        update_option('rkb_demo_orders', $demo_orders);
    }
    
    private function get_order_by_id($order_id) {
        $demo_orders = get_option('rkb_demo_orders', array());
        
        foreach ($demo_orders as $order) {
            if ($order['id'] == $order_id) {
                return $order;
            }
        }
        
        return null;
    }
    
    private function update_order_status($order_id, $status) {
        // Update in Risto Base if available
        if (class_exists('Risto_Base') && method_exists('Risto_Base', 'update_order_status')) {
            return Risto_Base::update_order_status($order_id, $status);
        }
        
        // Update WooCommerce order
        if (class_exists('WC_Order')) {
            $order = wc_get_order($order_id);
            if ($order) {
                $order->update_status('completed');
                return true;
            }
        }
        
        // Update demo orders
        $demo_orders = get_option('rkb_demo_orders', array());
        
        foreach ($demo_orders as &$order) {
            if ($order['id'] == $order_id) {
                $order['status'] = $status;
                update_option('rkb_demo_orders', $demo_orders);
                return true;
            }
        }
        
        return false;
    }
    
    private function generate_receipt($order) {
        $receipt = "========================================\n";
        $receipt .= "       RISTO KITCHEN - ORDINE #" . $order['number'] . "\n";
        $receipt .= "========================================\n";
        $receipt .= "Data: " . current_time('d/m/Y') . "\n";
        $receipt .= "Ora: " . current_time('H:i:s') . "\n";
        $receipt .= "----------------------------------------\n\n";
        
        foreach ($order['items'] as $item) {
            $receipt .= sprintf("%dx %s\n", $item['quantity'], $item['name']);
            if (!empty($item['notes'])) {
                $receipt .= "   Note: " . $item['notes'] . "\n";
            }
        }
        
        $receipt .= "\n----------------------------------------\n";
        
        if (!empty($order['notes'])) {
            $receipt .= "Note ordine: " . $order['notes'] . "\n";
            $receipt .= "----------------------------------------\n";
        }
        
        $receipt .= "\n       ORDINE PRONTO PER IL RITIRO\n";
        $receipt .= "========================================\n";
        
        return $receipt;
    }
    
    private function get_notification_sound_data() {
        // Base64 encoded notification sound (short beep)
        return 'data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBgoOEhYaHiImKi4yNjo+QkZKTlJWWl5iZmpucnZ6foKGio6SlpqeoqaqrrK2ur7CxsrO0tba3uLm6u7y9vr/AwcLDxMXGx8jJysvMzc7P0NHS09TV1tfY2drb3N3e3+Dh4uPk5ebn6Onq6+zt7u/w8fLz9PX29/j5+vv8/f7/AAECAwQFBgcICQoLDA0ODxAREhMUFRYXGBkaGxwdHh8gISIjJCUmJygpKissLS4vMDEyMzQ1Njc4OTo7PD0+P0BBQkNERUZHSElKS0xNTk9QUVJTVFVWV1hZWltcXV5fYGFiY2RlZmdoaWprbG1ub3BxcnN0dXZ3eHl6e3x9fn+AgYKDhIWGh4iJiouMjY6PkJGSk5SVlpeYmZqbnJ2en6ChoqOkpaanqKmqq6ytrq+wsbKztLW2t7i5uru8vb6/wMHCw8TFxsfIycrLzM3Oz9DR0tPU1dbX2Nna29zd3t/g4eLj5OXm5+jp6uvs7e7v8PHy8/T19vf4+fr7/P3+/w==';
    }
    
    private function get_inline_css() {
        return '
        .rkb-container {
            max-width: 100%;
            margin: 0 auto;
            padding: 20px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            box-sizing: border-box;
        }
        
        .rkb-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }
        
        .rkb-title {
            margin: 0;
            font-size: 2.5em;
            color: #fff;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .rkb-icon {
            font-size: 1.2em;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .rkb-status {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.4em;
            margin-left: 20px;
        }
        
        .rkb-status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #4ade80;
            animation: blink 1.5s ease-in-out infinite;
        }
        
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        
        .rkb-controls {
            display: flex;
            gap: 15px;
        }
        
        .rkb-btn {
            padding: 12px 24px;
            font-size: 1.1em;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .rkb-btn-voice {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }
        
        .rkb-btn-voice.active {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            animation: voicePulse 1.5s ease-in-out infinite;
        }
        
        @keyframes voicePulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .rkb-btn-sound {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: #fff;
        }
        
        .rkb-btn-sound.muted {
            background: #6b7280;
        }
        
        .rkb-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }
        
        .rkb-ready-flash {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(220, 38, 38, 0.95);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: flash 0.5s ease-in-out infinite;
        }
        
        @keyframes flash {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        .rkb-ready-message {
            font-size: 5em;
            font-weight: 900;
            color: #fff;
            text-align: center;
            text-shadow: 4px 4px 8px rgba(0, 0, 0, 0.5);
            padding: 40px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 20px;
        }
        
        .rkb-orders-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .rkb-order {
            background: #fff;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            border: 4px solid transparent;
        }
        
        .rkb-order:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }
        
        .rkb-order.status-new {
            border-color: #10b981;
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        }
        
        .rkb-order.status-preparing {
            border-color: #f59e0b;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        }
        
        .rkb-order.status-ready {
            border-color: #ef4444;
            background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%);
            animation: readyPulse 1s ease-in-out infinite;
        }
        
        @keyframes readyPulse {
            0%, 100% { transform: scale(1); box-shadow: 0 10px 30px rgba(239, 68, 68, 0.4); }
            50% { transform: scale(1.02); box-shadow: 0 15px 40px rgba(239, 68, 68, 0.6); }
        }
        
        .rkb-order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid rgba(0, 0, 0, 0.1);
        }
        
        .rkb-order-number {
            font-size: 3em;
            font-weight: 900;
            color: #1f2937;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .rkb-order-time {
            font-size: 1.5em;
            font-weight: 600;
            color: #6b7280;
        }
        
        .rkb-order-status {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 1em;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 15px;
        }
        
        .status-new .rkb-order-status {
            background: #10b981;
            color: #fff;
        }
        
        .status-preparing .rkb-order-status {
            background: #f59e0b;
            color: #fff;
        }
        
        .status-ready .rkb-order-status {
            background: #ef4444;
            color: #fff;
        }
        
        .rkb-order-items {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }
        
        .rkb-order-item {
            padding: 15px;
            margin-bottom: 10px;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 8px;
            font-size: 1.3em;
            font-weight: 600;
            color: #1f2937;
            border-left: 4px solid #3b82f6;
        }
        
        .rkb-item-quantity {
            display: inline-block;
            width: 40px;
            height: 40px;
            line-height: 40px;
            text-align: center;
            background: #3b82f6;
            color: #fff;
            border-radius: 50%;
            font-weight: 900;
            margin-right: 15px;
        }
        
        .rkb-item-notes {
            display: block;
            margin-top: 8px;
            font-size: 0.85em;
            color: #6b7280;
            font-style: italic;
            padding-left: 55px;
        }
        
        .rkb-order-notes {
            margin-top: 15px;
            padding: 12px;
            background: rgba(251, 191, 36, 0.2);
            border-left: 4px solid #f59e0b;
            border-radius: 5px;
            font-size: 1.1em;
            color: #92400e;
        }
        
        .rkb-loading {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px;
            color: #fff;
            font-size: 1.5em;
        }
        
        .rkb-spinner {
            width: 60px;
            height: 60px;
            border: 6px solid rgba(255, 255, 255, 0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .rkb-footer {
            text-align: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }
        
        .rkb-footer-text {
            margin: 0;
            font-size: 1.2em;
            color: #fff;
            font-weight: 500;
        }
        
        #rkb-voice-hint {
            color: #fbbf24;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .rkb-orders-container {
                grid-template-columns: 1fr;
            }
            
            .rkb-title {
                font-size: 1.8em;
            }
            
            .rkb-header {
                flex-direction: column;
                gap: 15px;
            }
            
            .rkb-order-number {
                font-size: 2em;
            }
            
            .rkb-ready-message {
                font-size: 3em;
            }
        }
        ';
    }
    
    private function get_inline_js() {
        return "
        (function($) {
            'use strict';
            
            let voiceEnabled = false;
            let soundEnabled = true;
            let recognition = null;
            let synth = window.speechSynthesis;
            let lastOrderCount = 0;
            let knownOrders = new Set();
            
            // Initialize
            $(document).ready(function() {
                initializeKitchenBoard();
                setupVoiceRecognition();
                setupEventHandlers();
                startPolling();
            });
            
            function initializeKitchenBoard() {
                loadOrders();
            }
            
            function setupEventHandlers() {
                $('#rkb-voice-toggle').on('click', toggleVoice);
                $('#rkb-sound-toggle').on('click', toggleSound);
            }
            
            function toggleVoice() {
                voiceEnabled = !voiceEnabled;
                
                if (voiceEnabled) {
                    startVoiceRecognition();
                    $('#rkb-voice-toggle').addClass('active');
                    $('#rkb-voice-status').text('Voice: On');
                    $('#rkb-voice-hint').show();
                } else {
                    stopVoiceRecognition();
                    $('#rkb-voice-toggle').removeClass('active');
                    $('#rkb-voice-status').text('Voice: Off');
                    $('#rkb-voice-hint').hide();
                }
            }
            
            function toggleSound() {
                soundEnabled = !soundEnabled;
                
                if (soundEnabled) {
                    $('#rkb-sound-toggle').removeClass('muted');
                    $('#rkb-sound-status').text('Sound: On');
                } else {
                    $('#rkb-sound-toggle').addClass('muted');
                    $('#rkb-sound-status').text('Sound: Off');
                }
            }
            
            function setupVoiceRecognition() {
                if (!('SpeechRecognition' in window) && !('webkitSpeechRecognition' in window)) {
                    console.warn('Speech recognition not supported');
                    $('#rkb-voice-toggle').prop('disabled', true);
                    return;
                }
                
                const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                recognition = new SpeechRecognition();
                recognition.continuous = true;
                recognition.interimResults = false;
                recognition.lang = 'it-IT';
                
                recognition.onresult = function(event) {
                    const last = event.results.length - 1;
                    const transcript = event.results[last][0].transcript.toLowerCase().trim();
                    
                    console.log('Voice command:', transcript);
                    
                    // Match: \"ordine numero X pronto\"
                    const match = transcript.match(/ordine\\\\s+numero\\\\s+(\\\\d+)\\\\s+pronto/i);
                    if (match) {
                        const orderNumber = parseInt(match[1]);
                        markOrderReady(orderNumber);
                    }
                };
                
                recognition.onerror = function(event) {
                    console.error('Speech recognition error:', event.error);
                    if (event.error === 'no-speech' || event.error === 'network') {
                        // Restart on common errors
                        if (voiceEnabled) {
                            setTimeout(startVoiceRecognition, 1000);
                        }
                    }
                };
                
                recognition.onend = function() {
                    if (voiceEnabled) {
                        recognition.start();
                    }
                };
            }
            
            function startVoiceRecognition() {
                if (recognition) {
                    try {
                        recognition.start();
                    } catch (e) {
                        console.error('Failed to start recognition:', e);
                    }
                }
            }
            
            function stopVoiceRecognition() {
                if (recognition) {
                    recognition.stop();
                }
            }
            
            function speak(text) {
                if (!soundEnabled || !synth) return;
                
                // Cancel any ongoing speech
                synth.cancel();
                
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'it-IT';
                utterance.rate = 1.0;
                utterance.pitch = 1.0;
                utterance.volume = 1.0;
                
                synth.speak(utterance);
            }
            
            function playNotificationSound() {
                if (!soundEnabled) return;
                
                try {
                    const audio = new Audio(rkbConfig.soundUrl);
                    audio.play();
                } catch (e) {
                    console.error('Failed to play sound:', e);
                }
            }
            
            function startPolling() {
                setInterval(loadOrders, rkbConfig.refreshInterval);
            }
            
            function loadOrders() {
                $.ajax({
                    url: rkbConfig.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'rkb_get_orders',
                        nonce: rkbConfig.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            updateOrders(response.data.orders);
                            updateTimestamp(response.data.timestamp);
                            updateConnectionStatus(true);
                        }
                    },
                    error: function() {
                        updateConnectionStatus(false);
                    }
                });
            }
            
            function updateOrders(orders) {
                const container = $('#rkb-orders-container');
                
                if (orders.length === 0) {
                    container.html('<div class=\"rkb-loading\"><p>No active orders</p></div>');
                    return;
                }
                
                // Check for new orders
                orders.forEach(function(order) {
                    if (!knownOrders.has(order.id) && order.status === 'new') {
                        onNewOrder(order);
                    }
                    knownOrders.add(order.id);
                });
                
                let html = '';
                
                orders.forEach(function(order) {
                    html += renderOrder(order);
                });
                
                container.html(html);
                lastOrderCount = orders.length;
            }
            
            function onNewOrder(order) {
                playNotificationSound();
                
                let announcement = 'Nuovo ordine Numero ' + order.number + '. ';
                
                order.items.forEach(function(item) {
                    announcement += item.quantity + ' ' + item.name + '. ';
                });
                
                if (order.notes) {
                    announcement += 'Note: ' + order.notes;
                }
                
                speak(announcement);
            }
            
            function renderOrder(order) {
                let html = '<div class=\"rkb-order status-' + order.status + '\" data-order-id=\"' + order.id + '\">';
                html += '<div class=\"rkb-order-header\">';
                html += '<div class=\"rkb-order-number\">#' + order.number + '</div>';
                html += '<div class=\"rkb-order-time\">' + order.time + '</div>';
                html += '</div>';
                
                html += '<div class=\"rkb-order-status\">' + getStatusLabel(order.status) + '</div>';
                
                html += '<ul class=\"rkb-order-items\">';
                order.items.forEach(function(item) {
                    html += '<li class=\"rkb-order-item\">';
                    html += '<span class=\"rkb-item-quantity\">' + item.quantity + '</span>';
                    html += '<span class=\"rkb-item-name\">' + item.name + '</span>';
                    if (item.notes) {
                        html += '<span class=\"rkb-item-notes\">📝 ' + item.notes + '</span>';
                    }
                    html += '</li>';
                });
                html += '</ul>';
                
                if (order.notes) {
                    html += '<div class=\"rkb-order-notes\">📋 ' + order.notes + '</div>';
                }
                
                html += '</div>';
                
                return html;
            }
            
            function getStatusLabel(status) {
                const labels = {
                    'new': '🆕 Nuovo',
                    'preparing': '👨‍🍳 In Preparazione',
                    'ready': '✅ Pronto'
                };
                return labels[status] || status;
            }
            
            function markOrderReady(orderNumber) {
                // Find order by number
                const orders = $('.rkb-order');
                let orderId = null;
                
                orders.each(function() {
                    const orderNumText = $(this).find('.rkb-order-number').text();
                    const num = parseInt(orderNumText.replace('#', ''));
                    if (num === orderNumber) {
                        orderId = $(this).data('order-id');
                        return false;
                    }
                });
                
                if (!orderId) {
                    speak('Ordine numero ' + orderNumber + ' non trovato');
                    return;
                }
                
                $.ajax({
                    url: rkbConfig.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'rkb_mark_ready',
                        nonce: rkbConfig.nonce,
                        order_id: orderId
                    },
                    success: function(response) {
                        if (response.success) {
                            showReadyFlash(orderNumber);
                            printOrder(orderId);
                            speak('Ordine numero ' + orderNumber + ' pronto per il ritiro');
                            setTimeout(loadOrders, 1000);
                        }
                    }
                });
            }
            
            function showReadyFlash(orderNumber) {
                const flash = $('#rkb-ready-flash');
                const message = flash.find('.rkb-ready-message');
                
                message.html('🎉<br>ORDINE N. ' + orderNumber + '<br>PRONTO PER IL RITIRO');
                flash.fadeIn(200);
                
                setTimeout(function() {
                    flash.fadeOut(500);
                }, 3000);
            }
            
            function printOrder(orderId) {
                $.ajax({
                    url: rkbConfig.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'rkb_print_order',
                        nonce: rkbConfig.nonce,
                        order_id: orderId
                    },
                    success: function(response) {
                        if (response.success) {
                            console.log('Order sent to printer');
                            console.log(response.data.receipt);
                        }
                    }
                });
            }
            
            function updateTimestamp(timestamp) {
                $('#rkb-last-update').text(timestamp);
            }
            
            function updateConnectionStatus(connected) {
                const status = $('#rkb-connection-status');
                const indicator = status.find('.rkb-status-indicator');
                const text = status.find('.rkb-status-text');
                
                if (connected) {
                    indicator.css('background', '#4ade80');
                    text.text('Connected');
                } else {
                    indicator.css('background', '#ef4444');
                    text.text('Disconnected');
                }
            }
            
        })(jQuery);
        ";
    }
}

// Initialize plugin
Risto_Kitchen_Board::get_instance();
