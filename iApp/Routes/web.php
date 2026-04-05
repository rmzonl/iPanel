<?php

// Auth
Route::change('{start}auth/login{\/}')->uri('Auth/login');
Route::change('{start}auth/logout{\/}')->uri('Auth/logout');

// Dashboard
Route::change('{start}dashboard{\/}')->uri('Dashboard/main');

// Clients
Route::change('{start}clients{\/}')->uri('Clients/main');
Route::change('{start}clients/create{\/}')->uri('Clients/create');
Route::change('{start}clients/store{\/}')->uri('Clients/store');
Route::change('{start}clients/edit/({number}){end}')->uri('Clients/edit/$1');
Route::change('{start}clients/update/({number}){end}')->uri('Clients/update/$1');
Route::change('{start}clients/delete/({number}){end}')->uri('Clients/delete/$1');

// Sites
Route::change('{start}sites{\/}')->uri('Sites/main');
Route::change('{start}sites/create{\/}')->uri('Sites/create');
Route::change('{start}sites/store{\/}')->uri('Sites/store');
Route::change('{start}sites/edit/({number}){end}')->uri('Sites/edit/$1');
Route::change('{start}sites/update/({number}){end}')->uri('Sites/update/$1');
Route::change('{start}sites/delete/({number}){end}')->uri('Sites/delete/$1');

// Domains
Route::change('{start}domains{\/}')->uri('Domains/main');
Route::change('{start}domains/create{\/}')->uri('Domains/create');
Route::change('{start}domains/store{\/}')->uri('Domains/store');
Route::change('{start}domains/delete/({number}){end}')->uri('Domains/delete/$1');

// SSL
Route::change('{start}ssl{\/}')->uri('Ssl/main');
Route::change('{start}ssl/create{\/}')->uri('Ssl/create');
Route::change('{start}ssl/store{\/}')->uri('Ssl/store');
Route::change('{start}ssl/delete/({number}){end}')->uri('Ssl/delete/$1');

// Email
Route::change('{start}email{\/}')->uri('Email/main');
Route::change('{start}email/create{\/}')->uri('Email/create');
Route::change('{start}email/store{\/}')->uri('Email/store');
Route::change('{start}email/delete/({number}){end}')->uri('Email/delete/$1');

// DNS
Route::change('{start}dns{\/}')->uri('Dns/main');
Route::change('{start}dns/records/({number}){end}')->uri('Dns/records/$1');
Route::change('{start}dns/createRecord/({number}){end}')->uri('Dns/createRecord/$1');
Route::change('{start}dns/storeRecord{\/}')->uri('Dns/storeRecord');
Route::change('{start}dns/deleteZone/({number}){end}')->uri('Dns/deleteZone/$1');
Route::change('{start}dns/deleteRecord/({number}){end}')->uri('Dns/deleteRecord/$1');

// FTP
Route::change('{start}ftp{\/}')->uri('Ftp/main');
Route::change('{start}ftp/create{\/}')->uri('Ftp/create');
Route::change('{start}ftp/store{\/}')->uri('Ftp/store');
Route::change('{start}ftp/delete/({number}){end}')->uri('Ftp/delete/$1');

// Databases
Route::change('{start}databases{\/}')->uri('Databases/main');
Route::change('{start}databases/create{\/}')->uri('Databases/create');
Route::change('{start}databases/store{\/}')->uri('Databases/store');
Route::change('{start}databases/delete/({number}){end}')->uri('Databases/delete/$1');

// Backups
Route::change('{start}backups{\/}')->uri('Backups/main');
Route::change('{start}backups/create{\/}')->uri('Backups/create');
Route::change('{start}backups/delete/({number}){end}')->uri('Backups/delete/$1');

// Cron Jobs
Route::change('{start}cronjobs{\/}')->uri('Cronjobs/main');
Route::change('{start}cronjobs/create{\/}')->uri('Cronjobs/create');
Route::change('{start}cronjobs/store{\/}')->uri('Cronjobs/store');
Route::change('{start}cronjobs/delete/({number}){end}')->uri('Cronjobs/delete/$1');

// IP Addresses
Route::change('{start}ipaddresses{\/}')->uri('IpAddresses/main');
Route::change('{start}ipaddresses/create{\/}')->uri('IpAddresses/create');
Route::change('{start}ipaddresses/store{\/}')->uri('IpAddresses/store');
Route::change('{start}ipaddresses/delete/({number}){end}')->uri('IpAddresses/delete/$1');

// Firewall
Route::change('{start}firewall{\/}')->uri('Firewall/main');
Route::change('{start}firewall/create{\/}')->uri('Firewall/create');
Route::change('{start}firewall/store{\/}')->uri('Firewall/store');
Route::change('{start}firewall/delete/({number}){end}')->uri('Firewall/delete/$1');

// Settings
Route::change('{start}settings{\/}')->uri('Settings/main');
Route::change('{start}settings/save{\/}')->uri('Settings/save');

// Errors
Route::change('{start}errors/notFound{\/}')->uri('Errors/notFound');
