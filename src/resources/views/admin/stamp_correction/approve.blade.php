@extends('admin.layouts.admin-header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-stamp_correction_request-approve.css')}}">
@endsection

<!-- 修正申請承認画面（管理者） http://localhost/stamp_correction_request/approve/{attendance_correct_request_id}-->

@section('content')
<div class="attendance-detail-container">
    <h1 class="page-title">勤怠詳細</h1>

    {{-- 管理者用の承認ルートへ送信 --}}
    <form action="{{ route('admin.stamp_correction.update', ['id' => $request->id]) }}" method="POST">
        @csrf
        <table class="detail-table">
            <tr>
                <th>名前</th>
                <td>
                    <span class="user-name">{{ $request->user->name }}</span>
                </td>
            </tr>
            <tr>
                <th>日付</th>
                <td class="date-inputs-container">
                    <div class="date-year-wrapper">
                        <span class="year-unit">{{ $request->attendance->date->format('Y') }}年</span>
                    </div>
                    <span class="range-separator-hidden">〜</span>
                    <div class="date-day-wrapper">
                        <span class="date-unit">{{ $request->attendance->date->format('n月j日') }}</span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <div class="time-inputs">
                        <span class="time-text">{{ \Carbon\Carbon::parse($request->corrected_start_time)->format('H:i') }}</span>
                        <span class="range-separator">〜</span>
                        <span class="time-text">{{ $request->corrected_end_time ? \Carbon\Carbon::parse($request->corrected_end_time)->format('H:i') : '' }}</span>
                    </div>
                </td>
            </tr>

            {{-- 休憩時間（申請データに基づき表示） --}}
            <tr>
                <th>休憩</th>
                <td>
                    @php
                        // JSONデータを配列にデコード
                        $restTimes = is_string($request->corrected_rest_times) 
                            ? json_decode($request->corrected_rest_times, true) 
                            : $request->corrected_rest_times;
                    @endphp

                    @if(!empty($restTimes))
                        @foreach($restTimes as $index => $rest)
                            <div class="time-inputs" style="{{ $index > 0 ? 'margin-top: 10px;' : '' }}">
                                <span class="time-text">{{ \Carbon\Carbon::parse($rest['start'])->format('H:i') }}</span>
                                <span class="range-separator">〜</span>
                                <span class="time-text">{{ isset($rest['end']) ? \Carbon\Carbon::parse($rest['end'])->format('H:i') : '' }}</span>
                            </div>
                        @endforeach
                    @endif
                </td>
            </tr>

            {{-- 備考欄 --}}
            <tr>
                <th>備考</th>
                <td>
                    {{-- ここも status で判定 --}}
                    <p class="note-text">{{ $request->reason }}</p>
                </td>
            </tr>
        </table>

        <div class="form-actions">
            @if($request->status === 0)
                {{-- 承認待ち（status:0）なら承認ボタンを出す --}}
                <button type="submit" class="submit-button">承認</button>
            @else
                {{-- 承認済み（status:1）ならボタンを無効化する --}}
                <button type="button" class="submit-button approved" disabled>承認済み</button>
            @endif
        </div>
    </form>

</div>
@endsection