<?php

// Auth
Route::change('{start}auth/login{end}')->uri('Auth/login');
Route::change('{start}auth/logout{end}')->uri('Auth/logout');

// Dashboard
Route::change('{start}dashboard{end}')->uri('Dashboard/main');

// Clients
Route::change('{start}clients{end}')->uri('Clients/main');
Route::change('{start}clients/create{end}')->uri('Clients/create');
Route::change('{start}clients/store{end}')->uri('Clients/store');
Route::change('{start}clients/edit/({number}){end}')->uri('Clients/edit/$1');
Route::change('{start}clients/update/({number}){end}')->uri('Clients/update/$1');
Route::change('{start}clients/delete/({number}){end}')->uri('Clients/delete/$1');

// Sites
Route::change('{start}sites{end}')->uri('Sites/main');
Route::change('{start}sites/create{end}')->uri('Sites/create');
Route::change('{start}sites/store{end}')->uri('Sites/store');
Route::change('{start}sites/edit/({number}){end}')->uri('Sites/edit/$1');
Route::change('{start}sites/update/({number}){end}')->uri('Sites/update/$1');
Route::change('{start}sites/delete/({number}){end}')->uri('Sites/delete/$1');

// Domains
Route::change('{start}domains{end}')->uri('Domains/main');
Route::change('{start}domains/create{end}')->uri('Domains/create');
Route::change('{start}domains/store{end}')->uri('Domains/store');
Route::change('{start}domains/delete/({number}){end}')->uri('Domains/delete/$1');

// SSL
Route::change('{start}ssl{end}')->uri('Ssl/main');
Route::change('{start}ssl/create{end}')->uri('Ssl/create');
Route::change('{start}ssl/store{end}')->uri('Ssl/store');
Route::change('{start}ssl/delete/({number}){end}')->uri('Ssl/delete/$1');

// Email
Route::change('{start}email{end}')->uri('Email/main');
Route::change('{start}email/create{end}')->uri('Email/create');
Route::change('{start}email/store{end}')->uri('Email/store');
Route::change('{start}email/delete/({number}){end}')->uri('Email/delete/$1');

// DNS
Route::change('{start}dns{end}')->uri('Dns/main');
Route::change('{start}dns/records/({number}){end}')->uri('Dns/records/$1');
Route::change('{start}dns/createRecord/({number}){end}')->uri('Dns/createRecord/$1');
Route::change('{start}dns/storeRecord{end}')->uri('Dns/storeRecord');
Route::change('{start}dns/deleteZone/({number}){end}')->uri('Dns/deleteZone/$1');
Route::change('{start}dns/deleteRecord/({number}){end}')->uri('Dns/deleteRecord/$1');

// FTP
Route::change('{start}ftp{end}')->uri('Ftp/main');
Route::change('{start}ftp/create{end}')->uri('Ftp/create');
Route::change('{start}ftp/store{end}')->uri('Ftp/store');
Route::change('{start}ftp/delete/({number}){end}')->uri('Ftp/delete/$1');

// Databases
Route::change('{start}databases{end}')->uri('Databases/main');
Route::change('{start}databases/create{end}')->uri('Databases/create');
Route::change('{start}databases/store{end}')->uri('Databases/store');
Route::change('{start}databases/delete/({number}){end}')->uri('Databases/delete/$1');

// Backups
Route::change('{start}backups{end}')->uri('Backups/main');
Route::change('{start}backups/create{end}')->uri('Backups/create');
Route::change('{start}backups/delete/({number}){end}')->uri('Backups/delete/$1');

// Cron Jobs
Route::change('{start}cronjobs{end}')->uri('Cronjobs/main');
Route::change('{start}cronjobs/create{end}')->uri('Cronjobs/create');
Route::change('{start}cronjobs/store{end}')->uri('Cronjobs/store');
Route::change('{start}cronjobs/delete/({number}){end}')->uri('Cronjobs/delete/$1');

// IP Addresses
Route::change('{start}ipaddresses{end}')->uri('IpAddresses/main');
Route::change('{start}ipaddresses/create{end}')->uri('IpAddresses/create');
Route::change('{start}ipaddresses/store{end}')->uri('IpAddresses/store');
Route::change('{start}ipaddresses/delete/({number}){end}')->uri('IpAddresses/delete/$1');

// Firewall
Route::change('{start}firewall{end}')->uri('Firewall/main');
Route::change('{start}firewall/create{end}')->uri('Firewall/create');
Route::change('{start}firewall/store{end}')->uri('Firewall/store');
Route::change('{start}firewall/delete/({number}){end}')->uri('Firewall/delete/$1');

// Settings
Route::change('{start}settings{end}')->uri('Settings/main');
Route::change('{start}settings/save{end}')->uri('Settings/save');

// Stats (SSE + JSON endpoints)
Route::change('{start}stats/stream{end}')->uri('Stats/stream');
Route::change('{start}stats/snapshot{end}')->uri('Stats/snapshot');
Route::change('{start}stats/services{end}')->uri('Stats/services');

// Jobs
Route::change('{start}jobs{end}')->uri('Jobs/main');
Route::change('{start}jobs/status/({number}){end}')->uri('Jobs/status/$1');
Route::change('{start}jobs/active{end}')->uri('Jobs/active');
Route::change('{start}jobs/refreshRows{end}')->uri('Jobs/refreshRows');
Route::change('{start}jobs/cancel/({number}){end}')->uri('Jobs/cancel/$1');

// Errors
Route::change('{start}errors/notFound{end}')->uri('Errors/notFound');
