# Submit and Approve Final Deliverable — Setup

This branch adds two features:

1. Seller submits a final deliverable using a file, a URL, or both.
2. Buyer approves the submitted deliverable and completes the order.

## New files

- `app/Http/Controllers/OrderController.php`
- `app/Http/Controllers/DeliverableController.php`
- `app/Models/Order.php`
- `app/Models/Deliverable.php`
- `database/migrations/2026_08_04_000001_create_orders_table.php`
- `database/migrations/2026_08_04_000002_create_deliverables_table.php`
- `resources/views/orders/index.blade.php`
- `resources/views/orders/show.blade.php`

## Modified files

- `routes/web.php`
- `app/Models/Gig.php`
- `app/Http/Controllers/GigController.php`
- `resources/views/gigs/index.blade.php`
- `resources/views/gigs/show.blade.php`

## Run after copying the code

```powershell
php artisan migrate
php artisan storage:link
php artisan optimize:clear
php artisan serve
```

If `public/storage` already exists, the `storage:link` command may say that the link already exists. That is fine.

## Test flow

1. Open `http://127.0.0.1:8000/gigs`.
2. Create a gig if none exists.
3. Open the gig details page.
4. Click **Create Demo Order**.
5. Upload a final file or paste an HTTPS link and click **Submit Final Deliverable**.
6. Click **Approve Final Deliverable**.
7. Confirm that the order status becomes **completed**.

## Temporary placeholder

The project currently does not contain the teammate module that accepts a hire request and creates an order. Therefore, `OrderController::createDemo()` and the **Create Demo Order** button are clearly marked temporary. Remove them after the real order-creation flow is merged. The deliverable submission and approval code can remain.
