#!/bin/sh

export APP_NAME

envsubst '${APP_NAME}' < /config.template > /etc/nginx/nginx.conf

exec "$@"