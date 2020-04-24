<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
</head>
<body>
<style>
    @media only screen and (max-width: 600px) {
        .inner-body {
            width: 100% !important;
        }

        .footer {
            width: 100% !important;
        }
    }

    @media only screen and (max-width: 500px) {
        .button {
            width: 100% !important;
        }
    }
</style>
@section('style')
    @include('emails.layouts.default_css')
@show
<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
            @section('header')
                @include('emails.layouts.header')
            @show

            <!-- Email Body -->
                <tr>
                    <td class="body" width="100%" cellpadding="0" cellspacing="0">
                        <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0"
                               role="presentation">
                            <!-- Body content -->
                            <tr>
                                <td class="content-cell">
                                    <h1>
                                        @section('greeting')
                                            Hello
                                        @show
                                    </h1>

                                    @yield('body')

                                    @section('salutation')
                                        <p>
                                            {{ __('regards') }},
                                            <br>
                                            {{ config('app.name') }}
                                        </p>
                                    @show

                                    @if(isset($actionURL))

                                    @section('subcopy')
                                        @component('mail::subcopy')
                                            @lang(
                                            "If you’re having trouble clicking the \":actionText\" button, copy and paste the URL below\n".
                                                          'into your web browser: [:actionURL](:actionURL)',
                                                          [
                                                              'actionText' => $actionText,
                                                              'actionURL' => $actionURL,
                                                          ]
                                                      )
                                        @endcomponent
                                    @show
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                @component('mail::footer')
                    © {{ date('Y') }} {{ config('app.name') }}. @lang('all_rights_reserved')
                @endcomponent
            </table>
        </td>
    </tr>
</table>
</body>
</html>
