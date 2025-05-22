# Web Scraper API

A Symfony-based API for crawling and analyzing websites.

## Features

- Web crawling with asynchronous processing using Symfony Messenger
- Analysis of HTML content including tag count and request duration
- API endpoint for submitting URLs to be crawled

## System Architecture

The following diagram shows how the web crawler works:

![System Design](system-design.png)

### Process Flow

1. **Request Handling**:
   - Client sends URL to the API
   - Controller validates URL and creates initial database entry
   - CrawlMessage is dispatched to Message Bus

2. **Asynchronous Processing**:
   - Worker picks up the message
   - Crawler Service fetches the webpage
   - HTML content is analyzed
   - Results are stored in database

3. **Status Updates**:
   - Website entry is updated with results
   - Status changes from "waiting" to "completed"
   - Error handling for failed crawls

## Setup

### Local Development

1. Clone this repository
2. Install dependencies: `composer install`
3. Configure your PostgreSQL database in the `.env` file:
   ```
   DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=16&charset=utf8"
   ```
4. Create the database schema: `php bin/console doctrine:migrations:migrate`
5. Start the Symfony server: `symfony server:start`
6. Start the messenger worker: `php bin/console messenger:consume async`

### Running Tests

1. Create a test database:
   ```bash
   php bin/console --env=test doctrine:database:create
   php bin/console --env=test doctrine:schema:create
   ```

2. Run PHPUnit tests:
   ```bash
   # Run all tests
   php bin/phpunit

   # Run specific test suite
   php bin/phpunit --testsuite=unit

   # Run tests with coverage report
   php bin/phpunit --coverage-html coverage
   ```

3. Test environment variables are configured in `.env.test`. Make sure to update them if needed.

### Deployment on Fly.io

1. Install the Fly.io CLI:
   ```bash
   # For MacOS/Linux
   curl -L https://fly.io/install.sh | sh
   
   # For Windows PowerShell
   iwr https://fly.io/install.ps1 -useb | iex
   ```

2. Sign up and sign in:
   ```bash
   fly auth signup
   # or
   fly auth login
   ```

3. Deploy the application:
   ```bash
   fly launch
   ```

4. Set up a PostgreSQL database:
   ```bash
   fly postgres create --name webscraperapi-db
   fly postgres attach --postgres-app webscraperapi-db
   ```

5. Run database migrations:
   ```bash
   fly ssh console -C "php /app/bin/console doctrine:migrations:migrate --no-interaction"
   ```

6. Deploy changes:
   ```bash
   fly deploy
   ```

## API Endpoints

### Crawl Website

```
POST /api/crawler/crawl
```

Request body:
```json
{
    "url": "https://example.com"
}
```

Response:
```json
{
    "status": "success",
    "message": "We are crawling website now.",
    "data": {
        "id": 1,
        "status": "waiting"
    }
}
```

## Postman Collection

A Postman collection is included for testing the API. Import the collection from the file:
`postman/WebScraper_API.postman_collection.json`

## Architecture

The application follows a service-layer architecture:

1. **Controllers**: Handle HTTP requests and validation
2. **Services**: Implement business logic 
3. **Repositories**: Interact with the database
4. **Entities**: Define the data model
5. **Message/MessageHandlers**: Handle asynchronous processing
