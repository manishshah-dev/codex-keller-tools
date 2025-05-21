<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\User;

class ProjectSeeder extends Seeder
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

        // Create a sample project
        Project::create([
            'user_id' => $admin->id,
            'title' => 'Frontend Developer Recruitment',
            'department' => 'Engineering',
            'location' => 'Remote / New York',
            'status' => 'active',
            'description' => 'Recruiting for a senior frontend developer position for our client in the tech industry.',
            
            // Intake Form
            'job_title' => 'Senior Frontend Developer',
            'experience_level' => 'senior',
            'employment_type' => 'full-time',
            'education_requirements' => 'Bachelor\'s degree in Computer Science or related field',
            'salary_range' => '$120,000 - $150,000',
            'required_skills' => "- 5+ years of experience with JavaScript\n- 3+ years of experience with React\n- Experience with modern frontend tools and practices\n- Strong understanding of web standards and best practices",
            'preferred_skills' => "- Experience with TypeScript\n- Experience with Next.js\n- Experience with state management libraries (Redux, MobX, etc.)\n- Experience with testing frameworks (Jest, React Testing Library, etc.)",
            'additional_notes' => 'The ideal candidate will have experience working in a fast-paced, agile environment and be comfortable with remote work.',
            
            // Company Research
            'company_name' => 'TechInnovate Solutions',
            'founding_date' => '2015-03-15',
            'company_size' => '50-100 employees',
            'turnover' => '10-20 million',
            'linkedin_url' => 'https://www.linkedin.com/company/techinnovate-solutions',
            'website_url' => 'https://www.techinnovatesolutions.com',
            'competitors' => 'CodeCraft, DevSphere, InnovateTech',
            'industry_details' => 'SaaS, Enterprise Software, Developer Tools',
            'typical_clients' => 'Mid-size to large enterprises in finance, healthcare, and e-commerce sectors',
            
            // Salary Comparison
            'average_salary' => 135000,
            'min_salary' => 120000,
            'max_salary' => 150000,
            'benefits' => "- Health, dental, and vision insurance\n- 401(k) matching\n- Unlimited PTO\n- Remote work options\n- Professional development budget\n- Home office stipend",
            'similar_job_postings' => "- Senior Frontend Engineer at CodeCraft: $130,000 - $160,000\n- Lead React Developer at DevSphere: $125,000 - $145,000\n- Senior UI Developer at InnovateTech: $115,000 - $140,000",
            
            // AI Questions
            'candidate_questions' => "1. What is the company's remote work policy?\n2. What is the tech stack used by the team?\n3. What is the team size and structure?\n4. What are the opportunities for growth and advancement?\n5. What is the company culture like?",
            'recruiter_questions' => "1. How many years of experience do you have with React?\n2. Can you describe a challenging project you worked on recently?\n3. How do you stay updated with the latest frontend technologies?\n4. What is your experience with performance optimization?\n5. How do you approach testing in your frontend applications?",
        ]);

        // Create a second sample project
        Project::create([
            'user_id' => $admin->id,
            'title' => 'Product Manager Recruitment',
            'department' => 'Product',
            'location' => 'San Francisco, CA',
            'status' => 'active',
            'description' => 'Recruiting for a senior product manager position for our client in the fintech industry.',
            
            // Intake Form
            'job_title' => 'Senior Product Manager',
            'experience_level' => 'senior',
            'employment_type' => 'full-time',
            'education_requirements' => 'Bachelor\'s degree in Business, Computer Science, or related field',
            'salary_range' => '$140,000 - $180,000',
            'required_skills' => "- 5+ years of product management experience\n- Experience with agile methodologies\n- Strong analytical and problem-solving skills\n- Excellent communication and stakeholder management",
            'preferred_skills' => "- Experience in fintech or financial services\n- Technical background or understanding\n- Experience with data analysis tools\n- MBA or related advanced degree",
            'additional_notes' => 'The ideal candidate will have a track record of successfully launching and scaling products in the fintech space.',
            
            // Company Research
            'company_name' => 'FinTech Innovations',
            'founding_date' => '2017-06-22',
            'company_size' => '100-250 employees',
            'turnover' => '30-50 million',
            'linkedin_url' => 'https://www.linkedin.com/company/fintech-innovations',
            'website_url' => 'https://www.fintechinnovations.com',
            'competitors' => 'PaymentPro, FinanceFlow, MoneyTech',
            'industry_details' => 'Fintech, Payment Processing, Financial Services',
            'typical_clients' => 'Small to mid-size businesses, financial institutions, and e-commerce platforms',
        ]);
    }
}