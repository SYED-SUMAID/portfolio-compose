# Portfolio Compose

A Dockerized PHP web app that shows a live lab leaderboard ("Verventech Lab Command Center") powered by PostgreSQL.

## Features

- Live leaderboard read from a PostgreSQL view
- Stats for operatives, labs completed, and overall progress
- Database online/offline status indicator
- Dark, animated UI
- One-command setup with Docker Compose

## Tech Stack

- PHP 8.2 (Apache)
- PostgreSQL 15
- Docker and Docker Compose

## Project Structure

    portfolio-compose/
    ├── db/
    │   └── init.sql          # Creates table, view, and seed data
    ├── src/
    │   └── index.php         # Web page and DB queries
    ├── Dockerfile            # PHP + Apache with pgsql extensions
    ├── docker-compose.yml    # Web and database services
    ├── .env.example          # Environment variable template
    └── README.md

## Getting Started

1. Clone the repo:

    git clone https://github.com/SYED-SUMAID/portfolio-compose.git
    cd portfolio-compose

2. Create your env file:

    cp .env.example .env

3. Edit `.env` and set your own credentials:

    POSTGRES_USER=your_user
    POSTGRES_PASSWORD=your_password
    POSTGRES_DB=your_db

4. Start the app:

    docker compose up --build

5. Open http://localhost:8080:80

## How It Works

- `portfolio-web` runs PHP and Apache on port 8080.
- `portfolio-db` runs PostgreSQL and loads `db/init.sql` on first start.
- The web service waits for the database healthcheck before starting.
- `index.php` reads connection details from environment variables and queries the `verventech_leaderboard` view.
- Data persists in the `db-data` Docker volume.

## Database

- Table: `verventech` (name, labs_complete, total_labs, ranking)
- View: `verventech_leaderboard` (adds labs_remaining and commentary)

## Useful Commands

    docker compose up -d        # Run in background
    docker compose logs -f      # View logs
    docker compose down         # Stop containers
    docker compose down -v      # Stop and delete database data

## Notes

- `init.sql` runs only on first start. Run `docker compose down -v` to reseed.
- Never commit your `.env` file (it's already in `.gitignore`).

