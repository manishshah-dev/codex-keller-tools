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

## Implemented Features

This implementation focuses on the Job Description Generator module as a proof of concept. The following features have been implemented:

- AI-powered job description generation using OpenAI, Anthropic, or Google AI
- Manual job description creation and editing
- Qualifying questions management
- Export functionality for job descriptions (PDF, DOCX, TXT)
- Job description versioning
- AI provider and prompt management

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

### AI Provider Configuration

To use the AI-powered job description generation feature, you need to configure at least one AI provider:

1. Log in to the application
2. Navigate to AI Settings
3. Add a new AI provider with your API key
4. Select the available models and capabilities

## Usage

### Creating a Job Description

1. Create a new project with basic information
2. Navigate to the Job Descriptions section
3. Choose between AI-powered generation or manual creation
4. Fill in the required fields
5. Add qualifying questions if needed
6. Save and publish the job description
7. Export the job description in your preferred format (PDF, DOCX, TXT)

## Implementation Details

### Database Schema

The application uses the following main tables:

- `projects`: Stores project information and metadata
- `job_descriptions`: Stores job description content and metadata
- `qualifying_questions`: Stores screening questions for job descriptions
- `ai_settings`: Stores AI provider configurations
- `ai_prompts`: Stores prompts for AI-powered generation
- `ai_usage_logs`: Tracks AI usage and costs

### AI Integration

The application integrates with multiple AI providers:

- **OpenAI**: GPT-4, GPT-3.5 Turbo
- **Anthropic**: Claude 3 Opus, Claude 3 Sonnet
- **Google**: Gemini Pro

The AI integration is implemented in the `AIService` class, which handles:

- API calls to different providers
- Token usage tracking
- Cost calculation
- Error handling
- Content parsing

### Export Functionality

The application supports exporting job descriptions in multiple formats:

- **PDF**: Using dompdf
- **DOCX**: Using PHPWord
- **TXT**: Plain text format

The export functionality is implemented in the `ExportService` class.

## Assumptions

1. The application assumes that users have valid API keys for the AI providers they want to use.
2. The application assumes that the PostgreSQL database is properly configured and accessible.
3. The application assumes that the server has sufficient permissions to write to the storage directory for exports.
4. The application assumes that the server has the required PHP extensions installed (gd, xml, zip).

## Future Enhancements

1. Implement the remaining modules (CV Analyzer, Profile Creation, Submission to Client)
2. Add integration with external platforms (Claap, Workable, BrightHire)
3. Implement advanced analytics and reporting
4. Add support for additional AI providers
5. Implement user roles and permissions
6. Add support for multiple languages
7. Implement a more sophisticated AI prompt management system
8. Add support for custom templates and branding
