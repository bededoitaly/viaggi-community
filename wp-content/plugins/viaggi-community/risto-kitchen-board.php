<?php
/**
 * Plugin Name: Risto Kitchen Board - Tabellone Cucina
 * Plugin URI: https://bededoitaly.com/viaggi-community
 * Description: Plugin satellite per visualizzazione tabellone cucina con aggiornamenti real-time, notifiche audio/vocali e riconoscimento vocale
 * Version: 1.0.0
 * Author: Bededo Italy
 * Author URI: https://bededoitaly.com
 * License: GPL-2.0+
 * Text Domain: risto-kitchen-board
 * Requires: Risto Base
 */

if (!defined('ABSPATH')) {
    exit;
}

class RistoKitchenBoard {
    
    private static $instance = null;
    private $version = '1.0.0';
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Register shortcodes
        add_shortcode('risto_kitchen_board', array($this, 'shortcode_kitchen_board'));
        add_shortcode('risto_ready_board', array($this, 'shortcode_ready_board'));
        
        // AJAX handlers
        add_action('wp_ajax_risto_get_pending_orders', array($this, 'ajax_get_pending_orders'));
        add_action('wp_ajax_nopriv_risto_get_pending_orders', array($this, 'ajax_get_pending_orders'));
        add_action('wp_ajax_risto_mark_order_ready', array($this, 'ajax_mark_order_ready'));
        add_action('wp_ajax_risto_get_ready_orders', array($this, 'ajax_get_ready_orders'));
        add_action('wp_ajax_nopriv_risto_get_ready_orders', array($this, 'ajax_get_ready_orders'));
        add_action('wp_ajax_risto_update_order_status', array($this, 'ajax_update_order_status'));
    }
    
    public function init() {
        // Initialization
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style('risto-kitchen-board-style', plugins_url('', __FILE__) . '/kitchen-board.css', array(), $this->version);
        wp_enqueue_script('risto-kitchen-board-script', plugins_url('', __FILE__) . '/kitchen-board.js', array('jquery'), $this->version, true);
        
        wp_localize_script('risto-kitchen-board-script', 'ristoKitchen', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('risto-kitchen-nonce')
        ));
    }
    
    // Shortcode: Kitchen Board
    public function shortcode_kitchen_board($atts) {
        ob_start();
        ?>
        <div class="risto-kitchen-board">
            <div class="kitchen-header">
                <h1>🍳 Tabellone Cucina - Ordini in Arrivo</h1>
                <div class="kitchen-controls">
                    <button id="toggle-sound" class="control-btn">🔊 Audio: ON</button>
                    <button id="toggle-voice" class="control-btn">🎤 Voce: ON</button>
                    <button id="start-voice-recognition" class="control-btn">🎙️ Riconoscimento Vocale</button>
                </div>
            </div>
            
            <div class="kitchen-stats">
                <div class="stat-card">
                    <span class="stat-label">In Attesa</span>
                    <span class="stat-value" id="stat-waiting">0</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">In Preparazione</span>
                    <span class="stat-value" id="stat-preparing">0</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Pronti</span>
                    <span class="stat-value" id="stat-ready">0</span>
                </div>
            </div>
            
            <div id="kitchen-orders-container" class="orders-container"></div>
            
            <div id="voice-feedback" class="voice-feedback" style="display:none;">
                <div class="voice-animation"></div>
                <p id="voice-text"></p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    // Shortcode: Ready Board (Display Board)
    public function shortcode_ready_board($atts) {
        ob_start();
        ?>
        <div class="risto-ready-board">
            <div class="ready-board-header">
                <h1>📢 ORDINI PRONTI</h1>
            </div>
            
            <div id="ready-orders-display" class="ready-orders-display"></div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    // AJAX: Get Pending Orders
    public function ajax_get_pending_orders() {
        global $wpdb;
        $table_comande = $wpdb->prefix . 'risto_comande';
        $table_piatti = $wpdb->prefix . 'risto_piatti';
        
        $orders = $wpdb->get_results(
            "SELECT * FROM $table_comande 
            WHERE stato IN ('in_attesa', 'in_preparazione') 
            ORDER BY created_at ASC"
        );
        
        $formatted_orders = array();
        foreach ($orders as $order) {
            $items = json_decode($order->items, true);
            $items_details = array();
            
            if (is_array($items)) {
                foreach ($items as $item) {
                    $piatto = $wpdb->get_row($wpdb->prepare(
                        "SELECT nome FROM $table_piatti WHERE id = %d",
                        $item['id']
                    ));
                    
                    $items_details[] = array(
                        'name' => $piatto ? $piatto->nome : 'Piatto sconosciuto',
                        'quantity' => $item['quantity']
                    );
                }
            }
            
            $formatted_orders[] = array(
                'id' => $order->id,
                'numero_ordine' => $order->numero_ordine,
                'items' => $items_details,
                'totale' => $order->totale,
                'stato' => $order->stato,
                'note' => $order->note,
                'created_at' => $order->created_at
            );
        }
        
        wp_send_json_success($formatted_orders);
    }
    
    // AJAX: Mark Order as Ready
    public function ajax_mark_order_ready() {
        check_ajax_referer('risto-kitchen-nonce', 'nonce');
        
        global $wpdb;
        $table = $wpdb->prefix . 'risto_comande';
        $order_id = intval($_POST['order_id']);
        
        $wpdb->update(
            $table,
            array('stato' => 'pronto'),
            array('id' => $order_id)
        );
        
        $order = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $order_id
        ));
        
        wp_send_json_success(array(
            'order_id' => $order_id,
            'numero_ordine' => $order->numero_ordine
        ));
    }
    
    // AJAX: Get Ready Orders
    public function ajax_get_ready_orders() {
        global $wpdb;
        $table = $wpdb->prefix . 'risto_comande';
        
        $orders = $wpdb->get_results(
            "SELECT * FROM $table 
            WHERE stato = 'pronto' 
            ORDER BY created_at DESC 
            LIMIT 10"
        );
        
        $formatted_orders = array();
        foreach ($orders as $order) {
            $formatted_orders[] = array(
                'id' => $order->id,
                'numero_ordine' => $order->numero_ordine,
                'created_at' => $order->created_at
            );
        }
        
        wp_send_json_success($formatted_orders);
    }
    
    // AJAX: Update Order Status
    public function ajax_update_order_status() {
        check_ajax_referer('risto-kitchen-nonce', 'nonce');
        
        global $wpdb;
        $table = $wpdb->prefix . 'risto_comande';
        $order_id = intval($_POST['order_id']);
        $status = sanitize_text_field($_POST['status']);
        
        $wpdb->update(
            $table,
            array('stato' => $status),
            array('id' => $order_id)
        );
        
        wp_send_json_success(array('order_id' => $order_id, 'status' => $status));
    }
}

// Initialize plugin
RistoKitchenBoard::get_instance();

// Inline CSS
function risto_kitchen_board_inline_styles() {
    ?>
    <style>
    /* Kitchen Board Styles - Premium Design */
    :root {
        --kitchen-primary: #1e3a8a;
        --kitchen-secondary: #06b6d4;
        --kitchen-accent: #10b981;
        --kitchen-gold: linear-gradient(135deg, #d4af37 0%, #f4e5b1 50%, #d4af37 100%);
        --kitchen-warning: #f59e0b;
        --kitchen-danger: #ef4444;
        --kitchen-success: #10b981;
    }
    
    .risto-kitchen-board {
        min-height: 100vh;
        background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
        padding: 20px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    
    .kitchen-header {
        background: linear-gradient(135deg, var(--kitchen-primary) 0%, var(--kitchen-secondary) 100%);
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }
    
    .kitchen-header h1 {
        color: white;
        margin: 0;
        font-size: 36px;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }
    
    .kitchen-controls {
        display: flex;
        gap: 10px;
    }
    
    .control-btn {
        background: white;
        color: var(--kitchen-primary);
        border: none;
        padding: 12px 20px;
        border-radius: 25px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .control-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(255, 255, 255, 0.3);
    }
    
    .control-btn.active {
        background: var(--kitchen-success);
        color: white;
    }
    
    .kitchen-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }
    
    .stat-label {
        display: block;
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .stat-value {
        display: block;
        font-size: 48px;
        font-weight: bold;
        background: var(--kitchen-gold);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .orders-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
    }
    
    .order-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
        animation: slideIn 0.5s ease-out;
        position: relative;
        overflow: hidden;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .order-card.new-order {
        animation: pulse 2s infinite, slideIn 0.5s ease-out;
    }
    
    @keyframes pulse {
        0%, 100% {
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
        }
        50% {
            box-shadow: 0 6px 30px rgba(16, 185, 129, 0.6);
        }
    }
    
    .order-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: var(--kitchen-gold);
    }
    
    .order-card.in_attesa::before {
        background: linear-gradient(90deg, var(--kitchen-warning), var(--kitchen-warning));
    }
    
    .order-card.in_preparazione::before {
        background: linear-gradient(90deg, var(--kitchen-secondary), var(--kitchen-accent));
    }
    
    .order-number {
        font-size: 28px;
        font-weight: bold;
        color: var(--kitchen-primary);
        margin: 0 0 15px 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .order-time {
        font-size: 14px;
        color: #6b7280;
        font-weight: normal;
    }
    
    .order-items {
        background: #f3f4f6;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 15px;
    }
    
    .order-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #e5e7eb;
        font-size: 16px;
    }
    
    .order-item:last-child {
        border-bottom: none;
    }
    
    .item-name {
        font-weight: 600;
        color: var(--kitchen-primary);
    }
    
    .item-quantity {
        background: var(--kitchen-secondary);
        color: white;
        padding: 2px 10px;
        border-radius: 12px;
        font-weight: bold;
    }
    
    .order-notes {
        background: #fef3c7;
        padding: 10px;
        border-radius: 6px;
        margin-bottom: 15px;
        font-style: italic;
        color: #92400e;
    }
    
    .order-actions {
        display: flex;
        gap: 10px;
    }
    
    .order-btn {
        flex: 1;
        padding: 12px;
        border: none;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 14px;
    }
    
    .btn-preparing {
        background: linear-gradient(135deg, var(--kitchen-secondary), var(--kitchen-accent));
        color: white;
    }
    
    .btn-ready {
        background: linear-gradient(135deg, var(--kitchen-success), var(--kitchen-accent));
        color: white;
    }
    
    .order-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }
    
    /* Ready Board */
    .risto-ready-board {
        min-height: 100vh;
        background: linear-gradient(135deg, #111827 0%, #1f2937 100%);
        padding: 20px;
        display: flex;
        flex-direction: column;
    }
    
    .ready-board-header {
        background: linear-gradient(135deg, var(--kitchen-success) 0%, var(--kitchen-accent) 100%);
        padding: 40px;
        border-radius: 15px;
        text-align: center;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }
    
    .ready-board-header h1 {
        color: white;
        font-size: 48px;
        margin: 0;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        animation: pulse-text 2s infinite;
    }
    
    @keyframes pulse-text {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }
    
    .ready-orders-display {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    .ready-order-card {
        background: white;
        padding: 40px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        animation: blink 1.5s infinite, slideIn 0.5s ease-out;
        border: 5px solid;
        border-image: var(--kitchen-gold) 1;
    }
    
    @keyframes blink {
        0%, 49%, 100% {
            opacity: 1;
            background: white;
        }
        50%, 99% {
            opacity: 0.7;
            background: #fef3c7;
        }
    }
    
    .ready-order-number {
        font-size: 64px;
        font-weight: bold;
        background: var(--kitchen-gold);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin: 0;
    }
    
    .ready-order-text {
        font-size: 36px;
        color: var(--kitchen-primary);
        margin: 20px 0;
        font-weight: bold;
    }
    
    /* Voice Feedback */
    .voice-feedback {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: white;
        padding: 20px 30px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        z-index: 1000;
        animation: slideInRight 0.5s ease-out;
    }
    
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .voice-animation {
        width: 60px;
        height: 60px;
        margin: 0 auto 15px;
        background: linear-gradient(135deg, var(--kitchen-secondary), var(--kitchen-accent));
        border-radius: 50%;
        animation: voice-pulse 1s infinite;
    }
    
    @keyframes voice-pulse {
        0%, 100% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.2);
            opacity: 0.7;
        }
    }
    
    #voice-text {
        text-align: center;
        font-size: 18px;
        font-weight: bold;
        color: var(--kitchen-primary);
        margin: 0;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .kitchen-header {
            flex-direction: column;
            gap: 20px;
        }
        
        .kitchen-header h1 {
            font-size: 24px;
        }
        
        .orders-container {
            grid-template-columns: 1fr;
        }
        
        .ready-order-number {
            font-size: 48px;
        }
        
        .ready-order-text {
            font-size: 24px;
        }
    }
    </style>
    <?php
}
add_action('wp_head', 'risto_kitchen_board_inline_styles');

// Inline JavaScript
function risto_kitchen_board_inline_scripts() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        var soundEnabled = true;
        var voiceEnabled = true;
        var lastOrderCount = 0;
        var knownOrders = [];
        var voiceRecognition = null;
        
        // Check for Speech Synthesis API
        var speechSynthesis = window.speechSynthesis;
        var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        
        // Text-to-Speech function
        function speak(text) {
            if (!voiceEnabled || !speechSynthesis) return;
            
            var utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'it-IT';
            utterance.rate = 1.0;
            utterance.pitch = 1.0;
            speechSynthesis.speak(utterance);
            
            // Show voice feedback
            $('#voice-text').text(text);
            $('#voice-feedback').fadeIn();
            
            setTimeout(function() {
                $('#voice-feedback').fadeOut();
            }, 3000);
        }
        
        // Play notification sound
        function playSound() {
            if (!soundEnabled) return;
            
            // Create a simple beep using Web Audio API
            var audioContext = new (window.AudioContext || window.webkitAudioContext)();
            var oscillator = audioContext.createOscillator();
            var gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.value = 800;
            oscillator.type = 'sine';
            
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.5);
        }
        
        // Update Kitchen Board
        function updateKitchenBoard() {
            $.ajax({
                url: ristoKitchen.ajaxurl,
                method: 'POST',
                data: {
                    action: 'risto_get_pending_orders'
                },
                success: function(response) {
                    if (response.success) {
                        var orders = response.data;
                        
                        // Update stats
                        var waiting = orders.filter(o => o.stato === 'in_attesa').length;
                        var preparing = orders.filter(o => o.stato === 'in_preparazione').length;
                        
                        $('#stat-waiting').text(waiting);
                        $('#stat-preparing').text(preparing);
                        
                        // Check for new orders
                        orders.forEach(function(order) {
                            if (!knownOrders.includes(order.id)) {
                                knownOrders.push(order.id);
                                playSound();
                                speak('Nuovo ordine numero ' + order.numero_ordine);
                            }
                        });
                        
                        // Render orders
                        var html = '';
                        orders.forEach(function(order) {
                            var itemsHtml = '';
                            order.items.forEach(function(item) {
                                itemsHtml += '<div class="order-item">' +
                                    '<span class="item-name">' + item.name + '</span>' +
                                    '<span class="item-quantity">x' + item.quantity + '</span>' +
                                    '</div>';
                            });
                            
                            var notesHtml = '';
                            if (order.note) {
                                notesHtml = '<div class="order-notes">📝 Note: ' + order.note + '</div>';
                            }
                            
                            var actionsHtml = '';
                            if (order.stato === 'in_attesa') {
                                actionsHtml = '<button class="order-btn btn-preparing" data-id="' + order.id + '" data-action="preparing">In Preparazione</button>';
                            } else if (order.stato === 'in_preparazione') {
                                actionsHtml = '<button class="order-btn btn-ready" data-id="' + order.id + '" data-action="ready">Pronto</button>';
                            }
                            
                            var timeAgo = timeSince(new Date(order.created_at));
                            
                            html += '<div class="order-card ' + order.stato + '" data-id="' + order.id + '">' +
                                '<h2 class="order-number">' +
                                    order.numero_ordine +
                                    '<span class="order-time">' + timeAgo + '</span>' +
                                '</h2>' +
                                '<div class="order-items">' + itemsHtml + '</div>' +
                                notesHtml +
                                '<div class="order-actions">' + actionsHtml + '</div>' +
                                '</div>';
                        });
                        
                        $('#kitchen-orders-container').html(html);
                    }
                }
            });
        }
        
        // Time ago helper
        function timeSince(date) {
            var seconds = Math.floor((new Date() - date) / 1000);
            var interval = seconds / 60;
            
            if (interval < 1) return 'ora';
            if (interval < 60) return Math.floor(interval) + ' minuti fa';
            
            interval = interval / 60;
            if (interval < 24) return Math.floor(interval) + ' ore fa';
            
            return Math.floor(interval / 24) + ' giorni fa';
        }
        
        // Update order status
        $(document).on('click', '.order-btn', function() {
            var btn = $(this);
            var orderId = btn.data('id');
            var action = btn.data('action');
            var status = action === 'preparing' ? 'in_preparazione' : 'pronto';
            
            $.ajax({
                url: ristoKitchen.ajaxurl,
                method: 'POST',
                data: {
                    action: 'risto_update_order_status',
                    nonce: ristoKitchen.nonce,
                    order_id: orderId,
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        if (status === 'pronto') {
                            var orderNumber = btn.closest('.order-card').find('.order-number').text().trim().split('\n')[0];
                            speak('Ordine numero ' + orderNumber + ' pronto per il ritiro');
                        }
                        updateKitchenBoard();
                    }
                }
            });
        });
        
        // Control buttons
        $('#toggle-sound').on('click', function() {
            soundEnabled = !soundEnabled;
            $(this).text('🔊 Audio: ' + (soundEnabled ? 'ON' : 'OFF'));
            $(this).toggleClass('active', soundEnabled);
        });
        
        $('#toggle-voice').on('click', function() {
            voiceEnabled = !voiceEnabled;
            $(this).text('🎤 Voce: ' + (voiceEnabled ? 'ON' : 'OFF'));
            $(this).toggleClass('active', voiceEnabled);
        });
        
        // Voice Recognition
        $('#start-voice-recognition').on('click', function() {
            if (!SpeechRecognition) {
                alert('Il riconoscimento vocale non è supportato dal tuo browser');
                return;
            }
            
            if (voiceRecognition) {
                voiceRecognition.stop();
                voiceRecognition = null;
                $(this).text('🎙️ Riconoscimento Vocale');
                $(this).removeClass('active');
                return;
            }
            
            voiceRecognition = new SpeechRecognition();
            voiceRecognition.lang = 'it-IT';
            voiceRecognition.continuous = true;
            voiceRecognition.interimResults = false;
            
            voiceRecognition.onresult = function(event) {
                var transcript = event.results[event.results.length - 1][0].transcript.toLowerCase();
                
                // Match pattern: "ordine numero X pronto"
                var match = transcript.match(/ordine\s+numero\s+(\d+)\s+pronto/);
                if (match) {
                    var orderNumber = 'ORD-' + new Date().toISOString().slice(0, 10).replace(/-/g, '') + '-' + match[1].padStart(4, '0');
                    
                    // Find and mark order as ready
                    $('.order-card').each(function() {
                        if ($(this).find('.order-number').text().includes(orderNumber)) {
                            $(this).find('.btn-ready').click();
                        }
                    });
                }
            };
            
            voiceRecognition.start();
            $(this).text('🎙️ Ascoltando...');
            $(this).addClass('active');
            speak('Riconoscimento vocale attivato');
        });
        
        // Update Ready Board
        function updateReadyBoard() {
            $.ajax({
                url: ristoKitchen.ajaxurl,
                method: 'POST',
                data: {
                    action: 'risto_get_ready_orders'
                },
                success: function(response) {
                    if (response.success) {
                        var orders = response.data;
                        var html = '';
                        
                        orders.forEach(function(order) {
                            html += '<div class="ready-order-card">' +
                                '<h2 class="ready-order-number">' + order.numero_ordine + '</h2>' +
                                '<p class="ready-order-text">PRONTO PER IL RITIRO</p>' +
                                '</div>';
                        });
                        
                        $('#ready-orders-display').html(html);
                        
                        // Update stat
                        $('#stat-ready').text(orders.length);
                    }
                }
            });
        }
        
        // Auto-refresh
        if ($('.risto-kitchen-board').length) {
            updateKitchenBoard();
            setInterval(updateKitchenBoard, 5000); // Update every 5 seconds
        }
        
        if ($('.risto-ready-board').length) {
            updateReadyBoard();
            setInterval(updateReadyBoard, 3000); // Update every 3 seconds
        }
    });
    </script>
    <?php
}
add_action('wp_footer', 'risto_kitchen_board_inline_scripts');
