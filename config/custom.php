<?php

return [
  'resource_img_url' => env('RESOURCE_IMG_URL'),
  'upload_max_size' => env('UPLOAD_MAX_SIZE'),
  'user_session_key' => env('USER_SESSION_KEY'),
  'crypt_key' => env('CRYPT_KEY'),

  'sso' => [
    'login' => env('SSO_LOGIN'),
    'check_st' => env('SSO_CHECK_ST'),
    'check_tgc' => env('SSO_CHECK_TGC'),
    'logout' => env('SSO_LOGOUT'),
    'user_info' => env('SSO_USER_INFO'),
  ],


  'session' => [
    'user_info' => env('SESSION_USER_INFO', env('APP_NAME') . 'session_user_info_key')
  ],

  'cookie' => [
      'logged_tmp' => env('APP_NAME') . '_logged_tmp'
  ],
];