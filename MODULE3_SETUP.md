# Module 3 — Two Added Features

This package adds:

1. Submit Hire Request
2. View Incoming Hire Requests

## Temporary dependencies

Authentication and buyer/seller switching are not implemented yet. The placeholder logic is isolated in:

- `app/Support/CurrentUser.php`
- `database/seeders/DatabaseSeeder.php`

Temporary accounts:

- Seller: `seller@example.com`
- Buyer: `buyer@example.com`
- Password for both: `password`

## After copying/replacing the project

Run:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Configure MySQL in `.env`, then run:

```powershell
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

Open:

- Gig list: `/gigs`
- Submit request: open a gig and click **Request to Hire**
- Incoming requests: `/seller/hire-requests`

## Integration later

Replace `CurrentUser::buyer()` and `CurrentUser::seller()` with the real authenticated user after the authentication module is merged.
