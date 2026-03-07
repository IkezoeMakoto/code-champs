@extends('layouts.app')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">お題詳細</li>
@endsection

<div class="container">

    @section('content')
    @php
        // 最初のアクティブな言語を決定
        $firstAvailableLanguage = $availableLanguages->first();
    @endphp

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('fail'))
        <div class="alert alert-danger">
            {{ session('fail') }}
        </div>
    @endif

    <div class="row g-4">
        <!-- Left Column: Challenge Details -->
        <div class="col-md-4 text-wrap">
            <h2>{{ $challenge->title }}</h2>
            <p>期間: {{ $challenge->period_start }} 〜 {{ $challenge->period_end }}</p>
            <textarea readonly rows="5" style="width: 100%; border: 1px solid #ccc; border-radius: 5px; padding: 10px; background-color: #f9f9f9; resize: none;" oninput="adjustTextareaHeight(this)">{{ $challenge->description }}</textarea>
        @can('update', $challenge)
            <a href="{{ route('challenges.edit', ['id' => $challenge->id]) }}" class="btn btn-primary">編集</a>
        @endcan
    </div>

        <!-- Middle Column: Sample Code and Submit Form -->
        <div class="col-md-4 text-wrap">
            <h2>対応言語</h2>
            @if($availableLanguages->count() > 0)
                <ul class="nav nav-tabs tablist-horizontal" id="languageTabs" role="tablist">
                    @foreach ($availableLanguages as $language)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link @if ($language->id == $firstAvailableLanguage->id) active @endif custom-tab-btn" id="tab-{{ $language->id }}" data-bs-toggle="tab" data-bs-target="#content-{{ $language->id }}" type="button" role="tab" aria-controls="content-{{ $language->id }}" aria-selected="{{ $language->id == $firstAvailableLanguage->id ? 'true' : 'false' }}">
                                {{ $language->name }}
                            </button>
                        </li>
                    @endforeach
                </ul>
                <div class="tab-content" id="languageTabsContent" style="padding-top: 15px;">
                    @foreach ($availableLanguages as $language)
                        <div class="tab-pane fade @if ($language->id == $firstAvailableLanguage->id) show active @endif" id="content-{{ $language->id }}" role="tabpanel" aria-labelledby="tab-{{ $language->id }}">
                            <div class="border p-3 rounded bg-light position-relative">
                                <pre><code class="copy-target-{{ $language->id }}">{{ $language->pivot->sample_code }}</code></pre>
                                <button class="btn btn-success position-absolute top-0 end-0 m-2 copy-button" data-target="copy-target-{{ $language->id }}">コピー</button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Submit Your Code Section -->
                <div class="mt-4">
                    <h2>コードを提出する</h2>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('submissions.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="challenge_id" value="{{ $challenge->id }}">
                        <input type="hidden" name="user_id" value="{{ Auth::id() }}">

                        <div class="mb-3">
                            <label for="language_id" class="form-label">プログラミング言語</label>
                            <select name="language_id" id="language_id" class="form-select" required @if($isExpired) disabled @endif>
                                @foreach($availableLanguages as $language)
                                    <option value="{{ $language->id }}" @if(old('language_id') == $language->id || (!old('language_id') && $language->id == $firstAvailableLanguage->id)) selected @endif>{{ $language->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="code" class="form-label">コード <span style="font-weight: normal;">(スコア: <span id="score">0</span>)</span></label>
                            <textarea name="code" id="code" class="form-control" rows="10" required @if($isExpired) disabled @endif oninput="updateScore(); markAsEdited();">{{ old('code', '') }}</textarea>
                            @if ($errors->has('code'))
                                <div class="text-danger mt-1">{{ $errors->first('code') }}</div>
                            @endif
                        </div>

                        @if($isExpired)
                            <div class="alert alert-warning mt-3">
                                <i class="fas fa-exclamation-circle"></i> このチャレンジの提出期間は終了しました。
                            </div>
                        @endif
                        <button type="submit" class="btn btn-primary" @if($isExpired) disabled @endif>提出する</button>
                    </form>
                </div>
            @else
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-circle"></i> 現在利用可能な言語はありません。
                </div>
            @endif
        </div>

        <!-- Right Column: Ranking -->
        <div class="col-md-4 text-wrap">
            <h2>ランキング</h2>

            <!-- ランキングタブ -->
            <ul class="nav nav-tabs" id="rankingTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active custom-tab-btn" id="tab-overall" data-bs-toggle="tab"
                            data-bs-target="#ranking-overall" type="button" role="tab"
                            aria-controls="ranking-overall" aria-selected="true">
                        全体
                    </button>
                </li>
                @foreach ($availableLanguages as $language)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link custom-tab-btn" id="tab-ranking-{{ $language->id }}"
                                data-bs-toggle="tab" data-bs-target="#ranking-{{ $language->id }}"
                                type="button" role="tab" aria-controls="ranking-{{ $language->id }}"
                                aria-selected="false">
                            {{ $language->name }}
                        </button>
                    </li>
                @endforeach
            </ul>

            <!-- タブコンテンツ -->
            <div class="tab-content" id="rankingTabsContent">
                <!-- 全体ランキング -->
                <div class="tab-pane fade show active" id="ranking-overall" role="tabpanel" aria-labelledby="tab-overall">
                    @if (count($rankings) > 0)
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>順位</th>
                                    <th>名前</th>
                                    <th>言語</th>
                                    <th>スコア</th>
                                    <th>提出日時</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rankings as $index => $ranking)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            {{ $ranking->user->name }}
                                            @if ($isExpired)
                                            <button class="btn btn-sm view-code ms-2" data-submission-id="{{ $ranking->id }}" data-bs-toggle="tooltip" title="コードを見る" style="padding: 0.1rem 0.4rem;">
                                                <i class="bi bi-code-square"></i>
                                            </button>
                                            @endif
                                        </td>
                                        <td>{{ $ranking->language->name }}</td>
                                        <td>{{ $ranking->score }}</td>
                                        <td>{{ $ranking->created_at->format('Y/m/d H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="mt-3">コード提出されてません</p>
                    @endif
                </div>

                <!-- 言語別ランキング -->
                @foreach ($availableLanguages as $language)
                    <div class="tab-pane fade" id="ranking-{{ $language->id }}" role="tabpanel"
                         aria-labelledby="tab-ranking-{{ $language->id }}">
                        @if (isset($languageRankings[$language->id]) && count($languageRankings[$language->id]) > 0)
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>順位</th>
                                        <th>名前</th>
                                        <th>スコア</th>
                                        <th>提出日時</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($languageRankings[$language->id] as $index => $ranking)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                {{ $ranking->user->name }}
                                                @if ($isExpired)
                                                <button class="btn btn-sm view-code ms-2" data-submission-id="{{ $ranking->id }}" data-bs-toggle="tooltip" title="コードを見る" style="padding: 0.1rem 0.4rem;">
                                                    <i class="bi bi-code-square"></i>
                                                </button>
                                                @endif
                                            </td>
                                            <td>{{ $ranking->score }}</td>
                                            <td>{{ $ranking->created_at->format('Y/m/d H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="mt-3">{{ $language->name }}での提出はまだありません</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- コード表示用モーダル -->
<div class="modal fade" id="codeModal" tabindex="-1" aria-labelledby="codeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="codeModalLabel">提出コード</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="code-info mb-3">
                    <p><strong>ユーザー名：</strong> <span id="modal-user-name"></span></p>
                    <p><strong>言語：</strong> <span id="modal-language-name"></span></p>
                    <p><strong>スコア：</strong> <span id="modal-score"></span></p>
                    <p><strong>提出日時：</strong> <span id="modal-submitted-at"></span></p>
                </div>
                <div class="code-container p-3 border rounded bg-light position-relative">
                    <pre><code id="modal-code" class="language-plaintext"></code></pre>
                    <button class="btn btn-success position-absolute top-0 end-0 m-2" id="modal-copy-button">コピー</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function copyToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(() => {
                alert('コードをコピーしました！');
            }).catch(err => {
                console.error('コピーに失敗しました:', err);
            });
        } else {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                alert('コードをコピーしました！');
            } catch (err) {
                console.error('コピーに失敗しました:', err);
            }
            document.body.removeChild(textArea);
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

    document.querySelectorAll('.copy-button').forEach(button => {
        button.addEventListener('click', () => {
            const targetClass = button.getAttribute('data-target');
            const codeElement = document.querySelector(`.${targetClass}`);
            if (codeElement) {
                const code = codeElement.textContent;
                copyToClipboard(code);
            }
        });
    });

    function updateScore() {
        const code = document.getElementById('code').value;
        const cleanedCode = code.replace(/<\?php|\?>|\s+/g, '');
        document.getElementById('score').textContent = cleanedCode.length;
    }

    // 言語タブの同期機能
    document.addEventListener('DOMContentLoaded', function() {
        // ツールチップの初期化
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

        // 言語タブを切り替えた時のイベントリスナー
        const languageTabs = document.querySelectorAll('#languageTabs .nav-link');
        const languageSelect = document.getElementById('language_id');

        // 選択されている言語IDを取得して対応するタブを表示
        if (languageSelect) {
            const selectedLanguageId = languageSelect.value;

            // 初期表示時にベストスコアコードをセット
            setCodeForLanguage(selectedLanguageId);

            const selectedTab = document.querySelector('#tab-' + selectedLanguageId);
            if (selectedTab) {
                // 他のタブからactiveクラスを削除
                languageTabs.forEach(tab => {
                    tab.classList.remove('active');
                    tab.setAttribute('aria-selected', 'false');
                    const contentId = tab.getAttribute('data-bs-target');
                    const content = document.querySelector(contentId);
                    if (content) {
                        content.classList.remove('show', 'active');
                    }
                });

                // 選択されたタブをアクティブに
                selectedTab.classList.add('active');
                selectedTab.setAttribute('aria-selected', 'true');
                const contentId = selectedTab.getAttribute('data-bs-target');
                const content = document.querySelector(contentId);
                if (content) {
                    content.classList.add('show', 'active');
                }
            }
        }

        // 言語タブに対するイベントリスナー
        languageTabs.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(event) {
                const languageId = event.target.id.replace('tab-', '');

                // セレクトボックスも同期
                if (languageSelect) {
                    languageSelect.value = languageId;
                }

                // 言語選択時にベストスコアコードをセット
                setCodeForLanguage(languageId);
            });
        });

        // セレクトボックスのchangeイベント
        if (languageSelect) {
            languageSelect.addEventListener('change', function() {
                const languageId = this.value;

                // 対応する言語タブを取得して切り替え
                const languageTab = document.querySelector('#tab-' + languageId);
                if (languageTab) {
                    const bsTab = new bootstrap.Tab(languageTab);
                    bsTab.show();
                }

                // 言語選択時にベストスコアコードをセット
                setCodeForLanguage(languageId);
            });
        }

        // コードを見るボタンのクリックイベント
        const viewCodeButtons = document.querySelectorAll('.view-code');
        viewCodeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const submissionId = this.getAttribute('data-submission-id');
                fetchSubmissionCode(submissionId);
            });
        });

        // モーダル内のコピーボタン
        const modalCopyButton = document.getElementById('modal-copy-button');
        if (modalCopyButton) {
            modalCopyButton.addEventListener('click', function() {
                const codeElement = document.getElementById('modal-code');
                if (codeElement) {
                    const code = codeElement.textContent;
                    copyToClipboard(code);
                }
            });
        }
    });

    // 提出コードを取得する関数
    function fetchSubmissionCode(submissionId) {
        const challengeId = {{ $challenge->id }};
        fetch(`/challenges/${challengeId}/submissions/${submissionId}`)
            .then(response => {
                if (!response.ok) {
                    return response.json().then(error => {
                        throw new Error(error.error || '提出コードの取得に失敗しました');
                    });
                }
                return response.json();
            })
            .then(data => {
                // モーダルにデータをセット
                document.getElementById('modal-user-name').textContent = data.user_name;
                document.getElementById('modal-language-name').textContent = data.language_name;
                document.getElementById('modal-score').textContent = data.score;
                document.getElementById('modal-submitted-at').textContent = data.submitted_at;
                document.getElementById('modal-code').textContent = data.code;

                // モーダルを表示
                const codeModal = new bootstrap.Modal(document.getElementById('codeModal'));
                codeModal.show();
            })
            .catch(error => {
                alert(error.message);
                console.error('コード取得エラー:', error);
            });
    }

    // ユーザーのベストスコア提出コードを保持
    const userBestSubmissions = @json($userBestSubmissions ?? []);

    // テキストエリアが編集されたかどうかを追跡するフラグ
    let codeEdited = false;

    // テキストエリアが編集されたことをマークする関数
    function markAsEdited() {
        codeEdited = true;
    }

    // 言語選択時に対応するベストスコア提出コードをセットする
    function setCodeForLanguage(languageId) {
        // ユーザーが既にコードを編集していた場合は何もしない
        if (codeEdited) {
            return;
        }

        const codeTextarea = document.getElementById('code');

        // 選択された言語のベストスコア提出コードがある場合
        if (userBestSubmissions[languageId]) {
            codeTextarea.value = userBestSubmissions[languageId];
        } else {
            // ベスト提出がない場合は空にする
            codeTextarea.value = '';
        }

        // スコア表示を更新
        updateScore();
    }
</script>

@endsection
