# QuizArena

QuizArena is a modern, responsive, and dynamic web application for playing and creating quizzes. Built with pure PHP 8.3, PostgreSQL, vanilla JS, and a custom Neon Pulse design system.

## 🚀 Features

- **Gamification & Progression**: Earn XP, level up, and see your rank.
- **Dynamic Leaderboards**: Fast, competitive leaderboards showing top players.
- **Neon Pulse Design**: A sleek, custom design system utilizing CSS variables and modern UI trends.
- **Quiz Creator**: Dynamic form using vanilla JavaScript to build custom quizzes with customizable time limits.
- **Game Engine**: A robust API-based game engine written in pure JS, recording time taken, accuracy, and scoring.
- **Fully Responsive**: Flawless experience on desktop, tablet, and mobile devices.

## 💻 Tech Stack

- **Backend**: PHP 8.3 (PSR-4 Autoloading), Custom Router / Endpoints
- **Database**: PostgreSQL 17
- **Frontend**: HTML5, Vanilla JavaScript, Vanilla CSS (Neon Pulse design system)
- **Environment**: Docker & Docker Compose

## 🛠️ Installation & Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/KamilW-git/QuizArena.git
   cd QuizArena
   ```

2. **Configure Environment:**
   Copy the `.env.example` file to create your own configuration:
   ```bash
   cp .env.example .env
   ```
   Feel free to adjust the database credentials if necessary.

3. **Start the Application:**
   Run the project using Docker Compose. This will build the PHP image, pull the PostgreSQL image, install Composer dependencies, and start the servers.
   ```bash
   docker compose up --build -d
   ```

4. **Access the Application:**
   Open your browser and navigate to:
   [http://localhost:8000](http://localhost:8000)

## 🐳 Docker Services

- **app**: PHP 8.3 with Apache, exposed on port `8000`.
- **db**: PostgreSQL 17, exposed on port `5433` (preventing conflicts with local PG instances), handles automatic schema initialization.

## 📦 Deployment

To deploy in a production environment:

1. Ensure your production server has **Docker** and **Docker Compose** installed.
2. Clone the repository and configure `.env` with strong, production-ready passwords and keys.
3. Start the containers using:
   ```bash
   docker compose -f docker-compose.yml up -d --build
   ```
   > **Note**: For production, it's recommended to configure a reverse proxy (like Nginx or Traefik) in front of the `app` container to handle SSL/TLS termination. You should also ensure the `DB_PASS` and `POSTGRES_PASSWORD` are secure.

## 🤝 Contributing

Contributions, issues, and feature requests are welcome!