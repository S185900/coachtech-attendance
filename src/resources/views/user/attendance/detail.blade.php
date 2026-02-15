@extends('user.layouts.user-header')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user-attendance-detail.css')}}">
@endsection

{{-- 勤怠詳細画面（一般ユーザー） --}}
@section('content')
    <div class="attendance-detail-container">
        <h1 class="page-title">勤怠詳細</h1>

        <form class="attendance-detail-form" action="{{ route('attendance.update', ['id' => $attendance->id]) }}" method="POST">
            @csrf

            <table class="detail-table">

                <tr class="detail-table-row">
                    <th class="detail-table-th">名前</th>
                    <td class="detail-table-td">
                        <span class="user-name">{{ $attendance->user->name }}</span>
                    </td>
                </tr>

                <tr class="detail-table-row">
                    <th class="detail-table-th">日付</th>
                    <td class="detail-table-td date-inputs-container">
                        <div class="date-year-wrapper">
                            <span class="year-unit">{{ $attendance->date->format('Y') }}年</span>
                        </div>
                        <span class="range-separator-hidden">〜</span>
                        <div class="date-day-wrapper">
                            <span class="date-unit">{{ $attendance->date->format('n月j日') }}</span>
                        </div>
                    </td>
                </tr>

                <tr class="detail-table-row">
                    <th class="detail-table-th">出勤・退勤</th>
                    <td class="detail-table-td">
                        <div class="time-inputs">
                            @if($isPending)

                                <span class="time-text">{{ $pendingRequest->corrected_start_time->format('H:i') }}</span>
                                <span class="range-separator">〜</span>
                                <span class="time-text">{{ $pendingRequest->corrected_end_time ? $pendingRequest->corrected_end_time->format('H:i') : '' }}</span>

                            @else

                                <input type="time" name="start_time" value="{{ old('start_time', $attendance->start_time->format('H:i')) }}" class="input-time time-input-control">
                                <span class="range-separator">〜</span>
                                <input type="time" name="end_time" value="{{ old('end_time', $attendance->end_time ? $attendance->end_time->format('H:i') : '') }}" class="input-time time-input-control">

                            @endif
                        </div>

                        @if ($errors->has('start_time') || $errors->has('end_time'))
                            <p class="status-message">
                                {{ $errors->first('start_time') ?: $errors->first('end_time') }}
                            </p>
                        @endif

                    </td>
                </tr>

                @foreach($displayRestTimes as $index => $rest)
                    <tr class="detail-table-row">
                        <th class="detail-table-th">休憩{{ $index > 0 ? $index + 1 : '' }}</th>
                        <td class="detail-table-td">
                            <div class="time-inputs">

                                @if($isPending)

                                    <span class="time-text">{{ $rest['start'] }}</span>
                                    <span class="range-separator">〜</span>
                                    <span class="time-text">{{ $rest['end'] }}</span>

                                @else

                                    <input type="{{ old("rests.{$rest['key']}.start", $rest['start']) ? 'time' : 'text' }}"
                                        name="rests[{{ $rest['key'] }}][start]"
                                        value="{{ old("rests.{$rest['key']}.start", $rest['start']) }}"
                                        class="input-time time-input-control"
                                        onfocus="(this.type='time')"
                                        onblur="if(!this.value)this.type='text'">

                                    <span class="range-separator">〜</span>

                                    <input type="{{ old("rests.{$rest['key']}.end", $rest['end']) ? 'time' : 'text' }}" 
                                        name="rests[{{ $rest['key'] }}][end]"
                                        value="{{ old("rests.{$rest['key']}.end", $rest['end']) }}"
                                        class="input-time time-input-control"
                                        onfocus="(this.type='time')"
                                        onblur="if(!this.value)this.type='text'">

                                @endif

                            </div>

                            @if (!$isPending && ($errors->has("rests.{$rest['key']}.start") || $errors->has("rests.{$rest['key']}.end")))
                                <p class="status-message">
                                    {{ $errors->first("rests.{$rest['key']}.start") ?: $errors->first("rests.{$rest['key']}.end") }}
                                </p>
                            @endif

                        </td>
                    </tr>
                @endforeach

                @if(!$isPending)
                    <tr class="detail-table-row">
                        <th class="detail-table-th">休憩{{ count($displayRestTimes) + 1 }}</th>
                        <td class="detail-table-td">
                            <div class="time-inputs">
                                <input type="text" name="rests[new][start]" value="{{ old('rests.new.start') }}"
                                    class="input-time time-input-control"
                                    onfocus="(this.type='time')" onblur="if(!this.value)this.type='text'">

                                <span class="range-separator">〜</span>

                                <input type="text" name="rests[new][end]" value="{{ old('rests.new.end') }}"
                                    class="input-time time-input-control"
                                    onfocus="(this.type='time')" onblur="if(!this.value)this.type='text'">
                            </div>

                            @if ($errors->has('rests.new.start') || $errors->has('rests.new.end'))
                                <p class="status-message">
                                    {{ $errors->first('rests.new.start') ?: $errors->first('rests.new.end') }}
                                </p>
                            @endif
                        </td>
                    </tr>
                @endif

                <tr class="detail-table-row">
                    <th class="detail-table-th">備考</th>
                    <td class="detail-table-td">
                        @if($isPending)
                            <p class="note-text">{{ $displayReason }}</p>
                        @else
                            <textarea name="reason" class="input-textarea" autocomplete="off">{{ old('reason', $displayReason) }}</textarea>

                            @error('reason')
                                <p class="status-message">{{ $message }}</p>
                            @enderror

                        @endif
                    </td>
                </tr>
            </table>

            <div class="form-actions">
                @if($isPending)
                    <p class="info-message">*承認待ちのため修正はできません。</p>
                @else
                    <button type="submit" class="submit-button">修正</button>
                @endif
            </div>

        </form>

    </div>
@endsection