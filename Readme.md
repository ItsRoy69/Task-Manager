# Task Manager Application

A full-stack task management application built with React, Laravel, and Node.js.


[screen-capture (14).webm](https://github.com/user-attachments/assets/932271a2-5ff4-49ca-8bea-46508cee2ef6)

## Project Structure

The project consists of three main components:

1. **Frontend** (React.js)
   - Built with React 19
   - Uses React Router for navigation
   - Bootstrap for styling
   - Axios for API communication

2. **Backend** (Laravel)
   - RESTful API endpoints
   - Handles user authentication and task management
   - Runs on port 8000


## Prerequisites

- Node.js (v14 or higher)
- PHP (v8.0 or higher)
- Composer
- MongoDB
- npm or yarn

## Installation

### Frontend Setup
```bash
cd frontend
npm install
npm start
```

### Backend Setup
```bash
cd backend
composer install
php artisan serve
```

## Running the Application

1. Start the Backend:
   ```bash
   cd backend
   php artisan serve
   ```

2. Start the Frontend:
   ```bash
   cd frontend
   npm start
   ```

The application will be available at:
- Frontend: http://localhost:3000
- Backend API: http://localhost:8000

## Features

- User Authentication (Register/Login)
- Task Management (Create, Read, Update, Delete)
- Real-time logging
- Responsive Design
- Toast Notifications

## Technologies Used

- **Frontend**:
  - React.js
  - React Router
  - Bootstrap
  - Axios
  - React Toastify

- **Backend**:
  - Laravel
  - PHP
  - RESTful API
