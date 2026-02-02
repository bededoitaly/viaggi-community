# Guida Rapida all'Installazione - Risto Base

## 🚀 Setup in 5 Minuti

### Passo 1: Attivazione Plugin

1. Accedi al pannello WordPress
2. Vai su **Plugin → Plugin Installati**
3. Cerca "Risto Base" e clicca **Attiva**
4. Cerca "Risto Kitchen Board" e clicca **Attiva**

✅ **Fatto!** Le tabelle del database e i form predefiniti sono stati creati automaticamente.

---

### Passo 2: Aggiungi i Primi Piatti

1. Nel menu laterale, clicca su **Risto Base → Piatti**
2. Clicca **Aggiungi**
3. Compila i campi:
   - Nome: es. "Spaghetti Carbonara"
   - Descrizione: es. "Pasta tradizionale romana"
   - Categoria: es. "Primi"
   - Prezzo: es. "12.50"
   - Carica un'immagine
4. Clicca **Salva**

Ripeti per tutti i tuoi piatti!

---

### Passo 3: Crea le Pagine

#### Pagina Menu (Pubblica)

1. Vai su **Pagine → Aggiungi Nuova**
2. Titolo: "Il Nostro Menu"
3. Nel contenuto, scrivi:
   ```
   [risto_menu_showcase]
   ```
4. Clicca **Pubblica**

#### Pagina Ordini (Pubblica)

1. **Pagine → Aggiungi Nuova**
2. Titolo: "Ordina Online"
3. Contenuto:
   ```
   [risto_order_interface]
   ```
4. **Pubblica**

#### Pagina Cucina (Privata - Solo Staff)

1. **Pagine → Aggiungi Nuova**
2. Titolo: "Tabellone Cucina"
3. Contenuto:
   ```
   [risto_kitchen_board]
   ```
4. Prima di pubblicare:
   - Clicca su **Visibilità** → **Protetto da password**
   - Inserisci una password (es. "cucina123")
5. **Pubblica**

#### Pagina Display Ordini Pronti (Pubblica ma Dedicata)

1. **Pagine → Aggiungi Nuova**
2. Titolo: "Ordini Pronti"
3. Contenuto:
   ```
   [risto_ready_board]
   ```
4. **Pubblica**

---

### Passo 4: Aggiungi al Menu di Navigazione

1. Vai su **Aspetto → Menu**
2. Aggiungi le pagine create al menu:
   - Il Nostro Menu
   - Ordina Online
3. Salva il menu

**Nota:** Non aggiungere le pagine "Cucina" e "Display" al menu pubblico.

---

### Passo 5: Configura Tavoli e Camerieri (Opzionale)

#### Aggiungi Tavoli

1. **Risto Base → Tavoli → Aggiungi**
2. Compila:
   - Numero: es. "1"
   - Posti: es. "4"
   - Stato: "libero"
3. Salva

#### Aggiungi Camerieri

1. **Risto Base → Camerieri → Aggiungi**
2. Compila:
   - Nome: es. "Mario"
   - Cognome: es. "Rossi"
   - Telefono, Email
3. Salva

---

## 🎯 Test del Sistema

### Test 1: Visualizza il Menu

1. Apri il tuo sito
2. Vai alla pagina "Il Nostro Menu"
3. Verifica che i piatti siano visualizzati
4. Prova i filtri per categoria
5. Prova i bottoni +/- per le quantità

### Test 2: Effettua un Ordine di Prova

1. Vai su "Ordina Online"
2. Clicca sui piatti e usa i bottoni + per aggiungere
3. Verifica che il carrello si aggiorni
4. Clicca "Invia Ordine"
5. Dovresti vedere un messaggio di conferma con numero ordine

### Test 3: Visualizza Ordine in Cucina

1. Apri la pagina "Tabellone Cucina" (inserisci password)
2. Dovresti vedere l'ordine appena creato
3. Sentirai una notifica audio
4. Sentirai la voce: "Nuovo ordine numero ..."

### Test 4: Marca Ordine come Pronto

**Metodo 1 - Manuale:**
1. Nella pagina cucina, clicca "In Preparazione"
2. Poi clicca "Pronto"

**Metodo 2 - Vocale:**
1. Clicca "Riconoscimento Vocale" in cucina
2. Di' ad alta voce: "Ordine numero [numero] pronto"
3. L'ordine viene marcato automaticamente

### Test 5: Verifica Display Ordini Pronti

1. Apri la pagina "Ordini Pronti"
2. Dovresti vedere l'ordine pronto lampeggiare
3. Sentirai la sintesi vocale: "Ordine numero X pronto per il ritiro"

---

## 📱 Setup Hardware Consigliato

### Per Cucina
- **Tablet o PC** con altoparlanti
- Browser: Chrome o Edge (per riconoscimento vocale)
- Microfono (per comandi vocali)
- Posizione: visibile a tutto lo staff

### Per Display Ritiro
- **Monitor/TV** grande (min 24")
- Browser: qualsiasi moderno
- Altoparlanti per sintesi vocale
- Posizione: visibile ai clienti

### Per Ordini Cliente
- Qualsiasi dispositivo (PC, tablet, smartphone)
- Responsive e ottimizzato per touch

---

## ⚙️ Configurazioni Avanzate

### Importa Piatti da CSV

1. Prepara file CSV:
   ```csv
   nome,descrizione,categoria,prezzo,disponibile
   Margherita,Pizza classica,Pizze,8.00,1
   Marinara,Pizza con aglio,Pizze,7.00,1
   ```

2. **Risto Base → Piatti → Importa CSV**
3. Seleziona file e carica

### Personalizza Form

1. **Risto Base → Form Builder**
2. Seleziona il form da modificare
3. Aggiungi/Rimuovi campi
4. Salva

### Backup Database

Importante! Fai backup regolari delle tabelle:
- `wp_risto_forms`
- `wp_risto_tavoli`
- `wp_risto_camerieri`
- `wp_risto_piatti`
- `wp_risto_comande`
- `wp_risto_clienti`

Usa plugin come:
- UpdraftPlus
- BackupBuddy
- WP Database Backup

---

## 🔧 Risoluzione Problemi Comuni

### Problema: Non sento l'audio

**Soluzione:**
1. Verifica volume browser/sistema
2. Verifica che il sito sia HTTPS
3. Dai permessi audio al browser
4. Ricarica la pagina

### Problema: Riconoscimento vocale non funziona

**Soluzione:**
1. Usa Chrome o Edge (non Firefox)
2. Verifica che il sito sia HTTPS
3. Dai permessi microfono
4. Lingua browser deve essere Italiano

### Problema: Ordini non si aggiornano

**Soluzione:**
1. Ricarica la pagina
2. Verifica connessione internet
3. Controlla Console browser (F12) per errori
4. Verifica che AJAX funzioni

### Problema: Immagini non si caricano

**Soluzione:**
1. Verifica dimensione file (max upload PHP)
2. Verifica permessi cartella uploads
3. Ottimizza immagini (consigliato < 500KB)

---

## 📞 Supporto

Se hai problemi:

1. Controlla la documentazione completa: `RISTO-README.md`
2. Vedi esempi pratici: `RISTO-EXAMPLES.md`
3. Apri issue su GitHub
4. Email: support@bededoitaly.com

---

## ✅ Checklist Installazione Completa

- [ ] Plugin attivati
- [ ] Piatti inseriti con immagini
- [ ] Tavoli configurati
- [ ] Camerieri aggiunti
- [ ] Pagina "Menu" creata e pubblicata
- [ ] Pagina "Ordina" creata e pubblicata
- [ ] Pagina "Cucina" creata e protetta
- [ ] Pagina "Display" creata e dedicata
- [ ] Menu navigazione configurato
- [ ] Test ordine effettuato
- [ ] Audio testato
- [ ] Riconoscimento vocale testato
- [ ] Backup configurato

---

## 🎉 Sei Pronto!

Il tuo sistema di gestione ristorante è ora operativo!

**Prossimi Passi:**
1. Personalizza i colori del tema se desiderato
2. Aggiungi tutti i tuoi piatti
3. Forma il personale sull'uso del sistema
4. Inizia a ricevere ordini!

**Buon Lavoro! 🍽️✨**
