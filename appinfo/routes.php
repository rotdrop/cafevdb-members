<?php

return [
  // 'resources' => [
  //   'note' => [
  //     'url' => '/notes',
  //   ],
  //   'note_api' => [
  //     'url' => '/api/0.1/notes',
  //   ],
  // ],
  'routes' => [
    [
      'name' => 'page#index',
      'url' => '/',
      'verb' => 'GET',
    ],
    [
      'name' => 'page#index',
      'url' => '/f/{path}',
      'verb' => 'GET',
      'requirements' => [ 'path' => '.+' ],
      'postfix' => 'front',
    ],
    [
      'name' => 'project_registration#page',
      'url' => '/public/registration/{projectName}/{section}',
      'verb' => 'GET',
      'defaults' => [
        'projectName' => null,
        'section' => null,
      ],
    ],
    [
      'name' => 'project_events_api#preflighted_cors',
      'url' => '/api/{apiVersion}/{path}',
      'verb' => 'OPTIONS',
      'requirements' => [
        'apiVersion' => '0.1',
        'path' => '.+',
      ],
    ],
    [
      'name' => 'project_events_api#service_switch',
      'url' => '/api/{apiVersion}/projects/events/{indexObject}/{objectId}/{calendar}/{timezone}/{locale}',
      'verb' => 'GET',
      'defaults' => [
        'calendar' => 'all',
        'timezone' => null,
        'locale' => null,
      ],
      'requirements' => [
        'apiVersion' => '0.1',
      ],
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
      'name' => 'settings#get_app',
      'url' => '/settings/app/{setting}',
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
    [
      'name' => 'member_data#get',
      'url' => '/member',
      'verb' => 'GET',
    ],
    [
      'name' => 'member_data#download',
      'url' => '/download/member/{optionKey}',
      'verb' => 'GET',
    ],
    /**
     * Attempt a catch all ...
     */
    [
      'name' => 'catch_all#post',
      'postfix' => 'post',
      'url' => '/{a}/{b}/{c}/{d}/{e}/{f}/{g}',
      'verb' => 'POST',
      'defaults' => [
        'a' => '',
        'b' => '',
        'c' => '',
        'd' => '',
        'e' => '',
        'f' => '',
        'g' => '',
      ],
    ],
    [
      'name' => 'catch_all#get',
      'postfix' => 'get',
      'url' => '/{a}/{b}/{c}/{d}/{e}/{f}/{g}',
      'verb' => 'GET',
      'defaults' => [
        'a' => '',
        'b' => '',
        'c' => '',
        'd' => '',
        'e' => '',
        'f' => '',
        'g' => '',
      ],
    ],
  ],
];
