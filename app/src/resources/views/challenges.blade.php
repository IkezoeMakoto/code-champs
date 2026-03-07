@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">お題一覧</h1>
        @if(auth()->user() && auth()->user()->is_admin)
            <a href="{{ route('challenges.create') }}" class="btn btn-primary">お題登録</a>
        @endif
    </div>
    @if ($challenges->isEmpty())
        <p>現在、お題はありません。</p>
    @else
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th class="col-6 text-center">タイトル</th>
            <th class="col-2 text-center">対応言語</th>
            <th class="col-1 text-center">ステータス</th>
            <th class="col-3 text-center">期間</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($challenges as $challenge)
        <tr>
            <td>
                @if (now()->gte($challenge->period_start))
                    <a href="{{ route('challenge.show', $challenge->id) }}">{{ $challenge->title }}</a>
                @else
                    {{ $challenge->title }}
                @endif
            </td>
            <td class="text-center">
                @foreach ($challenge->languages as $language)
                    <span class="badge bg-secondary">{{ $language->name }}</span>
                @endforeach
            </td>
            <td class="text-center">
                @if (now()->lt($challenge->period_start))
                    <span class="badge bg-info text-dark">開催前</span>
                @elseif (now()->between($challenge->period_start, $challenge->period_end))
                    <span class="badge bg-success">開催中</span>
                @else
                    <span class="badge bg-danger">終了</span>
                @endif
            </td>
            <td class="text-center">{{ $challenge->period_start->format('Y-m-d') }} ~ {{ $challenge->period_end->format('Y-m-d') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
    @endif
</div>
@endsection
