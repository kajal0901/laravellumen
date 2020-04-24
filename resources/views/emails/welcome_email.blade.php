@extends('emails.layouts.layout', [
            'actionText' => 'Verify Email Address',
            'actionURL' => $verificationUrl
        ])

@section('greeting')
    @parent {{ $user->name }},
@endsection

@section('body')
    <p>Please click the button below to verify your email address.</p>

    @component('mail::button', ['url' => $verificationUrl])
        Verify Email Address
    @endcomponent

    <p>If you did not create an account, no further action is required.</p>

@endsection