<?php

namespace App\Services;

use App\Models\CandidateProfile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;

class CandidateProfileExportService
{
    /**
     * Export a candidate profile to the specified format.
     *
     * @param CandidateProfile $profile The profile to export
     * @param string $format The format to export to (pdf or docx)
     * @return array{file_path:string, download_url:string}
     */
    public function exportProfile(CandidateProfile $profile, string $format): array
    {
        $format = strtolower($format);
        $allowed = ['pdf', 'docx', 'html'];
        if (!in_array($format, $allowed)) {
            throw new Exception("Unsupported export format: {$format}");
        }

        $fileName = Str::slug($profile->title ?? 'candidate-profile') . '-' . date('Y-m-d') . '.' . $format;
        $exportPath = 'exports/candidate-profiles/' . $profile->id . '/' . $fileName;

        switch ($format) {
            case 'pdf':
                $this->exportToPdf($profile, $exportPath);
                break;
            case 'docx':
                $this->exportToDocx($profile, $exportPath);
                break;
            case 'html':
                $this->exportToHtml($profile, $exportPath);
                break;
        }

        $profile->export_format = $format;
        $profile->export_path = $exportPath;
        $profile->last_exported_at = now();
        $profile->save();

        return [
            'file_path' => $exportPath,
            'download_url' => Storage::url($exportPath),
        ];
    }

    private function exportToPdf(CandidateProfile $profile, string $exportPath): void
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', false);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'portrait');

        $html = $this->generateHtml($profile);
        $dompdf->loadHtml($html);
        $dompdf->render();

        $output = $dompdf->output();
        Storage::put($exportPath, $output);
    }

    private function exportToDocx(CandidateProfile $profile, string $exportPath): void
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection();

        $section->addText(
            $profile->title,
            ['bold' => true, 'size' => 16],
            ['alignment' => 'center', 'spaceAfter' => 240]
        );

        $candidate = $profile->candidate;
        if ($candidate) {
            $section->addText(
                $candidate->full_name,
                ['bold' => true, 'size' => 12],
                ['alignment' => 'center', 'spaceAfter' => 240]
            );
        }

        if ($profile->summary) {
            $section->addText('Summary', ['bold' => true, 'size' => 14], ['spaceAfter' => 120]);
            $section->addText($profile->summary, ['size' => 11], ['spaceAfter' => 240]);
        }

        if ($profile->headings && count($profile->formatted_headings) > 0) {
            $section->addText('Profile Highlights', ['bold' => true, 'size' => 14], ['spaceAfter' => 120]);
            foreach ($profile->formatted_headings as $heading) {
                $section->addText($heading['title'], ['bold' => true, 'size' => 12], ['spaceAfter' => 60]);
                foreach ($heading['content'] as $bullet) {
                    $section->addText('• ' . $bullet['content'], ['size' => 11], ['spaceAfter' => 30]);
                }
                $section->addText('', [], ['spaceAfter' => 60]);
            }
        }

        if ($candidate) {
            $section->addText('Candidate Details', ['bold' => true, 'size' => 14], ['spaceAfter' => 120]);
            $section->addText('Name: ' . $candidate->full_name, ['size' => 11], ['spaceAfter' => 60]);
            if ($candidate->email) {
                $section->addText('Email: ' . $candidate->email, ['size' => 11], ['spaceAfter' => 60]);
            }
            if ($candidate->phone) {
                $section->addText('Phone: ' . $candidate->phone, ['size' => 11], ['spaceAfter' => 60]);
            }
            if ($candidate->location) {
                $section->addText('Location: ' . $candidate->location, ['size' => 11], ['spaceAfter' => 60]);
            }
            if ($candidate->linkedin_url) {
                $section->addText('LinkedIn: ' . $candidate->linkedin_url, ['size' => 11], ['spaceAfter' => 60]);
            }
            if ($candidate->current_position) {
                $section->addText('Position: ' . $candidate->current_position, ['size' => 11], ['spaceAfter' => 60]);
            }
            if ($candidate->current_company) {
                $section->addText('Company: ' . $candidate->current_company, ['size' => 11], ['spaceAfter' => 60]);
            }
            $section->addText('Status: ' . ucfirst($candidate->status), ['size' => 11], ['spaceAfter' => 60]);
            $section->addText('Match Score: ' . $candidate->match_score_percentage, ['size' => 11], ['spaceAfter' => 60]);
        }

        $extracted = $profile->extracted_data;
        if (!empty($extracted)) {
            $section->addText('Resume Insights', ['bold' => true, 'size' => 14], ['spaceAfter' => 120]);

            if (!empty($extracted['education'])) {
                $section->addText('Education', ['bold' => true, 'size' => 12], ['spaceAfter' => 60]);
                foreach ($extracted['education'] as $edu) {
                    $text = ($edu['degree'] ?? '');
                    if (!empty($edu['institution'])) {
                        $text .= ', ' . $edu['institution'];
                    }
                    if (!empty($edu['date_range'])) {
                        $text .= ' (' . $edu['date_range'] . ')';
                    }
                    $section->addText($text, ['size' => 11], ['spaceAfter' => 30]);
                    if (!empty($edu['highlights']) && is_array($edu['highlights'])) {
                        foreach ($edu['highlights'] as $hl) {
                            $section->addText('• ' . $hl, ['size' => 11], ['spaceAfter' => 20]);
                        }
                    }
                }
            }

            if (!empty($extracted['experience'])) {
                $section->addText('Experience', ['bold' => true, 'size' => 12], ['spaceAfter' => 60]);
                foreach ($extracted['experience'] as $exp) {
                    $text = ($exp['title'] ?? '');
                    if (!empty($exp['company'])) {
                        $text .= ' at ' . $exp['company'];
                    }
                    if (!empty($exp['date_range'])) {
                        $text .= ' (' . $exp['date_range'] . ')';
                    }
                    $section->addText($text, ['size' => 11], ['spaceAfter' => 30]);
                    if (!empty($exp['responsibilities']) && is_array($exp['responsibilities'])) {
                        foreach ($exp['responsibilities'] as $resp) {
                            $section->addText('• ' . $resp, ['size' => 11], ['spaceAfter' => 20]);
                        }
                    }
                    if (!empty($exp['achievements']) && is_array($exp['achievements'])) {
                        foreach ($exp['achievements'] as $ach) {
                            $section->addText('• ' . $ach, ['size' => 11], ['spaceAfter' => 20]);
                        }
                    }
                }
            }

            if (!empty($extracted['skills'])) {
                $section->addText('Skills', ['bold' => true, 'size' => 12], ['spaceAfter' => 60]);
                if (is_array($extracted['skills'])) {
                    foreach ($extracted['skills'] as $category => $skills) {
                        if (empty($skills)) {
                            continue;
                        }
                        $section->addText(Str::title(str_replace('_', ' ', $category)), ['bold' => true, 'size' => 11], ['spaceAfter' => 40]);
                        $skills = is_array($skills) ? $skills : explode(',', $skills);
                        foreach ($skills as $skill) {
                            $section->addText('• ' . $skill, ['size' => 11], ['spaceAfter' => 20]);
                        }
                    }
                } else {
                    foreach ($extracted['skills'] as $skill) {
                        $section->addText('• ' . $skill, ['size' => 11], ['spaceAfter' => 20]);
                    }
                }
            }

            if (!empty($extracted['additional_info'])) {
                $section->addText('Additional Information', ['bold' => true, 'size' => 12], ['spaceAfter' => 60]);
                foreach ($extracted['additional_info'] as $category => $items) {
                    if (empty($items)) {
                        continue;
                    }
                    $section->addText(Str::title(str_replace('_', ' ', $category)), ['bold' => true, 'size' => 11], ['spaceAfter' => 40]);
                    $items = is_array($items) ? $items : explode(',', $items);
                    foreach ($items as $item) {
                        $section->addText('• ' . $item, ['size' => 11], ['spaceAfter' => 20]);
                    }
                }
            }
        }

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $tempFile = tempnam(sys_get_temp_dir(), 'candidate_profile');
        $objWriter->save($tempFile);
        $content = file_get_contents($tempFile);
        Storage::put($exportPath, $content);
        unlink($tempFile);
    }

    private function exportToHtml(CandidateProfile $profile, string $exportPath): void
    {
        $html = $this->generateHtml($profile);
        Storage::put($exportPath, $html);
    }

    private function generateHtml(CandidateProfile $profile): string
    {
        $candidate = $profile->candidate;
        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>' . htmlspecialchars($profile->title) . '</title>';
        $html .= '<style>body{font-family: Arial, sans-serif;line-height:1.6;color:#333;padding:20px;} h1{font-size:24px;text-align:center;margin-bottom:10px;} h2{font-size:18px;margin-top:20px;margin-bottom:10px;color:#2c3e50;} .section{margin-bottom:20px;} ul{margin-top:5px;margin-bottom:15px;} </style>';
        $html .= '</head><body>';
        $html .= '<h1>' . htmlspecialchars($profile->title) . '</h1>';
        if ($candidate) {
            $html .= '<div style="text-align:center;margin-bottom:20px;font-weight:bold;">' . htmlspecialchars($candidate->full_name) . '</div>';
        }

        if ($profile->summary) {
            $html .= '<div class="section"><h2>Summary</h2><p>' . nl2br(htmlspecialchars($profile->summary)) . '</p></div>';
        }

        if ($profile->headings && count($profile->formatted_headings) > 0) {
            $html .= '<div class="section"><h2>Profile Highlights</h2>';
            foreach ($profile->formatted_headings as $heading) {
                $html .= '<h3>' . htmlspecialchars($heading['title']) . '</h3><ul>';
                foreach ($heading['content'] as $bullet) {
                    $html .= '<li>' . htmlspecialchars($bullet['content']);
                    if (isset($bullet['evidence_source']) && $bullet['evidence_source']) {
                        $html .= ' <span style="font-size:12px;color:#555;">(Source: ' . htmlspecialchars(ucfirst($bullet['evidence_source'])) . ')</span>';
                    }
                    $html .= '</li>';
                }
                $html .= '</ul>';
            }
            $html .= '</div>';
        }

        if ($candidate) {
            $html .= '<div class="section"><h2>Candidate Details</h2>';
            $html .= '<p><strong>Name:</strong> ' . htmlspecialchars($candidate->full_name) . '</p>';
            if ($candidate->email) { $html .= '<p><strong>Email:</strong> ' . htmlspecialchars($candidate->email) . '</p>'; }
            if ($candidate->phone) { $html .= '<p><strong>Phone:</strong> ' . htmlspecialchars($candidate->phone) . '</p>'; }
            if ($candidate->location) { $html .= '<p><strong>Location:</strong> ' . htmlspecialchars($candidate->location) . '</p>'; }
            if ($candidate->linkedin_url) { $html .= '<p><strong>LinkedIn:</strong> ' . htmlspecialchars($candidate->linkedin_url) . '</p>'; }
            if ($candidate->current_position) { $html .= '<p><strong>Position:</strong> ' . htmlspecialchars($candidate->current_position) . '</p>'; }
            if ($candidate->current_company) { $html .= '<p><strong>Company:</strong> ' . htmlspecialchars($candidate->current_company) . '</p>'; }
            $html .= '<p><strong>Status:</strong> ' . htmlspecialchars(ucfirst($candidate->status)) . '</p>';
            $html .= '<p><strong>Match Score:</strong> ' . htmlspecialchars($candidate->match_score_percentage) . '</p>';
            $html .= '</div>';
        }

        $extracted = $profile->extracted_data;
        if (!empty($extracted)) {
            $html .= '<div class="section"><h2>Resume Insights</h2>';

            if (!empty($extracted['education'])) {
                $html .= '<h3>Education</h3><ul>';
                foreach ($extracted['education'] as $edu) {
                    $html .= '<li><span style="font-weight:bold;">' . htmlspecialchars($edu['degree'] ?? '') . '</span>';
                    if (!empty($edu['institution'])) { $html .= ', ' . htmlspecialchars($edu['institution']); }
                    if (!empty($edu['date_range'])) { $html .= ' <span style="color:#555;">(' . htmlspecialchars($edu['date_range']) . ')</span>'; }
                    if (!empty($edu['highlights']) && is_array($edu['highlights'])) {
                        $html .= '<ul>';
                        foreach ($edu['highlights'] as $hl) {
                            $html .= '<li>' . htmlspecialchars($hl) . '</li>';
                        }
                        $html .= '</ul>';
                    }
                    $html .= '</li>';
                }
                $html .= '</ul>';
            }

            if (!empty($extracted['experience'])) {
                $html .= '<h3>Experience</h3><ul>';
                foreach ($extracted['experience'] as $exp) {
                    $html .= '<li><span style="font-weight:bold;">' . htmlspecialchars($exp['title'] ?? '') . '</span>';
                    if (!empty($exp['company'])) { $html .= ' at ' . htmlspecialchars($exp['company']); }
                    if (!empty($exp['date_range'])) { $html .= ' <span style="color:#555;">(' . htmlspecialchars($exp['date_range']) . ')</span>'; }
                    if (!empty($exp['responsibilities']) && is_array($exp['responsibilities'])) {
                        $html .= '<ul>';
                        foreach ($exp['responsibilities'] as $resp) { $html .= '<li>' . htmlspecialchars($resp) . '</li>'; }
                        $html .= '</ul>';
                    }
                    if (!empty($exp['achievements']) && is_array($exp['achievements'])) {
                        $html .= '<ul>';
                        foreach ($exp['achievements'] as $ach) { $html .= '<li>' . htmlspecialchars($ach) . '</li>'; }
                        $html .= '</ul>';
                    }
                    $html .= '</li>';
                }
                $html .= '</ul>';
            }

            if (!empty($extracted['skills'])) {
                $html .= '<h3>Skills</h3>';
                if (is_array($extracted['skills'])) {
                    foreach ($extracted['skills'] as $category => $skills) {
                        if (!empty($skills)) {
                            $html .= '<strong>' . htmlspecialchars(str_replace('_',' ', $category)) . '</strong><ul>';
                            $skills = is_array($skills) ? $skills : explode(',', $skills);
                            foreach ($skills as $skill) { $html .= '<li>' . htmlspecialchars($skill) . '</li>'; }
                            $html .= '</ul>';
                        }
                    }
                } else {
                    $html .= '<ul>';
                    foreach ($extracted['skills'] as $skill) { $html .= '<li>' . htmlspecialchars($skill) . '</li>'; }
                    $html .= '</ul>';
                }
            }

            if (!empty($extracted['additional_info'])) {
                $html .= '<h3>Additional Information</h3>';
                foreach ($extracted['additional_info'] as $category => $items) {
                    if (!empty($items)) {
                        $items = is_array($items) ? $items : explode(',', $items);
                        $html .= '<strong>' . htmlspecialchars(str_replace('_',' ', $category)) . '</strong><ul>';
                        foreach ($items as $item) { $html .= '<li>' . htmlspecialchars($item) . '</li>'; }
                        $html .= '</ul>';
                    }
                }
            }

            $html .= '</div>';
        }

        $html .= '<div class="section" style="text-align:center;font-size:12px;color:#777;">Generated on ' . date('F j, Y') . '</div>';
        $html .= '</body></html>';

        return $html;
    }
}
