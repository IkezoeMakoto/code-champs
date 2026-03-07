@extends('layouts.app')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('challenge.show', $challenge->id) }}">お題詳細</a></li>
    <li class="breadcrumb-item active" aria-current="page">編集</li>
@endsection

@section('content')
<div class="container">
    <h1>お題の編集</h1>
    <form method="POST" action="{{ route('challenges.update', $challenge->id) }}" id="challenge-form" onsubmit="return validateForm()">
        @csrf
        @method('PUT')
        <div class="form-group mt-4">
            <label for="title" class="font-weight-bold text-primary h5">タイトル</label>
            <input type="text" id="title" name="title" class="form-control" value="{{ old('title', $challenge->title) }}" required>
            @if ($errors->has('title'))
                <div class="text-danger mt-1">{{ $errors->first('title') }}</div>
            @endif
        </div>
        <div class="form-group mt-4">
            <label for="description" class="font-weight-bold text-primary h5">説明文</label>
            <textarea id="description" name="description" class="form-control" required>{{ old('description', $challenge->description) }}</textarea>
            @if ($errors->has('description'))
                <div class="text-danger mt-1">{{ $errors->first('description') }}</div>
            @endif
        </div>
        <div class="form-group mt-4">
            <label class="font-weight-bold text-primary h5">期間</label>
            <div class="row">
                <div class="col-md-6">
                    <label for="period_start" class="form-label font-weight-bold text-primary h6">開始日</label>
                    <input type="date" id="period_start" name="period_start" class="form-control" value="{{ old('period_start', $challenge->period_start->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-6">
                    <label for="period_end" class="form-label font-weight-bold text-primary h6">終了日</label>
                    <input type="date" id="period_end" name="period_end" class="form-control" value="{{ old('period_end', $challenge->period_end->format('Y-m-d')) }}" required>
                </div>
            </div>
            @if ($errors->has('period'))
                <div class="text-danger mt-2">{{ $errors->first('period') }}</div>
            @endif
        </div>
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="test_cases" class="font-weight-bold text-primary h5">テストケース</label>
                    <div id="test_cases">
                        @if (!empty($challenge->testCases))
                            @foreach ($challenge->testCases as $index => $testCase)
                            <div class="test-case-group border p-3 mb-3 position-relative">
                                <button type="button" class="btn-close position-absolute top-0 end-0" aria-label="Close" onclick="removeTestCase(this)"></button>
                                <label class="font-weight-bold text-primary h6">入力値</label>
                                <textarea name="test_cases[{{ $index }}][input]" class="form-control mb-2" placeholder="Input" required>{{ old("test_cases.{$index}.input", $testCase->input) }}</textarea>
                                @if ($errors->has("test_cases.{$index}.input"))
                                    <div class="text-danger mt-1">{{ $errors->first("test_cases.{$index}.input") }}</div>
                                @endif
                                <label class="font-weight-bold text-primary h6">期待値</label>
                                <textarea name="test_cases[{{ $index }}][expected_output]" class="form-control mb-2" placeholder="Expected Output" required>{{ old("test_cases.{$index}.expected_output", $testCase->expected_output) }}</textarea>
                                @if ($errors->has("test_cases.{$index}.expected_output"))
                                    <div class="text-danger mt-1">{{ $errors->first("test_cases.{$index}.expected_output") }}</div>
                                @endif
                            </div>
                            @endforeach
                        @endif
                    </div>
                    <button type="button" id="add-test-case" class="btn btn-secondary mt-2">テストケースを追加</button>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="languages" class="font-weight-bold text-primary h5">対応言語（サンプル実装）</label>
                    <div id="languages">
                        @foreach ($languages as $index => $language)
                            <div class="language-group">
                                <label class="font-weight-bold text-primary h6">{{ $language->name }}</label>
                                <input type="hidden" name="languages[{{ $index }}][language_id]" value="{{ $language->id }}">

                                @php
                                    $sampleCode = '';
                                    if ($challenge->languages->contains($language->id)) {
                                        $existingLanguage = $challenge->languages->where('id', $language->id)->first();
                                        $sampleCode = $existingLanguage->pivot->sample_code ?? '';
                                    }
                                @endphp

                                <textarea name="languages[{{ $index }}][sample_code]" class="form-control mb-2" placeholder="Sample Code" style="resize: none;">{{ old("languages.{$index}.sample_code", $sampleCode) }}</textarea>
                                @if ($errors->has("languages.{$index}.sample_code"))
                                    <div class="text-danger mt-1">{{ $errors->first("languages.{$index}.sample_code") }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-primary">更新</button>
            <a href="#" class="btn btn-danger ml-2" onclick="document.getElementById('delete-form').submit(); return false;">削除</a>
        </div>
    </form>
    <form id="delete-form" method="POST" action="{{ route('challenges.destroy', $challenge->id) }}" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
    <style>
        textarea {
            resize: none;
        }
    </style>
    <script>
        document.getElementById('add-test-case').addEventListener('click', function () {
            const testCasesDiv = document.getElementById('test_cases');
            const index = testCasesDiv.children.length;
            const newTestCase = document.createElement('div');
            newTestCase.classList.add('border', 'p-3', 'mb-3');
            newTestCase.classList.add('test-case-group', 'border', 'p-3', 'mb-3', 'position-relative');
            newTestCase.innerHTML = `
                <button type="button" class="btn-close position-absolute top-0 end-0" aria-label="Close" onclick="removeTestCase(this)"></button>
                <label class="font-weight-bold text-primary h6">入力値</label>
                <textarea name="test_cases[${index}][input]" class="form-control mb-2" placeholder="Input" required></textarea>
                <label class="font-weight-bold text-primary h6">期待値</label>
                <textarea name="test_cases[${index}][expected_output]" class="form-control mb-2" placeholder="Expected Output" required></textarea>
            `;
            testCasesDiv.appendChild(newTestCase);

            // Add event listeners to new textareas
            newTestCase.querySelectorAll('textarea').forEach(textarea => {
                adjustTextareaHeight(textarea);
                textarea.addEventListener('input', () => adjustTextareaHeight(textarea));
            });
        });

        function validateForm() {
            // サンプルコードのバリデーション - 少なくとも1つは入力が必要
            const sampleCodeInputs = document.querySelectorAll('textarea[name^="languages"][name$="[sample_code]"]');
            let hasAnySampleCode = false;

            for (const input of sampleCodeInputs) {
                if (input.value.trim() !== '') {
                    hasAnySampleCode = true;
                    break;
                }
            }

            if (!hasAnySampleCode) {
                alert('少なくとも1つの言語にサンプルコードを入力してください。');
                return false;
            }

            return true;
        }

        function removeTestCase(button) {
            const testCasesDiv = document.getElementById('test_cases');
            if (testCasesDiv.children.length > 1) {
                if (confirm('このテストケースを削除しますか？')) {
                    button.parentElement.remove();
                }
            } else {
                alert('少なくとも1つのテストケースが必要です。');
            }
        }
        function adjustTextareaHeight(textarea) {
            textarea.style.height = '';
            textarea.style.height = textarea.scrollHeight + 'px';
        }

        document.querySelectorAll('textarea').forEach(textarea => {
            adjustTextareaHeight(textarea);
            textarea.addEventListener('input', () => adjustTextareaHeight(textarea));
        });
    </script>
</div>
@endsection
