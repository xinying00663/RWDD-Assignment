# Swap Notification System - Implementation Complete

## Overview
The swap notification system has been successfully implemented. When User B wants to swap an item with User A, the following flow occurs:

## Complete Flow

### 1. User B Submits Swap Request
- User B browses swap items on `swapPage.php`
- Clicks on an item to go to `swapConfirm.php`
- Fills out offer details (title, description, notes, image)
- Clicks "Confirm swap request"

**What happens:**
- Exchange record created in `exchange` table with status='pending'
- Notification created for User A (owner) with message: 
  ```
  "Hi, [User B's Username] wants to swap with you using "[Offer_title]" for your item "[Item_title]""
  ```
- Notification linked to ExchangeID
- User B redirected back to swap page

### 2. User A Receives Notification
- User A opens inbox (`inboxPage.html`)
- Sees unread notification count badge
- Notification appears in list with "new" indicator
- Shows preview of swap request message

**What happens:**
- `php/getNotifications.php` fetches notifications from database
- Displays all notifications for logged-in user
- Shows timestamp (e.g., "Just now", "2h ago")
- Unread notifications highlighted

### 3. User A Clicks Notification
- User A clicks on the notification
- Automatically redirected to `php/viewSwapRequest.php?exchange_id=[ID]`
- Notification marked as read

**What User A sees:**
- Complete swap request details in a clean card layout:
  - **Your Item section**: Shows item name and image
  - **They are Offering section**: Shows:
    - Requester's username
    - Offer item name
    - Offer description
    - Additional notes (if provided)
    - Offer item image
  - **Status badge**: Shows current status (Pending/Accepted/Declined)
  - **Action buttons** (only if status is 'pending'):
    - Green "Accept Swap" button
    - Red "Reject Swap" button
  - "Back to Inbox" button

### 4a. User A Accepts Swap
When User A clicks "Accept Swap":

**What happens:**
1. Confirmation dialog: "Are you sure you want to accept this swap? Your item will be marked as exchanged."
2. `php/handleSwapDecision.php` processes the acceptance:
   - Updates `exchange.status` to 'accepted'
   - Updates `items.Status` to 'Exchanged' (removes from swap page)
   - Creates notification for User B:
     ```
     "Great news! [User A's Username] has accepted your swap request for "[Item_title]""
     ```
3. Success message: "Swap accepted! The item has been marked as exchanged."
4. Redirects to inbox
5. User B receives notification about acceptance

### 4b. User A Rejects Swap
When User A clicks "Reject Swap":

**What happens:**
1. Confirmation dialog: "Are you sure you want to reject this swap?"
2. `php/handleSwapDecision.php` processes the rejection:
   - Updates `exchange.status` to 'declined'
   - Item remains Available on swap page
   - Creates notification for User B:
     ```
     "Sorry, [User A's Username] has declined your swap request for "[Item_title]""
     ```
3. Success message: "Swap rejected. The item remains available."
4. Redirects to inbox
5. User B receives notification about rejection

### 5. User B Receives Response
- User B opens inbox
- Sees notification from User A with acceptance or rejection
- Can view notification details by clicking

## Files Created/Modified

### New Files Created:
1. **`Frontend/php/viewSwapRequest.php`**
   - Displays complete swap request details
   - Shows Accept/Reject buttons for pending requests
   - Marks notification as read when viewed
   - Verifies user authorization (only owner can view)

2. **`Frontend/php/handleSwapDecision.php`**
   - Processes accept/reject actions
   - Updates exchange and item statuses
   - Sends response notifications to requester

3. **`Frontend/php/getNotifications.php`**
   - API endpoint to fetch notifications
   - Returns JSON with notifications and unread count
   - Orders by newest first

4. **`Frontend/database/update_notifications.sql`**
   - SQL script to add ExchangeID column to notifications table
   - Adds foreign key constraint linking to exchange table

### Modified Files:
1. **`Frontend/swapConfirm.php`**
   - Added notification creation after exchange insert
   - Gets requester username for notification message
   - Links notification to ExchangeID

2. **`Frontend/script/inboxPage.js`**
   - Replaced mock data with live database fetch
   - Fetches from `php/getNotifications.php`
   - Redirects to `viewSwapRequest.php` when notification clicked
   - Updates unread count badge dynamically
   - Formats timestamps (Just now, 2h ago, etc.)

## Database Schema Updates

### notifications Table
**New Column Added:**
```sql
ExchangeID INT(11) DEFAULT NULL
```
**Foreign Key Added:**
```sql
FOREIGN KEY (ExchangeID) REFERENCES exchange(ExchangeID) ON DELETE CASCADE
```

This links notifications to specific swap exchanges, allowing users to click and view details.

## Installation Steps

1. **Update Database Schema:**
   ```bash
   # In phpMyAdmin or MySQL command line:
   # Run the script: Frontend/database/update_notifications.sql
   ```
   Or manually execute:
   ```sql
   ALTER TABLE `notifications` ADD COLUMN `ExchangeID` INT(11) DEFAULT NULL AFTER `UserID`;
   ALTER TABLE `notifications` ADD CONSTRAINT `fk_notifications_exchange` 
   FOREIGN KEY (`ExchangeID`) REFERENCES `exchange` (`ExchangeID`) ON DELETE CASCADE;
   ```

2. **Files are ready to use** - No additional setup required

## Testing the Feature

### Test Scenario:
1. **Setup:**
   - Login as User A (owner)
   - Go to Swap page
   - Upload a swap item

2. **Login as User B (requester):**
   - Browse swap items
   - Click on User A's item
   - Fill out swap offer form with image
   - Submit swap request

3. **Login as User A:**
   - Open inbox (`inboxPage.html`)
   - Should see notification: "Hi, [User B] wants to swap with you using..."
   - Click the notification
   - View full swap details with User B's offer
   - Click "Accept Swap" or "Reject Swap"

4. **Login as User B:**
   - Open inbox
   - Should see response notification from User A
   - If accepted: Item removed from swap page
   - If rejected: Item still visible on swap page

## Security Features
- ✅ Admin users blocked from swapping (UI + server-side)
- ✅ Self-swap requests blocked (can't swap with own items)
- ✅ Authorization checks (only owner can accept/reject)
- ✅ Authentication required for all endpoints
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS prevention (htmlspecialchars on all outputs)

## User Experience Highlights
- 📱 Real-time unread count badge
- ⏰ Human-readable timestamps (Just now, 2h ago, etc.)
- 👁️ Visual indicators for unread notifications
- ✅ Confirmation dialogs before accept/reject actions
- 🔔 Bidirectional notifications (both users get updates)
- 🗑️ Automatic item removal upon acceptance
- 📦 Item remains available upon rejection

## Notes
- Admins can still view swap items but cannot initiate swaps
- When item is accepted, Status changes to 'Exchanged' (not deleted)
- Notifications cascade delete if exchange is deleted
- All timestamps use server time (NOW() in MySQL)
- Inbox shows most recent 50 notifications
