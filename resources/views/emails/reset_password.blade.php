@extends('emails.layouts.layout', [
            'actionText' => 'Reset Password',
            'actionURL' => $redirectUrl
        ])

@section('greeting')
    @parent {{ $user->name }},
@endsection

@section('body')
    <p>You are receiving this email because we received a password reset request for your account.</p>

    @component('mail::button', ['url' => $redirectUrl])
        Reset Password
    @endcomponent

    <p>If you did not create an account, no further action is required.</p>

@endsection
