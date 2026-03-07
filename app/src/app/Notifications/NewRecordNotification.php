<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\SlackMessage;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;
use App\Models\Submission;

class NewRecordNotification extends Notification
{
    use Queueable;

    private Submission $submission;

    /**
     * Create a new notification instance.
     */
    public function __construct(Submission $submission)
    {
        $this->submission = $submission;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['slack'];
    }

    /**
     * Get the Slack representation of the notification.
     */
    public function toSlack($notifiable): SlackMessage
    {
        $submission = $this->submission;
        $userName = $submission->user->name ?? 'Unknown User';
        $challengeTitle = $submission->challenge->title ?? 'Unknown Challenge';
        $challengeId = $submission->challenge->id ?? 'Unknown ID';
        $languageName = $submission->language->name ?? 'Unknown Language';
        $score = $submission->score;

        return (new SlackMessage)
            ->username('Code Champs Bot')
            ->emoji(':meow_code:')
            ->headerBlock('🏆 Code Champs - New Record! 🏆')
            ->dividerBlock()
            ->sectionBlock(function (SectionBlock $block) use ($userName, $challengeTitle, $languageName, $score, $challengeId) {
                $challengeUrl = route('challenge.show', ['id' => $challengeId]);
                $block->field("*🎮 お題*: <{$challengeUrl}|{$challengeTitle}>")->markdown();
                $block->field("*💻 使用言語*: `{$languageName}`")->markdown();
                $block->field("*👤 プレイヤー*: `{$userName}`")->markdown();
                $block->field("*📊 スコア*: {$score} 文字")->markdown();
            })
            ->dividerBlock()
            ->contextBlock(function ($block) {
                $block->text('🎉 おめでとうございます！新記録達成です！');
            });
    }

}
