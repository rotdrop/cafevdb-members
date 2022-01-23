<?php

return [
  'resources' => [
    'note' => [
      'url' => '/notes',
    ],
    'note_api' => [
      'url' => '/api/0.1/notes',
    ],
  ],
  'routes' => [
    [
      'name' => 'page#index',
      'url' => '/',
      'verb' => 'GET',
    ],
    [
      'name' => 'note_api#preflighted_cors',
      'url' => '/api/0.1/{path}',
      'verb' => 'OPTIONS',
      'requirements' => ['path' => '.+'],
    ],
    [
      'name' => 'settings#set_admin',
      'url' => '/settings/admin/{setting}',
      'verb' => 'POST',
    ],
    [
      'name' => 'settings#get_admin',
      'url' => '/settings/admin/{setting}',
      'verb' => 'GET',
    ],
    [
      'name' => 'settings#set_personal',
      'url' => '/settings/personal/{setting}',
      'verb' => 'POST',
    ],
    [
      'name' => 'settings#get_personal',
      'url' => '/settings/personal/{setting}',
      'verb' => 'GET',
    ],
  ],
];
