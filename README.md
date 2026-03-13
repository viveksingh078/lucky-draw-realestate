# FlexHome - Real Estate Lucky Draw Platform

A comprehensive Laravel-based real estate platform with integrated lucky draw system, membership plans, and property management features.

## Features

### Core Features
- **Property Management** - Browse, search, and filter properties with detailed information
- **User Authentication** - Secure registration, login, and profile management
- **Lucky Draw System** - Join draws, automatic winner selection, prize distribution
- **Membership Plans** - Multiple membership tiers with exclusive benefits and credits
- **Property Purchases** - Buy properties with integrated wallet system
- **Vendor/Dealer System** - Vendors can list and manage properties
- **Wallet Management** - Credits, on-hold amounts, transaction history
- **Multi-language Support** - English and Vietnamese language support

### Advanced Features
- **Payment Integration** - Razorpay, SSLCommerz, Paystack support
- **Admin Dashboard** - Complete admin panel for system management
- **Analytics & Reporting** - Track user activity, sales, and draw statistics
- **Email Notifications** - Automated emails for registrations, draws, and transactions
- **SEO Optimization** - Built-in SEO helper and sitemap generation
- **Audit Logging** - Track all system changes and user actions
- **Backup System** - Automated backup functionality

## Tech Stack

### Backend
- **Framework:** Laravel 8+
- **Database:** MySQL
- **Authentication:** Laravel Sanctum
- **Queue:** Laravel Queue System
- **Caching:** Redis/File-based caching

### Frontend
- **JavaScript:** Vue.js
- **CSS Framework:** Bootstrap 5
- **Build Tool:** Laravel Mix (Webpack)
- **Validation:** JS Validation Plugin

### Additional Tools
- **Payment Gateways:** Razorpay, SSLCommerz, Paystack
- **Email:** Laravel Mail with SMTP
- **File Storage:** Local/Cloud storage support
- **API Documentation:** OpenAPI/Swagger

## Screenshots Section

```
/public/storage/
├── properties/     - Property images
├── users/          - User avatars
├── banner/         - Banner images
├── logo/           - Logo files
├── projects/       - Project images
└── news/           - News images
```

## Installation Steps

### Prerequisites
- PHP 8.0 or higher
- Composer
- MySQL 5.7 or higher
- Node.js & npm (for frontend assets)

### Step 1: Clone Repository
```bash
git clone https://github.com/yourusername/flexhome-realestate.git
cd flexhome-realestate
```

### Step 2: Install Dependencies
```bash
composer install
npm install
```

### Step 3: Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### Step 4: Configure Database
Edit `.env` file with your database credentials:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=flexhome
DB_USERNAME=root
DB_PASSWORD=
```

### Step 5: Run Migrations & Seeders
```bash
php artisan migrate
php artisan db:seed
```

### Step 6: Build Frontend Assets
```bash
npm run dev
# or for production
npm run production
```

### Step 7: Create Storage Link
```bash
php artisan storage:link
```

### Step 8: Start Development Server
```bash
php artisan serve
```

Access the application at `http://localhost:8000`

## Configuration

### Payment Gateway Setup
Update `.env` with your payment credentials:
```
RAZORPAY_KEY=your_key
RAZORPAY_SECRET=your_secret

SSLCOMMERZ_STORE_ID=your_store_id
SSLCOMMERZ_STORE_PASSWORD=your_password

PAYSTACK_PUBLIC_KEY=your_public_key
PAYSTACK_SECRET_KEY=your_secret_key
```

### Email Configuration
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
```

## Project Structure

```
flexhome-realestate/
├── app/                    - Application code
│   ├── Http/              - Controllers, Middleware
│   ├── Models/            - Database models
│   └── Providers/         - Service providers
├── platform/              - Core modules & plugins
│   ├── core/              - Base functionality
│   ├── packages/          - Reusable packages
│   └── plugins/           - Feature plugins
├── database/              - Migrations, seeders, factories
├── public/                - Public assets, storage
├── resources/             - Views, JS, language files
├── routes/                - API and web routes
├── storage/               - Logs, cache, uploads
├── config/                - Configuration files
└── tests/                 - Unit and feature tests
```

## Database Schema

Key tables:
- `users` - User accounts and profiles
- `properties` - Property listings
- `draws` - Lucky draw information
- `draw_participants` - User draw participation
- `memberships` - User membership plans
- `wallet_transactions` - Wallet activity
- `property_purchases` - Purchase records

## API Documentation

API documentation is available at `/public/docs/` with OpenAPI specification.

## Development

### Running Tests
```bash
php artisan test
```

### Code Quality
```bash
php artisan tinker
```

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## Troubleshooting

### 404 Errors
- Run `php artisan route:clear`
- Check `.htaccess` configuration
- Verify storage link: `php artisan storage:link`

### Database Issues
- Verify MySQL is running
- Check database credentials in `.env`
- Run migrations: `php artisan migrate:fresh --seed`

### Asset Issues
- Rebuild assets: `npm run dev`
- Clear cache: `npm run dev` or `npm run production`

## Support & Documentation

- Check `VENDOR_SYSTEM_CLIENT_DOCUMENTATION.md` for vendor features
- See `MEMBERSHIP_PLAN_REDESIGN_FINAL.md` for membership details
- Review `PROPERTY_PURCHASE_SYSTEM_IMPLEMENTATION.md` for purchase flow

## License

This project is proprietary software. All rights reserved.

## Author

FlexHome Development Team

---

**Last Updated:** March 2026
