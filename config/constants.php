<?php

return [


    'DEFAULT_ROLES' => [
        'Admin',
        'Customer',
    ],

    'EMAIL_VERIFICATION_URL' => config('app.frontend_url') . '/email/verification',

    'DEFAULT_USER_ROLE' => 'Customer',
];