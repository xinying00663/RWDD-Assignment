# EcoGo - RWDD Assignment

I'm using Composer PHPMailer to do the sendresetemail. I did not use for other framework

## 🚀 Installation Guide

Follow these steps carefully to set up the project after downloading the zip file.

### Step 1: Extract the Project

1. Extract the downloaded zip file
2. Copy the entire **`RWDD-Assignment`** folder to your XAMPP `htdocs` directory:
   ```
   C:\xampp\htdocs\RWDD-Assignment\
   ```

### Step 2: Install Composer (if not already installed)

Composer is required to install PHPMailer for the password reset feature.

1. Visit: [https://getcomposer.org/download/](https://getcomposer.org/download/)
2. Download **Composer-Setup.exe** (for Windows)
3. Run the installer and follow the setup wizard
4. Verify installation by opening **Command Prompt** or **PowerShell** and typing:
   ```bash
   composer --version
   ```
   You should see something like: `Composer version 2.x.x`

### Step 3: Install PHPMailer Dependencies

⚠️ **CRITICAL:** You MUST run these commands inside the `Frontend` folder!

1. Open **Command Prompt** or **PowerShell**
2. Navigate to the Frontend folder:
   ```bash
   cd C:\xampp\htdocs\RWDD-Assignment\Frontend
   ```
3. Install PHPMailer:
   ```bash
   composer install
   ```
   This will:
   - Read `composer.json` and `composer.lock` files
   - Download PHPMailer into `Frontend/vendor/` folder
   - Create the autoloader file

4. **Verify installation** - Check that this folder exists:
   ```
   Frontend/vendor/phpmailer/phpmailer/
   ```

### Step 4: Enable OpenSSL in XAMPP

PHPMailer requires OpenSSL to send emails via SMTP.

1. Open XAMPP Control Panel
2. Click **Config** next to Apache → Select **PHP (php.ini)**
3. Find this line (use Ctrl+F to search):
   ```ini
   ;extension=openssl
   ```
4. Remove the semicolon (`;`) to enable it:
   ```ini
   extension=openssl
   ```
5. Save the file
6. **Restart Apache** from XAMPP Control Panel

### Step 5: Import the Database

1. Open XAMPP Control Panel and start **Apache** and **MySQL**
2. Open your browser and go to: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
3. Create a new database named **`ecogo`**
4. Click on the `ecogo` database
5. Click **Import** tab
6. Choose file: `Frontend/database/ecogo (3).sql`
7. Click **Go** to import



## 🌐 Accessing the Application

1. Make sure **Apache** and **MySQL** are running in XAMPP
2. Open your browser and visit:
   ```
   http://localhost/RWDD-Assignment/Frontend/landingPage.html
   ```

---

## 🔑 Features

- **User Authentication:** Sign up, login, password reset via email
- **Recycling Programs:** Browse and join environmental programs
- **Energy Tips:** Share and discover energy conservation guides
- **Gardening Community:** Post gardening projects and tips
- **Item Swap Marketplace:** Exchange items with other users
- **Inbox System:** Manage swap requests and notifications

---

## ⚠️ Common Issues & Solutions

### ❌ Error: "Class PHPMailer not found"
**Solution:** You forgot to run `composer install` in the Frontend folder. Go back to Step 3.

### ❌ Error: "vendor/autoload.php not found"
**Solution:** Make sure you ran composer commands inside the `Frontend` folder, not the root folder.

### ❌ Password reset email not sending
**Solution:** 
1. Check that OpenSSL extension is enabled (Step 4)
2. Verify `Frontend/vendor/phpmailer/` folder exists
3. Check your internet connection (SMTP requires internet)

### ❌ Database connection error
**Solution:**
1. Make sure MySQL is running in XAMPP
2. Verify the database `ecogo` exists in phpMyAdmin
3. Check `Frontend/php/connect.php` credentials

### ❌ Page not found (404 error)
**Solution:** Make sure you're using the correct URL:
- ✅ Correct: `http://localhost/RWDD-Assignment/Frontend/landingPage.html`
- ❌ Wrong: `http://localhost/Frontend/landingPage.html`


## 📁 Project Structure

```
RWDD-Assignment/
├── Frontend/
│   ├── php/                    # Backend PHP files
│   ├── styles/                 # CSS stylesheets
│   ├── script/                 # JavaScript files
│   ├── Pictures/               # Images and media
│   ├── database/               # SQL database file
│   ├── vendor/                 # Composer dependencies (created after Step 3)
│   ├── composer.json           # Composer configuration
│   ├── *.php                   # Main page files
│   └── *.html                  # Static page files
└── README.md                   # This file
```

