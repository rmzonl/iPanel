<?php

// Auth routes
Route::url('auth/login',   'Auth::login');
Route::url('auth/logout',  'Auth::logout');

// Dashboard
Route::url('dashboard',       'Dashboard::main');
Route::url('dashboard/main',  'Dashboard::main');

// Clients
Route::url('clients',                'Clients::main');
Route::url('clients/main',           'Clients::main');
Route::url('clients/create',         'Clients::create');
Route::url('clients/store',          'Clients::store');
Route::url('clients/edit/(:num)',     'Clients::edit/$1');
Route::url('clients/update/(:num)',   'Clients::update/$1');
Route::url('clients/delete/(:num)',   'Clients::delete/$1');

// Sites
Route::url('sites',                  'Sites::main');
Route::url('sites/main',             'Sites::main');
Route::url('sites/create',           'Sites::create');
Route::url('sites/store',            'Sites::store');
Route::url('sites/edit/(:num)',       'Sites::edit/$1');
Route::url('sites/update/(:num)',     'Sites::update/$1');
Route::url('sites/delete/(:num)',     'Sites::delete/$1');

// Domains
Route::url('domains',                'Domains::main');
Route::url('domains/main',           'Domains::main');
Route::url('domains/create',         'Domains::create');
Route::url('domains/store',          'Domains::store');
Route::url('domains/delete/(:num)',   'Domains::delete/$1');

// SSL
Route::url('ssl',                    'Ssl::main');
Route::url('ssl/main',               'Ssl::main');
Route::url('ssl/create',             'Ssl::create');
Route::url('ssl/store',              'Ssl::store');
Route::url('ssl/delete/(:num)',       'Ssl::delete/$1');

// Email
Route::url('email',                  'Email::main');
Route::url('email/main',             'Email::main');
Route::url('email/create',           'Email::create');
Route::url('email/store',            'Email::store');
Route::url('email/delete/(:num)',     'Email::delete/$1');

// DNS
Route::url('dns',                    'Dns::main');
Route::url('dns/main',               'Dns::main');
Route::url('dns/records/(:num)',      'Dns::records/$1');
Route::url('dns/createRecord/(:num)', 'Dns::createRecord/$1');
Route::url('dns/storeRecord',         'Dns::storeRecord');
Route::url('dns/deleteZone/(:num)',   'Dns::deleteZone/$1');
Route::url('dns/deleteRecord/(:num)', 'Dns::deleteRecord/$1');

// FTP
Route::url('ftp',                    'Ftp::main');
Route::url('ftp/main',               'Ftp::main');
Route::url('ftp/create',             'Ftp::create');
Route::url('ftp/store',              'Ftp::store');
Route::url('ftp/delete/(:num)',       'Ftp::delete/$1');

// Databases
Route::url('databases',              'Databases::main');
Route::url('databases/main',         'Databases::main');
Route::url('databases/create',       'Databases::create');
Route::url('databases/store',        'Databases::store');
Route::url('databases/delete/(:num)', 'Databases::delete/$1');

// Backups
Route::url('backups',                'Backups::main');
Route::url('backups/main',           'Backups::main');
Route::url('backups/create',         'Backups::create');
Route::url('backups/delete/(:num)',   'Backups::delete/$1');

// Cron Jobs
Route::url('cronjobs',               'Cronjobs::main');
Route::url('cronjobs/main',          'Cronjobs::main');
Route::url('cronjobs/create',        'Cronjobs::create');
Route::url('cronjobs/store',         'Cronjobs::store');
Route::url('cronjobs/delete/(:num)',  'Cronjobs::delete/$1');

// IP Addresses
Route::url('ipaddresses',                'IpAddresses::main');
Route::url('ipaddresses/main',           'IpAddresses::main');
Route::url('ipaddresses/create',         'IpAddresses::create');
Route::url('ipaddresses/store',          'IpAddresses::store');
Route::url('ipaddresses/delete/(:num)',   'IpAddresses::delete/$1');

// Firewall
Route::url('firewall',               'Firewall::main');
Route::url('firewall/main',          'Firewall::main');
Route::url('firewall/create',        'Firewall::create');
Route::url('firewall/store',         'Firewall::store');
Route::url('firewall/delete/(:num)', 'Firewall::delete/$1');

// Settings
Route::url('settings',               'Settings::main');
Route::url('settings/main',          'Settings::main');
Route::url('settings/save',          'Settings::save');

// Errors
Route::url('errors/notFound',        'Errors::notFound');
