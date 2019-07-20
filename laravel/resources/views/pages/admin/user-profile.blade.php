<?php

use App\Helpers;

?>

@extends('app')

@section('content')
    <h1>Viewing User Profile</h1>
    <hr>

    <div class="profile">
        <div class="row">
            <div class="col-sm-2 title">Username</div>
            <div class="col-sm-10 value">{{ $user->name }}</div>
        </div>

        <div class="row">
            <div class="col-sm-2 title">Email</div>
            <div class="col-sm-10 value">{{ $user->email }}</div>
        </div>

        <h3>Additional information</h3>

        <div class="row">
            <div class="col-sm-2 title">Full Name</div>
            <div class="col-sm-10 value">{{ $user->data->full_name or 'Not Provided' }}</div>
        </div>

        <div class="row">
            <div class="col-sm-2 title">Burner Name</div>
            <div class="col-sm-10 value">{{ Helpers::displayName($user, 'Not Provided') }}</div>
        </div>

        <div class="row">
            <div class="col-sm-2 title">Camp</div>
            <div class="col-sm-10 value">{{ $user->data->camp or 'Not Provided' }}</div>
        </div>

        <div class="row">
            <div class="col-sm-2 title">Phone</div>
            <div class="col-sm-10 value">{{ $user->data->phone or 'Not Provided' }}</div>
        </div>

        <div class="row">
            <div class="col-sm-2 title">Emergency Contact</div>
            <div class="col-sm-10 value">{{ $user->data->emergency_name or 'Not Provided' }}</div>
        </div>

        <div class="row">
            <div class="col-sm-2 title">Emergency Phone</div>
            <div class="col-sm-10 value">{{ $user->data->emergency_phone or 'Not Provided' }}</div>
        </div>

        <div class="row">
            <div class="col-sm-2 title">Birthday</div>
            <div class="col-sm-10 value">{{ $user->data->birthday or 'Not Provided' }}</div>
        </div>

        <h3>User Roles</h3>

        <div class="user-roles">
            @if( count($user->getRoleNames()) < 1 )
                <div>User has no roles</div>
            @endif
            @foreach ($user->getRoleNames() as $item)
                <li>{{$item}}</li>
            @endforeach
        </div>
        <hr>
    <a href="/user/{{$user->id}}/edit" class="btn btn btn-primary">Edit User</a>
    </div>

    @if($user->uploads->count())
        <hr>
        <h2>Uploaded Files</h2>

        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>File</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @foreach($user->uploads as $upload)
                    <tr>
                        <td>{{ $upload->name }}</td>
                        <td>{{ $upload->description }}</td>
                        <td><a href='/files/user/{{ $upload->file }}'>{{ $upload->file }}</a></td>
                        <td>{{ ucwords($upload->status) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
