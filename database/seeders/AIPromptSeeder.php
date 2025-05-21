<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AIPrompt;
use App\Models\User;

class AIPromptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        if (!$admin) {
            return;
        }

        // Create AI prompts
        AIPrompt::create([
            'feature' => 'job_description',
            'name' => 'Standard Job Description Prompt',
            'prompt_template' => "Create a job description for a {{job_title}} position at {{company_name}}.\n\n" .
                "Company Information:\n" .
                "- Company: {{company_name}}\n" .
                "- Industry: {{industry}}\n" .
                "- Size: {{company_size}}\n\n" .
                "Job Details:\n" .
                "- Title: {{job_title}}\n" .
                "- Department: {{department}}\n" .
                "- Location: {{location}}\n" .
                "- Experience Level: {{experience_level}}\n\n" .
                "Required Skills:\n{{required_skills}}\n\n" .
                "Preferred Skills:\n{{preferred_skills}}\n\n" .
                "Education Requirements:\n{{education_requirements}}\n\n" .
                "Please create a comprehensive job description with the following sections:\n" .
                "1. Overview\n2. Responsibilities\n3. Requirements (Non-Negotiable)\n4. Requirements (Preferred)\n5. Benefits\n6. Disclaimer",
            'parameters' => [
                'job_title',
                'company_name',
                'industry',
                'company_size',
                'department',
                'location',
                'experience_level',
                'required_skills',
                'preferred_skills',
                'education_requirements',
            ],
            // 'provider' => 'openai',
            // 'model' => 'gpt-4',
            'is_default' => true,
            'created_by' => $admin->id,
        ]);

        AIPrompt::create([
            'feature' => 'qualifying_questions',
            'name' => 'Standard Qualifying Questions Prompt',
            'prompt_template' => "Create a set of qualifying questions for a {{job_title}} position at {{company_name}}.\n\n" .
                "Job Details:\n" .
                "- Title: {{job_title}}\n" .
                "- Department: {{department}}\n" .
                "- Required Skills: {{required_skills}}\n" .
                "- Experience Level: {{experience_level}}\n\n" .
                "Please create 5-7 qualifying questions that will help screen candidates effectively. " .
                "The questions should be concise, not leading, and minimize open-ended answers. " .
                "Include questions about salary requirements, commute, language needs, years of experience, etc.\n\n" .
                "For each question, specify:\n" .
                "1. The question text\n" .
                "2. The type (multiple_choice, yes_no, text, numeric)\n" .
                "3. Whether it's a knockout question (if yes, provide the correct answer)\n" .
                "4. For multiple choice questions, provide the options",
            'parameters' => [
                'job_title',
                'company_name',
                'department',
                'required_skills',
                'experience_level',
            ],
            // 'provider' => 'openai',
            // 'model' => 'gpt-4',
            'is_default' => true,
            'created_by' => $admin->id,
        ]);

        // CV Analyzer prompt
        AIPrompt::create([
            'feature' => 'cv_analyzer',
            'name' => 'CV Analysis Prompt',
            'prompt_template' => "Analyze the following resume for a {{job_title}} position at {{company_name}}.\n\n" .
                "Job Requirements:\n{{requirements}}\n\n" .
                "Resume Text:\n{{resume_text}}\n\n" .
                "Please provide:\n" .
                "1. An overall match score (0-100%)\n" .
                "2. A breakdown of how well the candidate matches each requirement\n" .
                "3. Evidence from the resume for each match\n" .
                "4. Any red flags or concerns\n" .
                "5. Suggested interview questions based on the resume",
            'parameters' => [
                'job_title',
                'company_name',
                'requirements',
                'resume_text',
            ],
            // 'provider' => 'openai',
            // 'model' => 'gpt-4',
            'is_default' => true,
            'created_by' => $admin->id,
        ]);

        // Requirements extraction prompt
        AIPrompt::create([
            'feature' => 'requirements_extraction',
            'name' => 'Requirements Extraction Prompt',
            'prompt_template' => "Extract job requirements from the following job description for a {{job_title}} position.\n\n" .
                "Job Description:\n{{job_description}}\n\n" .
                "Please identify and categorize the requirements into the following types:\n" .
                "1. Skills (technical skills, soft skills)\n" .
                "2. Experience (years of experience, specific experience)\n" .
                "3. Education (degrees, certifications)\n" .
                "4. Languages\n" .
                "5. Location/Remote work\n\n" .
                "For each requirement, specify:\n" .
                "1. The requirement name\n" .
                "2. The type\n" .
                "3. Whether it's required or preferred\n" .
                "4. A suggested weight (0.1-1.0) based on importance",
            'parameters' => [
                'job_title',
                'job_description',
            ],
            // 'provider' => 'openai',
            // 'model' => 'gpt-4',
            'is_default' => true,
            'created_by' => $admin->id,
        ]);

        // Project-related prompts
        AIPrompt::create([
            'feature' => 'company_research',
            'name' => 'Standard Company Research Prompt',
            'prompt_template' => "You are an expert recruiter's assistant. I need you to research the following company and provide detailed information.\n\n" .
                "Company Name: {{company_name}}\n" .
                "Job Title: {{job_title}}\n" .
                "Industry: {{industry_details}}\n\n" .
                "IMPORTANT: If Google Search is enabled, please use it to search for accurate and up-to-date information about this company.\n" .
                "Search for the company's official website, LinkedIn page, and other reliable sources to gather the most accurate information.\n\n" .
                "Please provide the following information in JSON format:\n" .
                "1. Company founding date (if available) - search for when the company was established\n" .
                "2. Company size (number of employees) - look for employee count on LinkedIn or company website\n" .
                "3. Annual turnover/revenue (if available) - search for financial information\n" .
                "4. Company website URL - find the official website\n" .
                "5. LinkedIn URL - find the company's LinkedIn page\n" .
                "6. Main competitors - identify key competitors in the same industry\n" .
                "7. Industry details - provide comprehensive information about the industry\n" .
                "8. Typical clients - identify the types of clients or customers the company serves\n\n" .
                "Format your response as a valid JSON object with the following structure:\n" .
                "{\n" .
                "  \"founding_date\": \"YYYY-MM-DD\",\n" .
                "  \"company_size\": \"e.g., 50-100 employees\",\n" .
                "  \"turnover\": \"e.g., $5-10 million\",\n" .
                "  \"website_url\": \"https://company-website.com\",\n" .
                "  \"linkedin_url\": \"https://linkedin.com/company/company-name\",\n" .
                "  \"competitors\": \"Competitor1, Competitor2, Competitor3\",\n" .
                "  \"industry_details\": \"Detailed description of the industry\",\n" .
                "  \"typical_clients\": \"Description of typical clients\"\n" .
                "}\n\n" .
                "Only return the JSON object, no other text. If you cannot find information for a field, leave it as an empty string.",
            'parameters' => [
                'company_name',
                'job_title',
                'industry_details'
            ],
            // 'provider' => 'openai',
            // 'model' => 'gpt-4',
            'is_default' => true,
            'created_by' => $admin->id,
        ]);

        AIPrompt::create([
            'feature' => 'salary_comparison',
            'name' => 'Standard Salary Comparison Prompt',
            'prompt_template' => "You are an expert recruiter's assistant. I need you to provide salary comparison data for the following job:\n\n" .
                "Job Title: {{job_title}}\n" .
                "Location: {{location}}\n" .
                "Experience Level: {{experience_level}}\n" .
                "Industry: {{industry_details}}\n\n" .
                "IMPORTANT: If Google Search is enabled, please use it to search for accurate and up-to-date salary information for this role.\n" .
                "Search for salary data on sites like Glassdoor, Indeed, LinkedIn Salary, PayScale, and other reliable sources.\n\n" .
                "Please provide the following information in JSON format:\n" .
                "1. Average salary for this role in this location - search for current average salary data\n" .
                "2. Minimum salary range - find the lower end of the salary range\n" .
                "3. Maximum salary range - find the upper end of the salary range\n" .
                "4. Similar job postings (brief descriptions) - search for current job postings with similar titles and requirements\n" .
                "5. Salary data source - list the sources you used to gather this information\n\n" .
                "Format your response as a valid JSON object with the following structure:\n" .
                "{\n" .
                "  \"average_salary\": xxxx,\n" .
                "  \"min_salary\": xxxx,\n" .
                "  \"max_salary\": xxxx,\n" .
                "  \"similar_job_postings\": \"Brief descriptions of similar job postings\",\n" .
                "  \"salary_data_source\": \"Source of the salary data\"\n" .
                "}\n\n" .
                "Only return the JSON object, no other text. If you cannot find information for a field, provide a reasonable estimate based on similar roles or nearby locations.",
            'parameters' => [
                'job_title',
                'location',
                'experience_level',
                'industry_details'
            ],
            // 'provider' => 'openai',
            // 'model' => 'gpt-4',
            'is_default' => true,
            'created_by' => $admin->id,
        ]);

        AIPrompt::create([
            'feature' => 'search_strings',
            'name' => 'Standard Search Strings Prompt',
            'prompt_template' => "You are an expert recruiter's assistant. I need you to create effective search strings for finding candidates for the following job:\n\n" .
                "Job Title: {{job_title}}\n" .
                "Required Skills: {{required_skills}}\n" .
                "Preferred Skills: {{preferred_skills}}\n" .
                "Experience Level: {{experience_level}}\n" .
                "Industry: {{industry_details}}\n\n" .
                "Please create the following search strings in JSON format:\n" .
                "1. LinkedIn Boolean Search String - create an effective Boolean search string for LinkedIn Recruiter\n" .
                "2. Google X-Ray LinkedIn Search String - create a Google search string to find LinkedIn profiles (site:linkedin.com/in/)\n" .
                "3. Google X-Ray CV/Resume Search String - create a Google search string to find resumes/CVs (filetype:pdf OR filetype:doc OR filetype:docx)\n" .
                "4. Additional notes or tips for using these search strings effectively\n\n" .
                "Format your response as a valid JSON object with the following structure:\n" .
                "{\n" .
                "  \"linkedin_boolean_string\": \"Boolean search string for LinkedIn\",\n" .
                "  \"google_xray_linkedin_string\": \"Google X-Ray search for LinkedIn profiles\",\n" .
                "  \"google_xray_cv_string\": \"Google X-Ray search for resumes/CVs\",\n" .
                "  \"search_string_notes\": \"Additional notes and tips\"\n" .
                "}\n\n" .
                "Only return the JSON object, no other text. Make sure the search strings are properly formatted and effective for finding qualified candidates.",
            'parameters' => [
                'job_title',
                'required_skills',
                'preferred_skills',
                'experience_level',
                'industry_details'
            ],
            // 'provider' => 'openai',
            // 'model' => 'gpt-4',
            'is_default' => true,
            'created_by' => $admin->id,
        ]);

        AIPrompt::create([
            'feature' => 'keywords',
            'name' => 'Standard Keywords Prompt',
            'prompt_template' => "You are an expert recruiter's assistant. I need you to generate keywords and synonyms for the following job:\n\n" .
                "Job Title: {{job_title}}\n" .
                "Required Skills: {{required_skills}}\n" .
                "Preferred Skills: {{preferred_skills}}\n" .
                "Experience Level: {{experience_level}}\n" .
                "Industry: {{industry_details}}\n\n" .
                "Please provide the following in JSON format:\n" .
                "1. Keywords - list the most important keywords for this role (skills, technologies, qualifications, etc.)\n" .
                "2. Synonyms - for each keyword, provide alternative terms or synonyms that candidates might use\n" .
                "3. Translations - if requested, provide translations of these keywords in the {{translation_language}}\n\n" .
                "Format your response as a valid JSON object with the following structure:\n" .
                "{\n" .
                "  \"keywords\": \"Keyword1, Keyword2, Keyword3, ...\",\n" .
                "  \"synonyms\": \"Synonym1, Synonym2, Synonym3, ...\",\n" .
                "  \"translations\": \"Translation1, Translation2, Translation3, ...\"\n" .
                "}\n\n" .
                "Only return the JSON object, no other text. Focus on the most relevant and effective keywords for finding qualified candidates.",
            'parameters' => [
                'job_title',
                'required_skills',
                'preferred_skills',
                'experience_level',
                'industry_details'
            ],
            // 'provider' => 'openai',
            // 'model' => 'gpt-4',
            'is_default' => true,
            'created_by' => $admin->id,
        ]);

        AIPrompt::create([
            'feature' => 'ai_questions',
            'name' => 'Standard AI Questions Prompt',
            'prompt_template' => "You are an expert recruiter's assistant. I need you to generate interview questions for the following job:\n\n" .
                "Job Title: {{job_title}}\n" .
                "Required Skills: {{required_skills}}\n" .
                "Preferred Skills: {{preferred_skills}}\n" .
                "Experience Level: {{experience_level}}\n\n" .
                "Please provide the following in JSON format:\n" .
                "1. Candidate Questions - create a list of effective interview questions to ask candidates\n" .
                "2. Recruiter Questions - create a list of questions that recruiters should be prepared to answer from candidates\n\n" .
                "Format your response as a valid JSON object with the following structure:\n" .
                "{\n" .
                "  \"candidate_questions\": \"Question1\\nQuestion2\\nQuestion3\\n...\",\n" .
                "  \"recruiter_questions\": \"Question1\\nQuestion2\\nQuestion3\\n...\"\n" .
                "}\n\n" .
                "Only return the JSON object, no other text. Focus on questions that will effectively assess candidate qualifications and fit for the role.",
            'parameters' => [
                'job_title',
                'required_skills',
                'preferred_skills',
                'experience_level'
            ],
            // 'provider' => 'openai',
            // 'model' => 'gpt-4',
            'is_default' => true,
            'created_by' => $admin->id,
        ]);

        AIPrompt::create([
            'feature' => 'job_details',
            'name' => 'Standard Job Details Prompt',
            'prompt_template' => "You are an expert recruiter's assistant. I need you to extract job details from the following document:\n\n" .
                "{{document_text}}\n\n" .
                "Please extract the following information only in JSON format:\n" .
                "1. Job Title - the title of the position\n" .
                "2. Department - the department or team the role is in\n" .
                "3. Location - where the job is located\n" .
                "4. Company Name - the name of the company\n" .
                "5. Required Skills - the skills that are required for the role\n" .
                "6. Preferred Skills - the skills that are preferred but not required\n" .
                "7. Experience Level - the level of experience required\n" .
                "8. Education Requirements - the education requirements for the role\n" .
                "9. Employment Type - the type of employment (full-time, part-time, contract, etc.)\n" .
                "10. Salary Range - the salary range for the role\n" .
                "11. Industry Details - the industry that the company belongs to\n\n" .
                "Format your response as a valid JSON object with the following structure:\n" .
                "{\n" .
                "  \"job_title\": \"Title of the position\",\n" .
                "  \"department\": \"Department or team\",\n" .
                "  \"location\": \"Job location\",\n" .
                "  \"company_name\": \"Company name\",\n" .
                "  \"required_skills\": \"Required skills for the role\",\n" .
                "  \"preferred_skills\": \"Preferred skills for the role\",\n" .
                "  \"experience_level\": \"Level of experience required\",\n" .
                "  \"education_requirements\": \"Education requirements\",\n" .
                "  \"employment_type\": \"Type of employment\",\n" .
                "  \"salary_range\": \"Salary range for the role\"\n" .
                "  \"industry_details\": \"Industry the Company belongs to\"\n" .
                "}\n\n" .
                "Only return the JSON object, no other text. If you cannot find information for a field, leave it as an empty string.",
            'parameters' => [
                'document_text'
            ],
            // 'provider' => 'openai',
            // 'model' => 'gpt-4',
            'is_default' => true,
            'created_by' => $admin->id,
        ]);
    }
}