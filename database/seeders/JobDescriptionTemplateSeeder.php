<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobDescriptionTemplate;
use App\Models\TemplateCategory;
use App\Models\User;

class JobDescriptionTemplateSeeder extends Seeder
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

        // Create template categories
        $techCategory = TemplateCategory::updateOrCreate(
            ['slug' => 'technology'],
            [
                'name' => 'Technology',
                'description' => 'Templates for technology and IT roles',
            ]
        );

        $salesCategory = TemplateCategory::updateOrCreate(
            ['slug' => 'sales-marketing'],
            [
                'name' => 'Sales & Marketing',
                'description' => 'Templates for sales and marketing roles',
            ]
        );

        $financeCategory = TemplateCategory::updateOrCreate(
            ['slug' => 'finance-accounting'],
            [
                'name' => 'Finance & Accounting',
                'description' => 'Templates for finance and accounting roles',
            ]
        );

        // Create job description templates
        $softwareEngineerTemplate = JobDescriptionTemplate::create([
            'name' => 'Software Engineer Template',
            'industry' => 'Technology',
            'job_level' => 'mid',
            'description' => 'A template for software engineer positions',
            'overview_template' => "We are seeking a talented Software Engineer to join our development team. In this role, you will design, develop, and maintain software applications that meet our business needs and exceed user expectations. You will collaborate with cross-functional teams to deliver high-quality, scalable, and efficient code.",
            'responsibilities_template' => "- Design, develop, and maintain software applications\n- Write clean, efficient, and well-documented code\n- Collaborate with cross-functional teams to define, design, and ship new features\n- Identify and fix bugs and performance issues\n- Participate in code reviews and contribute to team knowledge sharing\n- Stay up-to-date with emerging trends and technologies",
            'requirements_template' => "- Bachelor's degree in Computer Science, Engineering, or related field\n- X+ years of experience in software development\n- Proficiency in [programming languages]\n- Experience with [frameworks/tools]\n- Strong problem-solving skills and attention to detail\n- Excellent communication and teamwork skills",
            'benefits_template' => "- Competitive salary and benefits package\n- Flexible work arrangements\n- Professional development opportunities\n- Collaborative and innovative work environment\n- Health insurance and retirement plans\n- Paid time off and holidays",
            'disclaimer_template' => "[Company Name] is an equal opportunity employer. We celebrate diversity and are committed to creating an inclusive environment for all employees.",
            'is_default' => true,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $softwareEngineerTemplate->categories()->attach($techCategory);

        $salesRepTemplate = JobDescriptionTemplate::create([
            'name' => 'Sales Representative Template',
            'industry' => 'Sales',
            'job_level' => 'mid',
            'description' => 'A template for sales representative positions',
            'overview_template' => "We are looking for a results-driven Sales Representative to actively seek out and engage customer prospects. You will provide complete and appropriate solutions for every customer in order to boost top-line revenue growth, customer acquisition levels, and profitability.",
            'responsibilities_template' => "- Develop new business with existing clients and identify new sales opportunities\n- Present products and services to potential clients\n- Meet or exceed sales goals\n- Negotiate contracts with clients\n- Maintain and expand client database within assigned territory\n- Follow up on sales leads promptly",
            'requirements_template' => "- Proven sales experience\n- Track record of achieving sales targets\n- Experience with CRM software\n- Strong communication and negotiation skills\n- Ability to build and maintain relationships\n- Bachelor's degree in Business or related field preferred",
            'benefits_template' => "- Competitive base salary plus commission structure\n- Comprehensive benefits package\n- Ongoing training and professional development\n- Career advancement opportunities\n- Company car or car allowance\n- Mobile phone and laptop",
            'disclaimer_template' => "[Company Name] is an equal opportunity employer. We celebrate diversity and are committed to creating an inclusive environment for all employees.",
            'is_default' => false,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $salesRepTemplate->categories()->attach($salesCategory);

        $accountantTemplate = JobDescriptionTemplate::create([
            'name' => 'Accountant Template',
            'industry' => 'Finance',
            'job_level' => 'mid',
            'description' => 'A template for accountant positions',
            'overview_template' => "We are seeking a detail-oriented Accountant to join our finance team. In this role, you will be responsible for maintaining financial records, preparing reports, and ensuring compliance with accounting policies and regulatory requirements.",
            'responsibilities_template' => "- Maintain accurate financial records\n- Prepare monthly, quarterly, and annual financial statements\n- Reconcile accounts and ledgers\n- Process accounts payable and receivable\n- Assist with budget preparation and monitoring\n- Support month-end and year-end close processes",
            'requirements_template' => "- Bachelor's degree in Accounting, Finance, or related field\n- X+ years of accounting experience\n- Proficiency in accounting software\n- Strong analytical and problem-solving skills\n- Attention to detail and accuracy\n- Knowledge of GAAP and financial regulations",
            'benefits_template' => "- Competitive salary and benefits package\n- Professional development opportunities\n- CPA exam support\n- Flexible work arrangements\n- Health insurance and retirement plans\n- Paid time off and holidays",
            'disclaimer_template' => "[Company Name] is an equal opportunity employer. We celebrate diversity and are committed to creating an inclusive environment for all employees.",
            'is_default' => false,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $accountantTemplate->categories()->attach($financeCategory);
    }
}