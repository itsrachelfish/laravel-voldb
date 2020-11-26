@extends('app')

@section('content')

<h1 class="title">User Shift Review</h1>
@if($event->departments->count())
            <h2>Available Shifts</h2>

            <form class="form-inline event-filter">
                Filter:

                <div class="form-group">
                    <select class="form-control filter-days">
                        <option value="all">Show All Days</option>

                        @foreach($event->days(true) as $day)
                            <option value="{{ $day->date->format('Y-m-d') }}">{{ $day->name }} - {{ $day->date->format('Y-m-d') }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <select class="form-control filter-departments">
                        <option value="all">Show All Departments</option>
                        @foreach($event->departments->sortBy('name') as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>

            <hr>
        <div class="days">
            @foreach($event->days(true) as $day)
                    <div class="day" data-date="{{ $day->date->format('Y-m-d') }}">
                        <div class="heading">
                            <h3>{{ $day->name }}</h3>
                            &mdash; <i>{{ $day->date->format('Y-m-d') }}</i>
                        </div>

                            <div class="department-wrap">
                                @foreach($event->departments->sortBy('name') as $department)
                                    <?php

                                    if($department->slots->where('start_date', $day->date->format('Y-m-d'))->isEmpty())
                                        continue;
                                    ?>

                                    <div class="department" data-id="{{ $department->id }}">
                                        <div class="title">
                                            <b>{{ $department->name }}</b>

                                            @if($department->description)
                                                <span class="description">
                                                    <span class="glyphicon glyphicon-question-sign"></span>

                                                    <div class="tip hidden">
                                                        {!! nl2br(e($department->description)) !!}
                                                        <hr>
                                                        <a class="btn btn-primary">Close</a>
                                                    </div>
                                                </span>
                                            @endif

                                            @can('edit-department')
                                                <span class="edit">
                                                    <a href="/department/{{ $department->id }}/edit">
                                                        <span class="glyphicon glyphicon-pencil"></span>
                                                    </a>
                                                </span>
                                            @endcan
                                        </div>

                                            @foreach($department->schedule as $schedule)
                                                <?php
                                                    if($schedule->slots->where('start_date', $day->date->format('Y-m-d'))->
                                                        where('user_id','!=',null)->isEmpty())
                                                    continue;
                                                ?>
                                                    
                                                    <div class="title">
                                                        <b>{{$schedule->shift->name}}</b>
                                                    </div>
                                                    
                                                <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Username</th>
                                                        <th>Start Time</th>
                                                        <th>End Time</th>
                                                        <th>Duration</th>
                                                        <th>Performance</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    <?php $slots = $schedule->slots->where('start_date', $day->date->format('Y-m-d'))->where('user_id','!=',null)->sortBy('start_time');
                                                    ?>

                                                    @foreach($slots as $slot)
                                                    <tr>
                                                        <td>{{$slot->user['name']}}</td>
                                                        <td>{{$slot->start_time}}</td>
                                                        <td>{{$slot->end_time}}</td>
                                                        <td>{{$schedule->duration}}</td>
                                                        <td>
                                                            <div class="value volunteer">
                                                                <input type="hidden" class="csrf-token" value="{{ csrf_token() }}">
                                                                <input type="hidden" class="slot-number" value="{{ $slot->id }}">

                                                                <select class="volunteer-status-review" data-status="{{ $slot->status }}">
                                                                    <option value="">Select One</option>
                                                                    <option value="flaked">Flaked</option>
                                                                    <option value="late">Late</option>
                                                                    <option value="ontime">On Time</option>
                                                                    <option value="excellent">Excellent</option>
                                                                </select>

                                                                <span class="toast-message">&ensp;
                                                                    <a href="#">Saved!</a>&ensp;
                                                                </span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                                </table>
                                            @endforeach
                                    </div>
                                @endforeach
                            </div> <!-- / .department-wrap -->
                    </div>
                @endforeach
            </div>
@endif

@endsection