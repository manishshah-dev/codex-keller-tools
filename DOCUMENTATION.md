# Recruiter Assistant - Implementation Documentation

This document provides detailed information about the implementation of the Recruiter Assistant application, focusing on the Job Description Originator module.

## Architecture Overview

The application follows the Laravel MVC architecture:

- **Models**: Represent the database tables and relationships
- **Views**: Blade templates for the user interface
- **Controllers**: Handle user requests and business logic

### Directory Structure

```
recruiter-assistant/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── JobDescriptionController.php
│   │   │   ├── ProjectController.php
│   │   │   └── ...
│   │   ├── Requests/
│   │   └── ...
│   ├── Models/
│   │   ├── JobDescription.php
│   │   ├── Project.php
│   │   ├── QualifyingQuestion.php
│   │   ├── AISetting.php
│   │   ├── AIPrompt.php
│   │   ├── AIUsageLog.php
│   │   └── ...
│   ├── Services/
│   │   ├── AIService.php
│   │   ├── ExportService.php
│   │   └── ...
│   └── ...
├── resources/
│   ├── views/
│   │   ├── job_descriptions/
│   │   │   ├── create.blade.php
│   │   │   ├── edit.blade.php
│   │   │   ├── show.blade.php
│   │   │   └── ...
│   │   ├── projects/
│   │   └── ...
│   └── ...
└── ...
```

## Database Schema

### Projects Table

The `projects` table stores information about recruitment projects:

- Basic project information (title, department, location, etc.)
- Intake form fields (job title, required skills, etc.)
- Company research fields (company name, founding date, etc.)
- Job description fields (overview, responsibilities, etc.)
- Salary comparison fields (average salary, min/max salary, etc.)
- Search strings fields (LinkedIn boolean string, Google X-ray string, etc.)
- Keywords fields (keywords, synonyms, translations)
- AI Questions fields (candidate questions, recruiter questions)

### Job Descriptions Table

The `job_descriptions` table stores job descriptions:

- Basic job description fields (title, overview, responsibilities, etc.)
- Additional fields for AI generation (industry, experience level, etc.)
- Template and versioning information
- Export information (format, path, last exported at)
- AI generation metadata (provider, model, generated at, parameters)

### Qualifying Questions Table

The `qualifying_questions` table stores screening questions for job descriptions:

- Question text and description
- Question type (multiple choice, yes/no, text, numeric)
- Options for multiple choice questions
- Required flag and order
- Category and knockout flag
- Correct answer for knockout questions
- AI generation metadata (is AI generated, provider, model)

### AI Settings Table

The `ai_settings` table stores AI provider configurations:

- Provider name and API key
- Organization ID (for OpenAI)
- Active and default flags
- Available models and capabilities

### AI Prompts Table

The `ai_prompts` table stores prompts for AI-powered generation:

- Feature (job description, qualifying questions, etc.)
- Prompt template and parameters
- Provider and model compatibility
- Default flag and creator

### AI Usage Logs Table

The `ai_usage_logs` table tracks AI usage and costs:

- User, provider, model, and feature
- Tokens used and cost
- Success flag and error message
- Request and response data

## Job Description Originator Module

The Job Description Originator module allows recruiters to generate complete, standardized job descriptions using AI or create them manually.

### AI-Powered Generation

The AI-powered generation feature uses the `AIService` class to:

1. Get the AI provider settings from the database
2. Get or create a prompt for job description generation
3. Format the prompt with project data
4. Call the AI provider API (OpenAI, Anthropic, or Google)
5. Parse the AI-generated content into structured sections
6. Create a new job description with the parsed content
7. Log the AI usage and cost

### Manual Creation

The manual creation feature allows recruiters to:

1. Select a project
2. Fill in job description fields (title, overview, responsibilities, etc.)
3. Add qualifying questions
4. Save and publish the job description

### Export Functionality

The export functionality uses the `ExportService` class to:

1. Generate the job description content in the requested format (PDF, DOCX, or TXT)
2. Save the file to the storage directory
3. Provide a download link to the user
4. Update the job description with the export information

## AI Integration

The application integrates with multiple AI providers through the `AIService` class:

### OpenAI Integration

- API endpoint: `https://api.openai.com/v1/chat/completions`
- Models: GPT-4, GPT-3.5 Turbo
- Authentication: API key in Authorization header
- Organization ID: Optional header for multi-org accounts
- Cost tracking: Based on token usage and model pricing

### Anthropic Integration

- API endpoint: `https://api.anthropic.com/v1/messages`
- Models: Claude 3 Opus, Claude 3 Sonnet
- Authentication: API key in x-api-key header
- Cost tracking: Estimated based on input/output length

### Google AI Integration

- API endpoint: `https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent`
- Models: Gemini Pro
- Authentication: API key as query parameter
- Cost tracking: Estimated based on input/output length

## Export Functionality

The application supports exporting job descriptions in multiple formats through the `ExportService` class:

### PDF Export

- Uses dompdf library
- Generates HTML with CSS styling
- Converts HTML to PDF
- Saves the PDF to the storage directory

### DOCX Export

- Uses PHPWord library
- Creates a Word document with sections and styling
- Adds job description content to the document
- Saves the DOCX to the storage directory

### TXT Export

- Generates plain text content
- Formats the content with headers and separators
- Saves the TXT to the storage directory

## Assumptions and Limitations

### Assumptions

1. **AI Provider API Keys**: The application assumes that users have valid API keys for the AI providers they want to use.
2. **Database Configuration**: The application assumes that the PostgreSQL database is properly configured and accessible.
3. **Storage Permissions**: The application assumes that the server has sufficient permissions to write to the storage directory for exports.
4. **PHP Extensions**: The application assumes that the server has the required PHP extensions installed (gd, xml, zip).
5. **AI Response Format**: The application assumes that AI providers will return content that can be parsed into the expected sections.

### Limitations

1. **AI Cost Control**: The application does not currently implement token usage limits or cost controls for AI usage.
2. **Export Customization**: The export formats have limited customization options.
3. **AI Provider Support**: The application currently supports only three AI providers (OpenAI, Anthropic, Google).
4. **Error Handling**: The error handling for AI API calls could be improved with more specific error messages and recovery options.
5. **Content Parsing**: The content parsing logic for AI-generated job descriptions is based on common patterns and may not work for all responses.

## Security Considerations

1. **API Key Storage**: API keys are stored in the database and should be encrypted at rest.
2. **User Authentication**: All routes are protected by authentication middleware.
3. **Authorization**: Project and job description access is restricted to the owner or authorized users.
4. **Input Validation**: All user inputs are validated using Laravel's validation system.
5. **CSRF Protection**: All forms include CSRF tokens to prevent cross-site request forgery.

## Performance Considerations

1. **AI API Calls**: AI API calls can be slow and should be handled asynchronously in a production environment.
2. **Export Generation**: Generating exports, especially PDFs, can be resource-intensive and should be handled in a background job.
3. **Database Queries**: The application uses eager loading to minimize database queries.
4. **Caching**: The application does not currently implement caching but could benefit from caching AI responses and exports.

## Future Enhancements

1. **Asynchronous Processing**: Implement queue-based processing for AI API calls and export generation.
2. **Caching**: Implement caching for AI responses and exports to improve performance.
3. **AI Cost Control**: Implement token usage limits and cost controls for AI usage.
4. **Export Customization**: Add more customization options for export formats.
5. **Additional AI Providers**: Add support for more AI providers.
6. **Content Parsing Improvements**: Improve the content parsing logic for AI-generated job descriptions.
7. **Error Handling**: Enhance error handling for AI API calls with more specific error messages and recovery options.
8. **User Interface Improvements**: Enhance the user interface with more interactive features and real-time updates.