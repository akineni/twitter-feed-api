# Twitter Feed API

This application implements a Laravel-based API for fetching, analyzing, and interacting with tweets.

## Endpoints

### 1. **GET /api/tweet/{id}**
- **Description**: Returns a single tweet by ID.
- **Response**: A JSON object of tweet.

### 2. **GET /api/tweets**
- **Description**: Returns the first 20 tweets.
- **Response**: A JSON array of tweets.

### 3. **GET /api/tweets/most-liked**
- **Description**: Returns the most liked tweet.
- **Response**: A JSON object containing the most liked tweet.

### 4. **GET /api/tweets/most-commented**
- **Description**: Returns the most commented tweet.
- **Response**: A JSON object containing the most commented tweet.

## Setup

Follow these steps to set up the application locally:

1. **Clone the repository**:
    ```bash
    git clone https://github.com/akineni/twitter-feed-api.git
    ```

2. **Install dependencies**:
    ```bash
    composer install
    ```

3. **Copy the example environment file**:
    ```bash
    cp .env.example .env
    ```

4. **Configure your `.env` file**:
    - Set up necessary environment variables in the `.env` file.

5. **Generate app key**:
    ```bash
    php artisan key:generate
    ```

6. **Run the application**:
    ```bash
    php artisan serve
    ```

The application should now be running locally at `http://127.0.0.1:8000`.

## Testing

To run the tests, use the following command:

```bash
php artisan test
```

## API Documentation

The API documentation for this project is available at the following URL:

[API Documentation](http://127.0.0.1:8000/api/documentation)

You can access the full list of available endpoints, descriptions, and request/response formats there.