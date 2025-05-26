# Recruiter Assistant

A Laravel web application designed to automate and streamline common recruitment tasks, providing AI-driven insights and integrations with existing platforms.

## Project Overview

The Recruiter's Assistant Tool is designed to automate and streamline common recruitment tasks, providing AI-driven insights and integrations with existing platforms (Claap, Workable, BrightHire). The tool reduces manual effort by generating research, job descriptions, search strings, screening questions, candidate analyses, and submission materials—allowing recruiters to focus on strategic, high-value activities.

The solution comprises five main modules:

1. Project Preparation
2. Job Description Generator
3. CV Analyzer
4. Profile Creation
5. Submission to Client

## Technical Stack

- **Backend**: Laravel 12
- **Database**: PostgreSQL
- **Frontend**: Tailwind CSS
- **Authentication**: Laravel Breeze
- **AI Integration**: OpenAI, Anthropic, Google AI

## Setup Instructions

### Prerequisites

- PHP 8.2 or higher
- Composer
- PostgreSQL
- Node.js and npm

### Installation

1. Clone the repository:
   ```
   git clone <repository-url>
   cd recruiter-assistant
   ```

2. Install PHP dependencies:
   ```
   composer install
   ```

3. Install JavaScript dependencies:
   ```
   npm install
   ```

4. Create a copy of the `.env.example` file:
   ```
   cp .env.example .env
   ```

5. Generate an application key:
   ```
   php artisan key:generate
   ```

6. Configure the database connection in the `.env` file:
   ```
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=recruiter_assistant
   DB_USERNAME=postgres
   DB_PASSWORD=your_password
   ```

7. Run the database migrations:
   ```
   php artisan migrate
   ```

8. Seed the database with initial data:
   ```
   php artisan db:seed
   ```

9. Create storage link for file uploads:
   ```
   php artisan storage:link
   ```

10. Build the frontend assets:
    ```
    npm run build
    ```

11. Start the development server:
    ```
    php artisan serve
    ```

12. Access the application at `http://localhost:8000`
