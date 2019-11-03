#!/usr/local/bin/docker-entrypoint

	# Installing envsubst"
	echo "Installing envsubst"
	apk --no-cache add gettext

	# Inserting variables
	echo "Inserting variables"
	envsubst < /usr/local/etc/varnish/default.vcl.template > /usr/local/etc/varnish/default.vcl