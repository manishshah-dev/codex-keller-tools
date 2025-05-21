<?php

namespace App\Services;

use App\Models\JobDescription;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Font;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;

class JobDescriptionExportService
{
    /**
     * Export a job description to the specified format.
     *
     * @param JobDescription $jobDescription The job description to export
     * @param string $format The format to export to (pdf, docx, txt)
     * @return array The export result with file path and download URL
     * @throws Exception If the export fails
     */
    public function exportJobDescription(JobDescription $jobDescription, string $format, $qualifyingQuestions = null): array
    {
        $format = strtolower($format);
        $allowedFormats = ['pdf', 'docx', 'txt'];

        if (!in_array($format, $allowedFormats)) {
            throw new Exception("Unsupported export format: {$format}");
        }

        $fileName = Str::slug($jobDescription->title) . '-' . date('Y-m-d') . '.' . $format;
        $exportPath = 'exports/job-descriptions/' . $jobDescription->id . '/' . $fileName;

        switch ($format) {
            case 'pdf':
                $this->exportToPdf($jobDescription, $exportPath, $qualifyingQuestions);
                break;
            case 'docx':
                $this->exportToDocx($jobDescription, $exportPath, $qualifyingQuestions);
                break;
            case 'txt':
                $this->exportToTxt($jobDescription, $exportPath, $qualifyingQuestions);
                break;
        }

        // Update the job description with the export information
        $jobDescription->export_format = $format;
        $jobDescription->export_path = $exportPath;
        $jobDescription->last_exported_at = now();
        $jobDescription->save();

        return [
            'file_path' => $exportPath,
            'download_url' => Storage::url($exportPath),
        ];
    }

    /**
     * Export a job description to PDF.
     *
     * @param JobDescription $jobDescription The job description to export
     * @param string $exportPath The path to save the exported file
     * @return void
     * @throws Exception If the export fails
     */
    private function exportToPdf(JobDescription $jobDescription, string $exportPath, $qualifyingQuestions = null): void
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', false);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'portrait');

        $html = $this->generateHtml($jobDescription, $qualifyingQuestions);
        $dompdf->loadHtml($html);
        $dompdf->render();

        $output = $dompdf->output();
        Storage::put($exportPath, $output);
    }

    /**
     * Export a job description to DOCX.
     *
     * @param JobDescription $jobDescription The job description to export
     * @param string $exportPath The path to save the exported file
     * @return void
     * @throws Exception If the export fails
     */
    private function exportToDocx(JobDescription $jobDescription, string $exportPath, $qualifyingQuestions = null): void
    {
        $phpWord = new PhpWord();
        
        // Set default font
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);

        // Add a section
        $section = $phpWord->addSection();

        // Add title
        $section->addText(
            $jobDescription->title,
            ['bold' => true, 'size' => 16],
            ['alignment' => 'center', 'spaceAfter' => 240]
        );

        // Add company and location
        $section->addText(
            $jobDescription->project->company_name . ' | ' . $jobDescription->location,
            ['bold' => true, 'size' => 12],
            ['alignment' => 'center', 'spaceAfter' => 240]
        );

        // Add overview
        if ($jobDescription->overview) {
            $section->addText(
                'Overview',
                ['bold' => true, 'size' => 14],
                ['spaceAfter' => 120]
            );
            $section->addText(
                $jobDescription->overview,
                ['size' => 11],
                ['spaceAfter' => 240]
            );
        }

        // Add responsibilities
        if ($jobDescription->responsibilities) {
            $section->addText(
                'Responsibilities',
                ['bold' => true, 'size' => 14],
                ['spaceAfter' => 120]
            );
            $section->addText(
                $jobDescription->responsibilities,
                ['size' => 11],
                ['spaceAfter' => 240]
            );
        }

        // Add requirements (non-negotiable)
        if ($jobDescription->requirements_non_negotiable) {
            $section->addText(
                'Requirements (Non-Negotiable)',
                ['bold' => true, 'size' => 14],
                ['spaceAfter' => 120]
            );
            $section->addText(
                $jobDescription->requirements_non_negotiable,
                ['size' => 11],
                ['spaceAfter' => 240]
            );
        }

        // Add requirements (preferred)
        if ($jobDescription->requirements_preferred) {
            $section->addText(
                'Requirements (Preferred)',
                ['bold' => true, 'size' => 14],
                ['spaceAfter' => 120]
            );
            $section->addText(
                $jobDescription->requirements_preferred,
                ['size' => 11],
                ['spaceAfter' => 240]
            );
        }

        // Add benefits
        if ($jobDescription->benefits) {
            $section->addText(
                'Benefits',
                ['bold' => true, 'size' => 14],
                ['spaceAfter' => 120]
            );
            $section->addText(
                $jobDescription->benefits,
                ['size' => 11],
                ['spaceAfter' => 240]
            );
        }

        // Add qualifying questions
        if ($qualifyingQuestions && $qualifyingQuestions->count() > 0) {
            $section->addText(
                'Qualifying Questions',
                ['bold' => true, 'size' => 14],
                ['spaceAfter' => 120]
            );
            
            $questionNumber = 1;
            foreach ($qualifyingQuestions as $question) {
                $section->addText(
                    $questionNumber . '. ' . $question->question,
                    ['bold' => true, 'size' => 11],
                    ['spaceAfter' => 60]
                );
                
                // Add question type and options if applicable
                if ($question->type === 'multiple_choice' && !empty($question->options)) {
                    $options = is_array($question->options) ? $question->options : json_decode($question->options, true);
                    if (is_array($options)) {
                        foreach ($options as $option) {
                            $section->addText(
                                '   □ ' . $option,
                                ['size' => 11],
                                ['spaceAfter' => 60]
                            );
                        }
                    }
                } elseif ($question->type === 'yes_no') {
                    $section->addText(
                        '   □ Yes   □ No',
                        ['size' => 11],
                        ['spaceAfter' => 60]
                    );
                } elseif ($question->type === 'text') {
                    $section->addText(
                        '   ____________________',
                        ['size' => 11],
                        ['spaceAfter' => 60]
                    );
                } elseif ($question->type === 'numeric') {
                    $section->addText(
                        '   ____________________',
                        ['size' => 11],
                        ['spaceAfter' => 60]
                    );
                }
                
                $questionNumber++;
                $section->addText('', [], ['spaceAfter' => 120]); // Add some space between questions
            }
        }

        // Add disclaimer
        if ($jobDescription->disclaimer) {
            $section->addText(
                'Disclaimer',
                ['bold' => true, 'size' => 14],
                ['spaceAfter' => 120]
            );
            $section->addText(
                $jobDescription->disclaimer,
                ['size' => 11],
                ['spaceAfter' => 240]
            );
        }

        // Save the document
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $tempFile = tempnam(sys_get_temp_dir(), 'job_description');
        $objWriter->save($tempFile);

        // Read the file and store it
        $content = file_get_contents($tempFile);
        Storage::put($exportPath, $content);

        // Delete the temporary file
        unlink($tempFile);
    }

    /**
     * Export a job description to TXT.
     *
     * @param JobDescription $jobDescription The job description to export
     * @param string $exportPath The path to save the exported file
     * @return void
     * @throws Exception If the export fails
     */
    private function exportToTxt(JobDescription $jobDescription, string $exportPath, $qualifyingQuestions = null): void
    {
        $content = $jobDescription->title . "\n";
        $content .= str_repeat('=', strlen($jobDescription->title)) . "\n\n";

        if ($jobDescription->project->company_name) {
            $content .= "Company: " . $jobDescription->project->company_name . "\n";
        }

        if ($jobDescription->location) {
            $content .= "Location: " . $jobDescription->location . "\n";
        }

        if ($jobDescription->experience_level) {
            $content .= "Experience Level: " . $jobDescription->experience_level . "\n";
        }

        if ($jobDescription->employment_type) {
            $content .= "Employment Type: " . $jobDescription->employment_type . "\n";
        }

        if ($jobDescription->compensation_range) {
            $content .= "Compensation Range: " . $jobDescription->compensation_range . "\n";
        }

        $content .= "\n";

        if ($jobDescription->overview) {
            $content .= "OVERVIEW\n--------\n";
            $content .= $jobDescription->overview . "\n\n";
        }

        if ($jobDescription->responsibilities) {
            $content .= "RESPONSIBILITIES\n---------------\n";
            $content .= $jobDescription->responsibilities . "\n\n";
        }

        if ($jobDescription->requirements_non_negotiable) {
            $content .= "REQUIREMENTS (NON-NEGOTIABLE)\n-----------------------------\n";
            $content .= $jobDescription->requirements_non_negotiable . "\n\n";
        }

        if ($jobDescription->requirements_preferred) {
            $content .= "REQUIREMENTS (PREFERRED)\n------------------------\n";
            $content .= $jobDescription->requirements_preferred . "\n\n";
        }

        if ($jobDescription->benefits) {
            $content .= "BENEFITS\n--------\n";
            $content .= $jobDescription->benefits . "\n\n";
        }

        // Add qualifying questions
        if ($qualifyingQuestions && $qualifyingQuestions->count() > 0) {
            $content .= "QUALIFYING QUESTIONS\n-------------------\n";
            
            $questionNumber = 1;
            foreach ($qualifyingQuestions as $question) {
                $content .= $questionNumber . ". " . $question->question . "\n";
                
                // Add question type and options if applicable
                if ($question->type === 'multiple_choice' && !empty($question->options)) {
                    $options = is_array($question->options) ? $question->options : json_decode($question->options, true);
                    if (is_array($options)) {
                        foreach ($options as $option) {
                            $content .= "   [ ] " . $option . "\n";
                        }
                    }
                } elseif ($question->type === 'yes_no') {
                    $content .= "   [ ] Yes   [ ] No\n";
                } elseif ($question->type === 'text') {
                    $content .= "   Answer: ____________________\n";
                } elseif ($question->type === 'numeric') {
                    $content .= "   Value: ____________________\n";
                }
                
                $questionNumber++;
                $content .= "\n"; // Add some space between questions
            }
            $content .= "\n";
        }

        if ($jobDescription->disclaimer) {
            $content .= "DISCLAIMER\n----------\n";
            $content .= $jobDescription->disclaimer . "\n\n";
        }

        Storage::put($exportPath, $content);
    }

    /**
     * Generate HTML for the job description.
     *
     * @param JobDescription $jobDescription The job description to generate HTML for
     * @return string The generated HTML
     */
    private function generateHtml(JobDescription $jobDescription, $qualifyingQuestions = null): string
    {
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>' . htmlspecialchars($jobDescription->title) . '</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 20px;
                }
                h1 {
                    font-size: 24px;
                    text-align: center;
                    margin-bottom: 10px;
                }
                h2 {
                    font-size: 18px;
                    margin-top: 20px;
                    margin-bottom: 10px;
                    color: #2c3e50;
                }
                .company-info {
                    text-align: center;
                    margin-bottom: 20px;
                    font-weight: bold;
                }
                .section {
                    margin-bottom: 20px;
                }
                .footer {
                    margin-top: 30px;
                    font-size: 12px;
                    color: #777;
                    text-align: center;
                }
            </style>
        </head>
        <body>
            <h1>' . htmlspecialchars($jobDescription->title) . '</h1>
            <div class="company-info">';
        
        if ($jobDescription->project->company_name) {
            $html .= htmlspecialchars($jobDescription->project->company_name);
        }
        
        if ($jobDescription->location) {
            $html .= ' | ' . htmlspecialchars($jobDescription->location);
        }
        
        $html .= '</div>';

        // Job details
        $html .= '<div class="section">';
        if ($jobDescription->experience_level) {
            $html .= '<strong>Experience Level:</strong> ' . htmlspecialchars($jobDescription->experience_level) . '<br>';
        }
        if ($jobDescription->employment_type) {
            $html .= '<strong>Employment Type:</strong> ' . htmlspecialchars($jobDescription->employment_type) . '<br>';
        }
        if ($jobDescription->compensation_range) {
            $html .= '<strong>Compensation Range:</strong> ' . htmlspecialchars($jobDescription->compensation_range) . '<br>';
        }
        $html .= '</div>';

        // Overview
        if ($jobDescription->overview) {
            $html .= '<div class="section">
                <h2>Overview</h2>
                <p>' . nl2br(htmlspecialchars($jobDescription->overview)) . '</p>
            </div>';
        }

        // Responsibilities
        if ($jobDescription->responsibilities) {
            $html .= '<div class="section">
                <h2>Responsibilities</h2>
                <p>' . nl2br(htmlspecialchars($jobDescription->responsibilities)) . '</p>
            </div>';
        }

        // Requirements (Non-Negotiable)
        if ($jobDescription->requirements_non_negotiable) {
            $html .= '<div class="section">
                <h2>Requirements (Non-Negotiable)</h2>
                <p>' . nl2br(htmlspecialchars($jobDescription->requirements_non_negotiable)) . '</p>
            </div>';
        }

        // Requirements (Preferred)
        if ($jobDescription->requirements_preferred) {
            $html .= '<div class="section">
                <h2>Requirements (Preferred)</h2>
                <p>' . nl2br(htmlspecialchars($jobDescription->requirements_preferred)) . '</p>
            </div>';
        }

        // Benefits
        if ($jobDescription->benefits) {
            $html .= '<div class="section">
                <h2>Benefits</h2>
                <p>' . nl2br(htmlspecialchars($jobDescription->benefits)) . '</p>
            </div>';
        }

        // Qualifying Questions
        if ($qualifyingQuestions && $qualifyingQuestions->count() > 0) {
            $html .= '<div class="section">
                <h2>Qualifying Questions</h2>';
            
            $questionNumber = 1;
            foreach ($qualifyingQuestions as $question) {
                $html .= '<div style="margin-bottom: 15px;">
                    <p style="font-weight: bold; margin-bottom: 5px;">' . $questionNumber . '. ' . htmlspecialchars($question->question) . '</p>';
                
                // Add question type and options if applicable
                if ($question->type === 'multiple_choice' && !empty($question->options)) {
                    $options = is_array($question->options) ? $question->options : json_decode($question->options, true);
                    if (is_array($options)) {
                        $html .= '<div style="margin-left: 20px;">';
                        foreach ($options as $option) {
                            $html .= '<div style="margin-bottom: 5px;">
                                <input type="checkbox" disabled> ' . htmlspecialchars($option) . '
                            </div>';
                        }
                        $html .= '</div>';
                    }
                } elseif ($question->type === 'yes_no') {
                    $html .= '<div style="margin-left: 20px;">
                        <input type="checkbox" disabled> Yes &nbsp;&nbsp;
                        <input type="checkbox" disabled> No
                    </div>';
                } elseif ($question->type === 'text') {
                    $html .= '<div style="margin-left: 20px;">
                        <input type="text" disabled style="width: 300px; border: 1px solid #ccc; padding: 12px;">
                    </div>';
                } elseif ($question->type === 'numeric') {
                    $html .= '<div style="margin-left: 20px;">
                        <input type="text" disabled style="width: 150px; border: 1px solid #ccc; padding: 12px;">
                    </div>';
                }
                
                $html .= '</div>';
                $questionNumber++;
            }
            
            $html .= '</div>';
        }

        // Disclaimer
        if ($jobDescription->disclaimer) {
            $html .= '<div class="section">
                <h2>Disclaimer</h2>
                <p>' . nl2br(htmlspecialchars($jobDescription->disclaimer)) . '</p>
            </div>';
        }

        // Footer
        $html .= '<div class="footer">
            Generated on ' . date('F j, Y') . ' | ' . htmlspecialchars($jobDescription->project->company_name ?? '') . '
        </div>';

        $html .= '</body></html>';

        return $html;
    }
}