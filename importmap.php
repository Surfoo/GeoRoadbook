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
 *
 * @return array<string, array{    // Import name as key, description of the imported file as value
 *     path: string,               // Logical, relative or absolute path to the file
 *     type?: 'js'|'css'|'json',   // Type of the file, defaults to 'js'
 *     entrypoint?: bool,          // Whether the file is an entrypoint, for 'js' only
 * }|array{
 *     version: string,            // Version of the remote package
 *     package_specifier?: string, // Remote "package-name/path" specifier, defaults to the import name
 *     type?: 'js'|'css'|'json',
 *     entrypoint?: bool,
 * }>
 */
return [
    'app' => ['path' => './assets/app.js', 'entrypoint' => true],
    'editor' => ['path' => './assets/editor.js', 'entrypoint' => true],
    'bootstrap' => ['version' => '5.3.3'],
    '@popperjs/core' => ['version' => '2.11.8'],
    'bootstrap/dist/css/bootstrap.min.css' => ['version' => '5.3.3', 'type' => 'css'],
    'bootstrap-icons/font/bootstrap-icons.min.css' => ['version' => '1.11.3', 'type' => 'css'],
    '@hotwired/stimulus' => ['version' => '3.2.2'],
    'jodit' => ['version' => '4.12.43'],
    'jodit/es2021/jodit.min.css' => ['version' => '4.12.43', 'type' => 'css'],
    '@symfony/stimulus-bundle' => ['path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js'],
    '@symfony/ux-dropzone' => ['path' => './vendor/symfony/ux-dropzone/assets/dist/controller.js'],
    '@symfony/ux-dropzone/dist/style.min.css' => ['path' => './vendor/symfony/ux-dropzone/assets/dist/style.min.css', 'type' => 'css'],
];
