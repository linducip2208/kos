<?php

return [
    'temporary_file_upload' => [
        'disk' => 'public',
        'directory' => 'livewire-tmp',
        'middleware' => null,
        'rules' => null,
        'max_upload_time' => 10,
        'cleanup' => true,
    ],
];
