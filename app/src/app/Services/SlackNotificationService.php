<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Models\Submission;
use App\Notifications\NewRecordNotification;

class SlackNotificationService
{
    public function notifyNewSubmission(Submission $submission): bool
    {
        $botToken = config('services.slack.notifications.bot_user_oauth_token');
        $channel = config('services.slack.notifications.channel');

        if (empty($botToken) || empty($channel)) {
            Log::warning('Slack bot token or channel is not configured');
            return false;
        }

        try {
            $submission->load(['user', 'challenge', 'language']);
            
            Notification::route('slack', [
                'token' => $botToken,
                'channel' => $channel
            ])->notify(new NewRecordNotification($submission));

            Log::info('Slack notification sent successfully', [
                'submission_id' => $submission->id,
                'user_id' => $submission->user_id
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send Slack notification', [
                'error' => $e->getMessage(),
                'submission_id' => $submission->id
            ]);
            return false;
        }
    }
}