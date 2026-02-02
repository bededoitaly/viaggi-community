# 📋 Specifiche Tecniche - Risto Base

## Informazioni Generali

**Nome Plugin Principale:** Risto Base - Restaurant Management System  
**Nome Plugin Satellite:** Risto Kitchen Board - Tabellone Cucina  
**Versione:** 1.0.0  
**Licenza:** GPL-2.0+  
**Autore:** Bededo Italy  
**Requisiti Minimi:**
- WordPress 5.0+
- PHP 7.4+
- MySQL 5.6+

---

## 🗄️ Struttura Database

### Tabelle Create (Prefisso: wp_)

#### 1. risto_forms
Memorizza le definizioni dei form personalizzati.

```sql
CREATE TABLE wp_risto_forms (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    archive_type VARCHAR(50) NOT NULL,
    fields LONGTEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Campi:**
- `id`: ID univoco form
- `name`: Nome descrittivo del form
- `archive_type`: Tipo archivio (tavoli, camerieri, piatti, comande, clienti)
- `fields`: JSON con definizione campi
- `created_at`: Data creazione

**Esempio fields JSON:**
```json
[
    {
        "type": "text",
        "name": "nome",
        "label": "Nome Piatto",
        "required": true
    },
    {
        "type": "number",
        "name": "prezzo",
        "label": "Prezzo (€)",
        "step": "0.01"
    }
]
```

#### 2. risto_tavoli
Gestione tavoli del ristorante.

```sql
CREATE TABLE wp_risto_tavoli (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    numero VARCHAR(50) NOT NULL,
    posti INT(11) DEFAULT 4,
    stato VARCHAR(50) DEFAULT 'libero',
    data LONGTEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Stati possibili:** libero, occupato, riservato

#### 3. risto_camerieri
Gestione personale/camerieri.

```sql
CREATE TABLE wp_risto_camerieri (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    nome VARCHAR(255) NOT NULL,
    cognome VARCHAR(255) NOT NULL,
    telefono VARCHAR(50),
    email VARCHAR(255),
    data LONGTEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### 4. risto_piatti
Gestione menù e piatti.

```sql
CREATE TABLE wp_risto_piatti (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    nome VARCHAR(255) NOT NULL,
    descrizione LONGTEXT,
    categoria VARCHAR(100),
    prezzo DECIMAL(10,2),
    immagine VARCHAR(500),
    galleria LONGTEXT,
    disponibile TINYINT(1) DEFAULT 1,
    data LONGTEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Categorie standard:** Antipasti, Primi, Secondi, Contorni, Dessert, Bevande

#### 5. risto_comande
Gestione ordini/comande.

```sql
CREATE TABLE wp_risto_comande (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    numero_ordine VARCHAR(50) NOT NULL,
    tavolo_id BIGINT(20),
    cameriere_id BIGINT(20),
    cliente_id BIGINT(20),
    items LONGTEXT NOT NULL,
    totale DECIMAL(10,2),
    stato VARCHAR(50) DEFAULT 'in_attesa',
    note LONGTEXT,
    data LONGTEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Stati ordine:** in_attesa, in_preparazione, pronto, consegnato, annullato

**Esempio items JSON:**
```json
[
    {
        "id": 5,
        "name": "Spaghetti Carbonara",
        "quantity": 2,
        "price": 12.50
    },
    {
        "id": 8,
        "name": "Tiramisù",
        "quantity": 1,
        "price": 6.00
    }
]
```

#### 6. risto_clienti
Database clienti.

```sql
CREATE TABLE wp_risto_clienti (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    nome VARCHAR(255) NOT NULL,
    cognome VARCHAR(255),
    telefono VARCHAR(50),
    email VARCHAR(255),
    indirizzo LONGTEXT,
    data LONGTEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 🔌 API Endpoints (AJAX)

### Risto Base

#### 1. risto_save_form
**Azione:** Salva/Aggiorna form personalizzato  
**Metodo:** POST  
**Permessi:** manage_options  
**Parametri:**
- `nonce`: Security nonce
- `form_id`: ID form (0 per nuovo)
- `form_name`: Nome form
- `form_archive`: Tipo archivio
- `form_fields`: Array campi

**Risposta:**
```json
{
    "success": true,
    "data": {
        "id": 5
    }
}
```

#### 2. risto_save_item
**Azione:** Salva elemento in archivio  
**Metodo:** POST  
**Permessi:** manage_options  
**Parametri:**
- `nonce`: Security nonce
- `archive`: Tipo archivio
- `item_id`: ID elemento (0 per nuovo)
- `item_data`: Array con dati

#### 3. risto_delete_item
**Azione:** Elimina elemento  
**Metodo:** POST  
**Permessi:** manage_options  
**Parametri:**
- `nonce`: Security nonce
- `archive`: Tipo archivio
- `item_id`: ID elemento

#### 4. risto_get_items
**Azione:** Recupera lista elementi  
**Metodo:** POST  
**Permessi:** manage_options  
**Parametri:**
- `nonce`: Security nonce
- `archive`: Tipo archivio

#### 5. risto_import_csv
**Azione:** Importa dati da CSV  
**Metodo:** POST  
**Permessi:** manage_options  
**Parametri:**
- `nonce`: Security nonce
- `archive`: Tipo archivio
- `csv_file`: File CSV

#### 6. risto_create_order
**Azione:** Crea nuovo ordine  
**Metodo:** POST  
**Permessi:** Nessuno (pubblico)  
**Parametri:**
- `nonce`: Security nonce
- `items`: JSON array items
- `totale`: Totale ordine
- `tavolo_id`: (opzionale)
- `note`: (opzionale)

**Risposta:**
```json
{
    "success": true,
    "data": {
        "order_id": 42,
        "numero_ordine": "ORD-20260202-0042"
    }
}
```

### Risto Kitchen Board

#### 7. risto_get_pending_orders
**Azione:** Recupera ordini pendenti  
**Metodo:** POST  
**Permessi:** Nessuno (pubblico per display)  
**Parametri:** Nessuno

**Risposta:**
```json
{
    "success": true,
    "data": [
        {
            "id": 42,
            "numero_ordine": "ORD-20260202-0042",
            "items": [
                {
                    "name": "Carbonara",
                    "quantity": 2
                }
            ],
            "totale": "25.00",
            "stato": "in_attesa",
            "note": "Senza pepe",
            "created_at": "2026-02-02 14:30:00"
        }
    ]
}
```

#### 8. risto_mark_order_ready
**Azione:** Marca ordine come pronto  
**Metodo:** POST  
**Permessi:** Nessuno (controllato da nonce)  
**Parametri:**
- `nonce`: Security nonce
- `order_id`: ID ordine

#### 9. risto_get_ready_orders
**Azione:** Recupera ordini pronti  
**Metodo:** POST  
**Permessi:** Nessuno (pubblico)  
**Parametri:** Nessuno

#### 10. risto_update_order_status
**Azione:** Aggiorna stato ordine  
**Metodo:** POST  
**Permessi:** Nessuno (controllato da nonce)  
**Parametri:**
- `nonce`: Security nonce
- `order_id`: ID ordine
- `status`: Nuovo stato

---

## 🎨 Architettura CSS

### Variabili CSS (Custom Properties)

```css
:root {
    --risto-primary: #1e3a8a;
    --risto-secondary: #06b6d4;
    --risto-accent: #10b981;
    --risto-gold: linear-gradient(135deg, #d4af37 0%, #f4e5b1 25%, #d4af37 50%, #f4e5b1 75%, #d4af37 100%);
    --risto-gold-solid: #d4af37;
    --risto-dark: #1f2937;
    --risto-light: #f3f4f6;
}
```

### Classi Principali

#### Layout
- `.risto-admin`: Container admin
- `.risto-dashboard`: Dashboard layout
- `.risto-stats`: Grid statistiche
- `.risto-list`: Container liste

#### Components
- `.dish-card`: Card piatto
- `.order-card`: Card ordine
- `.risto-item-card`: Card generica
- `.stat-card`: Card statistica

#### Controls
- `.qty-btn`: Bottone quantità
- `.qty-display`: Display quantità
- `.category-btn`: Bottone categoria
- `.order-btn`: Bottone azione ordine

#### Kitchen Board
- `.kitchen-header`: Header cucina
- `.kitchen-stats`: Statistiche cucina
- `.orders-container`: Container ordini
- `.voice-feedback`: Feedback vocale

#### Ready Board
- `.ready-board-header`: Header display
- `.ready-order-card`: Card ordine pronto

---

## 📱 JavaScript APIs

### Global Objects

#### ristoBase
```javascript
{
    ajaxurl: "https://example.com/wp-admin/admin-ajax.php",
    nonce: "abc123..."
}
```

#### ristoAdmin
```javascript
{
    ajaxurl: "https://example.com/wp-admin/admin-ajax.php",
    nonce: "def456..."
}
```

#### ristoKitchen
```javascript
{
    ajaxurl: "https://example.com/wp-admin/admin-ajax.php",
    nonce: "ghi789..."
}
```

### Browser APIs Utilizzate

#### Web Speech API
- **SpeechSynthesis**: Sintesi vocale per notifiche
- **SpeechRecognition**: Riconoscimento vocale comandi

```javascript
// Text-to-Speech
var utterance = new SpeechSynthesisUtterance("Nuovo ordine numero 42");
utterance.lang = 'it-IT';
window.speechSynthesis.speak(utterance);

// Speech Recognition
var recognition = new webkitSpeechRecognition();
recognition.lang = 'it-IT';
recognition.continuous = true;
recognition.onresult = function(event) {
    var transcript = event.results[0][0].transcript;
    // Process command
};
recognition.start();
```

#### Web Audio API
- **AudioContext**: Generazione suoni notifica

```javascript
var audioContext = new AudioContext();
var oscillator = audioContext.createOscillator();
oscillator.frequency.value = 800;
oscillator.connect(audioContext.destination);
oscillator.start();
```

---

## 🔒 Sicurezza

### Implementazioni di Sicurezza

#### 1. Nonce Verification
Tutti gli endpoint AJAX verificano nonce:
```php
check_ajax_referer('risto-admin-nonce', 'nonce');
```

#### 2. Capability Checks
Controllo permessi per operazioni admin:
```php
if (!current_user_can('manage_options')) {
    wp_send_json_error('Permessi insufficienti');
}
```

#### 3. Data Sanitization
Tutti gli input vengono sanitizzati:
```php
$nome = sanitize_text_field($_POST['nome']);
$email = sanitize_email($_POST['email']);
$descrizione = sanitize_textarea_field($_POST['descrizione']);
```

#### 4. Output Escaping
Tutti gli output sono escaped:
```php
echo esc_html($piatto->nome);
echo esc_url($piatto->immagine);
echo esc_attr($piatto->categoria);
```

#### 5. SQL Injection Prevention
Uso di prepared statements:
```php
$wpdb->prepare(
    "SELECT * FROM $table WHERE id = %d",
    $item_id
);
```

---

## 🎯 Performance

### Ottimizzazioni Implementate

#### 1. AJAX Polling Intelligente
- Kitchen Board: 5 secondi
- Ready Board: 3 secondi
- Solo quando pagina visibile

#### 2. CSS/JS Inline
- Riduce richieste HTTP
- Più veloce per singolo file plugin

#### 3. Lazy Loading
- Immagini caricate on-demand
- Skeleton screens durante loading

#### 4. Database Indexing
- Primary keys su tutte le tabelle
- Index su campi ricerca frequente

---

## 🔧 Hooks & Filters

### Actions Disponibili

```php
// Prima creazione ordine
do_action('risto_before_order_created', $order_data);

// Dopo creazione ordine
do_action('risto_after_order_created', $order_id, $order_data);

// Cambio stato ordine
do_action('risto_order_status_changed', $order_id, $old_status, $new_status);
```

### Filters Disponibili

```php
// Modifica totale ordine
$total = apply_filters('risto_order_total', $total, $items);

// Modifica items ordine
$items = apply_filters('risto_order_items', $items, $order_id);

// Modifica categorie piatti
$categories = apply_filters('risto_dish_categories', $categories);
```

---

## 📊 Metriche & Analytics

### Dati Tracciabili

- Numero ordini per giorno/settimana/mese
- Piatti più ordinati
- Tempi medi preparazione
- Orari di punta
- Revenue totale
- Tavoli più utilizzati

### Query Esempio

```sql
-- Piatti più ordinati
SELECT 
    JSON_EXTRACT(items, '$[*].name') as piatto,
    COUNT(*) as ordini
FROM wp_risto_comande
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY piatto
ORDER BY ordini DESC
LIMIT 10;

-- Revenue giornaliero
SELECT 
    DATE(created_at) as giorno,
    SUM(totale) as revenue
FROM wp_risto_comande
WHERE stato != 'annullato'
GROUP BY DATE(created_at)
ORDER BY giorno DESC;
```

---

## 🌐 Compatibilità Browser

### Supportati
- ✅ Chrome 90+ (Consigliato per voce)
- ✅ Edge 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Mobile Chrome/Safari

### Funzionalità Limitate
- ⚠️ Firefox: No riconoscimento vocale
- ⚠️ Safari iOS: Audio limitato senza interazione utente

---

## 📦 Deployment

### Checklist Pre-Produzione

- [ ] Test su ambiente staging
- [ ] Backup database
- [ ] Ottimizza immagini
- [ ] Configura caching
- [ ] Test su dispositivi reali
- [ ] Verifica HTTPS attivo
- [ ] Test permessi audio/microfono
- [ ] Configura backup automatici
- [ ] Documenta configurazioni custom
- [ ] Training staff

---

**Documento compilato da: Bededo Italy Development Team**  
**Ultima revisione: 2026-02-02**
