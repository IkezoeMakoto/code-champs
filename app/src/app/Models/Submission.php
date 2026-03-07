<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use function Avifinfo\read;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'challenge_id',
        'user_id',
        'language_id',
        'code',
        'score',
        'submitted_at',
        'is_passed',
    ];

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function prepareTestEnvironment($testCasesDir, $codeFile)
    {
        $testCases = $this->challenge->testCases;
        $tmp=umask(0);
        // Create test cases directory if not exists
        if (!is_dir($testCasesDir)) {
            mkdir($testCasesDir, 0777, true);
        }

        // Clear previous test cases
        array_map('unlink', glob("$testCasesDir/*"));
        // Clear previous code file
        if (file_exists($codeFile)) {
            unlink($codeFile);
        }

        // Write test cases to files
        foreach ($testCases as $index => $testCase) {
            file_put_contents("$testCasesDir/input" . ($index + 1) . ".txt", $testCase->input);
            // 改行コードを \n に統一する
            $normalized = str_replace(["\r\n", "\r"], "\n", $testCase->expected_output);
            file_put_contents("$testCasesDir/expect" . ($index + 1) . ".txt", $normalized);
        }

        // Write submission code to file
        file_put_contents($codeFile, $this->code);
        umask($tmp);
    }

    public function deleteTestEnvironment($directory): bool
    {
        $result = true;
        foreach(new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        ) as $file) {
            if ($file->isDir()) {
                $result &= @rmdir($file->getPathname());
            } else {
                $result &= @unlink($file->getPathname());
            }
        }
        return $result && @rmdir($directory);
    }

    /**
     * Check if this submission is a new record for the challenge and language
     */
    public function isNewRecord(): bool
    {
        $bestScore = self::where('challenge_id', $this->challenge_id)
            ->where('language_id', $this->language_id)
            ->where('id', '!=', $this->id)
            ->min('score');

        // If no previous submissions or current score is better
        return $bestScore === null || $this->score < $bestScore;
    }
}
