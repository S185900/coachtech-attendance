@extends('admin.layouts.admin-header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-attendance-detail.css')}}">
@endsection

<!-- 勤怠詳細画面（管理者） http://localhost/admin/attendance/{id} -->
@section('content')
<div class="attendance-detail-container">
    <h1 class="page-title">勤怠詳細</h1>

    <form action="{{ route('admin.attendance.approve', ['id' => $attendance->id]) }}" method="POST">
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
            {{-- 出勤・退勤部分 --}}
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <div class="time-inputs">
                        {{-- $isPendingに関わらず、管理者は編集できる必要があるのでinputにします --}}
                        {{-- 出勤時間 --}}
                        <input type="time" name="start_time" 
                            value="{{ old('start_time', $isPending ? \Carbon\Carbon::parse($pendingRequest->corrected_start_time)->format('H:i') : $attendance->start_time->format('H:i')) }}" 
                            class="input-time">

                        <span class="range-separator">〜</span>

                        {{-- 退勤時間 --}}
                        <input type="time" name="end_time" 
                            value="{{ old('end_time', $isPending ? ($pendingRequest->corrected_end_time ? \Carbon\Carbon::parse($pendingRequest->corrected_end_time)->format('H:i') : '') : ($attendance->end_time ? $attendance->end_time->format('H:i') : '')) }}" 
                            class="input-time">

                    </div>

                    {{-- 出勤・退勤時間のエラーメッセージ --}}
                    @error('start_time')
                        <p class="status-message">{{ $message }}</p>
                    @enderror
                    @error('end_time')
                        <p class="status-message">{{ $message }}</p>
                    @enderror
                </td>
            </tr>

            {{-- 休憩時間のループ --}}
            @foreach($attendance->restTimes as $index => $rest)
            <tr>
                <th>休憩{{ $index > 0 ? $index + 1 : '' }}</th>
                <td>
                    <div class="time-inputs">
                        <input type="time" name="rests[{{ $rest->id }}][start]" value="{{ old("rests.{$rest->id}.start", $rest->start_time->format('H:i')) }}" class="input-time">
                        <span class="range-separator">〜</span>
                        <input type="time" name="rests[{{ $rest->id }}][end]" value="{{ old("rests.{$rest->id}.end", $rest->end_time ? $rest->end_time->format('H:i') : '') }}" class="input-time">
                    </div>
                    {{-- 休憩個別のエラー表示 --}}
                    @error("rests.{$rest->id}.start")
                        <p class="status-message">{{ $message }}</p>
                    @enderror
                    @error("rests.{$rest->id}.end")
                        <p class="status-message">{{ $message }}</p>
                    @enderror
                </td>
            </tr>
            @endforeach

            {{-- 備考欄部分 --}}
            <tr>
                <th>備考</th>
                <td>
                    {{-- ここも old() を追加し、承認待ちの場合は申請理由をデフォルト値にします --}}
                    <textarea name="reason" class="input-textarea">{{ old('reason', $isPending ? $pendingRequest->reason : $attendance->reason) }}</textarea>

                    @error('reason')
                        <p class="status-message">{{ $message }}</p>
                    @enderror
                </td>
            </tr>
        </table>

        <div class="form-actions">
            @if($isPending)
                {{-- 修正申請中の場合は「承認」ボタン --}}
                <button type="submit" class="submit-button">承認</button>
            @else
                {{-- 通常時は「修正」ボタン --}}
                <button type="submit" class="submit-button">修正</button>
            @endif
        </div>
    </form>

</div>
@endsection