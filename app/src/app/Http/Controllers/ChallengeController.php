<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Challenge;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ChallengeController extends Controller
{
    use AuthorizesRequests;
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'languages' => 'required|array|min:1',
            'languages.*.language_id' => 'required|integer',
            'languages.*.sample_code' => 'nullable|string',
        ]);

        $challenge = Challenge::create($validated);

        // 言語情報の更新
        // サンプルコードが空でない言語のみを関連付ける
        // syncを使用して、指定したIDのみを維持し他は削除
        $syncData = [];
        foreach ($validated['languages'] as $language) {
            if (isset($language['language_id']) && !empty($language['sample_code'])) {
                $languageId = $language['language_id'];
                $sampleCode = $language['sample_code'];
                $syncData[$languageId] = ['sample_code' => $sampleCode];
            }
        }
        $challenge->languages()->sync($syncData);

        // Save test cases
        if ($request->has('test_cases')) {
            foreach ($request->input('test_cases') as $testCase) {
                $challenge->testCases()->create([
                    'input' => $testCase['input'],
                    'expected_output' => rtrim($testCase['expected_output'], "\n") . "\n",
                ]);
            }
        }

        return redirect()->route('challenge.show', ['id' => $challenge->id]);
    }

    /**
     * Show the form for editing the specified challenge.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $challenge = Challenge::with(['testCases', 'languages'])->findOrFail($id);
        $languages = \App\Models\Language::all();

        return view('challenge_update', compact('challenge', 'languages'));
    }

    /**
     * Remove the specified challenge from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $challenge = Challenge::findOrFail($id);
        $challenge->delete();

        return redirect()->route('challenges.index')->with('success', 'チャレンジが削除されました。');
    }

    public function update(Request $request, $id)
    {
        $challenge = Challenge::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'languages' => 'required|array',
            'languages.*.language_id' => 'required|integer',
            'languages.*.sample_code' => 'nullable|string',
        ]);

        // 言語情報を$validatedから分離
        $languagesData = $validated['languages'] ?? [];
        unset($validated['languages']);

        // チャレンジ自体の更新
        $challenge->update($validated);

        // 言語情報の更新
        // サンプルコードが空でない言語のみを関連付ける
        // syncを使用して、指定したIDのみを維持し他は削除
        $syncData = [];
        foreach ($languagesData as $language) {
            if (isset($language['language_id']) && !empty($language['sample_code'])) {
                $languageId = $language['language_id'];
                $sampleCode = $language['sample_code'];
                $syncData[$languageId] = ['sample_code' => $sampleCode];
            }
        }
        $challenge->languages()->sync($syncData);

        // テストケースの更新
        $challenge->testCases()->delete();
        if ($request->has('test_cases')) {
            foreach ($request->input('test_cases') as $testCase) {
                $challenge->testCases()->create([
                    'input' => $testCase['input'],
                    'expected_output' => rtrim($testCase['expected_output'], "\n") . "\n",
                ]);
            }
        }

        return redirect()->route('challenge.show', ['id' => $challenge->id])
                         ->with('success', 'お題が更新されました。');
    }

    public function index()
    {
        $challenges = Challenge::with('languages')
            ->orderBy('period_end', 'desc')
            ->paginate(10);

        return view('challenges', ['challenges' => $challenges]);
    }

    /**
     * 提出されたコードを取得するメソッド
     * チャレンジ期間終了後のみアクセス可能
     *
     * @param int $id チャレンジID
     * @param int $submissionId 提出ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubmissionCode($id, $submissionId)
    {
        // チャレンジを取得
        $challenge = Challenge::findOrFail($id);

        // 提出を取得し、指定されたチャレンジに紐づいているか確認
        $submission = \App\Models\Submission::with(['user', 'language'])
            ->where('challenge_id', $id)
            ->findOrFail($submissionId);

        // チャレンジ期間が終了しているか確認
        if ($challenge->isActive(now())) {
            return response()->json(['error' => 'チャレンジ期間中はコードを閲覧できません'], 403);
        }

        return response()->json([
            'code' => $submission->code,
            'score' => $submission->score,
            'user_name' => $submission->user->name,
            'language_name' => $submission->language->name,
            'submitted_at' => $submission->created_at->format('Y/m/d H:i')
        ]);
    }

    public function create()
    {
        $languages = \App\Models\Language::all();
        return view('challenge_create', ['languages' => $languages]);
    }

    public function show($id)
    {
        $challenge = Challenge::with(['languages' => function ($query) {
            $query->withPivot('sample_code');
        }])->findOrFail($id);

        // 現在の日付とチャレンジの終了日を比較して期間が終了しているかチェック
        $isExpired = now()->gt($challenge->period_end);

        // sample_codeが空でない言語のみをフィルタリング
        $availableLanguages = $challenge->languages->filter(function($language) {
            return !empty($language->pivot->sample_code);
        });

        // 現在のユーザーの各言語ごとのベストスコア提出を取得
        $userBestSubmissions = [];
        if (auth()->check()) {
            $userId = auth()->id();
            foreach ($availableLanguages as $language) {
                $bestSubmission = $challenge->submissions()
                    ->where('user_id', $userId)
                    ->where('language_id', $language->id)
                    ->orderBy('score', 'asc')
                    ->orderBy('submitted_at', 'asc')
                    ->first();

                if ($bestSubmission) {
                    $userBestSubmissions[$language->id] = $bestSubmission->code;
                }
            }
        }

        // 言語ごとのランキングを取得
        $languageRankings = [];
        foreach ($availableLanguages as $language) { // Use $availableLanguages instead of $challenge->languages
            $languageRankings[$language->id] = $challenge->submissions()
                ->where('language_id', $language->id)
                ->with(['user', 'language'])
                ->orderBy('score', 'asc')
                ->orderBy('submitted_at', 'asc')
                ->get()
                ->unique('user_id')
                ->values();
        }

        // 全体のランキングも取得
        $overallRankings = $challenge->submissions()
            ->with(['user', 'language'])
            ->whereIn('language_id', $availableLanguages->pluck('id')) // Use $availableLanguages instead of $challenge->languages
            ->orderBy('score', 'asc')
            ->orderBy('submitted_at', 'asc')
            ->get()
            ->unique('user_id')
            ->values();

        return view('challenge', [
            'challenge' => $challenge,
            'rankings' => $overallRankings,
            'languageRankings' => $languageRankings,
            'isExpired' => $isExpired,
            'availableLanguages' => $availableLanguages,
            'userBestSubmissions' => $userBestSubmissions,
        ]);
    }
}
