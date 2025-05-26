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

class CandidateProfileSubmissionMail extends Mailable
{
    use Queueable, SerializesModels;
    
    public Project $project;
    public Candidate $candidate;
    public CandidateProfile $profile;
    public string $messageText;

    public function __construct(Project $project, Candidate $candidate, CandidateProfile $profile, string $messageText)
    {
        $this->project = $project;
        $this->candidate = $candidate;
        $this->profile = $profile;
        $this->messageText = $messageText;
    }


    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Candidate Profile Submission: ' . $this->candidate->full_name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.candidate_profile_submission',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if (!$this->candidate->resume_path) {
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
