<?php

namespace App\Mail;

use App\Models\Candidate;
use App\Models\CandidateProfile;
use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\Storage;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class CandidateProfileSubmissionMail extends Mailable
{
    use Queueable, SerializesModels;
    
    public Project $project;
    public Candidate $candidate;
    public CandidateProfile $profile;
    public string $mail_subject;
    public ?string $emailBody = null;
    public bool $attachCv = true;

    public function __construct(Project $project, Candidate $candidate, CandidateProfile $profile, string $mail_subject, ?string $emailBody = null, bool $attachCv = true)
    {
        $this->project = $project;
        $this->candidate = $candidate;
        $this->profile = $profile;
        $this->mail_subject = $mail_subject;
        $this->emailBody = $emailBody;
        $this->attachCv = $attachCv;
    }

    public static function defaultTemplate(): string
    {
        return <<<'TEMPLATE'
<h2>{{ profile_title }}</h2>

<div style="margin: 20px 0;">
{{ profile }}
</div>

TEMPLATE;
    }


    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mail_subject ?? 'Candidate Profile Submission: ' . $this->candidate->full_name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $template = $this->emailBody ?? self::defaultTemplate();
        
        // Get the profile HTML
        $profileHtml = view('emails.partials.profile', ['profile' => $this->profile])->render();
        
        // Replace placeholders in the template
        $html = $template;
        $html = str_replace('{{ candidate_name }}', $this->candidate->full_name, $html);
        $html = str_replace('{{ profile_title }}', $this->profile->title, $html);
        $html = str_replace('{{ profile }}', $profileHtml, $html);

        // return new Content(
        //     view: 'emails.candidate_profile_submission',
        //     with: [
        //         'body' => $html
        //     ]
        // );

        return new Content(
            markdown: 'emails.candidate_profile_submission',
            with: [
                'emailContentHtml' => $html,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if (!$this->attachCv || !$this->candidate->resume_path) {
            return [];
        }
        
        $resumePath = $this->candidate->resume_path;
        $fullPath = storage_path('app/private/' . $resumePath);
        
        if (!file_exists($fullPath)) {
            return [];
        }
        
        try {
            // Get the filename from the path
            $filename = basename($resumePath);
            $mimeType = Storage::disk('private')->mimeType($resumePath);
            
            // Create attachment from data
            return [
                Attachment::fromPath($fullPath)
                    ->as($this->candidate->full_name . ' - Resume.'.pathinfo($filename, PATHINFO_EXTENSION))
                    ->withMime($mimeType)
            ];
        } catch (\Exception $e) {
            return [];
        }
    }
}
