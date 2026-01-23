Prophetic Life Embassy — Church Management System
Technical Documentation

1️⃣ System Overview
The Prophetic Life Embassy CMS is a web-based church management system designed to manage:

- Attendance tracking
- First-time visitors and partners
- Expenses and finances
- Roles and permissions
- Ministry departments
- Appointments and counseling scheduling
- Membership records (adults + children)
- Ministry and volunteer management

System Architecture:

- Frontend: Vue 3, Pinia (state management), Bootstrap 5 (UI), Axios for API calls
- Backend: Laravel 11 API, Sanctum for authentication
- Database: MySQL / PostgreSQL
- Communication: REST API (JSON)
- Deployment: Frontend SPA separated from backend API

2️⃣ Architecture Diagram
+--------------------+ REST API +------------------+
| Vue 3 SPA | <----------------> | Laravel 11 API |
| Bootstrap 5 UI | | Controllers |
| Pinia State Mgmt | | Models |
+--------------------+ +------------------+
| |
v v
Browser Database

3️⃣ Frontend Structure
Folder Layout:
src/
├─ assets/ # Images, CSS, fonts
├─ components/ # Reusable UI components
│ ├─ Sidebar.vue
│ ├─ Navbar.vue
│ └─ Card.vue
├─ views/ # Pages
│ ├─ SignIn.vue
│ ├─ Dashboard.vue
│ ├─ Attendance.vue
│ └─ Visitors.vue
├─ store/ # Pinia state management
│ └─ user.js
├─ router/
│ └─ index.js
├─ App.vue
└─ main.js

Frontend Features:

- Authentication: Sign-in page (Vue form + Pinia + API)
- Dashboard: Shows attendance, visitors, expenses, volunteers
- Attendance Page: Record and view weekly/monthly totals
- Visitors Page: Register first-time visitors & partners
- Volunteer Management: Assign tasks and track attendance
- Bootstrap 5 UI: Responsive layout for desktop and mobile

4️⃣ Pinia Store Example
// store/user.js
import { defineStore } from 'pinia'
import axios from 'axios'

export const useUserStore = defineStore('user', {
state: () => ({ user: null, token: localStorage.getItem('token') || '' }),
actions: {
async login(email, password) {
const res = await axios.post('http://localhost:8000/api/login', { email, password })
this.token = res.data.token
this.user = res.data.user
localStorage.setItem('token', res.data.token)
},
logout() {
this.user = null
this.token = ''
localStorage.removeItem('token')
}
}
})

5️⃣ Backend Structure
Folder Layout (Laravel 11):
app/
├─ Http/
│ ├─ Controllers/
│ │ ├─ AuthController.php
│ │ ├─ AttendanceController.php
│ │ ├─ VisitorController.php
│ │ └─ VolunteerController.php
├─ Models/
│ ├─ User.php
│ ├─ Attendance.php
│ ├─ Visitor.php
│ └─ Volunteer.php
routes/
├─ api.php # API routes for frontend

Key Backend Features:

- Authentication: Laravel Sanctum for SPA token-based auth
- Attendance: CRUD for service attendance
- Visitors: Track first-time visitors and partners
- Expenses: Record church expenses
- Volunteers & Ministries: Assign tasks and track participation
- Roles & Permissions: Different access levels for Pastor, PR, Finance, etc.

6️⃣ API Endpoints (Sample)
Endpoint | Method | Description
--- | --- | ---
/api/login | POST | Sign-in user, returns token
/api/user | GET | Fetch authenticated user info
/api/attendance | GET | Fetch attendance records
/api/attendance | POST | Create new attendance record
/api/visitors | GET | Fetch first-time visitors
/api/visitors | POST | Register a new visitor
/api/expenses | GET | Fetch expenses
/api/expenses | POST | Add a new expense
/api/volunteers | GET | Fetch volunteers
/api/volunteers | POST | Assign volunteer to a ministry/task

All API responses are JSON; frontend sends Bearer token in headers for authenticated endpoints.

7️⃣ Authentication Flow

- User submits email + password on Sign-In page
- Frontend calls /api/login
- Backend validates and returns a token + user info
- Frontend stores token in localStorage and sets user in Pinia
- Token used for all subsequent API requests (Authorization: Bearer <token>)

8️⃣ Attendance Feature Flow

- Navigate to Attendance page
- Select service type (Sunday/Wednesday)
- Enter number of attendees
- Save entry → API stores record
- Dashboard shows totals:
  - Weekly total
  - Monthly total
  - Year-to-date total

9️⃣ Visitor & Partner Feature Flow

- Navigate to Visitors page
- Enter visitor name, phone, category (general/partner), visit date
- Save → API stores record
- System schedules monthly SMS to partners
- Dashboard shows total first-time visitors & partners

🔟 Dashboard Overview
Metrics Displayed:

- Attendance this week & last week
- Total first-time visitors this month
- Partner count
- Monthly expenses
- Volunteers assigned for upcoming service
- Upcoming 10 appointments

11️⃣ Deployment Notes

- Frontend: Build Vue SPA (npm run build) → deploy to Nginx / Apache / Vercel / Netlify
- Backend: Laravel API → deploy to PHP 8.2+ server
- CORS: Enable CORS for frontend domain
- Environment: Separate .env for frontend API URL and backend DB credentials

12️⃣ Recommendations

- Version Control: GitHub or GitLab with main & dev branches
- Testing: Use Laravel Feature tests for APIs; Vue unit tests for components
- Backup: Daily DB backup & log storage
- Security:
  - Sanctum token auth
  - Role-based access control for sensitive endpoints

  13️⃣ Backend Deployment Playbook
  - Hosting: Render free Web Service pointing to repository backend/ directory
  - Build: composer install --no-dev --optimize-autoloader, php artisan config:cache, php artisan route:cache, php artisan view:cache
  - Start: php artisan serve --host 0.0.0.0 --port 8000 with PORT=8000 in environment
  - Database: Supabase Postgres; keep local MySQL if preferred but ensure php-pgsql is available in production
  - Environment: Render env vars include APP_NAME=Prophetic Life Embassy, APP_ENV=production, APP_DEBUG=false, APP_URL=https://your-api.onrender.com, FRONTEND_URL=https://your-app.vercel.app, DB_CONNECTION=pgsql, DB_HOST/PORT/NAME/USER/PASSWORD from Supabase, APP_KEY=base64:..., CACHE_DRIVER=file, SESSION_DRIVER=file
  - Security: Update config/cors.php allowed_origins to list https://your-app.vercel.app and http://localhost:5173; set supports_credentials true only when using Sanctum cookies; add SANCTUM_STATEFUL_DOMAINS=your-app.vercel.app,localhost,localhost:5173 when cookie mode is active
  - Reliability: Add GET /api/health route returning {"status":"ok"} for warm-up checks; after first deploy run php artisan migrate --force and php artisan db:seed --force via Render shell

  14️⃣ Frontend Deployment Playbook
  - Hosting: Vercel project rooted at frontend/ directory
  - Environment: VITE_API_BASE_URL=https://your-api.onrender.com configured in Vercel project settings
  - HTTP client: axios.defaults.baseURL = import.meta.env.VITE_API_BASE_URL so production calls use the Render API domain without hardcoded localhost
  - Build: Standard Vite build (npm install, npm run build) handled by Vercel defaults unless overridden
  - Local dev: Keep http://localhost:5173 in CORS origins to support Vite dev server proxying to backend

  15️⃣ Demo Hosting (Free Tier)
  - Hosting: Frontend on Vercel (Vue 3 SPA); backend on Render (Laravel 11 API); database on Supabase (PostgreSQL)
  - Environment: Frontend VITE_API_BASE_URL=https://<render-api-domain>; backend APP_URL=https://<render-api-domain>, FRONTEND_URL=https://<vercel-domain>, DB credentials sourced from Supabase
  - Caveats: Render free tier sleeps when idle; warm backend via /api/health and a login before stakeholders join
  - Scheduling: Demo mode stores pending jobs instead of triggering external schedulers; plan paid upgrades for production queue workers and cron

  16️⃣ Demo Checklist (T-10 Minutes)
  - Ping https://your-api.onrender.com/api/health to wake backend
  - Log in as demo admin account to refresh Sanctum token or Bearer auth flow
  - Open Dashboard, Attendance, Visitors, Expenses views to verify metrics hydrate
  - Spot-check create/edit on at least one record per module to ensure migrations and seed data loaded
  - Confirm Supabase connection remains active and Render logs are clean of connection errors
