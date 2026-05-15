<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/turbo' => [
        'version' => '8.0.23',
    ],

    'home' => [
        'path' => './assets/scripts/frontOffice/home/home.js',
        'entrypoint' => true,
    ],

    'navbar' => [
        'path' => './assets/scripts/frontOffice/navbar.js',
        'entrypoint' => true,
    ],
    
    'contact' => [
        'path' => './assets/scripts/frontOffice/contact/contact.js',
        'entrypoint' => true,
    ],
    
    'localisation' => [
        'path' => './assets/scripts/frontOffice/localisation/localisation.js',
        'entrypoint' => true,
    ],

    'creation' => [
        'path' => './assets/scripts/frontOffice/creation/index.js',
        'entrypoint' => true,
    ],
    
    'creation-form' => [
        'path' => './assets/scripts/backOffice/form/creation-form.js',
        'entrypoint' => true,
    ],

    'login' => [
        'path' => './assets/scripts/backOffice/login/login.js',
        'entrypoint' => true,
    ],
];
