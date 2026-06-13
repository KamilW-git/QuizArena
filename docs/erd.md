# QuizArena — diagram ERD

Diagram encji i relacji bazy danych PostgreSQL.

> Kolumny `users.role`, `users.status`, `quizzes.status` oraz tabela `admin_logs`
> pochodzą z `migrate_admin_system.php` (poza bazowym `schema.sql`).

## Diagram (Mermaid)

```mermaid
erDiagram
    users ||--o{ quizzes : "tworzy"
    users ||--o{ quiz_ratings : "ocenia"
    users ||--o{ game_sessions : "gra"
    users ||--o{ user_achievements : "odblokowuje"
    users ||--o{ notifications : "otrzymuje"
    users ||--o{ admin_logs : "wykonuje (admin)"

    quizzes ||--o{ questions : "zawiera"
    quizzes ||--o{ quiz_ratings : "otrzymuje"
    quizzes ||--o{ game_sessions : "rozgrywany_w"

    questions ||--o{ answers : "ma"
    questions ||--o{ game_answers : "odpowiedź_w"

    game_sessions ||--o{ game_answers : "rejestruje"

    achievements ||--o{ user_achievements : "przyznane"

    users {
        uuid id PK
        varchar username UK
        varchar email UK
        varchar password_hash
        text avatar_url
        int xp
        varchar role "po migracji admin"
        varchar status "po migracji admin"
        timestamp created_at
    }

    quizzes {
        uuid id PK
        uuid user_id FK
        varchar title
        varchar category
        smallint difficulty
        boolean is_public
        varchar status "po migracji admin"
        timestamp created_at
    }

    questions {
        uuid id PK
        uuid quiz_id FK
        text content
        smallint correct_answer
        smallint time_limit
        smallint order_index
    }

    answers {
        uuid id PK
        uuid question_id FK
        text content
        smallint index
    }

    quiz_ratings {
        uuid id PK
        uuid user_id FK
        uuid quiz_id FK
        smallint rating
        timestamp created_at
    }

    game_sessions {
        uuid id PK
        uuid user_id FK
        uuid quiz_id FK
        int score
        smallint correct_count
        int time_taken
        boolean is_finished
        timestamp completed_at
    }

    game_answers {
        uuid id PK
        uuid session_id FK
        uuid question_id FK
        smallint chosen_index
        boolean is_correct
        smallint time_spent
    }

    achievements {
        uuid id PK
        varchar key UK
        varchar name
        text description
        int xp_reward
    }

    user_achievements {
        uuid user_id PK_FK
        uuid achievement_id PK_FK
        timestamp unlocked_at
    }

    notifications {
        uuid id PK
        uuid user_id FK
        varchar title
        text message
        varchar type
        boolean is_read
        timestamp created_at
    }

    admin_logs {
        uuid id PK
        uuid admin_id FK
        varchar action
        varchar target_type
        uuid target_id
        text details
        timestamp created_at
    }
```

## Relacje tekstowo

```
users (1) ─────< quizzes                    tworzenie quizów
users (1) ─────< quiz_ratings               oceny 1–5 (UNIQUE user+quiz)
users (1) ─────< game_sessions              rozgrywki
users (1) ─────< user_achievements >──── (N) achievements
users (1) ─────< notifications
users (1) ─────< admin_logs                 audyt akcji admina

quizzes (1) ───< questions ───< answers     4 odpowiedzi na pytanie
quizzes (1) ───< game_sessions
quizzes (1) ───< quiz_ratings

game_sessions (1) ───< game_answers >── questions
                         UNIQUE (session_id, question_id)
```

## Kardynalność

| Relacja | Typ |
|---------|-----|
| users → quizzes | 1:N |
| users → user_achievements → achievements | N:M (PK złożony) |
| quizzes → questions → answers | 1:N → 1:N (4 odpowiedzi) |
| users + quizzes → game_sessions | N:1 + N:1 |
| game_sessions → game_answers | 1:N |

## Widoki SQL

Zdefiniowane w [`database/views.sql`](../database/views.sql):

| Widok | Opis |
|-------|------|
| `v_quiz_stats` | Statystyki quizów (pytania, gry, oceny) |
| `v_player_leaderboard` | Ranking graczy (XP, wynik, accuracy) |
| `v_session_summary` | Sesje gry z danymi użytkownika i quizu |
| `v_user_achievement_progress` | Osiągnięcia z flagą odblokowania |

### Instalacja widoków

```bash
# Windows PowerShell
Get-Content -Raw "database\views.sql" | docker compose exec -T db psql -U postgres -d quizarena

# Linux / macOS
cat database/views.sql | docker compose exec -T db psql -U postgres -d quizarena
```
