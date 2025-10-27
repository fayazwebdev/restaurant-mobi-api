🍽️ Restaurant Mobile Order API (Symfony)

A RESTful API built with Symfony for managing restaurant orders with kitchen capacity limits and VIP prioritization.

✅ Features Implemented

    ✅ Create new orders (with optional VIP flag)

    ✅ Enforce kitchen capacity (configurable)

    ✅ VIP orders bypass capacity limit

    ✅ Order status flow: active → completed

    ✅ Mark orders as completed

    ✅ Retrieve active orders

    ✅ Database persistence using Doctrine ORM (MySQL)

    ✅ Unit tests for core service logic

⚙️ Setup & Installation

Follow these steps to run the project locally:

1. Clone the repository

git clone https://github.com/fayazwebdev/restaurant-mobi-api.git
cd restaurant-mobi-api

2. Install dependencies

composer install

3. Configure environment

Update .env file:

DATABASE_URL="mysql://root:@127.0.0.1:3306/restaurant_db?serverVersion=10.4.32-MariaDB&charset=utf8mb4"

4. Setup database

php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

5. Start the Symfony local server

symfony serve

The API will be available at:
👉 http://127.0.0.1:8000

API Endpoints
Create Order

POST /api/orders

Request Body:
{
"items": ["Burger", "Fries"],
"pickup_time": "2025-10-25 14:00:00",
"VIP": true
}
Responses:

201 Created – Order successfully created

400 Bad Request – Missing or invalid data

429 Too Many Requests – Kitchen is full for non-VIP orders

Get Active Orders

GET /api/orders/active

Response Example:
[
{
"id": 1,
"items": ["Pizza"],
"status": "active"
}
]

Complete an Order

PUT /api/orders/{id}/complete

Response:
{
"message": "Order marked as completed",
"status": "completed"
}

Error Responses:

404 Not Found – Order does not exist

400 Bad Request – Invalid ID

🧪 Running Unit Tests

php bin/phpunit --testdox

App\Tests\Service\OrderServiceTest
✔ Create order fails when kitchen full for non VIP
✔ Create order succeeds for VIP even when kitchen full
✔ Create order fails for missing items
✔ Complete order fails when not found
✔ Complete order success
