<?php

namespace App\Mail;

use App\Models\Organization;
use App\Models\OrganizationMember;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class OrganizationInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $organizationMember;
    public $organization;
    public $invitationToken;
    public $invitationUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(OrganizationMember $organizationMember)
    {
        $this->organizationMember = $organizationMember;
        $this->organization = $organizationMember->organization;

        // 초대 토큰 생성 (실제 구현에서는 더 안전한 방식 사용)
        $this->invitationToken = Str::random(32);

        // 초대 URL 생성
        $this->invitationUrl = config('app.url') . '/organizations/' . $this->organization->id . '/invitation/accept?token=' . $this->invitationToken;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->organization->name . ' 조직 초대',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: '200-emails.300-organization-invitation',
            with: [
                'organization' => $this->organization,
                'organizationMember' => $this->organizationMember,
                'invitationUrl' => $this->invitationUrl,
                'invitationToken' => $this->invitationToken,
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
        return [];
    }
}