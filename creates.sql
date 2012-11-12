create table traffix_hits ( id int auto_increment primary key not null,
ip varchar(40) not null default '',
time_stamp int(11) not null default 0 );

create table traffix_request_log ( id int auto_increment primary key not null,
ip varchar(40) not null default '',
rDNS varchar(60) not null default '',
request_time int(11) not null default 0,
user_agent varchar(250) not null default '',
script varchar(35) not null default '',
request_headers text not null default '',
method varchar(10) not null default '',
rh_host tinyint(1) not null default 0,
rh_accept tinyint(1) not null default 0,
rh_accept_encoding tinyint(1) not null default 0,
rh_accept_language tinyint(1) not null default 0,
rh_cache_control tinyint(1) not null default 0,
rh_connection tinyint(1) not null default 0,
rh_user_agent tinyint(1) not null default 0,
analyzed tinyint(1) not null default 0 );

create table traffix_analysis ( id int auto_increment primary key not null,
ip varchar(40) not null default '',
proxy tinyint(1) not null default 2,
vpn tinyint(1) not null default 2,
banned tinyint(1) not null default 0 );

create table traffix_htaccess ( id int auto_increment primary key not null,
content text not null default '',
time_stamp int(11) not null default 0 );
