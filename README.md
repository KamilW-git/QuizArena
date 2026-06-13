# QuizArena – platforma quizów z grywalizacją

> Aplikacja webowa do tworzenia, przeglądania i rozwiązywania quizów
> z systemem XP, osiągnięć, rankingów i panelu administracyjnego.
> PHP 8.3 + PostgreSQL 17 + Docker Compose, frontend Vanilla JS/CSS (Neon Pulse).

Interfejs z trybem **jasnym** i **ciemnym** (przełącznik w navbarze, zapis w `localStorage`),
responsywny układ desktop/mobile. Uruchomienie jednym poleceniem `docker compose up --build -d`.

![Dashboard — tryb jasny](QuizArena_Screenshots/dashboard_desktop_light.png)

---

## Spis treści

1. [Opis aplikacji](#opis-aplikacji)
2. [Technologie i architektura](#technologie-i-architektura)
3. [Instrukcja uruchomienia](#instrukcja-uruchomienia)
4. [Zmienne środowiskowe](#zmienne-środowiskowe)
5. [Konta testowe](#konta-testowe)
6. [Trasy i endpointy](#trasy-i-endpointy)
7. [Flow aplikacji](#flow-aplikacji)
8. [Schemat bazy danych](#schemat-bazy-danych)
9. [Bezpieczeństwo](#bezpieczeństwo)
10. [Widoki aplikacji — galeria](#widoki-aplikacji--galeria)
11. [Scenariusz testowy](#scenariusz-testowy)
12. [Uruchamianie testów](#uruchamianie-testów)
13. [Checklista wymagań](#checklista-wymagań)
14. [Wdrożenie produkcyjne](#wdrożenie-produkcyjne)

---

## Opis aplikacji

**QuizArena** to platforma społecznościowa do quizów. Użytkownicy przeglądają publiczne quizy,
tworzą własne, grają z timerem i punktacją, zdobywają XP oraz odblokowują osiągnięcia.
Administratorzy zarządzają użytkownikami, quizami i audytem akcji.

### Główne funkcje wg roli

| Rola | Dostępne funkcje |
|------|------------------|
| **Gość** | Landing page, rejestracja, logowanie |
| **Gracz (USER)** | Dashboard, przeglądanie quizów (filtry, wyszukiwarka), tworzenie quizów, gra (API), wyniki, profil, leaderboard, oceny quizów, powiadomienia, przełącznik motywu |
| **Administrator** | Panel admin: użytkownicy, quizy, zmiana statusu (ACTIVE / SUSPENDED / BANNED), ukrywanie quizów, logi akcji (`admin_logs`) |

> **Uwaga:** kolumny `users.role`, `users.status`, `quizzes.status` oraz tabela `admin_logs`
> wymagają uruchomienia skryptu `migrate_admin_system.php` (patrz [Instrukcja uruchomienia](#instrukcja-uruchomienia)).

---

## Technologie i architektura

### Stack technologiczny

| Warstwa | Technologia |
|---------|-------------|
| Backend | PHP 8.3 OOP (bez frameworka), Apache |
| Baza danych | PostgreSQL 17 |
| Frontend | HTML5 (szablony PHP), Vanilla JavaScript, Vanilla CSS (Neon Pulse) |
| Konteneryzacja | Docker + Docker Compose |
| Testy | PHPUnit 11 (Unit + Integration) |
| Autoloader | Composer PSR-4 (`QuizArena\`) |

### Architektura (MVC-lite + Services)

```
┌─────────────────────────────────────────────────────────┐
│                      PRZEGLĄDARKA                       │
│   HTML + CSS (tryb jasny/ciemny) + Vanilla JS           │
│   Fetch API (gra, powiadomienia, oceny quizów)            │
└──────────────────────────┬──────────────────────────────┘
                           │ HTTP (port 8000)
┌──────────────────────────▼──────────────────────────────┐
│                    Apache (docroot: public/)             │
│   Routing plikowy — każdy URL = plik .php w public/      │
└──────────────────────────┬──────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────┐
│                      src/ (PSR-4)                        │
│  Controllers  │  Models  │  Services  │  Helpers         │
│  Auth, Quiz,  │  User,   │  GamePlay  │  Auth, Env       │
│  Admin        │  Quiz,   │  Service   │                  │
│               │  GameSession, ...     │                  │
└──────────────────────────┬──────────────────────────────┘
                           │ PDO (prepared statements)
┌──────────────────────────▼──────────────────────────────┐
│                   PostgreSQL 17                          │
│  users, quizzes, questions, answers, game_sessions,       │
│  game_answers, achievements, notifications, ...         │
└─────────────────────────────────────────────────────────┘
```

### Struktura katalogów

```
QuizArena/
├── database/
│   ├── schema.sql                          # Schemat bazowy (Docker init)
│   ├── views.sql                           # Widoki SQL (v_quiz_stats, ...)
│   ├── seeds/sample_data.sql               # 15 przykładowych quizów
│   └── migrations/
│       └── 001_game_session_security.sql   # is_finished, unique na odpowiedziach
├── docs/
│   └── erd.md                              # Diagram ERD (Mermaid)
├── QuizArena_Screenshots/                  # Zrzuty ekranu do README
├── public/
│   ├── index.php, login.php, register.php, dashboard.php
│   ├── quiz/           browse, create, play, results
│   ├── profile/, leaderboard/, admin/
│   ├── api/
│   │   ├── game/       start.php, answer.php, finish.php
│   │   ├── notifications.php, quiz/rate.php
│   │   └── admin/action.php
│   ├── assets/css/     main.css + style per strona
│   ├── assets/js/      game.js, notifications.js, theme.js, quiz-creator.js
│   └── includes/       theme-head.php, theme-toggle.php
├── src/
│   ├── Controllers/    AuthController, QuizController, AdminController
│   ├── Models/         User, Quiz, GameSession, Achievement, AdminLog, ...
│   ├── Services/       GamePlayService
│   ├── helpers/        Auth.php, Env.php
│   ├── Config/         Database.php
│   └── Exceptions/     GameException.php
├── tests/
│   ├── Unit/           40 testów jednostkowych
│   └── Integration/    8 testów integracyjnych (API gry + DB)
├── migrate_admin_system.php
├── docker-compose.yml
├── Dockerfile
├── phpunit.xml
└── composer.json
```

---

## Instrukcja uruchomienia

### Wymagania

- [Docker Desktop](https://www.docker.com/products/docker-desktop/)
- Git

### 1. Klonowanie repozytorium

```bash
git clone https://github.com/KamilW-git/QuizArena.git
cd QuizArena
```

### 2. Konfiguracja środowiska

```bash
cp .env.example .env
# Domyślne wartości działają z docker-compose.yml
```

### 3. Uruchomienie

```bash
docker compose up --build -d
```

Przy pierwszym uruchomieniu Docker:
- buduje obraz PHP 8.3 + Apache,
- uruchamia PostgreSQL 17,
- montuje `database/schema.sql` do inicjalizacji bazy,
- instaluje zależności Composer (w tym PHPUnit).

**Aplikacja:** [http://localhost:8000](http://localhost:8000)  
**PostgreSQL (host):** `localhost:5433` (w kontenerze: `5432`)

### 4. Migracja systemu administracyjnego

Na świeżej bazie z samego `schema.sql` brakuje kolumn `role`/`status` i tabeli `admin_logs`:

```bash
docker compose exec app php migrate_admin_system.php
```

### 5. Dane przykładowe (opcjonalnie)

- Plik `database/seeds/sample_data.sql` — 15 quizów (wymaga użytkownika `player_one`).
- Skrypt `public/seed.php` — **resetuje całą bazę** i ładuje dane od nowa.  
  **Używać wyłącznie w środowisku deweloperskim** — nie wystawiać publicznie na produkcji.

### 6. Testy

```bash
docker compose exec app composer install
docker compose exec app vendor/bin/phpunit --testdox
```

### 7. Restart z czystą bazą

```bash
docker compose down -v
docker compose up -d --build
docker compose exec app php migrate_admin_system.php
```

### 8. Zatrzymanie

```bash
docker compose down
```

---

## Zmienne środowiskowe

Plik `.env` (wzorzec: `.env.example`):

| Zmienna | Opis | Wartość domyślna |
|---------|------|------------------|
| `DB_HOST` | Host PostgreSQL | `db` |
| `DB_PORT` | Port PostgreSQL | `5432` |
| `DB_NAME` | Nazwa bazy | `quizarena` |
| `DB_USER` | Użytkownik DB | `postgres` |
| `DB_PASS` | Hasło DB | `secret` |

> Na produkcji ustaw silne hasła (`DB_PASS`, `POSTGRES_PASSWORD` w docker-compose)
> i nie commituj pliku `.env`.

---

## Konta testowe

| E-mail / login | Hasło | Rola | Źródło |
|----------------|-------|------|--------|
| `player@quizarena.com` (`player_one`) | `test1234` | USER | `database/schema.sql` / seed |
| `admin@quizarena.com` | `admin1209` | ADMIN | `migrate_admin_system.php` |

Hasła deweloperskie — **nie używać na produkcji**.

---

## Trasy i endpointy

Routing odbywa się **plikowo** — każdy URL wskazuje na plik w katalogu `public/`.
Brak centralnego routera (`routes.php`).

### Strony (HTML)

| URL | Opis | Auth |
|-----|------|------|
| `/` | Landing page | Gość |
| `/login.php`, `/register.php` | Logowanie / rejestracja | Gość |
| `/dashboard.php` | Panel gracza, statystyki | Sesja |
| `/quiz/browse.php` | Katalog quizów (filtry, paginacja) | Sesja |
| `/quiz/create.php` | Kreator quizu (dynamiczny JS) | Sesja |
| `/quiz/play.php?id=` | Ekran gry | Sesja |
| `/quiz/results.php?session=` | Wyniki (tylko własna sesja) | Sesja |
| `/profile/index.php` | Profil, osiągnięcia, historia | Sesja |
| `/leaderboard/index.php` | Ranking graczy | Sesja |
| `/admin/*` | Panel administracyjny | ADMIN |

### API JSON

| Metoda | URL | Auth | Opis |
|--------|-----|------|------|
| `POST` | `/api/game/start.php` | Sesja | `{ quiz_id }` → sesja + pytania (bez poprawnych odpowiedzi) |
| `POST` | `/api/game/answer.php` | Sesja | Odpowiedź na pytanie (+ weryfikacja właściciela sesji) |
| `POST` | `/api/game/finish.php` | Sesja | Zakończenie gry, XP, osiągnięcia (idempotentne) |
| `POST` | `/api/quiz/rate.php` | Sesja | Ocena quizu 1–5 |
| `GET` | `/api/notifications.php` | Sesja | Lista powiadomień |
| `POST` | `/api/notifications/read.php` | Sesja | Oznacz jako przeczytane |
| `POST` | `/api/admin/action.php` | ADMIN + CSRF | Akcje administracyjne |

### Kody odpowiedzi HTTP (API)

| Kod | Kiedy |
|-----|-------|
| `200` | Sukces |
| `302` | Przekierowanie (strony PHP, brak sesji) |
| `400` | Brak wymaganych pól / zły CSRF |
| `401` | Brak sesji (API) |
| `403` | Brak uprawnień / cudza sesja gry |
| `404` | Quiz / pytanie / sesja nie istnieje |
| `405` | Zła metoda HTTP |
| `409` | Sesja zakończona / duplikat odpowiedzi |
| `500` | Błąd serwera |

---

## Flow aplikacji

### Przepływ gry

```
[Gracz]
  1. Browse (/quiz/browse.php) — filtry, wyszukiwarka
  2. Play (/quiz/play.php?id=) — ekran startowy
  3. POST /api/game/start.php → session_id + pytania
  4. Pętla: POST /api/game/answer.php (timer w game.js)
  5. POST /api/game/finish.php → XP + achievements
  6. Redirect /quiz/results.php?session=
  7. Opcjonalnie: ocena quizu (POST /api/quiz/rate.php)
```

### Tworzenie quizu

```
Create (/quiz/create.php)
  → quiz-creator.js (dynamiczne pytania)
  → POST → QuizController::create (transakcja)
  → quiz + questions + answers
  → powiadomienie do pozostałych użytkowników
  → sprawdzenie osiągnięć
```

### Autoryzacja sesji gry

Logika w `GamePlayService` / `GameSession`:
- weryfikacja, że `session_id` należy do zalogowanego użytkownika,
- pytanie musi należeć do quizu sesji,
- `finish()` jest idempotentne (XP tylko raz),
- `results.php` pokazuje wyniki tylko właścicielowi sesji.

---

## Schemat bazy danych

### Diagram ERD

Pełny diagram encji i relacji: **[docs/erd.md](docs/erd.md)** (Mermaid — renderuje się na GitHubie).

```mermaid
erDiagram
    users ||--o{ quizzes : tworzy
    users ||--o{ game_sessions : gra
    users ||--o{ user_achievements : odblokowuje
    quizzes ||--o{ questions : zawiera
    questions ||--o{ answers : ma
    game_sessions ||--o{ game_answers : rejestruje
    achievements ||--o{ user_achievements : przyznane
    users ||--o{ quiz_ratings : ocenia
    quizzes ||--o{ quiz_ratings : otrzymuje
    users ||--o{ notifications : otrzymuje
    users ||--o{ admin_logs : "admin"
```

### Relacje (uproszczone)

```
users ──< quizzes ──< questions ──< answers
  │         │
  │         └──< quiz_ratings >── users
  │
  ├──< game_sessions ──< game_answers >── questions
  ├──< user_achievements >── achievements
  ├──< notifications
  └──< admin_logs          (po migracji admin)

users.role, users.status    (po migrate_admin_system.php)
quizzes.status              (PUBLISHED / HIDDEN)
game_sessions.is_finished   (migracja 001)
```

### Tabele

| Tabela | Opis |
|--------|------|
| `users` | Konta, XP, (role, status po migracji) |
| `quizzes` | Quizy użytkowników |
| `questions` | Pytania (4 odpowiedzi, timer) |
| `answers` | Odpowiedzi (index 0–3) |
| `quiz_ratings` | Oceny 1–5 (UNIQUE user + quiz) |
| `game_sessions` | Sesje gry |
| `game_answers` | Odpowiedzi w sesji (UNIQUE session + question) |
| `achievements` | Definicje osiągnięć |
| `user_achievements` | Odblokowane osiągnięcia |
| `notifications` | Powiadomienia użytkownika |
| `admin_logs` | Audyt akcji admina (po migracji) |

### Widoki SQL

Plik [`database/views.sql`](database/views.sql) — uruchom po `schema.sql`:

```bash
cat database/views.sql | docker compose exec -T db psql -U postgres -d quizarena
```

| Widok | Opis | Użycie w aplikacji |
|-------|------|-------------------|
| `v_quiz_stats` | Liczba pytań, gier, średnia ocena | Katalog quizów (`browse.php`) |
| `v_player_leaderboard` | XP, wynik, accuracy gracza | Ranking (`leaderboard/index.php`) |
| `v_session_summary` | Sesja + użytkownik + quiz | Wyniki, profil, dashboard |
| `v_user_achievement_progress` | Osiągnięcia z flagą unlock | Profil użytkownika |

### Elementy bazy — stan projektu

| Element | Status |
|---------|--------|
| Klucze obce + CASCADE | ✅ `database/schema.sql` |
| Indeksy | ✅ m.in. quiz_id, session_id, user_id |
| Widoki SQL (`CREATE VIEW`) | ✅ `database/views.sql` (4 widoki) |
| Triggery / funkcje PL/pgSQL | ❌ brak |
| Transakcje w aplikacji | ✅ m.in. tworzenie quizu, finish sesji (`FOR UPDATE`) |

---

## Bezpieczeństwo

### Zaimplementowane

| Obszar | Implementacja |
|--------|---------------|
| SQL Injection | Prepared statements (PDO) w modelach i API |
| Hasła | `password_hash` / `password_verify` (bcrypt) |
| CSRF | Tokeny na formularzach i akcjach admina (`Auth::validateCsrfToken`) |
| XSS (PHP) | `htmlspecialchars()` w szablonach |
| IDOR (gra) | Weryfikacja właściciela sesji w `GamePlayService` |
| Panel admin | `Auth::requireAdmin()` |
| Audyt admina | Tabela `admin_logs` |

---

## Widoki aplikacji — galeria

Zrzuty ekranu w katalogu [`QuizArena_Screenshots/`](QuizArena_Screenshots/).

Przełącznik motywu: ikona ☀️ / 🌙 w prawym górnym rogu (obok dzwonka powiadomień).
Wybór zapisywany w `localStorage` (`quizarena-theme`) — przetrwa odświeżenie strony.

---

### 🖥️ Desktop — tryb jasny

| Dashboard | Przeglądanie quizów | Tworzenie quizu |
|:---:|:---:|:---:|
| ![Dashboard light](QuizArena_Screenshots/dashboard_desktop_light.png) | ![Browse light](QuizArena_Screenshots/browse_desktop_light.png) | ![Create light](QuizArena_Screenshots/createquiz_desktop_light.png) |

| Profil | Leaderboard | Powiadomienia |
|:---:|:---:|:---:|
| ![Profile light](QuizArena_Screenshots/profile_desktop_light.png) | ![Leaderboard light](QuizArena_Screenshots/leaderbeoard_desktop_light.png) | ![Notifications light](QuizArena_Screenshots/dashboard_notifications_desktop_light.png) |

---

### 🖥️ Desktop — tryb ciemny

| Dashboard | Przeglądanie quizów | Profil |
|:---:|:---:|:---:|
| ![Dashboard dark](QuizArena_Screenshots/dashboard_desktop_dark.png) | ![Browse dark](QuizArena_Screenshots/browse_desktop_dark.png) | ![Profile dark](QuizArena_Screenshots/profile_desktop_dark.png) |

| Leaderboard | Przed grą | Gra — poprawna odpowiedź |
|:---:|:---:|:---:|
| ![Leaderboard dark](QuizArena_Screenshots/leaderboard_desktop_dark.png) | ![Pregame dark](QuizArena_Screenshots/pregame_desktop_dark.png) | ![Game correct dark](QuizArena_Screenshots/gamecorrect_desktop_dark.png) |

| Gra — błędna odpowiedź | Logowanie | Rejestracja |
|:---:|:---:|:---:|
| ![Game incorrect dark](QuizArena_Screenshots/gameincorrect_desktop_dark.png) | ![Login dark](QuizArena_Screenshots/login_desktop_dark.png) | ![Register dark](QuizArena_Screenshots/register_desktop_dark.png) |

| Landing (strona główna) | Landing — sekcja 2 |
|:---:|:---:|
| ![Landing](QuizArena_Screenshots/landing_desktop.png) | ![Landing 2](QuizArena_Screenshots/landing2_desktop.png) |

---

### 📱 Mobile — tryb jasny

| Dashboard | Przeglądanie | Profil |
|:---:|:---:|:---:|
| ![Dashboard mobile light](QuizArena_Screenshots/dashboard_mobile_light.png) | ![Browse mobile light](QuizArena_Screenshots/browse_mobile_light.png) | ![Profile mobile light](QuizArena_Screenshots/profile_mobile_light.png) |

| Leaderboard | Wyniki gry | Logowanie |
|:---:|:---:|:---:|
| ![Leaderboard mobile light](QuizArena_Screenshots/leaderboard_mobile_light.png) | ![Summary mobile light](QuizArena_Screenshots/summary_mobile_light.png) | ![Login mobile light](QuizArena_Screenshots/login_mobile_light.png) |

| Rejestracja | Landing |
|:---:|:---:|
| ![Register mobile light](QuizArena_Screenshots/register_mobile_light.png) | ![Landing mobile](QuizArena_Screenshots/landing_mobile.png) |

---

### 📱 Mobile — tryb ciemny

| Logowanie | Rejestracja | Wyniki gry |
|:---:|:---:|:---:|
| ![Login mobile dark](QuizArena_Screenshots/login_mobile_dark.png) | ![Register mobile dark](QuizArena_Screenshots/register_mobile_dark.png) | ![Summary mobile dark](QuizArena_Screenshots/summary_mobile_dark.png) |

| Alert wyjścia z gry | Landing — widok 2 | Landing — widok 3 |
|:---:|:---:|:---:|
| ![Exit game alert](QuizArena_Screenshots/exit_game_alert_mobile_dark.png) | ![Landing mobile 2](QuizArena_Screenshots/landing_2_mobile.png) | ![Landing mobile 3](QuizArena_Screenshots/landing_3_mobile.png) |

---

### 🛡️ Panel administracyjny

| Widok 1 | Widok 2 |
|:---:|:---:|
| ![Admin panel 1](QuizArena_Screenshots/admin_panel1.png) | ![Admin panel 2](QuizArena_Screenshots/admin_panel2.png) |

| Widok 3 | Widok 4 |
|:---:|:---:|
| ![Admin panel 3](QuizArena_Screenshots/admin_panel3.png) | ![Admin panel 4](QuizArena_Screenshots/admin_panel4.png) |

---

### Tryb jasny / ciemny — implementacja

Motyw sterowany atrybutem `data-theme` na `<html>`, zapamiętywany w `localStorage`.
Skrypt anty-FOUC w `<head>` ustawia motyw przed renderem strony.

```javascript
// public/assets/js/theme.js
localStorage.setItem('quizarena-theme', 'light' | 'dark');
document.documentElement.setAttribute('data-theme', theme);
```

```css
/* public/assets/css/main.css */
:root { --bg: #0d0e12; --text: #ffffff; /* ... */ }
[data-theme="light"] { --bg: #f0f2f7; --text: #12131a; /* ... */ }
```

---

## Scenariusz testowy

Krok po kroku — ręczna weryfikacja aplikacji:

1. **Uruchomienie:** `docker compose up --build -d` → otwórz `http://localhost:8000`.
2. **Landing:** strona główna, responsywność (< 768 px → układ mobile).
3. **Motyw:** kliknij ☀️/🌙 w navbarze → odśwież stronę → motyw zachowany.
4. **Rejestracja:** hasło krótsze niż 8 znaków → błąd walidacji.
5. **Rejestracja poprawna** → przekierowanie na dashboard.
6. **Logowanie:** `player_one` / `test1234` → dashboard ze statystykami.
7. **Browse:** filtr kategorii, sortowanie, paginacja.
8. **Gra:** wybierz quiz → start → odpowiedzi z timerem → finish → wyniki + XP.
9. **Profil:** statystyki, osiągnięcia, historia gier.
10. **Leaderboard:** ranking z filtrem okresu.
11. **Create quiz:** dodaj pytania (min. 1, dokładnie 4 odpowiedzi) → zapisz.
12. **Powiadomienia:** dzwonek w navbarze → lista + „Mark all as read”.
13. **Admin:** `php migrate_admin_system.php` → login `admin@quizarena.com` / `admin1209` → panel admin.
14. **IDOR:** próba dostępu do cudzej sesji w API → **403**.
15. **PHPUnit:** `vendor/bin/phpunit --testdox` → 48 testów OK.

---

## Uruchamianie testów

### Wszystkie testy (48)

```bash
docker compose exec app composer install      # raz
docker compose exec app vendor/bin/phpunit --testdox
```

### Tylko testy jednostkowe (40)

```bash
docker compose exec app vendor/bin/phpunit --testsuite Unit --testdox
```

### Tylko testy integracyjne (8)

```bash
docker compose exec app vendor/bin/phpunit --testsuite Integration --testdox
```

### Co jest pokryte testami

| Suite | Pliki | Zakres |
|-------|-------|--------|
| **Unit** (40) | `AuthTest`, `EnvTest`, `GameExceptionTest`, `AuthControllerValidationTest`, `QuizControllerValidationTest`, `GameSessionTest`, `GamePlayServiceUnitTest`, `UserTest` | Sesja, CSRF, walidacja formularzy, autoryzacja sesji (mock PDO), punktacja |
| **Integration** (8) | `GamePlayServiceTest` | IDOR, idempotencja `finish`, ownership sesji, duplikat odpowiedzi (PostgreSQL) |

> Brak testów smoke Bash/curl — planowane jako rozszerzenie.

---

## Checklista wymagań

### Funkcjonalność

- [x] Rejestracja użytkownika z walidacją
- [x] Logowanie / wylogowanie z sesją
- [x] Role USER / ADMIN + status konta (ACTIVE / SUSPENDED / BANNED)
- [x] Przeglądanie quizów (filtry, wyszukiwarka, paginacja)
- [x] Tworzenie quizów (dynamiczny kreator JS)
- [x] Silnik gry (API start / answer / finish)
- [x] XP, poziomy, osiągnięcia
- [x] Leaderboard z filtrem okresu
- [x] Profil użytkownika + historia gier
- [x] Oceny quizów (1–5 gwiazdek)
- [x] Powiadomienia (polling co 30 s)
- [x] Panel administracyjny + audit log
- [x] Tryb jasny / ciemny + `localStorage`
- [x] Responsywny design (mobile bottom nav, breakpoints)
- [x] PHPUnit — 40 unit + 8 integracyjnych

### Baza danych

- [x] Relacje 1:N (users → quizzes → questions → answers)
- [x] Relacja M:N user ↔ achievement (`user_achievements`)
- [x] Klucze obce z `ON DELETE CASCADE`
- [x] Indeksy na kluczowych kolumnach
- [x] Ograniczenia CHECK (difficulty, rating, chosen_index)
- [x] UNIQUE na `(session_id, question_id)` w `game_answers`
- [x] Seed z przykładowymi quizami
- [x] Widoki SQL (`v_quiz_stats`, `v_player_leaderboard`, `v_session_summary`, `v_user_achievement_progress`)
- [ ] Triggery / funkcje PL/pgSQL

### Kod i architektura

- [x] PHP 8.3 OOP, PSR-4 autoloading
- [x] Warstwa Models + Controllers + Services
- [x] Docker + Docker Compose
- [x] Fetch API (gra, powiadomienia, oceny)
- [x] CSRF na formularzach
- [ ] Centralny router (obecnie: routing plikowy)
- [ ] CI/CD
- [ ] Rate limiting

---

## Wdrożenie produkcyjne

1. Zainstaluj **Docker** i **Docker Compose** na serwerze.
2. Sklonuj repozytorium, skonfiguruj `.env` z **silnymi hasłami**.
3. Uruchom: `docker compose up -d --build`
4. Wykonaj migrację: `migrate_admin_system.php`
5. Skonfiguruj **reverse proxy** (Nginx / Traefik) z **SSL/TLS** przed kontenerem `app`.
6. **Usuń lub zabezpiecz** skrypty `public/seed.php`, `public/migrate_*.php`.
7. Nie wystawiaj portu PostgreSQL (`5433`) publicznie.

---

## 🤝 Contributing

Issues i pull requesty są mile widziane. Przed PR uruchom testy:

```bash
docker compose exec app vendor/bin/phpunit --testdox
```
