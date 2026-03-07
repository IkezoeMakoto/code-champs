# データベーステーブル設計

以下は、各テーブルの概要をまとめたものです。各テーブルの説明とNULL制約などの属性が記載されています。

---

## challenges table

| Field | Type | Null | description |
|-------|------|------|-------------|
| id | INT | NO | 主キー |
| title | VARCHAR | NO | お題のタイトル |
| description | TEXT | YES | お題の詳細な説明 |
| period_start | DATE | NO | 開始日（月単位） |
| period_end | DATE | NO | 終了日 |
| created_at | TIMESTAMP | NO | 作成日時 |
| updated_at | TIMESTAMP | NO | 更新日時 |

---

## challenge_languages table

各お題がおそらく複数の対応言語を持ち、その言語ごとにサンプルコードが異なる場合に利用します。

| Field | Type | Null | description |
|-------|------|------|-------------|
| id | INT | NO | 主キー |
| challenge_id | INT | NO | challengesテーブルの外部キー |
| language_id | INT | NO | languagesテーブルの外部キー。対象の言語を指定 |
| sample_code | TEXT | NO | お題に対する言語別のサンプルコード |
| created_at | TIMESTAMP | NO | 作成日時 |
| updated_at | TIMESTAMP | NO | 更新日時 |

---

## test_cases table

| Field | Type | Null | description |
|-------|------|------|-------------|
| id | INT | NO | 主キー |
| challenge_id | INT | NO | challengesテーブルの外部キー |
| input | TEXT | NO | テスト入力 |
| expected_output | TEXT | NO | 期待される出力 |
| order | INT | YES | テストケースの順序（任意） |
| created_at | TIMESTAMP | NO | 作成日時 |
| updated_at | TIMESTAMP | NO | 更新日時 |

---

## submissions table

| Field | Type | Null | description |
|-------|------|------|-------------|
| id | INT | NO | 主キー |
| challenge_id | INT | NO | challengesテーブルの外部キー |
| user_id | INT | NO | usersテーブルの外部キー |
| language_id | INT | NO | languagesテーブルの外部キー。提出された言語を参照 |
| code | TEXT | NO | 提出されたコード |
| score | INT | NO | 計算されたスコア（除外後のコード文字数） |
| submitted_at | TIMESTAMP | NO | 提出日時 |
| created_at | TIMESTAMP | NO | 作成日時 |
| updated_at | TIMESTAMP | NO | 更新日時 |

*※ ランキングは score の昇順、同率の場合は提出時間で判定します。*

---

## languages table

| Field | Type | Null | description |
|-------|------|------|-------------|
| id | INT | NO | 主キー |
| name | VARCHAR | NO | 言語名（例：PHP, TSなど） |
| created_at | TIMESTAMP | NO | 作成日時 |
| updated_at | TIMESTAMP | NO | 更新日時 |

---

## users table

| Field | Type | Null | description |
|-------|------|------|-------------|
| id | INT | NO | 主キー |
| name | VARCHAR | NO | ユーザの名前 |
| email | VARCHAR | NO | ユーザのメールアドレス |
| icon_url | VARCHAR | YES | プロフィールアイコンのURLまたはパス（NULLならデフォルト利用） |
| is_admin | BOOLEAN | NO | 管理者フラグ（trueなら管理者権限あり） |
| deleted_at | TIMESTAMP | YES | ソフトデリート用。NULLなら有効なユーザ |
| created_at | TIMESTAMP | NO | 作成日時 |
| updated_at | TIMESTAMP | NO | 更新日時 |

*※ 削除されていないユーザは全員参加者として扱われます。*

---

## user_passkeys table

| Field | Type | Null | description |
|-------|------|------|-------------|
| id | INT | NO | 主キー |
| user_id | INT | NO | usersテーブルの外部キー |
| credential_id | VARCHAR | NO | パスキー認証に使用される識別子 |
| public_key | TEXT | NO | 認証用の公開鍵情報 |
| sign_count | INT | NO | セキュリティのための署名カウンタ |
| device_name | VARCHAR | YES | 登録されたデバイス名（例："iPhone", "MacBook"） |
| created_at | TIMESTAMP | NO | 作成日時 |
| updated_at | TIMESTAMP | NO | 更新日時 |

---

その他の実装や設計に関する詳細については、各機能ごとのドキュメントを参照してください。
