<?php

use App\Helpers;

?>

@extends('app')

@section('content')
    <h1>Edit User Information</h1>
    <hr>

    <div class="profile">

        {!! Form::open(['url' => 'user/'.$user->id.'/edit']) !!}
        <input type="hidden" name="type" value="data">
        <input type="hidden" class="user-id" value="{{ $user->id }}">
        <input type="hidden" class="csrf-token" value="{{ csrf_token() }}">

        <h3>Account Information</h3>

        @include('partials/form/text', 
        [
            'name' => 'name', 
            'label' => 'Username', 
            'placeholder' => 'Their login name',
            'value' => (is_null($user->name)) ? '' : $user->name
        ])
        
        @include('partials/form/text', 
        [
            'name' => 'email', 
            'label' => 'Email address', 
            'placeholder' => 'Their email',
            'value' => (is_null($user->email)) ? '' : $user->email
        ])

        <hr>

        <h3>User details</h3>

        @include('partials/form/text',
        [
            'name' => 'full_name',
            'label' => 'Full Name',
            'placeholder' => "User's name in the Default World",
            'help' => "Required. User's full name is used for reporting and ticketing purposes",
            'value' => (is_null($user->data)) ? '' : $user->data->full_name
        ])

        @include('partials/form/text',
        [
            'name' => 'burner_name',
            'label' => 'Burner Name',
            'placeholder' => 'Users name on the Playa',
            'help' => "This name will be shown to other users when they sign up for a shift",
            'value' => Helpers::displayName($user)
        ])

        @include('partials/form/text',
        [
            'name' => 'camp',
            'label' => 'Your Camp',
            'placeholder' => 'Camp Creative Name',
            'help' => "Enter their camp name if they have one, or 'open camping' if not",
            'value' => (is_null($user->data)) ? '' : $user->data->camp
        ])

        @include('partials/form/text',
        [
            'name' => 'phone',
            'label' => 'Phone Number',
            'value' => (is_null($user->data)) ? '' : $user->data->phone
        ])

        @include('partials/form/text',
        [
            'name' => 'emergency_name',
            'label' => 'Emergency Contact',
            'value' => (is_null($user->data)) ? '' : $user->data->emergency_name
        ])

        @include('partials/form/text',
        [
            'name' => 'emergency_phone',
            'label' => 'Emergency Phone Number',
            'value' => (is_null($user->data)) ? '' : $user->data->emergency_phone
        ])

        @include('partials/form/date',
        [
            'name' => 'birthday',
            'label' => 'Birthday',
            'placeholder' => 'YYYY-MM-DD',
            'help' => 'This will only be used as a part of the event census',
            'value' => (is_null($user->data)) ? '' : $user->data->birthday
        ])

        <hr>

        <h3>User Roles</h3>
        <div class="user-roles">

            @include('partials/form/checkbox', 
            [
                'name' => 'roles', 
                'options' => $roleNames, 
                'selected' => $user->getRoleNames()
            ])

        </div>

        <button type="submit" class="btn btn-success">Save Changes</button>
        <a href='/user/{{$user->id }}' class="btn btn-primary">Cancel</a>
        
        {!! Form::close() !!}
        
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
