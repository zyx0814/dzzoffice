#!/bin/bash

# 启动PHP-FPM
php-fpm -D

# 启动Nginx
nginx -g "daemon off;"