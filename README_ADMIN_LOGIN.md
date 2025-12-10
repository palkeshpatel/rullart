# Admin Login Setup

## Admin Authentication

The admin login system uses the `admin` table from your CI database.

### Login Credentials
- **Username**: Any username from the `admin` table (e.g., "admin", "user", "june", etc.)
- **Password**: "password" (for all admin users)

### Update Admin Passwords

To set all admin passwords to "password", run:

```bash
php update_admin_passwords.php
```

Or manually via SQL:
```sql
UPDATE `admin` SET `pass` = MD5('password');
```

### Database Configuration

Make sure your `.env` file points to the same database as your CI project:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rullart_rullart
DB_USERNAME=root
DB_PASSWORD=
```

### Login URL
- Admin Login: `/admin/login`
- Dashboard: `/admin/dashboard`

