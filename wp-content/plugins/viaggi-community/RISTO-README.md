# Risto Base - Sistema Completo di Gestione Ristorante per WordPress

Un sistema completo e moderno per la gestione di ristoranti con WordPress, composto da due plugin principali che offrono tutte le funzionalità necessarie per gestire menù digitali, ordini, tavoli, personale e molto altro.

## 🎯 Caratteristiche Principali

### Risto Base (Plugin Principale)
- ✅ **Menù Digitale Completo** con immagini, descrizioni e categorizzazione
- ✅ **Form Builder Visuale Moderno** con drag & drop, anteprima e tutti i tipi di campo
- ✅ **5 Archivi Integrati**: Tavoli, Camerieri, Piatti, Comande, Clienti
- ✅ **Importazione CSV** per tutti gli archivi
- ✅ **Interfaccia Ordini Frontend** intuitiva e responsive
- ✅ **Vetrina Piatti** con controlli quantità (+/-)
- ✅ **Gestione Ordini** completa con stati e tracciamento
- ✅ **Design Premium** con colori Blu, Turchese, Verde e finiture Oro realistico
- ✅ **100% Responsive** e adaptive per tutti i dispositivi

### Risto Kitchen Board (Plugin Satellite)
- ✅ **Tabellone Cucina Real-Time** con aggiornamenti automatici
- ✅ **Notifiche Audio e Vocali** per nuovi ordini
- ✅ **Riconoscimento Vocale** per confermare ordini pronti
- ✅ **Display Board Lampeggiante** per ordini pronti al ritiro
- ✅ **Sintesi Vocale** in italiano con messaggi personalizzati
- ✅ **Gestione Stati Ordini** (In Attesa, In Preparazione, Pronto)
- ✅ **Statistiche Real-Time** degli ordini

## 📦 Installazione

### Requisiti
- WordPress 5.0 o superiore
- PHP 7.4 o superiore
- MySQL 5.6 o superiore
- Browser moderno con supporto Web Speech API per funzionalità vocali

### Passaggi di Installazione

1. **Carica i file del plugin:**
   - Copia `risto-base.php` in `wp-content/plugins/viaggi-community/`
   - Copia `risto-kitchen-board.php` in `wp-content/plugins/viaggi-community/`

2. **Attiva i plugin:**
   - Vai su WordPress Admin → Plugin
   - Attiva "Risto Base - Restaurant Management System"
   - Attiva "Risto Kitchen Board - Tabellone Cucina"

3. **Configurazione iniziale:**
   - All'attivazione vengono create automaticamente le tabelle del database
   - Vengono generati i form predefiniti per tutti e 5 gli archivi
   - Il sistema è pronto all'uso!

## 🚀 Utilizzo

### Shortcode Disponibili

#### Risto Base

**[risto_form_builder]**
- Interfaccia di gestione form builder
- Permesso richiesto: Administrator
- Esempio: `[risto_form_builder]`

**[risto_list_tavoli]**
- Visualizza l'elenco dei tavoli
- Esempio: `[risto_list_tavoli]`

**[risto_list_camerieri]**
- Visualizza l'elenco dei camerieri
- Esempio: `[risto_list_camerieri]`

**[risto_list_piatti]**
- Visualizza l'elenco dei piatti
- Esempio: `[risto_list_piatti]`

**[risto_list_comande]**
- Visualizza l'elenco delle comande
- Esempio: `[risto_list_comande]`

**[risto_list_clienti]**
- Visualizza l'elenco dei clienti
- Esempio: `[risto_list_clienti]`

**[risto_menu_showcase]**
- Vetrina menù con filtri per categoria
- Controlli quantità con bottoni +/-
- Parametri opzionali: `categoria="Primi"`
- Esempio: `[risto_menu_showcase]` o `[risto_menu_showcase categoria="Primi"]`

**[risto_order_interface]**
- Interfaccia completa per effettuare ordini
- Menu a tendina per categorie
- Riepilogo ordine e invio
- Esempio: `[risto_order_interface]`

#### Risto Kitchen Board

**[risto_kitchen_board]**
- Tabellone cucina con ordini in tempo reale
- Notifiche audio/vocali automatiche
- Riconoscimento vocale integrato
- Gestione stati ordini
- Esempio: `[risto_kitchen_board]`

**[risto_ready_board]**
- Display board per ordini pronti al ritiro
- Animazioni lampeggianti
- Sintesi vocale automatica
- Esempio: `[risto_ready_board]`

### Area Amministrativa

Accedi al menu "Risto Base" nel pannello WordPress per:

1. **Dashboard**: Statistiche e panoramica generale
2. **Form Builder**: Crea e gestisci form personalizzati
3. **Tavoli**: Gestisci tavoli del ristorante
4. **Camerieri**: Gestisci il personale
5. **Piatti**: Gestisci il menù con immagini e prezzi
6. **Comande**: Visualizza e gestisci ordini
7. **Clienti**: Database clienti

### Importazione CSV

Per ogni archivio è disponibile la funzione di importazione CSV:

1. Prepara un file CSV con le colonne corrispondenti ai campi
2. Clicca su "Importa CSV" nella pagina dell'archivio
3. Seleziona il file e conferma
4. I dati verranno importati automaticamente

Formato CSV esempio per piatti:
```csv
nome,descrizione,categoria,prezzo,disponibile
Spaghetti Carbonara,Pasta con uova e guanciale,Primi,12.50,1
Tiramisù,Dolce al caffè,Dessert,6.00,1
```

## 🎨 Personalizzazione Colori

Il sistema utilizza uno schema colori premium personalizzabile tramite CSS:

```css
:root {
    --risto-primary: #1e3a8a;      /* Blu principale */
    --risto-secondary: #06b6d4;     /* Turchese */
    --risto-accent: #10b981;        /* Verde */
    --risto-gold: #d4af37;          /* Oro */
}
```

## 🔊 Funzionalità Vocali

### Sintesi Vocale
Il sistema include sintesi vocale in italiano per:
- Annuncio nuovi ordini: "Nuovo ordine numero ORD-20260202-0001"
- Conferma ordini pronti: "Ordine numero ORD-20260202-0001 pronto per il ritiro"

### Riconoscimento Vocale
Per utilizzare il riconoscimento vocale:

1. Clicca su "Riconoscimento Vocale" nel tabellone cucina
2. Quando un ordine è pronto, pronuncia: **"Ordine numero [numero] pronto"**
3. Il sistema segnerà automaticamente l'ordine come pronto
4. Verrà inviata la notifica al display board

**Nota**: Richiede browser con supporto Web Speech API (Chrome, Edge)

## 📱 Responsive Design

Tutti i componenti sono completamente responsive:

- **Desktop**: Layout a griglia multi-colonna
- **Tablet**: Layout adattivo a 2 colonne
- **Mobile**: Layout a singola colonna con controlli ottimizzati per touch

## 🔧 Struttura Database

### Tabelle Create

1. **wp_risto_forms**: Definizioni form personalizzati
2. **wp_risto_tavoli**: Tavoli del ristorante
3. **wp_risto_camerieri**: Personale/camerieri
4. **wp_risto_piatti**: Menù e piatti
5. **wp_risto_comande**: Ordini e comande
6. **wp_risto_clienti**: Database clienti

## 🌟 Workflow Consigliato

### Setup Iniziale
1. Aggiungi i tavoli del ristorante
2. Registra i camerieri
3. Inserisci i piatti del menù con immagini e prezzi
4. (Opzionale) Importa clienti da CSV

### Gestione Quotidiana
1. **Frontend**: Cliente usa `[risto_order_interface]` per ordinare
2. **Cucina**: Chef visualizza ordini su `[risto_kitchen_board]`
3. **Preparazione**: Chef marca ordini "In Preparazione"
4. **Pronto**: Chef marca ordini "Pronto" (vocalmente o manualmente)
5. **Display**: `[risto_ready_board]` mostra ordini pronti lampeggianti
6. **Consegna**: Cameriere consegna ordine al cliente

## 🎯 Best Practices

### Performance
- Gli aggiornamenti real-time usano AJAX polling (5 secondi per cucina, 3 per display)
- Le immagini dovrebbero essere ottimizzate (max 500KB)
- Usa cache WordPress per migliori prestazioni

### Sicurezza
- Tutti i form sono protetti con nonce verification
- Input sanitizzati e validati
- SQL injection prevention con prepared statements
- XSS protection con escaping

### Accessibilità
- Interfaccia keyboard-friendly
- ARIA labels per screen readers
- Contrasti colori conformi WCAG 2.1
- Notifiche audio/visive alternative

## 🐛 Troubleshooting

### L'audio non funziona
- Verifica che il browser supporti Web Audio API
- Controlla le impostazioni audio del browser
- Assicurati che il sito sia servito via HTTPS

### Il riconoscimento vocale non funziona
- Usa Chrome o Edge (Firefox non supportato)
- Concedi i permessi microfono quando richiesto
- Assicurati che il sito sia servito via HTTPS
- Verifica la lingua del browser (deve essere IT)

### Gli ordini non si aggiornano in real-time
- Verifica che AJAX funzioni correttamente
- Controlla la console browser per errori JavaScript
- Verifica che wp-admin/admin-ajax.php sia accessibile

## 📝 Note di Sviluppo

### Architettura Plugin
- **Single File Architecture**: Tutto il codice è contenuto in un unico file PHP per plugin
- **Inline CSS/JS**: Stili e script incorporati per facilità di distribuzione
- **OOP Pattern**: Uso di classi singleton per gestione istanze
- **WordPress Coding Standards**: Codice conforme agli standard WordPress

### Estensibilità
Il sistema è progettato per essere facilmente estendibile:

```php
// Hook per personalizzare ordini
add_action('risto_before_order_created', function($order_data) {
    // Tua logica personalizzata
});

add_filter('risto_order_total', function($total, $items) {
    // Aggiungi tasse, sconti, etc.
    return $total;
}, 10, 2);
```

## 📄 Licenza

GPL-2.0+ - Puoi usare, modificare e distribuire liberamente questo software secondo i termini della GNU General Public License.

## 👨‍💻 Autore

**Bededo Italy**
- Website: https://bededoitaly.com
- GitHub: https://github.com/bededoitaly

## 🆘 Supporto

Per supporto, segnalazione bug o richieste di funzionalità:
- Apri una issue su GitHub
- Contatta: support@bededoitaly.com

## 🔄 Changelog

### Version 1.0.0 (2026-02-02)
- ✨ Release iniziale
- ✅ Sistema completo di gestione ristorante
- ✅ Form builder visuale con tutti i tipi di campo
- ✅ 5 archivi integrati
- ✅ Interfaccia ordini frontend
- ✅ Tabellone cucina con real-time updates
- ✅ Notifiche audio e sintesi vocale
- ✅ Riconoscimento vocale per ordini
- ✅ Design premium responsive
- ✅ Importazione CSV

---

**Grazie per aver scelto Risto Base! 🍽️**
