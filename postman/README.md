# Web Scraper API - Postman Collection

This directory contains Postman collection and environment files for testing the Web Scraper API.

## Files

- `WebScraper_API.postman_collection.json`: The Postman collection containing all API endpoints and example requests.
- `WebScraper_API.postman_environment.json`: Environment variables for the collection.

## How to Import

1. Open Postman
2. Click on "Import" in the top left corner
3. Drag and drop both the collection and environment files, or browse to select them
4. Click "Import"

## Environment Setup

The environment file includes the following variables:

- `base_url`: The base URL for the API (default: http://localhost:8000)

Make sure to select the "WebScraper API - Local" environment from the environment dropdown in the top right corner of Postman.

## Available Endpoints

### Crawl Website

- **URL**: `{{base_url}}/api/crawler/crawl`
- **Method**: POST
- **Body**: JSON
  ```json
  {
    "url": "https://symfony.com"
  }
  ```

- **Success Response**:
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

- **Error Response (Invalid URL)**:
  ```json
  {
    "status": "error",
    "message": "Validation failed",
    "errors": {
      "url": "This value is not a valid URL."
    }
  }
  ```

- **Error Response (Missing URL)**:
  ```json
  {
    "status": "error",
    "message": "Validation failed",
    "errors": {
      "url": "URL cannot be blank."
    }
  }
  ``` 