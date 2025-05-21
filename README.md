Symfony for Platform.sh
=======================

<p align="center">
<a href="https://console.platform.sh/projects/create-project?template=https://raw.githubusercontent.com/symfonycorp/platformsh-symfony-template-metadata/main/sf7.2-php8.4-webapp.template.yaml&utm_content=symfonycorp&utm_source=github&utm_medium=button&utm_campaign=deploy_on_platform">
    <img src="https://platform.sh/images/deploy/lg-blue.svg" alt="Deploy on Platform.sh" width="180px" />
</a>
</p>

# Web Scraper API

A Symfony-based API for crawling and analyzing websites.

## Features

- Web crawling with asynchronous processing using Symfony Messenger
- Analysis of HTML content including tag count and request duration
- API endpoint for submitting URLs to be crawled

## Setup

1. Clone this repository
2. Install dependencies: `composer install`
3. Configure your PostgreSQL database in the `.env` file:
   ```
   DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=16&charset=utf8"
   ```
4. Create the database schema: `php bin/console doctrine:migrations:migrate`
5. Start the Symfony server: `symfony server:start`
6. Start the messenger worker: `php bin/console messenger:consume async`

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
`public/webscraper-api.postman_collection.json`

## Architecture

The application follows a service-layer architecture:

1. **Controllers**: Handle HTTP requests and validation
2. **Services**: Implement business logic 
3. **Repositories**: Interact with the database
4. **Entities**: Define the data model
5. **Message/MessageHandlers**: Handle asynchronous processing
