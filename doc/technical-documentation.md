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
