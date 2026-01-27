@extends('admin.layouts.admin-header')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin-stamp_correction_request-approve.css')}}">
@endsection

{{-- 修正申請承認画面（管理者） http://localhost/stamp_correction_request/approve/{attendance_correct_request_id} --}}
@section('content')
    <div class="attendance-detail-container">
        <h1 class="page-title">勤怠詳細</h1>

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

                {{-- 出勤・退勤 --}}
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <div class="time-inputs">
                            <span class="time-text">{{ $request->corrected_start_time->format('H:i') }}</span>
                            <span class="range-separator">〜</span>
                            <span class="time-text">{{ $request->corrected_end_time ? $request->corrected_end_time->format('H:i') : '' }}</span>
                        </div>
                    </td>
                </tr>

                {{-- 休憩時間 --}}
                @php $displayRestTimes = $request->corrected_rest_times ?? []; @endphp

                @foreach($displayRestTimes as $index => $rest)
                    <tr>
                        <th>休憩{{ $index > 0 ? $index + 1 : '' }}</th>
                        <td>
                            <div class="time-inputs">
                                <span class="time-text">{{ \Carbon\Carbon::parse($rest['start'])->format('H:i') }}</span>
                                <span class="range-separator">〜</span>
                                <span class="time-text">{{ isset($rest['end']) ? \Carbon\Carbon::parse($rest['end'])->format('H:i') : '' }}</span>
                            </div>
                        </td>
                    </tr>
                @endforeach

                <tr>
                    <th>休憩{{ count($displayRestTimes) > 0 ? count($displayRestTimes) + 1 : '' }}</th>
                    <td>
                        <div class="time-inputs">
                            <span class="time-text"></span>
                            <span class="time-text"></span>
                        </div>
                    </td>
                </tr>

                {{-- 備考欄 --}}
                <tr>
                    <th>備考</th>
                    <td>
                        <p class="note-text">{{ $request->reason }}</p>
                    </td>
                </tr>

            </table>

            <div class="form-actions">
                @if($request->status === \App\Models\StampCorrectionRequest::STATUS_PENDING)
                    <button type="submit" class="submit-button">承認</button>
                @else
                    <button type="button" class="submit-button approved" disabled>承認済み</button>
                @endif
            </div>

        </form>
    </div>
@endsection