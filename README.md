# Restaurant Mobile Order API (Symfony)

## Features Implemented

✅ Create new orders (with optional VIP flag)
✅ Enforce kitchen capacity (configurable)
✅ VIP orders bypass capacity limit
✅ Order status flow: pending → active → completed
✅ Mark orders as completed
✅ Retrieve active orders
✅ Database persistence using Doctrine ORM (MySQL)

## Setup & Installation

Follow these steps to run the project locally:

### Clone this repository

1. Clone the repository

git clone https://github.com/fayazwebdev/restaurant-mobi-api.git
cd restaurant-mobi-api

2. Install dependencies

composer install

3. Configure environment

DATABASE_URL="mysql://root:@127.0.0.1:3306/restaurant_db?serverVersion=10.4.32-MariaDB&charset=utf8mb4"

4. Setup database

php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

5. Start the Symfony local server

symfony serve

The API will be available at: http://127.0.0.1:8000
