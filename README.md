# RWDD-Assignment
# We Use Composer Only for PHPMailer (no other frameworks)

## 🧩 Step 1: Install Composer

Composer helps manage PHP dependencies easily.

### 🔹 Installation:
1. Visit the official Composer website: [https://getcomposer.org/](https://getcomposer.org/)
2. Download the **Composer Setup** file for your operating system.
3. Run the installer and follow the setup instructions.
4. After installation, verify Composer by running this command in your terminal:
   composer -V

## 🧩 Step 2: Install PHPMailer (IMPORTANT: Run in Frontend folder)
1. **Open your terminal and navigate to the Frontend folder:**
   ```powershell
   cd Frontend
   ```
2. **Install dependencies and PHPMailer:**
   ```powershell
   composer install
   composer require phpmailer/phpmailer
   ```
   This will create a `vendor` folder inside `Frontend`.

## 🧩 Step 3: Troubleshooting PHPMailer autoload errors
- If you see an error like `failed to open stream: No such file or directory` for `vendor/autoload.php`,
  it means Composer was run in the wrong folder.
- Make sure `Frontend/vendor/autoload.php` exists.
- The code in `php/sendResetLink.php` uses:
  ```php
  require '../vendor/autoload.php';
  ```
  This works only if `vendor` is inside `Frontend`.

**Summary:** Always run Composer commands inside the `Frontend` folder so dependencies are installed in the correct place.

## 🧩 Step 4: Enable OpenSSL extension in XAMPP (required for PHPMailer SMTP)
PHPMailer needs the OpenSSL extension to send emails securely.

1. Open your XAMPP `php.ini` file (usually at `xampp/php/php.ini`).
2. Find this line:
   ```
   ;extension=openssl
   ```
3. Remove the semicolon (`;`) so it becomes:
   ```
   extension=openssl
   ```
4. Save the file.
5. Restart Apache from the XAMPP control panel.

If you skip this step, PHPMailer will not be able to send emails using SMTP with TLS/SSL.

**Summary:** Always run Composer commands inside the `Frontend` folder and make sure `extension=openssl` is enabled in your XAMPP `php.ini`.

