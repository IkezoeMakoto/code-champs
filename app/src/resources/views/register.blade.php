@extends('layouts.app')

@section('title', 'ユーザ登録')

@section('content')
<div class="container mt-5">
    <h1 class="text-center mb-4">ユーザ登録</h1>
    <form action="/register" method="POST" class="needs-validation" novalidate>
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">名前</label>
            <input type="text" id="name" name="name" class="form-control" required>
            <div class="invalid-feedback">名前を入力してください。</div>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">メールアドレス</label>
            <input type="email" id="email" name="email" class="form-control" required>
            <div class="invalid-feedback">有効なメールアドレスを入力してください。</div>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">パスワード</label>
            <input type="password" id="password" name="password" class="form-control" required>
            <div class="invalid-feedback">パスワードを入力してください。</div>
        </div>
        <button type="submit" class="btn btn-primary w-100">登録</button>
    </form>
</div>
@endsection
