<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Submission;
use App\Services\SlackNotificationService;

class SubmissionController extends Controller
{
    /**
     * Store a new submission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'challenge_id' => 'required|integer',
            'user_id' => 'required|integer',
            'language_id' => 'required|integer',
            'code' => 'required|string',
        ]);

        // データベースからテストケースを取得
        $challenge = \App\Models\Challenge::with(['testCases', 'languages'])->find($validated['challenge_id']);
        if (!$challenge) {
            return redirect()->route('challenge.show', ['id' => $validated['challenge_id']])
                             ->with(['fail' => 'チャレンジが見つかりませんでした']);
        }

        // 有効期限のチェック
        if (!$challenge->isActive(now())) {
            return redirect()->route('challenge.show', ['id' => $validated['challenge_id']])
                             ->with(['fail' => 'このチャレンジは現在有効ではありません。']);
        }

        // テストケースとコードを準備
        $baseDir = sys_get_temp_dir() . uniqid('/', true);
        $testCasesDir = $baseDir . '/test_cases';
        $codeFile = $baseDir . '/submission';

        $submission = new Submission($validated);
        $submission->prepareTestEnvironment($testCasesDir, $codeFile);

        // language_idから言語を取得
        $language = $challenge->languages()->where('language_id', $validated['language_id'])->first();
        if (!$language) {
            return redirect()->route('challenge.show', ['id' => $validated['challenge_id']])
                             ->with(['fail' => '指定された言語はこのチャレンジで使用できません。']);
        }

        // Docker コンテナで 提出されたコードを実行してテストケースを確認
        $command = 'docker compose -f /opt/submission-executer/compose.yml run --rm ' . $language->name . ' timeout 5 /app/run-tests.sh ' . escapeshellarg($codeFile) . ' ' . escapeshellarg($testCasesDir);
        // 標準出力に標準エラー出力をリダイレクトして実行
        exec($command . ' 2>&1', $output, $exitCode);
        // 実行コマンドと標準出力,exitCodeをまとめてログに記録
        Log::info([
            'command' => $command,
            'output' => $output,
            'code' => $exitCode,
        ]);
        // 作成したテストケースとコードファイルを削除
        $submission->deleteTestEnvironment($baseDir);

        // テストケースの結果 $exitCode が 0 の場合は成功
        if ($exitCode !== 0) {
            // 失敗時はエラー表示をして戻る
            return redirect()->route('challenge.show', ['id' => $submission->challenge_id])
                             ->with(['fail' => '提出コードの実行に失敗しました。'])
                             ->withInput($request->only(['code', 'language_id']));
        }

        // Submission の保存
        $submission->score = strlen(
            preg_replace(
                [
                    '/<\?php|\?>/', // PHPタグを削除
                    '/\s+/' // 空白を削除
                ],
                '',
                $validated['code']
            )
        );
        $submission->submitted_at = now();
        $submission->save();

        // 新記録の場合のみSlack通知を送信
        if ($submission->isNewRecord()) {
            $slackNotificationService = app(SlackNotificationService::class);
            $slackNotificationService->notifyNewSubmission($submission);
        }

        return redirect()->route('challenge.show', ['id' => $submission->challenge_id])
                         ->with('success', '提出コードが保存されました！')
                         ->withInput($request->only(['code', 'language_id']));
    }
}
