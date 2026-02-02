# Risto Kitchen Board - Real-Time Kitchen Display

## Overview
A premium WordPress plugin that provides a real-time kitchen display system with voice control and audio notifications for restaurant orders. Seamlessly integrates with the Risto Base plugin.

## Features

### 🎯 Core Functionality
- **Real-time order display** with 5-second auto-refresh
- **Large, readable interface** optimized for kitchen viewing
- **Color-coded order status**: Green (new), Yellow (preparing), Red (ready)
- **Order details** including items, quantities, and special notes

### 🔊 Audio & Voice Control
- **Text-to-Speech announcements** for new orders in Italian
- **Voice recognition** - say "ordine numero X pronto" to mark orders ready
- **Notification sounds** for new orders
- **Toggleable controls** for sound and voice features

### 🖨️ Printer Integration
- Simulated printer integration via AJAX
- Auto-generates printable receipts
- Triggered when orders are marked as ready

### 🎨 Premium Design
- Blue/turquoise gradient background
- Gold accent colors
- Pulsing animations for active orders
- Full-screen flashing alerts for ready orders
- Responsive design for tablets and large displays

### 🔒 Security
- Nonce verification on all AJAX requests
- User capability checks (edit_posts required)
- Input sanitization and validation
- Protected endpoints

## Installation

1. Upload `risto-kitchen-board.php` to `/wp-content/plugins/`
2. Ensure **Risto Base** plugin is installed and activated
3. Activate the plugin through WordPress admin

## Usage

### Basic Setup
Add the shortcode to any page or post:
```
[risto_kitchen_board]
```

### Voice Commands
1. Click the "Voice" button to enable voice recognition
2. Say: **"ordine numero [X] pronto"** (e.g., "ordine numero 5 pronto")
3. Order will be marked as ready and sent to printer

### Display Controls
- **🎤 Voice Button**: Toggle voice recognition on/off
- **🔊 Sound Button**: Toggle audio notifications on/off

## Integration

### With Risto Base Plugin
Automatically fetches orders from Risto Base when available.

### With WooCommerce
Falls back to WooCommerce orders if Risto Base is not available.

### Demo Mode
Automatically generates demo orders for testing when no order system is detected.

## AJAX Endpoints

### Get Orders
- **Action**: `rkb_get_orders`
- **Method**: POST
- **Auth**: Public (read-only)

### Mark Order Ready
- **Action**: `rkb_mark_ready`
- **Method**: POST
- **Auth**: Requires `edit_posts` capability
- **Params**: `order_id`

### Print Order
- **Action**: `rkb_print_order`
- **Method**: POST
- **Auth**: Requires `edit_posts` capability
- **Params**: `order_id`

## Browser Requirements

- **Voice Recognition**: Chrome, Edge, Safari (WebKit Speech API)
- **Text-to-Speech**: All modern browsers
- **Audio**: HTML5 Audio support

## Customization

### Refresh Interval
Default: 5000ms (5 seconds)
Modify in `wp_localize_script`:
```php
'refreshInterval' => 5000
```

### Demo Order Generation
Default: Every 30 seconds
Modify constants:
```php
const DEMO_ORDER_INTERVAL = 30;
const DEMO_ORDER_TRANSIENT_EXPIRY = 300;
```

## System Requirements

- WordPress 5.0+
- PHP 7.2+
- Risto Base plugin (required dependency)
- Modern browser with JavaScript enabled

## Support

For issues or feature requests, contact Viaggi Cloud support.

## License

GPL-2.0+ - Same as WordPress

## Version

1.0.0 - Initial Release

---

**Developed by Viaggi Cloud** 🍽️
