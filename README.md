# Real-Time Live Poll Platform (Laravel + Core PHP Rules)

This project outlines a full implementation plan and starter code for a real-time polling platform with IP-based voting restrictions, admin moderation, and live updates via AJAX.

## Step-by-step implementation plan (4 Modules)

### Module 1: Authentication & Poll Display (1 Hour)
1. **Set up Laravel authentication (login only).**
   - Use a simple login form.
   - Store the user ID in session.
2. **Create tables for polls and options.**
   - `polls`: question + status.
   - `poll_options`: multiple options per poll.
3. **Create poll list + detail screens.**
   - Only show active polls.
   - Use AJAX to load a poll into the right panel with no page reload.

### Module 2: IP-Restricted Voting (1 Hour)
1. **Capture vote metadata**: poll ID, option ID, IP address, timestamp.
2. **Block repeat votes per IP per poll** using a unique index + check in controller.
3. **Submit votes over AJAX** and show error/success messages without reload.

### Module 3: Real-Time Results (1 Hour)
1. **Expose a results endpoint** returning option counts.
2. **Poll results every second** (AJAX interval) to update counts live.

### Module 4: Admin Release + Vote Rollback (1 Hour)
1. **Show list of current IP votes** to admin.
2. **Release an IP** → delete vote and insert into `vote_histories`.
3. **Allow re-vote after release** and update history to show old vote + new vote.

---

## Project Structure
- `routes/web.php` — Routing for login, polls, voting, admin tools.
- `app/Http/Controllers/*` — Authentication, polling, admin moderation.
- `database/migrations/*` — MySQL schema (polls, options, votes, history).
- `resources/views/*` — Blade views, Bootstrap UI, jQuery AJAX.

## Setup
1. **Install dependencies**
   ```bash
   composer install
   npm install
   npm run build
   ```
2. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. **Run migrations & seed**
   ```bash
   php artisan migrate --seed
   ```
4. **Login**
   - Email: `admin@example.com`
   - Password: `password123`

## AJAX Flow Summary
- **Poll list → poll detail** loads via `GET /polls/{id}` (partial HTML).
- **Vote submit** via `POST /polls/{id}/vote`.
- **Results** via `GET /polls/{id}/results` every 1s.
- **Admin release** via `POST /admin/polls/{id}/release`.
- **History** via `GET /admin/polls/{id}/history` every 1s.

## Notes
- IP restriction uses **unique index + controller check**.
- Vote history is preserved in `vote_histories` with old and new vote info.
- All interactions are AJAX-based; no page reload required.
