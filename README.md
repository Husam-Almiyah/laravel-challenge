# Laravel Assessment Project

This is a technical assessment project for the Senior Laravel/PHP Developer role. It implements a Multi-Gateway Payment System and a Trial Subscription/Service Booking platform.

## Setup Instructions

Follow these steps to set up and run the project locally:

### 1. Requirements
Ensure you have the following installed:
- PHP 8.3+
- Composer
- MySQL/MariaDB
- Node.js & NPM (for frontend assets if applicable)

### 2. Installation
Clone the repository and install dependencies:
```bash
composer install
npm install
```

### 3. Environment Configuration
Copy the `.env.example` file and configure your database settings:
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database Setup
Run migrations and seeders to initialize the database schema and sample data:
```bash
php artisan migrate --seed
```

### 5. Start the Server
Run the local development server:
```bash
php artisan serve
```

The API endpoints are versioned under `/api/v1/`.
