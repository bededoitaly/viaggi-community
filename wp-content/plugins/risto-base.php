<?php
/**
 * Plugin Name: Risto Base - Restaurant Management System
 * Plugin URI: https://example.com/risto-base
 * Description: Complete restaurant management system with tables, waiters, dishes, orders, and customer management
 * Version: 1.0.0
 * Author: Risto Base Team
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: risto-base
 */

if (!defined('ABSPATH')) {
    exit;
}

class RistoBase {
    
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new RistoBase();
        }
        return self::$instance;
    }
    
    private function __construct() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        add_action('admin_menu', array($this, 'addAdminMenus'));
        add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueAdminScripts'));
        
        add_action('wp_ajax_risto_save_form', array($this, 'ajaxSaveForm'));
        add_action('wp_ajax_risto_save_record', array($this, 'ajaxSaveRecord'));
        add_action('wp_ajax_risto_delete_record', array($this, 'ajaxDeleteRecord'));
        add_action('wp_ajax_risto_get_records', array($this, 'ajaxGetRecords'));
        add_action('wp_ajax_risto_save_order', array($this, 'ajaxSaveOrder'));
        add_action('wp_ajax_nopriv_risto_save_order', array($this, 'ajaxSaveOrder'));
        add_action('wp_ajax_risto_import_csv', array($this, 'ajaxImportCsv'));
        
        add_shortcode('risto_lista_tavoli', array($this, 'shortcodeTavoli'));
        add_shortcode('risto_lista_camerieri', array($this, 'shortcodeCamerieri'));
        add_shortcode('risto_lista_piatti', array($this, 'shortcodePiatti'));
        add_shortcode('risto_lista_comande', array($this, 'shortcodeComande'));
        add_shortcode('risto_lista_clienti', array($this, 'shortcodeClienti'));
        add_shortcode('risto_menu_vetrina', array($this, 'shortcodeMenuVetrina'));
        add_shortcode('risto_ordini_frontend', array($this, 'shortcodeOrdiniFrontend'));
    }
    
    public function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        $table_forms = $wpdb->prefix . 'risto_forms';
        $sql_forms = "CREATE TABLE IF NOT EXISTS $table_forms (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            slug varchar(100) NOT NULL,
            fields longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY slug (slug)
        ) $charset_collate;";
        
        $table_records = $wpdb->prefix . 'risto_records';
        $sql_records = "CREATE TABLE IF NOT EXISTS $table_records (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            form_id bigint(20) NOT NULL,
            data longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY form_id (form_id)
        ) $charset_collate;";
        
        $table_orders = $wpdb->prefix . 'risto_orders';
        $sql_orders = "CREATE TABLE IF NOT EXISTS $table_orders (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            table_number varchar(50) DEFAULT NULL,
            waiter_id bigint(20) DEFAULT NULL,
            items longtext NOT NULL,
            total decimal(10,2) DEFAULT 0.00,
            status varchar(50) DEFAULT 'pending',
            notes text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_forms);
        dbDelta($sql_records);
        dbDelta($sql_orders);
        
        $this->createDefaultForms();
    }
    
    private function createDefaultForms() {
        global $wpdb;
        $table = $wpdb->prefix . 'risto_forms';
        
        $forms = array(
            array(
                'name' => 'Tavoli',
                'slug' => 'tavoli',
                'fields' => json_encode(array(
                    array('name' => 'numero_tavolo', 'label' => 'Numero Tavolo', 'type' => 'text', 'required' => true),
                    array('name' => 'posti', 'label' => 'Posti', 'type' => 'number', 'required' => true),
                    array('name' => 'zona', 'label' => 'Zona', 'type' => 'text', 'required' => false),
                    array('name' => 'note', 'label' => 'Note', 'type' => 'textarea', 'required' => false)
                ))
            ),
            array(
                'name' => 'Camerieri',
                'slug' => 'camerieri',
                'fields' => json_encode(array(
                    array('name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'required' => true),
                    array('name' => 'cognome', 'label' => 'Cognome', 'type' => 'text', 'required' => true),
                    array('name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true),
                    array('name' => 'telefono', 'label' => 'Telefono', 'type' => 'text', 'required' => false),
                    array('name' => 'foto', 'label' => 'Foto', 'type' => 'file', 'required' => false)
                ))
            ),
            array(
                'name' => 'Piatti',
                'slug' => 'piatti',
                'fields' => json_encode(array(
                    array('name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'required' => true),
                    array('name' => 'descrizione', 'label' => 'Descrizione', 'type' => 'textarea', 'required' => false),
                    array('name' => 'categoria', 'label' => 'Categoria', 'type' => 'text', 'required' => true),
                    array('name' => 'prezzo', 'label' => 'Prezzo', 'type' => 'number', 'required' => true, 'step' => '0.01'),
                    array('name' => 'immagine', 'label' => 'Immagine', 'type' => 'file', 'required' => false),
                    array('name' => 'galleria', 'label' => 'Galleria', 'type' => 'text', 'required' => false),
                    array('name' => 'vegetariano', 'label' => 'Vegetariano', 'type' => 'checkbox', 'required' => false),
                    array('name' => 'vegano', 'label' => 'Vegano', 'type' => 'checkbox', 'required' => false),
                    array('name' => 'senza_glutine', 'label' => 'Senza Glutine', 'type' => 'checkbox', 'required' => false)
                ))
            ),
            array(
                'name' => 'Comande',
                'slug' => 'comande',
                'fields' => json_encode(array(
                    array('name' => 'tavolo', 'label' => 'Tavolo', 'type' => 'text', 'required' => true),
                    array('name' => 'cameriere', 'label' => 'Cameriere', 'type' => 'text', 'required' => true),
                    array('name' => 'stato', 'label' => 'Stato', 'type' => 'select', 'required' => true, 'options' => array('pending', 'in_progress', 'completed', 'cancelled')),
                    array('name' => 'note', 'label' => 'Note', 'type' => 'textarea', 'required' => false)
                ))
            ),
            array(
                'name' => 'Clienti',
                'slug' => 'clienti',
                'fields' => json_encode(array(
                    array('name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'required' => true),
                    array('name' => 'cognome', 'label' => 'Cognome', 'type' => 'text', 'required' => true),
                    array('name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true),
                    array('name' => 'telefono', 'label' => 'Telefono', 'type' => 'text', 'required' => false),
                    array('name' => 'indirizzo', 'label' => 'Indirizzo', 'type' => 'textarea', 'required' => false),
                    array('name' => 'note', 'label' => 'Note', 'type' => 'textarea', 'required' => false)
                ))
            )
        );
        
        foreach ($forms as $form) {
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table WHERE slug = %s",
                $form['slug']
            ));
            
            if (!$existing) {
                $wpdb->insert($table, $form);
            }
        }
    }
    
    public function addAdminMenus() {
        add_menu_page('Risto Base', 'Risto Base', 'manage_options', 'risto-base', array($this, 'adminDashboard'), 'dashicons-food', 30);
        add_submenu_page('risto-base', 'Tavoli', 'Tavoli', 'manage_options', 'risto-tavoli', array($this, 'adminTavoli'));
        add_submenu_page('risto-base', 'Camerieri', 'Camerieri', 'manage_options', 'risto-camerieri', array($this, 'adminCamerieri'));
        add_submenu_page('risto-base', 'Piatti', 'Piatti', 'manage_options', 'risto-piatti', array($this, 'adminPiatti'));
        add_submenu_page('risto-base', 'Comande', 'Comande', 'manage_options', 'risto-comande', array($this, 'adminComande'));
        add_submenu_page('risto-base', 'Clienti', 'Clienti', 'manage_options', 'risto-clienti', array($this, 'adminClienti'));
        add_submenu_page('risto-base', 'Ordini', 'Ordini', 'manage_options', 'risto-ordini', array($this, 'adminOrdini'));
    }
    
    public function enqueueScripts() {
        $this->outputInlineStyles();
    }
    
    public function enqueueAdminScripts() {
        $this->outputInlineStyles();
    }
    
    private function outputInlineStyles() {
        ?>
        <style>
            .risto-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
            .risto-card { background: #fff; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: transform 0.3s ease, box-shadow 0.3s ease; }
            .risto-card:hover { transform: translateY(-5px); box-shadow: 0 6px 20px rgba(0,0,0,0.15); }
            .risto-header { background: linear-gradient(135deg, #1a5490 0%, #17a2b8 100%); color: #fff; padding: 30px; border-radius: 10px; margin-bottom: 30px; text-align: center; }
            .risto-header h1 { margin: 0; font-size: 2.5em; text-shadow: 2px 2px 4px rgba(0,0,0,0.2); }
            .risto-btn { background: linear-gradient(135deg, #1a5490 0%, #17a2b8 100%); color: #fff; border: none; padding: 12px 30px; border-radius: 25px; font-size: 16px; font-weight: bold; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
            .risto-btn:hover { background: linear-gradient(135deg, #17a2b8 0%, #28a745 100%); transform: translateY(-2px); box-shadow: 0 6px 15px rgba(0,0,0,0.3); }
            .risto-btn-success { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }
            .risto-btn-danger { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); }
            .risto-btn-large { padding: 20px 40px; font-size: 24px; min-width: 80px; margin: 0 10px; }
            .risto-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; }
            .risto-table th { background: linear-gradient(135deg, #1a5490 0%, #17a2b8 100%); color: #fff; padding: 15px; text-align: left; font-weight: bold; }
            .risto-table td { padding: 12px 15px; border-bottom: 1px solid #eee; }
            .risto-table tr:hover { background: #f8f9fa; }
            .risto-form-group { margin-bottom: 20px; }
            .risto-form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #333; }
            .risto-form-control { width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px; transition: border-color 0.3s ease; box-sizing: border-box; }
            .risto-form-control:focus { outline: none; border-color: #17a2b8; box-shadow: 0 0 10px rgba(23,162,184,0.2); }
            .risto-menu-item { background: #fff; border-radius: 15px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 20px; }
            .risto-menu-item-image { width: 120px; height: 120px; object-fit: cover; border-radius: 10px; }
            .risto-menu-item-info { flex: 1; }
            .risto-menu-item-name { font-size: 24px; font-weight: bold; color: #1a5490; margin-bottom: 10px; }
            .risto-menu-item-description { color: #666; margin-bottom: 10px; }
            .risto-menu-item-price { font-size: 28px; font-weight: bold; background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
            .risto-menu-item-badges { display: flex; gap: 10px; margin-top: 10px; }
            .risto-badge { padding: 5px 15px; border-radius: 15px; font-size: 12px; font-weight: bold; color: #fff; }
            .risto-badge-vegetarian { background: #28a745; }
            .risto-badge-vegan { background: #20c997; }
            .risto-badge-gluten-free { background: #ffc107; color: #333; }
            .risto-quantity-controls { display: flex; align-items: center; gap: 15px; }
            .risto-quantity-display { font-size: 24px; font-weight: bold; min-width: 50px; text-align: center; }
            .risto-category-filters { display: flex; gap: 10px; margin-bottom: 30px; flex-wrap: wrap; }
            .risto-category-filter { padding: 10px 20px; background: #fff; border: 2px solid #1a5490; color: #1a5490; border-radius: 25px; cursor: pointer; transition: all 0.3s ease; font-weight: bold; }
            .risto-category-filter:hover, .risto-category-filter.active { background: linear-gradient(135deg, #1a5490 0%, #17a2b8 100%); color: #fff; }
            .risto-order-summary { position: sticky; top: 20px; background: #fff; border-radius: 15px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
            .risto-order-total { font-size: 32px; font-weight: bold; color: #28a745; text-align: center; margin: 20px 0; }
            .risto-tabs { display: flex; gap: 10px; margin-bottom: 30px; overflow-x: auto; padding-bottom: 10px; }
            .risto-tab { padding: 15px 30px; background: #fff; border: 2px solid #1a5490; color: #1a5490; border-radius: 25px; cursor: pointer; transition: all 0.3s ease; font-weight: bold; white-space: nowrap; }
            .risto-tab:hover, .risto-tab.active { background: linear-gradient(135deg, #1a5490 0%, #17a2b8 100%); color: #fff; }
            .risto-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
            @media (max-width: 768px) {
                .risto-menu-item { flex-direction: column; text-align: center; }
                .risto-btn-large { padding: 15px 30px; font-size: 20px; min-width: 60px; }
                .risto-grid { grid-template-columns: 1fr; }
                .risto-tabs { flex-wrap: nowrap; }
            }
            .risto-gold-accent { background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
            .risto-success-message { background: #28a745; color: #fff; padding: 15px; border-radius: 10px; margin: 20px 0; text-align: center; font-weight: bold; }
            .risto-error-message { background: #dc3545; color: #fff; padding: 15px; border-radius: 10px; margin: 20px 0; text-align: center; font-weight: bold; }
        </style>
        <?php
    }
    
    public function adminDashboard() {
        ?>
        <div class="wrap risto-container">
            <div class="risto-header">
                <h1 class="risto-gold-accent">Risto Base - Dashboard</h1>
                <p>Sistema di Gestione Ristorante</p>
            </div>
            <div class="risto-grid">
                <div class="risto-card">
                    <h2>Tavoli</h2>
                    <p>Gestisci i tavoli del ristorante</p>
                    <a href="?page=risto-tavoli" class="risto-btn">Vai ai Tavoli</a>
                </div>
                <div class="risto-card">
                    <h2>Camerieri</h2>
                    <p>Gestisci il personale di sala</p>
                    <a href="?page=risto-camerieri" class="risto-btn">Vai ai Camerieri</a>
                </div>
                <div class="risto-card">
                    <h2>Piatti</h2>
                    <p>Gestisci il menu e i piatti</p>
                    <a href="?page=risto-piatti" class="risto-btn">Vai ai Piatti</a>
                </div>
                <div class="risto-card">
                    <h2>Comande</h2>
                    <p>Gestisci le comande</p>
                    <a href="?page=risto-comande" class="risto-btn">Vai alle Comande</a>
                </div>
                <div class="risto-card">
                    <h2>Clienti</h2>
                    <p>Gestisci i clienti</p>
                    <a href="?page=risto-clienti" class="risto-btn">Vai ai Clienti</a>
                </div>
                <div class="risto-card">
                    <h2>Ordini</h2>
                    <p>Visualizza gli ordini ricevuti</p>
                    <a href="?page=risto-ordini" class="risto-btn">Vai agli Ordini</a>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function adminTavoli() { $this->renderAdminArchive('tavoli', 'Tavoli'); }
    public function adminCamerieri() { $this->renderAdminArchive('camerieri', 'Camerieri'); }
    public function adminPiatti() { $this->renderAdminArchive('piatti', 'Piatti'); }
    public function adminComande() { $this->renderAdminArchive('comande', 'Comande'); }
    public function adminClienti() { $this->renderAdminArchive('clienti', 'Clienti'); }
    
    public function adminOrdini() {
        global $wpdb;
        $table = $wpdb->prefix . 'risto_orders';
        $orders = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
        ?>
        <div class="wrap risto-container">
            <div class="risto-header"><h1>Ordini</h1></div>
            <div class="risto-card">
                <table class="risto-table">
                    <thead>
                        <tr>
                            <th>ID</th><th>Tavolo</th><th>Cameriere</th><th>Totale</th><th>Stato</th><th>Data</th><th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo esc_html($order->id); ?></td>
                            <td><?php echo esc_html($order->table_number); ?></td>
                            <td><?php echo esc_html($order->waiter_id); ?></td>
                            <td>€<?php echo esc_html(number_format($order->total, 2)); ?></td>
                            <td><?php echo esc_html($order->status); ?></td>
                            <td><?php echo esc_html($order->created_at); ?></td>
                            <td><button onclick="alert('Dettagli ordine #<?php echo $order->id; ?>')" class="risto-btn">Dettagli</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
    
    private function renderAdminArchive($slug, $title) {
        global $wpdb;
        $forms_table = $wpdb->prefix . 'risto_forms';
        $records_table = $wpdb->prefix . 'risto_records';
        
        $form = $wpdb->get_row($wpdb->prepare("SELECT * FROM $forms_table WHERE slug = %s", $slug));
        
        if (!$form) {
            echo '<div class="wrap"><h1>Modulo non trovato</h1></div>';
            return;
        }
        
        $fields = json_decode($form->fields, true);
        $records = $wpdb->get_results($wpdb->prepare("SELECT * FROM $records_table WHERE form_id = %d ORDER BY created_at DESC", $form->id));
        
        $editing_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
        $editing_record = null;
        
        if ($editing_id) {
            $editing_record = $wpdb->get_row($wpdb->prepare("SELECT * FROM $records_table WHERE id = %d", $editing_id));
        }
        ?>
        <div class="wrap risto-container">
            <div class="risto-header"><h1><?php echo esc_html($title); ?></h1></div>
            
            <div class="risto-card">
                <h2><?php echo $editing_id ? 'Modifica' : 'Aggiungi Nuovo'; ?></h2>
                <form id="risto-record-form">
                    <input type="hidden" name="form_id" value="<?php echo esc_attr($form->id); ?>">
                    <input type="hidden" name="record_id" value="<?php echo esc_attr($editing_id); ?>">
                    
                    <?php foreach ($fields as $field): ?>
                        <div class="risto-form-group">
                            <label><?php echo esc_html($field['label']); ?></label>
                            <?php
                            $field_name = esc_attr($field['name']);
                            $field_value = '';
                            
                            if ($editing_record) {
                                $data = json_decode($editing_record->data, true);
                                $field_value = isset($data[$field['name']]) ? $data[$field['name']] : '';
                            }
                            
                            switch ($field['type']) {
                                case 'textarea':
                                    echo '<textarea name="' . $field_name . '" class="risto-form-control">' . esc_textarea($field_value) . '</textarea>';
                                    break;
                                case 'checkbox':
                                    $checked = $field_value ? 'checked' : '';
                                    echo '<input type="checkbox" name="' . $field_name . '" value="1" ' . $checked . '>';
                                    break;
                                case 'select':
                                    echo '<select name="' . $field_name . '" class="risto-form-control">';
                                    if (isset($field['options'])) {
                                        foreach ($field['options'] as $option) {
                                            $selected = ($field_value == $option) ? 'selected' : '';
                                            echo '<option value="' . esc_attr($option) . '" ' . $selected . '>' . esc_html($option) . '</option>';
                                        }
                                    }
                                    echo '</select>';
                                    break;
                                default:
                                    $step = isset($field['step']) ? 'step="' . esc_attr($field['step']) . '"' : '';
                                    echo '<input type="' . esc_attr($field['type']) . '" name="' . $field_name . '" value="' . esc_attr($field_value) . '" class="risto-form-control" ' . $step . '>';
                            }
                            ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <button type="submit" class="risto-btn risto-btn-success">Salva</button>
                    <?php if ($editing_id): ?>
                        <a href="?page=risto-<?php echo esc_attr($slug); ?>" class="risto-btn">Annulla</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <div class="risto-card">
                <h2>Elenco <?php echo esc_html($title); ?></h2>
                <table class="risto-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <?php foreach ($fields as $field): ?>
                                <th><?php echo esc_html($field['label']); ?></th>
                            <?php endforeach; ?>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $record): ?>
                            <?php $data = json_decode($record->data, true); ?>
                            <tr>
                                <td><?php echo esc_html($record->id); ?></td>
                                <?php foreach ($fields as $field): ?>
                                    <td>
                                        <?php
                                        $value = isset($data[$field['name']]) ? $data[$field['name']] : '';
                                        if ($field['type'] == 'checkbox') {
                                            echo $value ? '✓' : '✗';
                                        } else {
                                            echo esc_html($value);
                                        }
                                        ?>
                                    </td>
                                <?php endforeach; ?>
                                <td>
                                    <a href="?page=risto-<?php echo esc_attr($slug); ?>&edit=<?php echo $record->id; ?>" class="risto-btn">Modifica</a>
                                    <button onclick="deleteRecord(<?php echo $record->id; ?>, '<?php echo esc_js($slug); ?>')" class="risto-btn risto-btn-danger">Elimina</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#risto-record-form').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                formData.append('action', 'risto_save_record');
                formData.append('nonce', '<?php echo wp_create_nonce('risto_nonce'); ?>');
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            alert('Salvato con successo!');
                            location.href = '?page=risto-<?php echo esc_js($slug); ?>';
                        } else {
                            alert('Errore: ' + response.data);
                        }
                    }
                });
            });
        });
        
        function deleteRecord(recordId, slug) {
            if (!confirm('Sei sicuro di voler eliminare questo record?')) return;
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'risto_delete_record',
                    record_id: recordId,
                    nonce: '<?php echo wp_create_nonce('risto_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        alert('Eliminato con successo!');
                        location.reload();
                    } else {
                        alert('Errore: ' + response.data);
                    }
                }
            });
        }
        </script>
        <?php
    }
    
    public function ajaxSaveForm() {
        check_ajax_referer('risto_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Permessi insufficienti');
        
        global $wpdb;
        $table = $wpdb->prefix . 'risto_forms';
        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'slug' => sanitize_title($_POST['slug']),
            'fields' => wp_kses_post($_POST['fields'])
        );
        
        if (isset($_POST['form_id']) && $_POST['form_id']) {
            $wpdb->update($table, $data, array('id' => intval($_POST['form_id'])));
        } else {
            $wpdb->insert($table, $data);
        }
        wp_send_json_success('Form salvato');
    }
    
    public function ajaxSaveRecord() {
        check_ajax_referer('risto_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Permessi insufficienti');
        
        global $wpdb;
        $table = $wpdb->prefix . 'risto_records';
        $form_id = intval($_POST['form_id']);
        $record_id = isset($_POST['record_id']) ? intval($_POST['record_id']) : 0;
        
        $record_data = array();
        foreach ($_POST as $key => $value) {
            if (!in_array($key, array('action', 'nonce', 'form_id', 'record_id'))) {
                $record_data[$key] = sanitize_text_field($value);
            }
        }
        
        $data = array('form_id' => $form_id, 'data' => json_encode($record_data));
        
        if ($record_id) {
            $wpdb->update($table, $data, array('id' => $record_id));
        } else {
            $wpdb->insert($table, $data);
        }
        wp_send_json_success('Record salvato');
    }
    
    public function ajaxDeleteRecord() {
        check_ajax_referer('risto_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Permessi insufficienti');
        
        global $wpdb;
        $table = $wpdb->prefix . 'risto_records';
        $record_id = intval($_POST['record_id']);
        $wpdb->delete($table, array('id' => $record_id));
        wp_send_json_success('Record eliminato');
    }
    
    public function ajaxGetRecords() {
        check_ajax_referer('risto_nonce', 'nonce');
        global $wpdb;
        $table = $wpdb->prefix . 'risto_records';
        $form_id = intval($_POST['form_id']);
        $records = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE form_id = %d", $form_id));
        wp_send_json_success($records);
    }
    
    public function ajaxSaveOrder() {
        global $wpdb;
        $table = $wpdb->prefix . 'risto_orders';
        $data = array(
            'table_number' => sanitize_text_field($_POST['table_number']),
            'waiter_id' => isset($_POST['waiter_id']) ? intval($_POST['waiter_id']) : null,
            'items' => wp_kses_post($_POST['items']),
            'total' => floatval($_POST['total']),
            'status' => 'pending',
            'notes' => sanitize_textarea_field($_POST['notes'])
        );
        $wpdb->insert($table, $data);
        wp_send_json_success('Ordine salvato con successo');
    }
    
    public function ajaxImportCsv() {
        check_ajax_referer('risto_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Permessi insufficienti');
        
        if (!isset($_FILES['csv_file'])) wp_send_json_error('Nessun file caricato');
        
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        if (!$handle) wp_send_json_error('Impossibile aprire il file');
        
        $imported = 0;
        $headers = fgetcsv($handle);
        while (($data = fgetcsv($handle)) !== false) {
            $imported++;
        }
        fclose($handle);
        wp_send_json_success("Importati $imported record");
    }
    
    public function shortcodeTavoli($atts) { return $this->renderListShortcode('tavoli', 'Tavoli'); }
    public function shortcodeCamerieri($atts) { return $this->renderListShortcode('camerieri', 'Camerieri'); }
    public function shortcodePiatti($atts) { return $this->renderListShortcode('piatti', 'Piatti'); }
    public function shortcodeComande($atts) { return $this->renderListShortcode('comande', 'Comande'); }
    public function shortcodeClienti($atts) { return $this->renderListShortcode('clienti', 'Clienti'); }
    
    private function renderListShortcode($slug, $title) {
        global $wpdb;
        $forms_table = $wpdb->prefix . 'risto_forms';
        $records_table = $wpdb->prefix . 'risto_records';
        
        $form = $wpdb->get_row($wpdb->prepare("SELECT * FROM $forms_table WHERE slug = %s", $slug));
        
        if (!$form) return '<p>Archivio non trovato</p>';
        
        $fields = json_decode($form->fields, true);
        $records = $wpdb->get_results($wpdb->prepare("SELECT * FROM $records_table WHERE form_id = %d ORDER BY created_at DESC", $form->id));
        
        ob_start();
        ?>
        <div class="risto-container">
            <div class="risto-header"><h1><?php echo esc_html($title); ?></h1></div>
            <div class="risto-card">
                <table class="risto-table">
                    <thead>
                        <tr>
                            <?php foreach ($fields as $field): ?>
                                <th><?php echo esc_html($field['label']); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $record): ?>
                            <?php $data = json_decode($record->data, true); ?>
                            <tr>
                                <?php foreach ($fields as $field): ?>
                                    <td>
                                        <?php
                                        $value = isset($data[$field['name']]) ? $data[$field['name']] : '';
                                        if ($field['type'] == 'checkbox') {
                                            echo $value ? '✓' : '✗';
                                        } elseif ($field['type'] == 'file' && $value) {
                                            echo '<img src="' . esc_url($value) . '" style="max-width: 100px;">';
                                        } else {
                                            echo esc_html($value);
                                        }
                                        ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function shortcodeMenuVetrina($atts) {
        global $wpdb;
        $forms_table = $wpdb->prefix . 'risto_forms';
        $records_table = $wpdb->prefix . 'risto_records';
        
        $form = $wpdb->get_row($wpdb->prepare("SELECT * FROM $forms_table WHERE slug = %s", 'piatti'));
        
        if (!$form) return '<p>Menu non disponibile</p>';
        
        $records = $wpdb->get_results($wpdb->prepare("SELECT * FROM $records_table WHERE form_id = %d ORDER BY created_at DESC", $form->id));
        
        $categories = array();
        foreach ($records as $record) {
            $data = json_decode($record->data, true);
            if (isset($data['categoria']) && !in_array($data['categoria'], $categories)) {
                $categories[] = $data['categoria'];
            }
        }
        
        ob_start();
        ?>
        <div class="risto-container">
            <div class="risto-header"><h1 class="risto-gold-accent">Menu</h1></div>
            
            <div class="risto-category-filters">
                <button class="risto-category-filter active" onclick="filterCategory('all')">Tutti</button>
                <?php foreach ($categories as $cat): ?>
                    <button class="risto-category-filter" onclick="filterCategory('<?php echo esc_js($cat); ?>')">
                        <?php echo esc_html($cat); ?>
                    </button>
                <?php endforeach; ?>
            </div>
            
            <div id="menu-items">
                <?php foreach ($records as $record): ?>
                    <?php $data = json_decode($record->data, true); ?>
                    <div class="risto-menu-item" data-category="<?php echo esc_attr($data['categoria']); ?>" data-id="<?php echo $record->id; ?>">
                        <?php if (isset($data['immagine']) && $data['immagine']): ?>
                            <img src="<?php echo esc_url($data['immagine']); ?>" class="risto-menu-item-image" alt="<?php echo esc_attr($data['nome']); ?>">
                        <?php endif; ?>
                        
                        <div class="risto-menu-item-info">
                            <div class="risto-menu-item-name"><?php echo esc_html($data['nome']); ?></div>
                            <?php if (isset($data['descrizione'])): ?>
                                <div class="risto-menu-item-description"><?php echo esc_html($data['descrizione']); ?></div>
                            <?php endif; ?>
                            <div class="risto-menu-item-price">€<?php echo esc_html(number_format($data['prezzo'], 2)); ?></div>
                            
                            <div class="risto-menu-item-badges">
                                <?php if (isset($data['vegetariano']) && $data['vegetariano']): ?>
                                    <span class="risto-badge risto-badge-vegetarian">Vegetariano</span>
                                <?php endif; ?>
                                <?php if (isset($data['vegano']) && $data['vegano']): ?>
                                    <span class="risto-badge risto-badge-vegan">Vegano</span>
                                <?php endif; ?>
                                <?php if (isset($data['senza_glutine']) && $data['senza_glutine']): ?>
                                    <span class="risto-badge risto-badge-gluten-free">Senza Glutine</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="risto-quantity-controls">
                            <button class="risto-btn risto-btn-large" onclick="changeQuantity(<?php echo $record->id; ?>, -1)">-</button>
                            <span class="risto-quantity-display" id="qty-<?php echo $record->id; ?>">0</span>
                            <button class="risto-btn risto-btn-success risto-btn-large" onclick="changeQuantity(<?php echo $record->id; ?>, 1)">+</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <script>
        var quantities = {};
        
        function filterCategory(category) {
            var items = document.querySelectorAll('.risto-menu-item');
            var filters = document.querySelectorAll('.risto-category-filter');
            
            filters.forEach(function(filter) {
                filter.classList.remove('active');
            });
            
            event.target.classList.add('active');
            
            items.forEach(function(item) {
                if (category === 'all' || item.getAttribute('data-category') === category) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }
        
        function changeQuantity(itemId, change) {
            if (!quantities[itemId]) {
                quantities[itemId] = 0;
            }
            
            quantities[itemId] += change;
            
            if (quantities[itemId] < 0) {
                quantities[itemId] = 0;
            }
            
            document.getElementById('qty-' + itemId).textContent = quantities[itemId];
        }
        </script>
        <?php
        return ob_get_clean();
    }
    
    public function shortcodeOrdiniFrontend($atts) {
        global $wpdb;
        $forms_table = $wpdb->prefix . 'risto_forms';
        $records_table = $wpdb->prefix . 'risto_records';
        
        $form = $wpdb->get_row($wpdb->prepare("SELECT * FROM $forms_table WHERE slug = %s", 'piatti'));
        
        if (!$form) return '<p>Menu non disponibile</p>';
        
        $records = $wpdb->get_results($wpdb->prepare("SELECT * FROM $records_table WHERE form_id = %d ORDER BY created_at DESC", $form->id));
        
        $categories = array();
        $items_by_category = array();
        
        foreach ($records as $record) {
            $data = json_decode($record->data, true);
            $cat = isset($data['categoria']) ? $data['categoria'] : 'Altro';
            
            if (!in_array($cat, $categories)) {
                $categories[] = $cat;
            }
            
            if (!isset($items_by_category[$cat])) {
                $items_by_category[$cat] = array();
            }
            
            $items_by_category[$cat][] = array('id' => $record->id, 'data' => $data);
        }
        
        ob_start();
        ?>
        <div class="risto-container">
            <div class="risto-header"><h1 class="risto-gold-accent">Crea il Tuo Ordine</h1></div>
            
            <div class="risto-tabs">
                <?php $first = true; foreach ($categories as $cat): ?>
                    <button class="risto-tab <?php echo $first ? 'active' : ''; ?>" onclick="switchTab('<?php echo esc_js($cat); ?>')">
                        <?php echo esc_html($cat); ?>
                    </button>
                    <?php $first = false; ?>
                <?php endforeach; ?>
            </div>
            
            <?php $first = true; foreach ($items_by_category as $cat => $items): ?>
                <div class="tab-content" id="tab-<?php echo esc_attr($cat); ?>" style="<?php echo $first ? '' : 'display:none;'; ?>">
                    <?php foreach ($items as $item): ?>
                        <?php $data = $item['data']; ?>
                        <div class="risto-menu-item">
                            <?php if (isset($data['immagine']) && $data['immagine']): ?>
                                <img src="<?php echo esc_url($data['immagine']); ?>" class="risto-menu-item-image" alt="<?php echo esc_attr($data['nome']); ?>">
                            <?php endif; ?>
                            
                            <div class="risto-menu-item-info">
                                <div class="risto-menu-item-name"><?php echo esc_html($data['nome']); ?></div>
                                <?php if (isset($data['descrizione'])): ?>
                                    <div class="risto-menu-item-description"><?php echo esc_html($data['descrizione']); ?></div>
                                <?php endif; ?>
                                <div class="risto-menu-item-price">€<?php echo esc_html(number_format($data['prezzo'], 2)); ?></div>
                            </div>
                            
                            <div class="risto-quantity-controls">
                                <button class="risto-btn risto-btn-large" onclick="updateQuantity(<?php echo $item['id']; ?>, -1, <?php echo $data['prezzo']; ?>)">-</button>
                                <span class="risto-quantity-display" id="qty-<?php echo $item['id']; ?>">0</span>
                                <button class="risto-btn risto-btn-success risto-btn-large" onclick="updateQuantity(<?php echo $item['id']; ?>, 1, <?php echo $data['prezzo']; ?>)">+</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php $first = false; ?>
            <?php endforeach; ?>
            
            <div class="risto-order-summary">
                <h2>Riepilogo Ordine</h2>
                <div class="risto-order-total" id="order-total">€0.00</div>
                
                <div class="risto-form-group">
                    <label>Numero Tavolo</label>
                    <input type="text" id="table-number" class="risto-form-control" placeholder="Es. 12">
                </div>
                
                <div class="risto-form-group">
                    <label>Note</label>
                    <textarea id="order-notes" class="risto-form-control" placeholder="Note aggiuntive..."></textarea>
                </div>
                
                <button onclick="submitOrder()" class="risto-btn risto-btn-success" style="width: 100%; padding: 20px; font-size: 20px;">
                    Invia Ordine
                </button>
                
                <div id="order-message" style="margin-top: 20px;"></div>
            </div>
        </div>
        
        <script>
        var orderItems = {};
        var orderTotal = 0;
        
        function switchTab(category) {
            var tabs = document.querySelectorAll('.risto-tab');
            var contents = document.querySelectorAll('.tab-content');
            
            tabs.forEach(function(tab) {
                tab.classList.remove('active');
            });
            
            contents.forEach(function(content) {
                content.style.display = 'none';
            });
            
            event.target.classList.add('active');
            document.getElementById('tab-' + category).style.display = 'block';
        }
        
        function updateQuantity(itemId, change, price) {
            if (!orderItems[itemId]) {
                orderItems[itemId] = {quantity: 0, price: price};
            }
            
            orderItems[itemId].quantity += change;
            
            if (orderItems[itemId].quantity < 0) {
                orderItems[itemId].quantity = 0;
            }
            
            document.getElementById('qty-' + itemId).textContent = orderItems[itemId].quantity;
            calculateTotal();
        }
        
        function calculateTotal() {
            orderTotal = 0;
            for (var itemId in orderItems) {
                orderTotal += orderItems[itemId].quantity * orderItems[itemId].price;
            }
            document.getElementById('order-total').textContent = '€' + orderTotal.toFixed(2);
        }
        
        function submitOrder() {
            var tableNumber = document.getElementById('table-number').value;
            var notes = document.getElementById('order-notes').value;
            
            if (!tableNumber) {
                alert('Inserisci il numero del tavolo');
                return;
            }
            
            if (orderTotal === 0) {
                alert('Aggiungi almeno un piatto al tuo ordine');
                return;
            }
            
            var orderData = {
                action: 'risto_save_order',
                table_number: tableNumber,
                items: JSON.stringify(orderItems),
                total: orderTotal,
                notes: notes
            };
            
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.getElementById('order-message').innerHTML = '<div class="risto-success-message">Ordine inviato con successo!</div>';
                    
                    orderItems = {};
                    orderTotal = 0;
                    document.getElementById('order-total').textContent = '€0.00';
                    document.getElementById('table-number').value = '';
                    document.getElementById('order-notes').value = '';
                    
                    var quantities = document.querySelectorAll('.risto-quantity-display');
                    quantities.forEach(function(qty) {
                        qty.textContent = '0';
                    });
                } else {
                    document.getElementById('order-message').innerHTML = '<div class="risto-error-message">Errore durante l\'invio dell\'ordine</div>';
                }
            };
            
            var formData = Object.keys(orderData).map(function(key) {
                return encodeURIComponent(key) + '=' + encodeURIComponent(orderData[key]);
            }).join('&');
            
            xhr.send(formData);
        }
        </script>
        <?php
        return ob_get_clean();
    }
}

RistoBase::getInstance();
