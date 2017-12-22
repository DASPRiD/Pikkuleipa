<?php
return [
    'pikkuleipa' => [
        'default_cookie_settings' => [
            // Path which the cookie applies to
            'path' => '/',

            // Whether the cookie is limited to HTTPS
            'secure' => true,

            // Lifetime of the cookie, here 30 days
            'lifetime' => 2592000,
        ],

        'cookie_settings' => [
            // Here you can configure all the different cookies you are using
            'some_cookie_name' => [
                'path' => '/',
                'secure' => true,
                'lifetime' => 60
            ],
        ],

        'token' => [
            // Signer used for signing and verification
            'signer_class' => Lcobucci\JWT\Signer\Rsa\Sha256::class,

            // Signature and verification keys. See: https://github.com/lcobucci/jwt#token-signature
            'signature_key' => '',
            'verification_key' => '',
        ],
    ],
];
