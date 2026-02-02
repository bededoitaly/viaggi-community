<?php
/**
 * Plugin Name: Risto Base - Restaurant Management System
 * Plugin URI: https://bededoitaly.com/viaggi-community
 * Description: Sistema completo di gestione ristorante con menù digitale, ordini, form builder e interfaccia visuale moderna
 * Version: 1.0.0
 * Author: Bededo Italy
 * Author URI: https://bededoitaly.com
 * License: GPL-2.0+
 * Text Domain: risto-base
 */

if (!defined('ABSPATH')) {
    exit;
}

class RistoBase {
    
    private static $instance = null;
    private $version = '1.0.0';
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        
        // Register shortcodes
        add_shortcode('risto_form_builder', array($this, 'shortcode_form_builder'));
        add_shortcode('risto_list_tavoli', array($this, 'shortcode_list_tavoli'));
        add_shortcode('risto_list_camerieri', array($this, 'shortcode_list_camerieri'));
        add_shortcode('risto_list_piatti', array($this, 'shortcode_list_piatti'));
        add_shortcode('risto_list_comande', array($this, 'shortcode_list_comande'));
        add_shortcode('risto_list_clienti', array($this, 'shortcode_list_clienti'));
        add_shortcode('risto_menu_showcase', array($this, 'shortcode_menu_showcase'));
        add_shortcode('risto_order_interface', array($this, 'shortcode_order_interface'));
        
        // AJAX handlers
        add_action('wp_ajax_risto_save_form', array($this, 'ajax_save_form'));
        add_action('wp_ajax_risto_save_item', array($this, 'ajax_save_item'));
        add_action('wp_ajax_risto_delete_item', array($this, 'ajax_delete_item'));
        add_action('wp_ajax_risto_get_items', array($this, 'ajax_get_items'));
        add_action('wp_ajax_risto_import_csv', array($this, 'ajax_import_csv'));
        add_action('wp_ajax_risto_create_order', array($this, 'ajax_create_order'));
        add_action('wp_ajax_nopriv_risto_create_order', array($this, 'ajax_create_order'));
    }
    
    public function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Table: risto_forms
        $table_forms = $wpdb->prefix . 'risto_forms';
        $sql_forms = "CREATE TABLE $table_forms (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            archive_type varchar(50) NOT NULL,
            fields longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql_forms);
        
        // Table: risto_tavoli
        $table_tavoli = $wpdb->prefix . 'risto_tavoli';
        $sql_tavoli = "CREATE TABLE $table_tavoli (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            numero varchar(50) NOT NULL,
            posti int(11) DEFAULT 4,
            stato varchar(50) DEFAULT 'libero',
            data longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql_tavoli);
        
        // Table: risto_camerieri
        $table_camerieri = $wpdb->prefix . 'risto_camerieri';
        $sql_camerieri = "CREATE TABLE $table_camerieri (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            nome varchar(255) NOT NULL,
            cognome varchar(255) NOT NULL,
            telefono varchar(50),
            email varchar(255),
            data longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql_camerieri);
        
        // Table: risto_piatti
        $table_piatti = $wpdb->prefix . 'risto_piatti';
        $sql_piatti = "CREATE TABLE $table_piatti (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            nome varchar(255) NOT NULL,
            descrizione longtext,
            categoria varchar(100),
            prezzo decimal(10,2),
            immagine varchar(500),
            galleria longtext,
            disponibile tinyint(1) DEFAULT 1,
            data longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql_piatti);
        
        // Table: risto_comande
        $table_comande = $wpdb->prefix . 'risto_comande';
        $sql_comande = "CREATE TABLE $table_comande (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            numero_ordine varchar(50) NOT NULL,
            tavolo_id bigint(20),
            cameriere_id bigint(20),
            cliente_id bigint(20),
            items longtext NOT NULL,
            totale decimal(10,2),
            stato varchar(50) DEFAULT 'in_attesa',
            note longtext,
            data longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql_comande);
        
        // Table: risto_clienti
        $table_clienti = $wpdb->prefix . 'risto_clienti';
        $sql_clienti = "CREATE TABLE $table_clienti (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            nome varchar(255) NOT NULL,
            cognome varchar(255),
            telefono varchar(50),
            email varchar(255),
            indirizzo longtext,
            data longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql_clienti);
        
        // Create default forms
        $this->create_default_forms();
    }
    
    private function create_default_forms() {
        global $wpdb;
        $table = $wpdb->prefix . 'risto_forms';
        
        $default_forms = array(
            array(
                'name' => 'Form Tavoli',
                'archive_type' => 'tavoli',
                'fields' => json_encode(array(
                    array('type' => 'text', 'name' => 'numero', 'label' => 'Numero Tavolo', 'required' => true),
                    array('type' => 'number', 'name' => 'posti', 'label' => 'Numero Posti', 'required' => true),
                    array('type' => 'select', 'name' => 'stato', 'label' => 'Stato', 'options' => 'libero,occupato,riservato')
                ))
            ),
            array(
                'name' => 'Form Camerieri',
                'archive_type' => 'camerieri',
                'fields' => json_encode(array(
                    array('type' => 'text', 'name' => 'nome', 'label' => 'Nome', 'required' => true),
                    array('type' => 'text', 'name' => 'cognome', 'label' => 'Cognome', 'required' => true),
                    array('type' => 'tel', 'name' => 'telefono', 'label' => 'Telefono'),
                    array('type' => 'email', 'name' => 'email', 'label' => 'Email')
                ))
            ),
            array(
                'name' => 'Form Piatti',
                'archive_type' => 'piatti',
                'fields' => json_encode(array(
                    array('type' => 'text', 'name' => 'nome', 'label' => 'Nome Piatto', 'required' => true),
                    array('type' => 'textarea', 'name' => 'descrizione', 'label' => 'Descrizione'),
                    array('type' => 'select', 'name' => 'categoria', 'label' => 'Categoria', 'options' => 'Antipasti,Primi,Secondi,Contorni,Dessert,Bevande'),
                    array('type' => 'number', 'name' => 'prezzo', 'label' => 'Prezzo (€)', 'step' => '0.01'),
                    array('type' => 'file', 'name' => 'immagine', 'label' => 'Immagine'),
                    array('type' => 'gallery', 'name' => 'galleria', 'label' => 'Galleria Immagini'),
                    array('type' => 'checkbox', 'name' => 'disponibile', 'label' => 'Disponibile')
                ))
            ),
            array(
                'name' => 'Form Comande',
                'archive_type' => 'comande',
                'fields' => json_encode(array(
                    array('type' => 'text', 'name' => 'numero_ordine', 'label' => 'Numero Ordine', 'required' => true),
                    array('type' => 'select', 'name' => 'stato', 'label' => 'Stato', 'options' => 'in_attesa,in_preparazione,pronto,consegnato,annullato'),
                    array('type' => 'textarea', 'name' => 'note', 'label' => 'Note')
                ))
            ),
            array(
                'name' => 'Form Clienti',
                'archive_type' => 'clienti',
                'fields' => json_encode(array(
                    array('type' => 'text', 'name' => 'nome', 'label' => 'Nome', 'required' => true),
                    array('type' => 'text', 'name' => 'cognome', 'label' => 'Cognome'),
                    array('type' => 'tel', 'name' => 'telefono', 'label' => 'Telefono'),
                    array('type' => 'email', 'name' => 'email', 'label' => 'Email'),
                    array('type' => 'textarea', 'name' => 'indirizzo', 'label' => 'Indirizzo')
                ))
            )
        );
        
        foreach ($default_forms as $form) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table WHERE archive_type = %s",
                $form['archive_type']
            ));
            
            if (!$exists) {
                $wpdb->insert($table, $form);
            }
        }
    }
    
    public function init() {
        // Initialization code
    }
    
    public function admin_menu() {
        add_menu_page(
            'Risto Base',
            'Risto Base',
            'manage_options',
            'risto-base',
            array($this, 'admin_page_dashboard'),
            'dashicons-food',
            30
        );
        
        add_submenu_page('risto-base', 'Dashboard', 'Dashboard', 'manage_options', 'risto-base', array($this, 'admin_page_dashboard'));
        add_submenu_page('risto-base', 'Form Builder', 'Form Builder', 'manage_options', 'risto-forms', array($this, 'admin_page_forms'));
        add_submenu_page('risto-base', 'Tavoli', 'Tavoli', 'manage_options', 'risto-tavoli', array($this, 'admin_page_tavoli'));
        add_submenu_page('risto-base', 'Camerieri', 'Camerieri', 'manage_options', 'risto-camerieri', array($this, 'admin_page_camerieri'));
        add_submenu_page('risto-base', 'Piatti', 'Piatti', 'manage_options', 'risto-piatti', array($this, 'admin_page_piatti'));
        add_submenu_page('risto-base', 'Comande', 'Comande', 'manage_options', 'risto-comande', array($this, 'admin_page_comande'));
        add_submenu_page('risto-base', 'Clienti', 'Clienti', 'manage_options', 'risto-clienti', array($this, 'admin_page_clienti'));
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style('risto-base-style', plugins_url('', __FILE__) . '/risto-base.css', array(), $this->version);
        wp_enqueue_script('risto-base-script', plugins_url('', __FILE__) . '/risto-base.js', array('jquery'), $this->version, true);
        
        wp_localize_script('risto-base-script', 'ristoBase', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('risto-base-nonce')
        ));
    }
    
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'risto-') === false) {
            return;
        }
        
        wp_enqueue_media();
        wp_enqueue_style('risto-base-admin', plugins_url('', __FILE__) . '/risto-admin.css', array(), $this->version);
        wp_enqueue_script('risto-base-admin', plugins_url('', __FILE__) . '/risto-admin.js', array('jquery', 'jquery-ui-sortable'), $this->version, true);
        
        wp_localize_script('risto-base-admin', 'ristoAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('risto-admin-nonce')
        ));
    }
    
    public function admin_page_dashboard() {
        ?>
        <div class="wrap risto-admin">
            <h1>Risto Base - Dashboard</h1>
            <div class="risto-dashboard">
                <div class="risto-stats">
                    <div class="risto-stat-card">
                        <h3>Tavoli</h3>
                        <p class="stat-number"><?php echo $this->get_count('tavoli'); ?></p>
                    </div>
                    <div class="risto-stat-card">
                        <h3>Camerieri</h3>
                        <p class="stat-number"><?php echo $this->get_count('camerieri'); ?></p>
                    </div>
                    <div class="risto-stat-card">
                        <h3>Piatti</h3>
                        <p class="stat-number"><?php echo $this->get_count('piatti'); ?></p>
                    </div>
                    <div class="risto-stat-card">
                        <h3>Comande</h3>
                        <p class="stat-number"><?php echo $this->get_count('comande'); ?></p>
                    </div>
                    <div class="risto-stat-card">
                        <h3>Clienti</h3>
                        <p class="stat-number"><?php echo $this->get_count('clienti'); ?></p>
                    </div>
                </div>
                <div class="risto-quick-links">
                    <h2>Shortcodes Disponibili</h2>
                    <ul>
                        <li><code>[risto_form_builder]</code> - Form Builder Interfaccia</li>
                        <li><code>[risto_list_tavoli]</code> - Elenco Tavoli</li>
                        <li><code>[risto_list_camerieri]</code> - Elenco Camerieri</li>
                        <li><code>[risto_list_piatti]</code> - Elenco Piatti</li>
                        <li><code>[risto_list_comande]</code> - Elenco Comande</li>
                        <li><code>[risto_list_clienti]</code> - Elenco Clienti</li>
                        <li><code>[risto_menu_showcase]</code> - Vetrina Menù</li>
                        <li><code>[risto_order_interface]</code> - Interfaccia Ordini</li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function get_count($table) {
        global $wpdb;
        return $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}risto_{$table}");
    }
    
    public function admin_page_forms() {
        global $wpdb;
        $table = $wpdb->prefix . 'risto_forms';
        $forms = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
        
        ?>
        <div class="wrap risto-admin">
            <h1>Form Builder</h1>
            <button class="button button-primary" id="risto-new-form">Nuovo Form</button>
            
            <div id="risto-form-builder" style="display:none;">
                <h2>Crea/Modifica Form</h2>
                <form id="risto-form-editor">
                    <input type="hidden" id="form-id" name="form_id" value="">
                    <p>
                        <label>Nome Form:</label>
                        <input type="text" id="form-name" name="form_name" required>
                    </p>
                    <p>
                        <label>Archivio:</label>
                        <select id="form-archive" name="form_archive" required>
                            <option value="tavoli">Tavoli</option>
                            <option value="camerieri">Camerieri</option>
                            <option value="piatti">Piatti</option>
                            <option value="comande">Comande</option>
                            <option value="clienti">Clienti</option>
                        </select>
                    </p>
                    
                    <div id="form-fields">
                        <h3>Campi del Form</h3>
                        <div id="fields-list"></div>
                        <button type="button" class="button" id="add-field">Aggiungi Campo</button>
                    </div>
                    
                    <p>
                        <button type="submit" class="button button-primary">Salva Form</button>
                        <button type="button" class="button" id="cancel-form">Annulla</button>
                    </p>
                </form>
            </div>
            
            <h2>Forms Esistenti</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Archivio</th>
                        <th>Campi</th>
                        <th>Data Creazione</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($forms as $form): 
                        $fields = json_decode($form->fields, true);
                        $field_count = is_array($fields) ? count($fields) : 0;
                    ?>
                    <tr>
                        <td><?php echo esc_html($form->name); ?></td>
                        <td><?php echo esc_html($form->archive_type); ?></td>
                        <td><?php echo $field_count; ?> campi</td>
                        <td><?php echo esc_html($form->created_at); ?></td>
                        <td>
                            <button class="button edit-form" data-id="<?php echo $form->id; ?>">Modifica</button>
                            <button class="button delete-form" data-id="<?php echo $form->id; ?>">Elimina</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    public function admin_page_tavoli() {
        $this->render_archive_page('tavoli', 'Tavoli');
    }
    
    public function admin_page_camerieri() {
        $this->render_archive_page('camerieri', 'Camerieri');
    }
    
    public function admin_page_piatti() {
        $this->render_archive_page('piatti', 'Piatti');
    }
    
    public function admin_page_comande() {
        $this->render_archive_page('comande', 'Comande');
    }
    
    public function admin_page_clienti() {
        $this->render_archive_page('clienti', 'Clienti');
    }
    
    private function render_archive_page($archive, $title) {
        global $wpdb;
        $table = $wpdb->prefix . 'risto_' . $archive;
        $items = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
        
        $form_table = $wpdb->prefix . 'risto_forms';
        $form = $wpdb->get_row($wpdb->prepare("SELECT * FROM $form_table WHERE archive_type = %s", $archive));
        
        ?>
        <div class="wrap risto-admin">
            <h1><?php echo esc_html($title); ?></h1>
            <button class="button button-primary risto-add-item" data-archive="<?php echo esc_attr($archive); ?>">Aggiungi</button>
            <button class="button risto-import-csv" data-archive="<?php echo esc_attr($archive); ?>">Importa CSV</button>
            
            <div class="risto-items-container">
                <?php if ($items): ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <?php 
                                if ($items && isset($items[0])) {
                                    $first_item = (array)$items[0];
                                    foreach ($first_item as $key => $value) {
                                        if (!in_array($key, array('id', 'data', 'created_at'))) {
                                            echo '<th>' . esc_html(ucfirst($key)) . '</th>';
                                        }
                                    }
                                }
                                ?>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): 
                                $item_array = (array)$item;
                            ?>
                            <tr>
                                <td><?php echo $item->id; ?></td>
                                <?php foreach ($item_array as $key => $value): 
                                    if (!in_array($key, array('id', 'data', 'created_at'))):
                                ?>
                                    <td><?php echo esc_html(substr($value, 0, 100)); ?></td>
                                <?php endif; endforeach; ?>
                                <td>
                                    <button class="button risto-edit-item" data-id="<?php echo $item->id; ?>" data-archive="<?php echo esc_attr($archive); ?>">Modifica</button>
                                    <button class="button risto-delete-item" data-id="<?php echo $item->id; ?>" data-archive="<?php echo esc_attr($archive); ?>">Elimina</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Nessun elemento trovato.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    // Shortcode: Form Builder Interface
    public function shortcode_form_builder($atts) {
        if (!current_user_can('manage_options')) {
            return '<p>Accesso negato.</p>';
        }
        
        ob_start();
        $this->admin_page_forms();
        return ob_get_clean();
    }
    
    // Shortcode: List Archives
    public function shortcode_list_tavoli($atts) {
        return $this->render_list_shortcode('tavoli', $atts);
    }
    
    public function shortcode_list_camerieri($atts) {
        return $this->render_list_shortcode('camerieri', $atts);
    }
    
    public function shortcode_list_piatti($atts) {
        return $this->render_list_shortcode('piatti', $atts);
    }
    
    public function shortcode_list_comande($atts) {
        return $this->render_list_shortcode('comande', $atts);
    }
    
    public function shortcode_list_clienti($atts) {
        return $this->render_list_shortcode('clienti', $atts);
    }
    
    private function render_list_shortcode($archive, $atts) {
        global $wpdb;
        $table = $wpdb->prefix . 'risto_' . $archive;
        $items = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
        
        ob_start();
        ?>
        <div class="risto-list risto-list-<?php echo esc_attr($archive); ?>">
            <h2 class="risto-list-title"><?php echo esc_html(ucfirst($archive)); ?></h2>
            <?php if ($items): ?>
                <div class="risto-items-grid">
                    <?php foreach ($items as $item): ?>
                        <div class="risto-item-card">
                            <?php 
                            $item_array = (array)$item;
                            foreach ($item_array as $key => $value):
                                if (!in_array($key, array('id', 'data', 'created_at'))):
                            ?>
                                <div class="risto-item-field">
                                    <strong><?php echo esc_html(ucfirst($key)); ?>:</strong>
                                    <span><?php echo esc_html($value); ?></span>
                                </div>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Nessun elemento disponibile.</p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    // Shortcode: Menu Showcase
    public function shortcode_menu_showcase($atts) {
        global $wpdb;
        $table = $wpdb->prefix . 'risto_piatti';
        
        $atts = shortcode_atts(array(
            'categoria' => '',
        ), $atts);
        
        $where = "WHERE disponibile = 1";
        if (!empty($atts['categoria'])) {
            $where .= $wpdb->prepare(" AND categoria = %s", $atts['categoria']);
        }
        
        $piatti = $wpdb->get_results("SELECT * FROM $table $where ORDER BY categoria, nome");
        
        $categorie = $wpdb->get_col("SELECT DISTINCT categoria FROM $table WHERE disponibile = 1 ORDER BY categoria");
        
        ob_start();
        ?>
        <div class="risto-menu-showcase">
            <h2 class="showcase-title">Il Nostro Menù</h2>
            
            <div class="category-filter">
                <button class="category-btn active" data-category="">Tutti</button>
                <?php foreach ($categorie as $cat): ?>
                    <button class="category-btn" data-category="<?php echo esc_attr($cat); ?>"><?php echo esc_html($cat); ?></button>
                <?php endforeach; ?>
            </div>
            
            <div class="dishes-grid">
                <?php foreach ($piatti as $piatto): ?>
                    <div class="dish-card" data-category="<?php echo esc_attr($piatto->categoria); ?>" data-id="<?php echo $piatto->id; ?>">
                        <?php if (!empty($piatto->immagine)): ?>
                            <div class="dish-image">
                                <img src="<?php echo esc_url($piatto->immagine); ?>" alt="<?php echo esc_attr($piatto->nome); ?>">
                            </div>
                        <?php endif; ?>
                        <div class="dish-info">
                            <h3 class="dish-name"><?php echo esc_html($piatto->nome); ?></h3>
                            <p class="dish-description"><?php echo esc_html($piatto->descrizione); ?></p>
                            <div class="dish-price">€ <?php echo number_format($piatto->prezzo, 2, ',', '.'); ?></div>
                            <div class="dish-quantity-controls">
                                <button class="qty-btn qty-minus" data-id="<?php echo $piatto->id; ?>">-</button>
                                <span class="qty-display" data-id="<?php echo $piatto->id; ?>">0</span>
                                <button class="qty-btn qty-plus" data-id="<?php echo $piatto->id; ?>">+</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    // Shortcode: Order Interface
    public function shortcode_order_interface($atts) {
        global $wpdb;
        $table = $wpdb->prefix . 'risto_piatti';
        $piatti = $wpdb->get_results("SELECT * FROM $table WHERE disponibile = 1 ORDER BY categoria, nome");
        
        $categorie = $wpdb->get_col("SELECT DISTINCT categoria FROM $table WHERE disponibile = 1 ORDER BY categoria");
        
        ob_start();
        ?>
        <div class="risto-order-interface">
            <div class="order-header">
                <h2>Effettua un Ordine</h2>
                <div class="order-cart-btn">
                    🛒 Carrello (<span id="cart-count">0</span>)
                </div>
            </div>
            
            <div class="order-categories">
                <?php foreach ($categorie as $cat): ?>
                    <button class="order-category-btn" data-category="<?php echo esc_attr($cat); ?>"><?php echo esc_html($cat); ?></button>
                <?php endforeach; ?>
            </div>
            
            <div class="order-dishes">
                <?php 
                $current_category = '';
                foreach ($piatti as $piatto): 
                    if ($piatto->categoria != $current_category):
                        if ($current_category != '') echo '</div>';
                        $current_category = $piatto->categoria;
                        ?>
                        <div class="order-category-section" data-category="<?php echo esc_attr($current_category); ?>">
                            <h3 class="category-title"><?php echo esc_html($current_category); ?></h3>
                            <div class="category-dishes">
                    <?php endif; ?>
                    
                    <div class="order-dish-card" data-id="<?php echo $piatto->id; ?>" data-price="<?php echo $piatto->prezzo; ?>" data-name="<?php echo esc_attr($piatto->nome); ?>">
                        <?php if (!empty($piatto->immagine)): ?>
                            <div class="order-dish-image">
                                <img src="<?php echo esc_url($piatto->immagine); ?>" alt="<?php echo esc_attr($piatto->nome); ?>">
                            </div>
                        <?php endif; ?>
                        <div class="order-dish-info">
                            <h4><?php echo esc_html($piatto->nome); ?></h4>
                            <p class="order-dish-price">€ <?php echo number_format($piatto->prezzo, 2, ',', '.'); ?></p>
                        </div>
                        <div class="order-quantity-controls">
                            <button class="order-qty-minus">-</button>
                            <span class="order-qty-display">0</span>
                            <button class="order-qty-plus">+</button>
                        </div>
                    </div>
                <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="order-summary">
                <div class="summary-content">
                    <h3>Riepilogo Ordine</h3>
                    <div id="order-items-list"></div>
                    <div class="order-total">
                        <strong>Totale:</strong> € <span id="order-total">0.00</span>
                    </div>
                    <button class="submit-order-btn">Invia Ordine</button>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    // AJAX Handlers
    public function ajax_save_form() {
        check_ajax_referer('risto-admin-nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti');
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'risto_forms';
        
        $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
        $form_name = sanitize_text_field($_POST['form_name']);
        $form_archive = sanitize_text_field($_POST['form_archive']);
        $form_fields = isset($_POST['form_fields']) ? $_POST['form_fields'] : array();
        
        $data = array(
            'name' => $form_name,
            'archive_type' => $form_archive,
            'fields' => json_encode($form_fields)
        );
        
        if ($form_id > 0) {
            $wpdb->update($table, $data, array('id' => $form_id));
        } else {
            $wpdb->insert($table, $data);
            $form_id = $wpdb->insert_id;
        }
        
        wp_send_json_success(array('id' => $form_id));
    }
    
    public function ajax_save_item() {
        check_ajax_referer('risto-admin-nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti');
        }
        
        global $wpdb;
        $archive = sanitize_text_field($_POST['archive']);
        $table = $wpdb->prefix . 'risto_' . $archive;
        
        $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
        $item_data = $_POST['item_data'];
        
        // Sanitize data
        $data = array();
        foreach ($item_data as $key => $value) {
            $data[sanitize_key($key)] = sanitize_text_field($value);
        }
        
        if ($item_id > 0) {
            $wpdb->update($table, $data, array('id' => $item_id));
        } else {
            $wpdb->insert($table, $data);
            $item_id = $wpdb->insert_id;
        }
        
        wp_send_json_success(array('id' => $item_id));
    }
    
    public function ajax_delete_item() {
        check_ajax_referer('risto-admin-nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti');
        }
        
        global $wpdb;
        $archive = sanitize_text_field($_POST['archive']);
        $table = $wpdb->prefix . 'risto_' . $archive;
        $item_id = intval($_POST['item_id']);
        
        $wpdb->delete($table, array('id' => $item_id));
        
        wp_send_json_success();
    }
    
    public function ajax_get_items() {
        check_ajax_referer('risto-admin-nonce', 'nonce');
        
        global $wpdb;
        $archive = sanitize_text_field($_POST['archive']);
        $table = $wpdb->prefix . 'risto_' . $archive;
        
        $items = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
        
        wp_send_json_success($items);
    }
    
    public function ajax_import_csv() {
        check_ajax_referer('risto-admin-nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti');
        }
        
        $archive = sanitize_text_field($_POST['archive']);
        
        if (!isset($_FILES['csv_file'])) {
            wp_send_json_error('Nessun file caricato');
        }
        
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        
        if ($handle === false) {
            wp_send_json_error('Impossibile aprire il file');
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'risto_' . $archive;
        
        $headers = fgetcsv($handle);
        $imported = 0;
        
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) != count($headers)) continue;
            
            $data = array();
            for ($i = 0; $i < count($headers); $i++) {
                $key = sanitize_key($headers[$i]);
                $data[$key] = sanitize_text_field($row[$i]);
            }
            
            $wpdb->insert($table, $data);
            $imported++;
        }
        
        fclose($handle);
        
        wp_send_json_success(array('imported' => $imported));
    }
    
    public function ajax_create_order() {
        check_ajax_referer('risto-base-nonce', 'nonce');
        
        global $wpdb;
        $table = $wpdb->prefix . 'risto_comande';
        
        $items = isset($_POST['items']) ? json_decode(stripslashes($_POST['items']), true) : array();
        $totale = isset($_POST['totale']) ? floatval($_POST['totale']) : 0;
        $tavolo_id = isset($_POST['tavolo_id']) ? intval($_POST['tavolo_id']) : null;
        $note = isset($_POST['note']) ? sanitize_textarea_field($_POST['note']) : '';
        
        // Generate order number
        $numero_ordine = 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $data = array(
            'numero_ordine' => $numero_ordine,
            'tavolo_id' => $tavolo_id,
            'items' => json_encode($items),
            'totale' => $totale,
            'stato' => 'in_attesa',
            'note' => $note
        );
        
        $wpdb->insert($table, $data);
        $order_id = $wpdb->insert_id;
        
        wp_send_json_success(array(
            'order_id' => $order_id,
            'numero_ordine' => $numero_ordine
        ));
    }
}

// Initialize plugin
RistoBase::get_instance();

// Inline CSS
function risto_base_inline_styles() {
    ?>
    <style>
    /* Premium Color Scheme: Blue, Turquoise, Green, Gold */
    :root {
        --risto-primary: #1e3a8a; /* Blue */
        --risto-secondary: #06b6d4; /* Turquoise */
        --risto-accent: #10b981; /* Green */
        --risto-gold: linear-gradient(135deg, #d4af37 0%, #f4e5b1 25%, #d4af37 50%, #f4e5b1 75%, #d4af37 100%);
        --risto-gold-solid: #d4af37;
        --risto-dark: #1f2937;
        --risto-light: #f3f4f6;
    }
    
    .risto-admin {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    }
    
    .risto-dashboard {
        padding: 20px 0;
    }
    
    .risto-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .risto-stat-card {
        background: linear-gradient(135deg, var(--risto-primary) 0%, var(--risto-secondary) 100%);
        color: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        text-align: center;
    }
    
    .risto-stat-card h3 {
        margin: 0 0 10px 0;
        font-size: 16px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .stat-number {
        font-size: 48px;
        font-weight: bold;
        margin: 0;
        background: var(--risto-gold);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .risto-quick-links {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .risto-quick-links code {
        background: var(--risto-light);
        padding: 5px 10px;
        border-radius: 5px;
        border-left: 3px solid var(--risto-gold-solid);
    }
    
    /* Lists */
    .risto-list {
        padding: 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .risto-list-title {
        color: var(--risto-primary);
        border-bottom: 3px solid var(--risto-gold-solid);
        padding-bottom: 10px;
        margin-bottom: 20px;
    }
    
    .risto-items-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }
    
    .risto-item-card {
        background: var(--risto-light);
        padding: 20px;
        border-radius: 8px;
        border-top: 4px solid var(--risto-secondary);
        transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .risto-item-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
    }
    
    .risto-item-field {
        margin-bottom: 10px;
    }
    
    /* Menu Showcase */
    .risto-menu-showcase {
        padding: 40px 20px;
    }
    
    .showcase-title {
        text-align: center;
        font-size: 36px;
        color: var(--risto-primary);
        margin-bottom: 30px;
        background: var(--risto-gold);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .category-filter {
        display: flex;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 30px;
    }
    
    .category-btn {
        background: white;
        border: 2px solid var(--risto-primary);
        color: var(--risto-primary);
        padding: 12px 24px;
        border-radius: 25px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s;
    }
    
    .category-btn:hover, .category-btn.active {
        background: linear-gradient(135deg, var(--risto-primary) 0%, var(--risto-secondary) 100%);
        color: white;
        border-color: transparent;
    }
    
    .dishes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 30px;
    }
    
    .dish-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .dish-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
    }
    
    .dish-image {
        width: 100%;
        height: 200px;
        overflow: hidden;
    }
    
    .dish-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }
    
    .dish-card:hover .dish-image img {
        transform: scale(1.1);
    }
    
    .dish-info {
        padding: 20px;
    }
    
    .dish-name {
        color: var(--risto-primary);
        margin: 0 0 10px 0;
        font-size: 20px;
    }
    
    .dish-description {
        color: #6b7280;
        margin: 0 0 15px 0;
        font-size: 14px;
        line-height: 1.5;
    }
    
    .dish-price {
        font-size: 24px;
        font-weight: bold;
        background: var(--risto-gold);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 15px;
    }
    
    .dish-quantity-controls, .order-quantity-controls {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
    }
    
    .qty-btn, .order-qty-minus, .order-qty-plus {
        width: 50px;
        height: 50px;
        border: none;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--risto-accent) 0%, var(--risto-secondary) 100%);
        color: white;
        font-size: 24px;
        cursor: pointer;
        transition: transform 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .qty-btn:hover, .order-qty-minus:hover, .order-qty-plus:hover {
        transform: scale(1.1);
    }
    
    .qty-btn:active, .order-qty-minus:active, .order-qty-plus:active {
        transform: scale(0.95);
    }
    
    .qty-display, .order-qty-display {
        font-size: 24px;
        font-weight: bold;
        min-width: 40px;
        text-align: center;
        color: var(--risto-primary);
    }
    
    /* Order Interface */
    .risto-order-interface {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding: 20px;
        background: linear-gradient(135deg, var(--risto-primary) 0%, var(--risto-secondary) 100%);
        color: white;
        border-radius: 10px;
    }
    
    .order-cart-btn {
        background: white;
        color: var(--risto-primary);
        padding: 12px 24px;
        border-radius: 25px;
        font-weight: bold;
        cursor: pointer;
        transition: transform 0.3s;
    }
    
    .order-cart-btn:hover {
        transform: scale(1.05);
    }
    
    .order-categories {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding: 10px 0;
        margin-bottom: 20px;
    }
    
    .order-category-btn {
        background: white;
        border: 2px solid var(--risto-accent);
        color: var(--risto-accent);
        padding: 15px 30px;
        border-radius: 25px;
        font-weight: bold;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.3s;
    }
    
    .order-category-btn:hover, .order-category-btn.active {
        background: var(--risto-accent);
        color: white;
    }
    
    .order-category-section {
        margin-bottom: 30px;
    }
    
    .category-title {
        color: var(--risto-primary);
        border-bottom: 3px solid var(--risto-gold-solid);
        padding-bottom: 10px;
        margin-bottom: 20px;
    }
    
    .category-dishes {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .order-dish-card {
        background: white;
        border-radius: 10px;
        padding: 15px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .order-dish-image {
        width: 100%;
        height: 150px;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .order-dish-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .order-dish-info h4 {
        margin: 0;
        color: var(--risto-primary);
    }
    
    .order-dish-price {
        font-weight: bold;
        color: var(--risto-gold-solid);
    }
    
    .order-summary {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: white;
        box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.1);
        padding: 20px;
        z-index: 1000;
    }
    
    .summary-content {
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .order-total {
        font-size: 24px;
        margin: 15px 0;
        text-align: right;
        color: var(--risto-primary);
    }
    
    .submit-order-btn {
        width: 100%;
        padding: 18px;
        background: linear-gradient(135deg, var(--risto-accent) 0%, var(--risto-secondary) 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 18px;
        font-weight: bold;
        cursor: pointer;
        transition: transform 0.3s;
    }
    
    .submit-order-btn:hover {
        transform: scale(1.02);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .dishes-grid, .category-dishes {
            grid-template-columns: 1fr;
        }
        
        .risto-stats {
            grid-template-columns: 1fr;
        }
        
        .order-header {
            flex-direction: column;
            gap: 15px;
        }
    }
    </style>
    <?php
}
add_action('wp_head', 'risto_base_inline_styles');
add_action('admin_head', 'risto_base_inline_styles');

// Inline JavaScript
function risto_base_inline_scripts() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Category Filter
        $('.category-btn').on('click', function() {
            $('.category-btn').removeClass('active');
            $(this).addClass('active');
            var category = $(this).data('category');
            
            if (category === '') {
                $('.dish-card').show();
            } else {
                $('.dish-card').hide();
                $('.dish-card[data-category="' + category + '"]').show();
            }
        });
        
        // Quantity Controls - Menu Showcase
        var quantities = {};
        
        $('.qty-plus').on('click', function() {
            var id = $(this).data('id');
            quantities[id] = (quantities[id] || 0) + 1;
            $('.qty-display[data-id="' + id + '"]').text(quantities[id]);
        });
        
        $('.qty-minus').on('click', function() {
            var id = $(this).data('id');
            if (quantities[id] > 0) {
                quantities[id]--;
                $('.qty-display[data-id="' + id + '"]').text(quantities[id]);
            }
        });
        
        // Order Interface
        var orderItems = {};
        var orderTotal = 0;
        
        function updateOrderDisplay() {
            var itemsList = '';
            var count = 0;
            orderTotal = 0;
            
            for (var id in orderItems) {
                if (orderItems[id].quantity > 0) {
                    count += orderItems[id].quantity;
                    var itemTotal = orderItems[id].quantity * orderItems[id].price;
                    orderTotal += itemTotal;
                    itemsList += '<div class="order-item-row">' +
                        orderItems[id].name + ' x ' + orderItems[id].quantity +
                        ' = € ' + itemTotal.toFixed(2) +
                        '</div>';
                }
            }
            
            $('#cart-count').text(count);
            $('#order-items-list').html(itemsList);
            $('#order-total').text(orderTotal.toFixed(2));
        }
        
        $('.order-qty-plus').on('click', function() {
            var card = $(this).closest('.order-dish-card');
            var id = card.data('id');
            var name = card.data('name');
            var price = parseFloat(card.data('price'));
            
            if (!orderItems[id]) {
                orderItems[id] = {name: name, price: price, quantity: 0};
            }
            
            orderItems[id].quantity++;
            card.find('.order-qty-display').text(orderItems[id].quantity);
            updateOrderDisplay();
        });
        
        $('.order-qty-minus').on('click', function() {
            var card = $(this).closest('.order-dish-card');
            var id = card.data('id');
            
            if (orderItems[id] && orderItems[id].quantity > 0) {
                orderItems[id].quantity--;
                card.find('.order-qty-display').text(orderItems[id].quantity);
                updateOrderDisplay();
            }
        });
        
        // Category navigation
        $('.order-category-btn').on('click', function() {
            $('.order-category-btn').removeClass('active');
            $(this).addClass('active');
            var category = $(this).data('category');
            
            $('.order-category-section').hide();
            $('.order-category-section[data-category="' + category + '"]').show();
        });
        
        // Initialize - show first category
        if ($('.order-category-btn').length > 0) {
            $('.order-category-btn').first().click();
        }
        
        // Submit Order
        $('.submit-order-btn').on('click', function() {
            if (orderTotal === 0) {
                alert('Il carrello è vuoto!');
                return;
            }
            
            var items = [];
            for (var id in orderItems) {
                if (orderItems[id].quantity > 0) {
                    items.push({
                        id: id,
                        name: orderItems[id].name,
                        quantity: orderItems[id].quantity,
                        price: orderItems[id].price
                    });
                }
            }
            
            $.ajax({
                url: ristoBase.ajaxurl,
                method: 'POST',
                data: {
                    action: 'risto_create_order',
                    nonce: ristoBase.nonce,
                    items: JSON.stringify(items),
                    totale: orderTotal
                },
                success: function(response) {
                    if (response.success) {
                        alert('Ordine creato con successo! Numero: ' + response.data.numero_ordine);
                        // Reset
                        orderItems = {};
                        orderTotal = 0;
                        $('.order-qty-display').text('0');
                        updateOrderDisplay();
                    } else {
                        alert('Errore nella creazione dell\'ordine');
                    }
                }
            });
        });
    });
    </script>
    <?php
}
add_action('wp_footer', 'risto_base_inline_scripts');
