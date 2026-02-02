# Risto Base - Restaurant Management System

## Plugin Overview
A comprehensive WordPress restaurant management plugin created at `wp-content/plugins/risto-base.php`

**Version:** 1.0.0  
**File Size:** 52KB  
**Lines of Code:** 1072

## Features Implemented

### 1. Database Tables (Created on Activation)
- **risto_forms** - Stores form definitions for all archives
- **risto_records** - Universal storage for all archive records
- **risto_orders** - Stores customer orders from frontend

### 2. Default Forms for 5 Archives

#### Tavoli (Tables)
- numero_tavolo (text)
- posti (number)
- zona (text)
- note (textarea)

#### Camerieri (Waiters)
- nome (text)
- cognome (text)
- email (email)
- telefono (text)
- foto (file)

#### Piatti (Dishes)
- nome (text)
- descrizione (textarea)
- categoria (text)
- prezzo (number with step 0.01)
- immagine (file)
- galleria (text)
- vegetariano (checkbox)
- vegano (checkbox)
- senza_glutine (checkbox)

#### Comande (Orders)
- tavolo (text)
- cameriere (text)
- stato (select: pending, in_progress, completed, cancelled)
- note (textarea)

#### Clienti (Customers)
- nome (text)
- cognome (text)
- email (email)
- telefono (text)
- indirizzo (textarea)
- note (textarea)

### 3. Admin Menu Pages
- Dashboard - Overview with cards for each section
- Tavoli - Manage restaurant tables
- Camerieri - Manage waitstaff
- Piatti - Manage dishes and menu items
- Comande - Manage orders
- Clienti - Manage customers
- Ordini - View frontend orders

Each admin page includes:
- Add/Edit/Delete functionality
- Dynamic form rendering based on field definitions
- Data table display
- Inline editing

### 4. Shortcodes

#### List Shortcodes
- `[risto_lista_tavoli]` - Display tables list
- `[risto_lista_camerieri]` - Display waiters list
- `[risto_lista_piatti]` - Display dishes list
- `[risto_lista_comande]` - Display orders list
- `[risto_lista_clienti]` - Display customers list

#### Interactive Shortcodes
- `[risto_menu_vetrina]` - Menu showcase with:
  - Category filtering
  - +/- quantity buttons
  - Dietary badges (vegetarian, vegan, gluten-free)
  - Responsive design

- `[risto_ordini_frontend]` - Complete ordering interface with:
  - Category tabs navigation
  - Large +/- buttons for quantities
  - Real-time order total calculation
  - Table number input
  - Notes field
  - AJAX order submission

### 5. AJAX Handlers
- **risto_save_form** - Save form definitions (admin only)
- **risto_save_record** - Save archive records (admin only)
- **risto_delete_record** - Delete records (admin only)
- **risto_get_records** - Retrieve records (admin only)
- **risto_save_order** - Save frontend orders (public)
- **risto_import_csv** - Import CSV data (admin only)

### 6. Premium Styling

#### Color Palette
- **Primary Blue:** #1a5490
- **Turquoise:** #17a2b8
- **Success Green:** #28a745
- **Gold Gradient:** #ffd700 to #ffed4e (accents)

#### Design Features
- Gradient backgrounds
- Card-based layouts with hover effects
- Rounded corners and shadows
- Responsive grid system
- Mobile-optimized buttons (large touch targets)
- Smooth transitions and animations
- Modern badges for dietary information

#### Responsive Design
- Grid adapts to screen size
- Mobile-first approach
- Large buttons for touch interfaces
- Horizontal scrolling tabs on mobile

### 7. JavaScript Functionality

#### Category Filtering
- Dynamic show/hide based on category selection
- Active state management
- "All" option to show everything

#### Quantity Controls
- Increment/decrement buttons
- Real-time display updates
- Minimum value of 0
- Integration with order total

#### Order Management
- Tab switching for categories
- Order total calculation
- Form validation
- AJAX submission
- Success/error messaging
- Form reset after submission

#### Admin Interface
- Form submission via AJAX
- Record deletion with confirmation
- Dynamic form field rendering
- Real-time data updates

## Technical Implementation

### Architecture
- Singleton pattern for plugin instance
- Object-oriented approach
- WordPress best practices
- Proper sanitization and escaping
- Nonce verification for security

### Database Design
- Flexible form/record structure
- JSON storage for dynamic fields
- Proper indexing
- Timestamp tracking

### Security Features
- ABSPATH check
- Capability checks for admin functions
- Nonce verification on AJAX calls
- Input sanitization
- Output escaping
- SQL injection protection via prepared statements

## Usage

### Activation
1. Place file in `wp-content/plugins/`
2. Activate via WordPress admin
3. Database tables created automatically
4. Default forms populated

### Admin Usage
1. Navigate to "Risto Base" in admin menu
2. Use submenu items to manage each archive
3. Add/Edit/Delete records using forms
4. View orders from frontend

### Frontend Usage
1. Add shortcodes to pages/posts
2. Use `[risto_menu_vetrina]` for menu display
3. Use `[risto_ordini_frontend]` for order taking
4. Orders saved to database for admin review

## File Structure
Single file plugin (risto-base.php) containing:
- Class definition
- Database schema
- Admin interface
- Shortcode handlers
- AJAX handlers
- Inline CSS (premium styling)
- Inline JavaScript (all functionality)

## Production Ready Features
✅ Complete functionality
✅ Security hardened
✅ Responsive design
✅ Browser compatible
✅ WordPress standards compliant
✅ No external dependencies
✅ Inline styles and scripts
✅ Error handling
✅ Data validation
✅ Professional UI/UX
