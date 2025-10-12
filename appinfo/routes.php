<?php

use OCA\CAFeVDBMembers\Constants;

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
    // Registration with given project and section, optional token
    [
      'name' => 'ProjectRegistration#showShare',
      'postfix' => '_pages',
      'url' => '/registration/{section}/{token}',
      'verb' => 'GET',
      'defaults' => [
        'token' => Constants::NEW_APPLICATION_TOKEN,
      ],
      'requirements' => [
        'section' => '[a-z-]+',
        'token' => (
           '^('
           // plain project name, needs to be remapped from token to projec name
           . Constants::TEMPORARY_PROJECT_NAME_REGEXP
           . '|'
           // composite project name and token, this is required as the
           // URLGenerator call in the public-share framework just allows one
           // parameter for the token and we decided to use the project-name +
           // some fancy hash
           . Constants::TEMPORARY_PROJECT_NAME_REGEXP . '/' . Constants::PROJECT_APPLICATION_TOKEN_REGEXP
           . ')$'
         ),
      ],
    ],
    // Registration home with optional project and optional token
    [
      'name' => 'ProjectRegistration#showShare',
      'url' => '/registration/{token}',
      'verb' => 'GET',
       'requirements' => [
         'token' => (
           '^('
           // empty is ok
           . '|'
           // plain project name, needs to be remapped
           . Constants::TEMPORARY_PROJECT_NAME_REGEXP
           . '|'
           // composite project name and token, this is required as the
           // URLGenerator call in the public-share framework just allows one
           // parameter for the token and we decided to use the project-name +
           // some fancy hash
           . Constants::TEMPORARY_PROJECT_NAME_REGEXP . '/' . Constants::PROJECT_APPLICATION_TOKEN_REGEXP
           . ')$'
         ),
       ],
      'defaults' => [
        'token' => Constants::NEW_APPLICATION_TOKEN,
      ],
    ],
    [
      'name' => 'ProjectRegistration#submit',
      'url' => '/registration/submit/{token}',
      'verb' => 'POST',
      'requirements' => [
        'token' => (
           '^('
           // plain project name, needs to be remapped from token to project name
           . Constants::TEMPORARY_PROJECT_NAME_REGEXP
           . '|'
           // composite project name and token, this is required as the
           // URLGenerator call in the public-share framework just allows one
           // parameter for the token and we decided to use the project-name +
           // some fancy hash
           . Constants::TEMPORARY_PROJECT_NAME_REGEXP . '/' . Constants::PROJECT_APPLICATION_TOKEN_REGEXP
           . ')$'
         ),
      ],
      'defaults' => [
        'token' => Constants::NEW_APPLICATION_TOKEN,
      ],
    ],
    [
      'name' => 'ProjectRegistration#showAuthenticate',
      'url' => '/registration/{token}/authenticate/{redirect}',
      'verb' => 'GET',
      'requirements' => [
        'token' => (
          '^'
          . Constants::TEMPORARY_PROJECT_NAME_REGEXP
          . '/'
          . Constants::PROJECT_APPLICATION_TOKEN_REGEXP
          . '$'
        ),
      ],
    ],
    [
      'name' => 'ProjectRegistration#authenticate',
      'url' => '/registration/{token}/authenticate/{redirect}',
      'verb' => 'POST',
      'requirements' => [
        'token' => (
          '^'
          . Constants::TEMPORARY_PROJECT_NAME_REGEXP
          . '/'
          . Constants::PROJECT_APPLICATION_TOKEN_REGEXP
          . '$'
        ),
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
      'defaults' => [
        'setting' => null,
       ],
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
  ],
];
