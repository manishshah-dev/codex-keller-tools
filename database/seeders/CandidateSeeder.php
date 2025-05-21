<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Candidate;
use App\Models\ProjectRequirement;
use App\Models\Project;
use App\Models\User;

class CandidateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $project = Project::first();

        if (!$admin || !$project) {
            return;
        }

        // Create requirements
        $requirements = [
            [
                'type' => 'skill',
                'name' => 'JavaScript',
                'description' => 'Proficiency in JavaScript programming',
                'weight' => 0.9,
                'is_required' => true,
                'source' => 'job_description',
            ],
            [
                'type' => 'skill',
                'name' => 'React',
                'description' => 'Experience with React framework',
                'weight' => 0.8,
                'is_required' => true,
                'source' => 'job_description',
            ],
            [
                'type' => 'skill',
                'name' => 'Node.js',
                'description' => 'Experience with Node.js',
                'weight' => 0.7,
                'is_required' => false,
                'source' => 'job_description',
            ],
            [
                'type' => 'experience',
                'name' => '3+ years of experience',
                'description' => 'At least 3 years of professional experience',
                'weight' => 0.6,
                'is_required' => true,
                'source' => 'job_description',
            ],
            [
                'type' => 'education',
                'name' => 'Computer Science Degree',
                'description' => 'Bachelor\'s degree in Computer Science or related field',
                'weight' => 0.5,
                'is_required' => false,
                'source' => 'job_description',
            ],
            [
                'type' => 'language',
                'name' => 'English',
                'description' => 'Fluent in English',
                'weight' => 0.4,
                'is_required' => true,
                'source' => 'job_description',
            ],
            [
                'type' => 'location',
                'name' => 'Remote',
                'description' => 'Able to work remotely',
                'weight' => 0.3,
                'is_required' => false,
                'source' => 'job_description',
            ],
        ];
        
        foreach ($requirements as $requirement) {
            ProjectRequirement::create([
                'project_id' => $project->id,
                'user_id' => $admin->id,
                'type' => $requirement['type'],
                'name' => $requirement['name'],
                'description' => $requirement['description'],
                'weight' => $requirement['weight'],
                'is_required' => $requirement['is_required'],
                'is_active' => true,
                'source' => $requirement['source'],
                'created_by_chat' => false,
            ]);
        }
        
        // Create sample candidates
        $candidates = [
            [
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'john.smith@example.com',
                'phone' => '+1 (555) 123-4567',
                'location' => 'New York, NY',
                'current_company' => 'Tech Solutions Inc.',
                'current_position' => 'Senior Frontend Developer',
                'linkedin_url' => 'https://www.linkedin.com/in/johnsmith',
                'resume_text' => "John Smith\nSenior Frontend Developer\n\nExperience:\n- Tech Solutions Inc. (2018-Present): Senior Frontend Developer\n- Web Innovations (2015-2018): Frontend Developer\n\nSkills:\n- JavaScript, React, Redux, HTML5, CSS3\n- Node.js, Express\n- Git, Webpack, Jest\n\nEducation:\n- Bachelor of Science in Computer Science, NYU (2015)",
                'match_score' => 0.92,
                'analysis_details' => json_encode([
                    'match_score' => 0.92,
                    'justification' => 'The candidate has strong JavaScript and React skills with relevant experience.',
                    'requirement_breakdown' => [
                        [
                            'requirement' => 'JavaScript',
                            'match' => true,
                            'evidence' => 'Found JavaScript in skills section and work experience',
                            'score' => 0.95
                        ],
                        [
                            'requirement' => 'React',
                            'match' => true,
                            'evidence' => 'Found React in skills section',
                            'score' => 0.9
                        ],
                        [
                            'requirement' => 'Node.js',
                            'match' => true,
                            'evidence' => 'Found Node.js in skills section',
                            'score' => 0.8
                        ],
                        [
                            'requirement' => '3+ years of experience',
                            'match' => true,
                            'evidence' => 'Has 7+ years of experience based on work history',
                            'score' => 1.0
                        ],
                        [
                            'requirement' => 'Computer Science Degree',
                            'match' => true,
                            'evidence' => 'Has a Bachelor of Science in Computer Science from NYU',
                            'score' => 1.0
                        ],
                        [
                            'requirement' => 'English',
                            'match' => true,
                            'evidence' => 'Resume is in English, suggesting fluency',
                            'score' => 1.0
                        ],
                        [
                            'requirement' => 'Remote',
                            'match' => false,
                            'evidence' => 'No explicit mention of remote work preference',
                            'score' => 0.5
                        ]
                    ],
                    'red_flags' => [
                        'No significant red flags identified'
                    ],
                    'interview_questions' => [
                        'Can you describe your experience with React in more detail?',
                        'Have you worked remotely before?',
                        'What are your most challenging projects using Node.js?'
                    ]
                ]),
                'status' => 'new',
                'source' => 'manual',
            ],
            [
                'first_name' => 'Emily',
                'last_name' => 'Johnson',
                'email' => 'emily.johnson@example.com',
                'phone' => '+1 (555) 987-6543',
                'location' => 'San Francisco, CA',
                'current_company' => 'InnovateTech',
                'current_position' => 'Frontend Team Lead',
                'linkedin_url' => 'https://www.linkedin.com/in/emilyjohnson',
                'resume_text' => "Emily Johnson\nFrontend Team Lead\n\nExperience:\n- InnovateTech (2019-Present): Frontend Team Lead\n- CodeCraft (2016-2019): Senior Developer\n- TechStart (2014-2016): Junior Developer\n\nSkills:\n- JavaScript, TypeScript, React, Vue.js\n- UI/UX Design, Responsive Design\n- Team Leadership, Agile Methodologies\n\nEducation:\n- Master of Computer Science, Stanford University (2014)\n- Bachelor of Arts in Design, UCLA (2012)",
                'match_score' => 0.85,
                'analysis_details' => json_encode([
                    'match_score' => 0.85,
                    'justification' => 'The candidate has strong JavaScript and React skills with leadership experience.',
                    'requirement_breakdown' => [
                        [
                            'requirement' => 'JavaScript',
                            'match' => true,
                            'evidence' => 'Found JavaScript in skills section',
                            'score' => 0.95
                        ],
                        [
                            'requirement' => 'React',
                            'match' => true,
                            'evidence' => 'Found React in skills section',
                            'score' => 0.9
                        ],
                        [
                            'requirement' => 'Node.js',
                            'match' => false,
                            'evidence' => 'No mention of Node.js in resume',
                            'score' => 0.0
                        ],
                        [
                            'requirement' => '3+ years of experience',
                            'match' => true,
                            'evidence' => 'Has 9+ years of experience based on work history',
                            'score' => 1.0
                        ],
                        [
                            'requirement' => 'Computer Science Degree',
                            'match' => true,
                            'evidence' => 'Has a Master of Computer Science from Stanford University',
                            'score' => 1.0
                        ],
                        [
                            'requirement' => 'English',
                            'match' => true,
                            'evidence' => 'Resume is in English, suggesting fluency',
                            'score' => 1.0
                        ],
                        [
                            'requirement' => 'Remote',
                            'match' => false,
                            'evidence' => 'No explicit mention of remote work preference',
                            'score' => 0.5
                        ]
                    ],
                    'red_flags' => [
                        'No Node.js experience mentioned',
                        'No explicit remote work experience'
                    ],
                    'interview_questions' => [
                        'Do you have any experience with Node.js?',
                        'Are you comfortable working remotely?',
                        'Can you describe your leadership experience in frontend development?'
                    ]
                ]),
                'status' => 'contacted',
                'source' => 'manual',
            ],
            [
                'first_name' => 'Michael',
                'last_name' => 'Chen',
                'email' => 'michael.chen@example.com',
                'phone' => '+1 (555) 456-7890',
                'location' => 'Austin, TX',
                'current_company' => 'DevSphere',
                'current_position' => 'Full Stack Developer',
                'linkedin_url' => 'https://www.linkedin.com/in/michaelchen',
                'resume_text' => "Michael Chen\nFull Stack Developer\n\nExperience:\n- DevSphere (2020-Present): Full Stack Developer\n- TechHub (2018-2020): Backend Developer\n\nSkills:\n- JavaScript, Python, PHP\n- React, Angular, Vue.js\n- Node.js, Express, Django\n- MongoDB, MySQL, PostgreSQL\n\nEducation:\n- Bachelor of Engineering, University of Texas (2018)",
                'match_score' => 0.78,
                'analysis_details' => json_encode([
                    'match_score' => 0.78,
                    'justification' => 'The candidate has strong JavaScript, React, and Node.js skills with full stack experience.',
                    'requirement_breakdown' => [
                        [
                            'requirement' => 'JavaScript',
                            'match' => true,
                            'evidence' => 'Found JavaScript in skills section',
                            'score' => 0.95
                        ],
                        [
                            'requirement' => 'React',
                            'match' => true,
                            'evidence' => 'Found React in skills section',
                            'score' => 0.9
                        ],
                        [
                            'requirement' => 'Node.js',
                            'match' => true,
                            'evidence' => 'Found Node.js in skills section',
                            'score' => 0.9
                        ],
                        [
                            'requirement' => '3+ years of experience',
                            'match' => true,
                            'evidence' => 'Has approximately 5 years of experience based on work history',
                            'score' => 0.8
                        ],
                        [
                            'requirement' => 'Computer Science Degree',
                            'match' => false,
                            'evidence' => 'Has a Bachelor of Engineering, which is related but not exactly Computer Science',
                            'score' => 0.7
                        ],
                        [
                            'requirement' => 'English',
                            'match' => true,
                            'evidence' => 'Resume is in English, suggesting fluency',
                            'score' => 1.0
                        ],
                        [
                            'requirement' => 'Remote',
                            'match' => false,
                            'evidence' => 'No explicit mention of remote work preference',
                            'score' => 0.5
                        ]
                    ],
                    'red_flags' => [
                        'No Computer Science degree (has Engineering degree instead)',
                        'No explicit remote work experience'
                    ],
                    'interview_questions' => [
                        'Can you describe your experience with React and Node.js?',
                        'Are you comfortable working remotely?',
                        'How has your Engineering background prepared you for this role?'
                    ]
                ]),
                'status' => 'interviewing',
                'source' => 'manual',
            ],
        ];
        
        foreach ($candidates as $candidateData) {
            $candidate = new Candidate($candidateData);
            $candidate->user_id = $admin->id;
            $candidate->project_id = $project->id;
            $candidate->last_analyzed_at = now();
            $candidate->save();
        }
    }
}