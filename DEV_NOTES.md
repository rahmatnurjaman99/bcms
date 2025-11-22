# Project Execution Notes

-   **Frontend npm/yarn/pnpm commands:** run them inside the `web` container. Example:

    ```bash
    cd ~/microsites/php84
    docker compose exec web bash -lc "cd /app/bcms/frontend && npm run dev"
    ```

    Replace `npm run dev` with the command you need (e.g., `npm run check`, `npm install`).

-   **Laravel/Lighthouse commands:** also run inside the `web` container. Examples:

    ```bash
    cd ~/microsites/php84
    docker compose exec web bash -lc "cd /app/bcms && php artisan migrate"
    docker compose exec web bash -lc "cd /app/bcms && php artisan lighthouse:clear-cache"
    ```

-   Do chown -R 1000:1000 if you create file from docker container so it can be editable
Keep these in mind for new sessions so commands run in the right environment. This document is authoritative for the AI agent—always read and follow it before making changes.

## High-Level Plan: School Management System (sync with `TASKS.md`)

Mirror the living plan below with `TASKS.md`; update both together.

-   **Immediate Tasks**
    -   ✅ Update `ActivityQuery` for `createdFrom`/`createdTo`.
    -   ⏳ Verify Activity page filters with the new date range.

-   **Core Foundation**
    -   **User Management**
        -   ✅ Users CRUD.
        -   ✅ Roles & Permissions.
        -   ⏳ User Profile & Settings.
    -   **Academic Structure**
        -   ✅ Academic Years.
        -   ⏳ Classes & Sections (Models, Migration, GraphQL, UI).
        -   ⏳ Subjects (Models, Migration, GraphQL, UI).
        -   ⏳ Timetables (Models, Migration, GraphQL, UI).

-   **Human Resources**
    -   Employee Management: model/migration, directory/search, attendance & leave, payroll basics (all pending).

-   **Student Management**
    -   Students & Guardians: models/migrations, enrollment, profile UI, guardian portal (all pending).

-   **Academic Operations**
    -   Attendance: model, teacher UI, reports (pending).
    -   Gradebook & Assessments: assessment types, grade entry, report cards (pending).

-   **Finance**
    -   Fees & Billing: fee structures, invoices/payments, receipts, financial reports (pending).

-   **Communication & Misc**
    -   Announcements: model + notice board UI (pending).
    -   File Management: document uploads (pending).

### Technical Principles

-   **Backend (Laravel + Lighthouse GraphQL)**
    -   Sanctum auth with role/permission checks; keep audit logging for critical actions.
    -   Provide GraphQL queries/mutations per domain object with validation + pagination.
    -   Run migrations/seeders for baseline data; clear Lighthouse cache after schema changes.

-   **Frontend (SvelteKit + shadcn-svelte + Tailwind)**
    -   Auth flow: login/password reset, `(app)` layout guards, tokens stored securely.
    -   Navigation must cover dashboard, user management, academic years, students, classes, attendance, grades, finance, announcements.
    -   Screens should align with the task list above (Academic Years, Users/Roles/Permissions, etc.) and surface API errors clearly.
    -   Reuse shadcn components (button, input, select, checkbox, badge, dropdown, card, skeleton, textarea, etc.).
    -   All Svelte code uses Svelte 5 runes (`$state`, `$derived`, `$effect`, …); avoid legacy stores unless bridging existing code.
    -   Data fetching: use load functions with SSR-disabled `(app)` layout; pass `fetchImpl` to API helpers; optimistic updates only when safe.
    -   Prefer slide panels for simple CRUD forms.
-   **Implementation Guardrails**
    -   Every component/screen that fetches data must include a skeleton placeholder during loading.
    -   All submit buttons should prevent double clicks (disable while processing) and show an inline loader/spinner.
    -   Calendar/date pickers must use the shared calendar component; prefer shadcn-svelte components when available instead of rolling custom implementations.
    -   Any icon-only button needs a popover tooltip or accessible label describing the action.
    -   If a shadcn-svelte component exists, install/import it instead of creating a manual alternative (e.g., popover, calendar).
    -   After changing any UI, run `npm run check` inside the `frontend` directory (via the web container) before handing work back.
