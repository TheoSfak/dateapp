# DateApp

A full-featured dating web application built with pure PHP and MySQL.

## Requirements
- XAMPP (Apache + MySQL + PHP 8.x)
- PHP 8.1+
- MySQL 5.7+ / MariaDB 10.4+

## Setup

1. **Import the database:**
   ```
   mysql -u root < database/schema.sql
   ```

2. **Copy to XAMPP htdocs:**
   Copy this entire folder to `C:\xampp\htdocs\dateapp`

3. **Configure:**
   Edit `config/database.php` and `config/app.php` as needed.

4. **Start XAMPP:**
   Start Apache and MySQL from the XAMPP Control Panel.

5. **Visit:**
   Open `http://localhost/dateapp/` in your browser.

## Project Structure
```
dateapp/
├── app/
│   ├── controllers/    # Request handlers
│   ├── core/           # Framework: Router, Database, Session, CSRF, View
│   ├── models/         # Data access (PDO)
│   └── views/          # PHP templates
│       ├── auth/       # Login & Register
│       ├── errors/     # 404, etc.
│       ├── home/       # Landing & Dashboard
│       └── layouts/    # Main layout wrapper
├── config/             # App, database, and route config
├── database/           # SQL schema files
├── public/             # Web root (index.php, .htaccess, css, js, uploads)
└── storage/            # Logs
```

## Security
- Passwords hashed with bcrypt (cost 12)
- CSRF protection on all forms
- PDO prepared statements (no SQL injection)
- XSS prevention via htmlspecialchars
- Session fixation prevention via regeneration on login

## License
MIT
