# Esempi di Utilizzo - Risto Base

Questa guida fornisce esempi pratici di come utilizzare i shortcode del sistema Risto Base nelle tue pagine WordPress.

## 📄 Pagine Consigliate da Creare

### 1. Pagina "Il Nostro Menù"
Mostra il menù completo con vetrina visuale e possibilità di filtrare per categoria.

**Shortcode da inserire:**
```
[risto_menu_showcase]
```

**Risultato:**
- Griglia di piatti con immagini
- Filtri per categoria (Antipasti, Primi, Secondi, etc.)
- Controlli quantità con bottoni +/-
- Design premium con effetti hover

---

### 2. Pagina "Ordina Online"
Interfaccia completa per permettere ai clienti di effettuare ordini.

**Shortcode da inserire:**
```
[risto_order_interface]
```

**Risultato:**
- Menu navigazione per categorie
- Piatti organizzati per categoria
- Carrello con riepilogo in tempo reale
- Pulsante invio ordine
- Responsive per mobile

---

### 3. Pagina "Tabellone Cucina" (Area Riservata)
Per il personale di cucina - mostra ordini in tempo reale.

**Shortcode da inserire:**
```
[risto_kitchen_board]
```

**Caratteristiche:**
- Auto-refresh ogni 5 secondi
- Notifiche audio per nuovi ordini
- Sintesi vocale: "Nuovo ordine numero X"
- Pulsanti per cambiare stato ordine
- Riconoscimento vocale per comandi

**Nota:** Consigliato su pagina protetta da password o area riservata.

---

### 4. Pagina "Display Ordini Pronti"
Display per sala/banco ritiro - mostra ordini pronti lampeggianti.

**Shortcode da inserire:**
```
[risto_ready_board]
```

**Caratteristiche:**
- Auto-refresh ogni 3 secondi
- Animazioni lampeggianti
- Numeri ordine grandi e visibili
- Sintesi vocale automatica
- Design ottimizzato per monitor grandi

**Suggerimento:** Usa questa pagina su un tablet/monitor dedicato vicino al banco di ritiro.

---

### 5. Pagina "Gestione Tavoli"
Visualizza tutti i tavoli e il loro stato.

**Shortcode da inserire:**
```
[risto_list_tavoli]
```

---

### 6. Pagina "Elenco Piatti" (Admin)
Lista completa di tutti i piatti in formato tabellare.

**Shortcode da inserire:**
```
[risto_list_piatti]
```

---

### 7. Pagina "Storico Ordini"
Visualizza tutte le comande registrate.

**Shortcode da inserire:**
```
[risto_list_comande]
```

---

## 🎯 Esempi Avanzati

### Menù per Categoria Specifica
Mostra solo i dessert:

```
[risto_menu_showcase categoria="Dessert"]
```

Mostra solo i primi piatti:

```
[risto_menu_showcase categoria="Primi"]
```

---

## 🏗️ Struttura Pagine Consigliata

### Homepage
```html
<h1>Benvenuti al Ristorante</h1>
<p>Scopri il nostro menù e ordina online!</p>

<h2>I Nostri Piatti</h2>
[risto_menu_showcase]

<a href="/ordina-online">Ordina Ora →</a>
```

### Pagina Ordini
```html
<h1>Effettua un Ordine</h1>
<p>Seleziona i piatti che desideri e invia l'ordine</p>

[risto_order_interface]
```

### Area Cucina
```html
<h1>Tabellone Cucina</h1>
[risto_kitchen_board]
```

### Display Sala
```html
[risto_ready_board]
```

---

## 🎨 Personalizzazione CSS

Puoi personalizzare l'aspetto aggiungendo CSS personalizzato:

```css
/* Personalizza colore primario */
.dish-card {
    border: 2px solid #your-color;
}

/* Personalizza dimensione testo */
.dish-name {
    font-size: 24px;
}

/* Nasconde categoria specifica */
.dish-card[data-category="Bevande"] {
    display: none;
}
```

Aggiungi questo CSS in:
- **Aspetto → Personalizza → CSS Aggiuntivo** (WordPress)
- Oppure nel tuo tema child

---

## 📱 Template per Mobile

Per una migliore esperienza mobile, considera di creare pagine separate:

### Menu Mobile Semplificato
```
[risto_menu_showcase]
```
Con CSS:
```css
@media (max-width: 768px) {
    .dishes-grid {
        grid-template-columns: 1fr !important;
    }
}
```

---

## 🔐 Proteggere Pagine Riservate

Per le pagine destinate solo allo staff:

1. **Proteggi con Password:**
   - Modifica pagina → Visibilità → Protetto da password

2. **Usa Plugin Membership:**
   - Installa "Members" o simili
   - Limita accesso a ruoli specifici

3. **Shortcode Condizionale:**
```php
<?php if (current_user_can('manage_options')): ?>
    [risto_kitchen_board]
<?php else: ?>
    <p>Accesso non autorizzato</p>
<?php endif; ?>
```

---

## 🎭 Esempi di Widget

Puoi anche usare gli shortcode nei widget della sidebar:

**Widget "Menù del Giorno":**
```
<h3>Speciale del Giorno</h3>
[risto_menu_showcase categoria="Speciali"]
```

**Widget "Ordina Veloce":**
```
<a href="/ordina-online" class="btn-ordina">
    🍕 Ordina Online
</a>
```

---

## 🚀 Setup Rapido - Checklist

- [ ] Crea pagina "Menu" con `[risto_menu_showcase]`
- [ ] Crea pagina "Ordina" con `[risto_order_interface]`
- [ ] Crea pagina "Cucina" con `[risto_kitchen_board]` (protetta)
- [ ] Crea pagina "Display" con `[risto_ready_board]`
- [ ] Aggiungi link menu navigazione
- [ ] Testa su mobile
- [ ] Configura permessi audio/microfono

---

## 💡 Tips & Tricks

### Performance
- Non usare troppi shortcode nella stessa pagina
- Ottimizza le immagini dei piatti (max 500KB)
- Usa caching per pagine pubbliche

### UX
- Usa pagine dedicate per ogni funzionalità
- Mantieni menu navigazione semplice
- Testa su dispositivi reali

### Accessibilità
- Aggiungi testo alternativo alle immagini
- Usa heading gerarchici (H1, H2, H3)
- Contrasto colori sufficiente

---

## 📞 Esempi Completi

### Esempio 1: Ristorante Pizzeria

**Pagina Homepage:**
```html
<h1>Pizzeria Da Mario</h1>
<p>Le migliori pizze della città!</p>
[risto_menu_showcase categoria="Pizze"]
<a href="/ordina">Ordina Subito</a>
```

**Pagina Menu Completo:**
```html
<h1>Il Nostro Menu</h1>
[risto_menu_showcase]
```

**Pagina Ordini:**
```html
[risto_order_interface]
```

### Esempio 2: Ristorante Fine Dining

**Pagina Menu Elegante:**
```html
<section class="menu-section">
    <h2>Antipasti</h2>
    [risto_menu_showcase categoria="Antipasti"]
</section>

<section class="menu-section">
    <h2>Primi Piatti</h2>
    [risto_menu_showcase categoria="Primi"]
</section>

<section class="menu-section">
    <h2>Secondi</h2>
    [risto_menu_showcase categoria="Secondi"]
</section>
```

---

**Buon lavoro con Risto Base! 🍽️✨**
