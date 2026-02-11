@extends('admin.layouts.admin-header')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin-attendance-detail.css')}}">
@endsection

{{-- 勤怠詳細画面（管理者） http://localhost/admin/attendance/{id} --}}
@section('content')
    <div class="attendance-detail-container">
        <h1 class="page-title">勤怠詳細</h1>

        <form class="attendance-detail-form" action="{{ route('admin.attendance.approve', ['id' => $attendance->id]) }}" method="POST">
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

                {{-- 出勤・退勤部分 --}}
                <tr class="detail-table-row">
                    <th class="detail-table-th">出勤・退勤</th>
                    <td class="detail-table-td">
                        <div class="time-inputs">

                            @if($isPending)

                                <span class="time-text">{{ \Carbon\Carbon::parse($pendingRequest->corrected_start_time)->format('H:i') }}</span>
                                <span class="range-separator">〜</span>
                                <span class="time-text">{{ $pendingRequest->corrected_end_time ? \Carbon\Carbon::parse($pendingRequest->corrected_end_time)->format('H:i') : '' }}</span>

                            @else

                                <input type="time" name="start_time" value="{{ old('start_time', $attendance->start_time->format('H:i')) }}" class="input-time time-input-control">
                                <span class="range-separator">〜</span>
                                <input type="time" name="end_time" value="{{ old('end_time', $attendance->end_time ? $attendance->end_time->format('H:i') : '') }}" class="input-time time-input-control">

                            @endif

                        </div>

                        @if ($errors->has('start_time') || $errors->has('end_time'))
                            <p class="status-message">
                                {{ $errors->first('end_time') ?: $errors->first('start_time') }}
                            </p>
                        @endif

                    </td>
                </tr>

                {{-- 休憩時間の表示 --}}
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

                                    <input type="{{ old("rests.{$rest['rest_id']}.start", $rest['start']) ? 'time' : 'text' }}" 
                                        name="rests[{{ $rest['rest_id'] }}][start]"
                                        value="{{ old("rests.{$rest['rest_id']}.start", $rest['start']) }}"
                                        class="input-time time-input-control"
                                        onfocus="(this.type='time')" onblur="if(!this.value)this.type='text'">

                                    <span class="range-separator">〜</span>

                                    <input type="{{ old("rests.{$rest['rest_id']}.end", $rest['end']) ? 'time' : 'text' }}"
                                        name="rests[{{ $rest['rest_id'] }}][end]"
                                        value="{{ old("rests.{$rest['rest_id']}.end", $rest['end']) }}"
                                        class="input-time time-input-control"
                                        onfocus="(this.type='time')" onblur="if(!this.value)this.type='text'">

                                @endif

                            </div>

                            @if (!$isPending && ($errors->has("rests.{$rest['rest_id']}.start") || $errors->has("rests.{$rest['rest_id']}.end")))
                                <p class="status-message">
                                    {{ $errors->first("rests.{$rest['rest_id']}.start") ?: $errors->first("rests.{$rest['rest_id']}.end") }}
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

                {{-- 備考欄部分 --}}
                <tr class="detail-table-row">
                    <th class="detail-table-th">備考</th>
                    <td class="detail-table-td">
                        @if($isPending)
                            <p class="note-text">{{ old('reason', $pendingRequest->reason) }}</p>
                        @else
                            <textarea name="reason" class="input-textarea">{{ old('reason', $attendance->reason) }}</textarea>

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
                    <button type="submit" class="submit-button approve">承認</button>
                @else
                    <button type="submit" class="submit-button update">修正</button>
                @endif
            </div>

        </form>
    </div>
@endsection