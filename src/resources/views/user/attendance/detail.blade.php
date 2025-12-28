@extends('user.layouts.user-header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user-attendance-detail.css')}}">
@endsection

<!-- 勤怠詳細画面（一般ユーザー） http://localhost/attendance/detail/{id} -->
@section('content')
<div class="attendance-detail-container">
    <h1 class="page-title">勤怠詳細</h1>

    <form action="{{ route('attendance.update', ['attendance_id' => $attendance->id]) }}" method="POST">
        @csrf
        <table class="detail-table">
            <tr>
                <th>名前</th>
                <td>
                    <span class="user-name">{{ $attendance->user->name }}</span>
                </td>
            </tr>
            <tr>
                <th>日付</th>
                <td class="date-inputs-container">
                    <div class="date-year-wrapper">
                        <span class="year-unit">{{ $attendance->date->format('Y') }}年</span>
                    </div>
                    <span class="range-separator-hidden">〜</span> {{-- 中央を揃えるための見えないスペーサー --}}
                    <div class="date-day-wrapper">
                        <span class="date-unit">{{ $attendance->date->format('n月j日') }}</span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td class="time-inputs">
                    @if($isPending)
                        <span class="time-text">{{ $attendance->start_time->format('H:i') }}</span>
                        <span class="range-separator">〜</span>
                        <span class="time-text">{{ $attendance->end_time ? $attendance->end_time->format('H:i') : '' }}</span>
                    @else
                        <input type="time" name="start_time" value="{{ $attendance->start_time->format('H:i') }}" class="input-time">
                        <span class="range-separator">〜</span>
                        <input type="time" name="end_time" value="{{ $attendance->end_time ? $attendance->end_time->format('H:i') : '' }}" class="input-time">
                    @endif

                    {{-- 出勤・退勤時間のエラーメッセージ --}}
                    @error('start_time')
                        <p class="status-message">{{ $message }}</p>
                    @enderror
                </td>
            </tr>

            {{-- 休憩時間のループ --}}
            @foreach($attendance->restTimes as $index => $rest)
            <tr>
                <th>休憩{{ $index > 0 ? $index + 1 : '' }}</th>
                <td class="time-inputs">
                    @if($isPending)
                        <span class="time-text">{{ $rest->start_time->format('H:i') }}</span>
                        <span class="range-separator">〜</span>
                        <span class="time-text">{{ $rest->end_time ? $rest->end_time->format('H:i') : '' }}</span>
                    @else
                        <input type="time" name="rests[{{ $rest->id }}][start]" value="{{ $rest->start_time->format('H:i') }}" class="input-time">
                        <span class="range-separator">〜</span>
                        <input type="time" name="rests[{{ $rest->id }}][end]" value="{{ $rest->end_time ? $rest->end_time->format('H:i') : '' }}" class="input-time">
                    @endif
                </td>
            </tr>

            {{-- 休憩時間のエラーメッセージ --}}
            @if($errors->has("rests.{$rest->id}.start") || $errors->has("rests.{$rest->id}.end"))
                <tr>
                    <th></th>
                    <td><p class="status-message">休憩時間もしくは休憩終了時間が不適切な値です</p></td>
                </tr>
            @endif

            @endforeach

            {{-- 備考欄 --}}
            <tr>
                <th>備考</th>
                <td>
                    @if($isPending)
                        {{-- 承認待ちの場合は申請テーブル（StampCorrectionRequest）に保存した理由を表示 --}}
                        <p class="note-text">{{ $pendingRequest->reason }}</p>
                    @else
                        <textarea name="reason" class="input-textarea">{{ $attendance->reason }}</textarea>

                        {{-- 備考欄のエラーメッセージ --}}
                        @error('reason')
                            <p class="status-message">{{ $message }}</p>
                        @enderror
                    @endif
                </td>
            </tr>
        </table>

        <div class="form-actions">
            {{-- 修正申請が「承認待ち」の場合 --}}
            @if($isPending)
                <p class="status-message">*承認待ちのため修正はできません。</p>
            @else
                <button type="submit" class="submit-button">修正</button>
            @endif
        </div>
    </form>

</div>
@endsection