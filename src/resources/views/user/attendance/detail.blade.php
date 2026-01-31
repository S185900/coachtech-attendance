@extends('user.layouts.user-header')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user-attendance-detail.css')}}">
@endsection

{{-- 勤怠詳細画面（一般ユーザー） http://localhost/attendance/detail/{id} --}}
@section('content')
    <div class="attendance-detail-container">
        <h1 class="page-title">勤怠詳細</h1>

        <form action="{{ route('attendance.update', ['id' => $attendance->id]) }}" method="POST">
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
                        <span class="range-separator-hidden">〜</span>
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
                            @if($isPending)

                                <span class="time-text">{{ $pendingRequest->corrected_start_time->format('H:i') }}</span>
                                <span class="range-separator">〜</span>
                                <span class="time-text">{{ $pendingRequest->corrected_end_time ? $pendingRequest->corrected_end_time->format('H:i') : '' }}</span>

                            @else

                                <input type="time" name="start_time" value="{{ old('start_time', $attendance->start_time->format('H:i')) }}" class="input-time">
                                <span class="range-separator">〜</span>
                                <input type="time" name="end_time" value="{{ old('end_time', $attendance->end_time ? $attendance->end_time->format('H:i') : '') }}" class="input-time">

                            @endif
                        </div>

                        @if ($errors->has('start_time') || $errors->has('end_time'))
                            <p class="status-message">
                                {{ $errors->first('start_time') ?: $errors->first('end_time') }}
                            </p>
                        @endif

                    </td>
                </tr>

                {{-- 休憩時間の表示 --}}
                @foreach($displayRestTimes as $index => $rest)
                    @php
                        $restKey = $rest['rest_id'] ?? $index;
                        if ($isPending && empty($rest['start'])) {
                            continue;
                        }
                    @endphp
                    <tr>
                        <th>休憩{{ $index > 0 ? $index + 1 : '' }}</th>

                        <td>
                            <div class="time-inputs">

                                @if($isPending)

                                    <span class="time-text">{{ $rest['start'] }}</span>
                                    <span class="range-separator">〜</span>
                                    <span class="time-text">{{ $rest['end'] }}</span>

                                @else

                                    @php
                                        $startTime = old("rests.{$restKey}.start", $rest['start']);
                                        $endTime = old("rests.{$restKey}.end", $rest['end']);
                                    @endphp

                                    <input type="{{ $startTime ? 'time' : 'text' }}" 
                                        name="rests[{{ $restKey }}][start]" 
                                        value="{{ $startTime }}" 
                                        class="input-time"
                                        onfocus="(this.type='time')" 
                                        onblur="if(!this.value)this.type='text'">

                                    <span class="range-separator">〜</span>

                                    <input type="{{ $endTime ? 'time' : 'text' }}" 
                                        name="rests[{{ $restKey }}][end]" 
                                        value="{{ $endTime }}" 
                                        class="input-time"
                                        onfocus="(this.type='time')" 
                                        onblur="if(!this.value)this.type='text'">

                                @endif

                            </div>

                            @if (!$isPending && ($errors->has("rests.{$restKey}.start") || $errors->has("rests.{$restKey}.end")))
                                <p class="status-message">
                                    {{ $errors->first("rests.{$restKey}.start") ?: $errors->first("rests.{$restKey}.end") }}
                                </p>
                            @endif

                        </td>

                    </tr>
                @endforeach

                @if(!$isPending)
                    <tr>
                        <th>休憩{{ count($displayRestTimes) + 1 }}</th>
                        <td>
                            <div class="time-inputs">

                                @if($isPending)

                                    <input type="text" name="rests[new][start]" value="{{ old('rests.new.start') }}" 
                                        class="input-time"
                                        onfocus="(this.type='time')" onblur="if(!this.value)this.type='text'">

                                    <span class="range-separator">〜</span>

                                    <input type="text" name="rests[new][end]" value="{{ old('rests.new.end') }}" 
                                        class="input-time"
                                        onfocus="(this.type='time')" onblur="if(!this.value)this.type='text'">

                                @else

                                    @php
                                        $startTime = old("rests.{$restKey}.start", $rest['start']);
                                        $endTime = old("rests.{$restKey}.end", $rest['end']);
                                    @endphp

                                    <input type="text" name="rests[new][start]" value="{{ old('rests.new.start') }}" 
                                        class="input-time"
                                        onfocus="(this.type='time')" onblur="if(!this.value)this.type='text'">

                                    <span class="range-separator">〜</span>

                                    <input type="text" name="rests[new][end]" value="{{ old('rests.new.end') }}" 
                                        class="input-time"
                                        onfocus="(this.type='time')" onblur="if(!this.value)this.type='text'">

                                @endif

                            </div>

                        </td>
                    </tr>
                @endif

                {{-- 備考欄部分 --}}
                <tr>
                    <th>備考</th>
                    <td>
                        @if($isPending)
                            <p class="note-text">{{ $displayReason }}</p>
                        @else
                            <textarea name="reason" class="input-textarea">{{ old('reason', $displayReason) }}</textarea>

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