# 🎨 Anteprima Visuale - Risto Base

## Design System - Schema Colori Premium

### Palette Principale
```
🔵 Blu Principale    #1e3a8a  ████████
🌊 Turchese         #06b6d4  ████████
🟢 Verde Accento    #10b981  ████████
🌟 Oro Premium      #d4af37  ████████
⚫ Dark             #1f2937  ████████
⚪ Light            #f3f4f6  ████████
```

---

## 📱 Layout Interfacce

### 1. Menu Showcase (Frontend)

```
╔══════════════════════════════════════════════════════════╗
║              🍽️ IL NOSTRO MENÙ                          ║
╠══════════════════════════════════════════════════════════╣
║                                                          ║
║  [Tutti] [Antipasti] [Primi] [Secondi] [Dessert]       ║
║                                                          ║
╠═══════════════╦═══════════════╦═══════════════╗         ║
║   ┌─────────┐ ║   ┌─────────┐ ║   ┌─────────┐ ║         ║
║   │ Immagine│ ║   │ Immagine│ ║   │ Immagine│ ║         ║
║   └─────────┘ ║   └─────────┘ ║   └─────────┘ ║         ║
║ Carbonara     ║ Amatriciana   ║ Cacio e Pepe  ║         ║
║ Pasta romana  ║ Sugo piccante ║ Tradizionale  ║         ║
║ € 12.50       ║ € 11.00       ║ € 10.50       ║         ║
║               ║               ║               ║         ║
║  [-]  0  [+]  ║  [-]  0  [+]  ║  [-]  0  [+]  ║         ║
╠═══════════════╩═══════════════╩═══════════════╣         ║
╚══════════════════════════════════════════════════════════╝
```

### 2. Order Interface (Frontend)

```
╔══════════════════════════════════════════════════════════╗
║  🍕 EFFETTUA UN ORDINE          🛒 Carrello (3)         ║
╠══════════════════════════════════════════════════════════╣
║                                                          ║
║  [Antipasti] [Primi] [Secondi] [Contorni] [Dessert]    ║
║                                                          ║
╠══════════════════════════════════════════════════════════╣
║                                                          ║
║  ▼ PRIMI PIATTI                                         ║
║  ┌────────────────────────────────────────────┐         ║
║  │ [Img]  Spaghetti Carbonara    € 12.50     │         ║
║  │                            [-] 2 [+]       │         ║
║  ├────────────────────────────────────────────┤         ║
║  │ [Img]  Pasta Amatriciana      € 11.00     │         ║
║  │                            [-] 1 [+]       │         ║
║  └────────────────────────────────────────────┘         ║
║                                                          ║
╠══════════════════════════════════════════════════════════╣
║  RIEPILOGO ORDINE:                                      ║
║  • Carbonara x2 = € 25.00                               ║
║  • Amatriciana x1 = € 11.00                             ║
║  ─────────────────────────────                          ║
║  TOTALE: € 36.00                                        ║
║                                                          ║
║         [ 📤 INVIA ORDINE ]                             ║
╚══════════════════════════════════════════════════════════╝
```

### 3. Kitchen Board (Tabellone Cucina)

```
╔══════════════════════════════════════════════════════════╗
║   🍳 TABELLONE CUCINA - ORDINI IN ARRIVO                ║
║                                                          ║
║  [🔊 Audio: ON] [🎤 Voce: ON] [🎙️ Riconoscimento]      ║
╠══════════════════════════════════════════════════════════╣
║                                                          ║
║  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐     ║
║  │ In Attesa   │  │In Preparaz. │  │   Pronti    │     ║
║  │     8       │  │     3       │  │     2       │     ║
║  └─────────────┘  └─────────────┘  └─────────────┘     ║
║                                                          ║
╠═══════════════╦═══════════════╦═══════════════╗         ║
║ ┌───────────┐ ║ ┌───────────┐ ║ ┌───────────┐ ║         ║
║ │ 🔴 NUOVO  │ ║ │ 🟡 PREP.  │ ║ │ 🟢 PRONTO │ ║         ║
║ ├───────────┤ ║ ├───────────┤ ║ ├───────────┤ ║         ║
║ │ORD-001    │ ║ │ORD-002    │ ║ │ORD-003    │ ║         ║
║ │5 min fa   │ ║ │12 min fa  │ ║ │3 min fa   │ ║         ║
║ ├───────────┤ ║ ├───────────┤ ║ ├───────────┤ ║         ║
║ │Carbonara 2│ ║ │Margherita │ ║ │Tiramisù   │ ║         ║
║ │Cacio 1    │ ║ │Diavola 1  │ ║ │Panna C. 2 │ ║         ║
║ ├───────────┤ ║ ├───────────┤ ║ └───────────┘ ║         ║
║ │📝 Senza   │ ║ │💬 Ben     │ ║               ║         ║
║ │  pepe     │ ║ │  cotta    │ ║  ✅ Pronto  ║         ║
║ ├───────────┤ ║ ├───────────┤ ║               ║         ║
║ │[In Prep.] │ ║ │[Pronto]   │ ║               ║         ║
║ └───────────┘ ║ └───────────┘ ║               ║         ║
╚═══════════════╩═══════════════╩═══════════════╝         ║
```

### 4. Ready Board (Display Ordini Pronti)

```
╔══════════════════════════════════════════════════════════╗
║                                                          ║
║           📢 ORDINI PRONTI PER IL RITIRO                ║
║                                                          ║
╠══════════════════════════════════════════════════════════╣
║                                                          ║
║   ╔════════════════════════════════════════╗            ║
║   ║  ⚡⚡⚡ LAMPEGGIANTE ⚡⚡⚡           ║            ║
║   ║                                        ║            ║
║   ║      🌟 ORD-20260202-0042 🌟          ║            ║
║   ║                                        ║            ║
║   ║    PRONTO PER IL RITIRO               ║            ║
║   ║                                        ║            ║
║   ╚════════════════════════════════════════╝            ║
║                                                          ║
║   ╔════════════════════════════════════════╗            ║
║   ║                                        ║            ║
║   ║      🌟 ORD-20260202-0041 🌟          ║            ║
║   ║                                        ║            ║
║   ║    PRONTO PER IL RITIRO               ║            ║
║   ║                                        ║            ║
║   ╚════════════════════════════════════════╝            ║
║                                                          ║
╚══════════════════════════════════════════════════════════╝

🔊 "Ordine numero quarantadue pronto per il ritiro"
```

### 5. Admin Dashboard

```
╔══════════════════════════════════════════════════════════╗
║  RISTO BASE - Dashboard                                 ║
╠══════════════════════════════════════════════════════════╣
║                                                          ║
║  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐  ║
║  │ Tavoli   │ │Camerieri │ │  Piatti  │ │ Comande  │  ║
║  │   12     │ │    8     │ │    45    │ │   127    │  ║
║  └──────────┘ └──────────┘ └──────────┘ └──────────┘  ║
║                                         ┌──────────┐    ║
║                                         │ Clienti  │    ║
║                                         │   234    │    ║
║                                         └──────────┘    ║
║                                                          ║
╠══════════════════════════════════════════════════════════╣
║  SHORTCODES DISPONIBILI:                                ║
║  • [risto_menu_showcase] - Vetrina Menù                 ║
║  • [risto_order_interface] - Interfaccia Ordini         ║
║  • [risto_kitchen_board] - Tabellone Cucina             ║
║  • [risto_ready_board] - Display Ordini Pronti          ║
║  • [risto_list_piatti] - Elenco Piatti                  ║
║  • [risto_list_tavoli] - Elenco Tavoli                  ║
║  • [risto_list_camerieri] - Elenco Camerieri            ║
║  • [risto_list_comande] - Elenco Comande                ║
║  • [risto_list_clienti] - Elenco Clienti                ║
╚══════════════════════════════════════════════════════════╝
```

### 6. Form Builder Interface

```
╔══════════════════════════════════════════════════════════╗
║  FORM BUILDER - Crea Form Personalizzati               ║
╠══════════════════════════════════════════════════════════╣
║                                                          ║
║  Nome Form: [Form Piatti                          ]     ║
║  Archivio:  [Piatti ▼]                                  ║
║                                                          ║
║  ┌────────────────────────────────────────────────┐     ║
║  │ CAMPI DEL FORM:                                │     ║
║  │                                                │     ║
║  │ 1. [Text     ▼] Nome Piatto        [📝][❌]   │     ║
║  │ 2. [Textarea ▼] Descrizione        [📝][❌]   │     ║
║  │ 3. [Select   ▼] Categoria          [📝][❌]   │     ║
║  │ 4. [Number   ▼] Prezzo             [📝][❌]   │     ║
║  │ 5. [File     ▼] Immagine           [📝][❌]   │     ║
║  │ 6. [Gallery  ▼] Galleria           [📝][❌]   │     ║
║  │ 7. [Checkbox ▼] Disponibile        [📝][❌]   │     ║
║  │                                                │     ║
║  │           [+ Aggiungi Campo]                   │     ║
║  └────────────────────────────────────────────────┘     ║
║                                                          ║
║  [💾 Salva Form]  [❌ Annulla]                          ║
╚══════════════════════════════════════════════════════════╝
```

---

## 🎬 Flusso di Lavoro Animato

### Scenario: Nuovo Ordine

```
1. 👤 CLIENTE                    2. 🍳 CUCINA                 3. 📢 DISPLAY
   ↓                                ↓                           ↓
┌─────────────┐              ┌─────────────┐            ┌─────────────┐
│   Ordina    │              │  Riceve     │            │   Attesa    │
│   sul sito  │─────────────>│  notifica   │            │             │
│             │   AJAX       │  🔔 BEEP!   │            │             │
└─────────────┘              │  🗣️ "Nuovo │            │             │
                              │   ordine!"  │            │             │
                              └─────────────┘            │             │
                                     ↓                   │             │
                              ┌─────────────┐            │             │
                              │  In Prep.   │            │             │
                              │  (click)    │            │             │
                              └─────────────┘            │             │
                                     ↓                   │             │
                              ┌─────────────┐            │             │
                              │   Pronto    │───────────>│ MOSTRA      │
                              │  (click o   │   AJAX     │ ORDINE      │
                              │   vocale)   │            │ ⚡BLINK⚡   │
                              └─────────────┘            │ 🗣️ "Pronto"│
                                                          └─────────────┘
```

---

## 🎯 Elementi Grafici Premium

### Bottoni Quantità
```
┌─────────────────────────┐
│    [-]    5    [+]      │  ← Grandi, circolari
│                         │     Gradiente turchese-verde
│   (50px)  (24px) (50px) │     Effetto hover: scale(1.1)
└─────────────────────────┘
```

### Card Piatto
```
┌───────────────────────────┐
│  ┌─────────────────────┐  │
│  │                     │  │ ← Immagine 200px
│  │   IMMAGINE PIATTO   │  │   Border-radius: 15px
│  │                     │  │   Hover: scale(1.1)
│  └─────────────────────┘  │
│                           │
│  Spaghetti Carbonara      │ ← Titolo blu
│  ─────────────────────    │
│  Pasta tradizionale       │ ← Descrizione grigia
│  romana con uova e...     │
│                           │
│      💰 € 12.50           │ ← Prezzo oro gradient
│                           │
│    [-]   0   [+]          │ ← Controlli quantità
└───────────────────────────┘
   ↑
   Bordo top oro 4px
   Box-shadow premium
   Hover: translateY(-10px)
```

### Ordine Card Cucina
```
┌─────────────────────────────┐
│ 🔴 IN ATTESA               │ ← Barra status colorata
├─────────────────────────────┤
│ ORD-20260202-0042  5 min fa│ ← Numero + tempo
├─────────────────────────────┤
│ ┌─────────────────────────┐ │
│ │ • Carbonara      x2     │ │ ← Items con badge
│ │ • Tiramisù       x1     │ │   quantità
│ └─────────────────────────┘ │
├─────────────────────────────┤
│ 📝 Note: Senza pepe        │ ← Note evidenziate
├─────────────────────────────┤
│  [In Preparazione]         │ ← Pulsante azione
└─────────────────────────────┘
   ↑
   Animation: pulse quando nuovo
   Shadow: 0 6px 16px rgba(0,0,0,0.2)
```

---

## 📐 Dimensioni Responsive

### Desktop (>1200px)
```
Menu Grid:     4 colonne
Orders Grid:   3 colonne
Kitchen Board: 3 colonne
Font Size:     100%
```

### Tablet (768px - 1200px)
```
Menu Grid:     2 colonne
Orders Grid:   2 colonne
Kitchen Board: 2 colonne
Font Size:     95%
```

### Mobile (<768px)
```
Menu Grid:     1 colonna
Orders Grid:   1 colonna
Kitchen Board: 1 colonna
Font Size:     90%
Buttons:       Più grandi (touch-friendly)
```

---

## ✨ Animazioni & Effetti

### Hover Effects
- Cards: `translateY(-10px)` + shadow increase
- Buttons: `scale(1.05)` + shadow glow
- Images: `scale(1.1)` con overflow hidden

### Loading States
- Skeleton screens per liste
- Spinner per operazioni AJAX
- Progress bar per upload

### Notifiche
- Slide in from right per feedback vocale
- Fade in/out per messaggi
- Pulse per nuovi ordini
- Blink per ordini pronti

### Transizioni
- Tutte: `transition: all 0.3s ease`
- Smooth scroll per navigazione
- Fade cross per cambio categoria

---

## 🎨 Tipografia

```
Famiglia:    -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto
Heading H1:  36px / Bold / Primary Color
Heading H2:  28px / Bold / Primary Color
Heading H3:  20px / SemiBold / Primary Color
Body:        16px / Regular / Dark Gray
Small:       14px / Regular / Medium Gray
Price:       24px / Bold / Gold Gradient
```

---

**Questo design è stato creato per essere moderno, intuitivo e premium! 🌟**
