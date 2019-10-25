#!/bin/sh

export APP_NAME

envsubst '\$APP_NAME' < /etc/nginx/conf.d/default.conf > /etc/nginx/conf.d/default.conf

exec "$@"