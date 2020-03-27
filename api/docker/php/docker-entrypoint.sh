#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
	PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-production"
	if [ "$APP_ENV" != 'prod' ]; then
		PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-development"
	fi
	ln -sf "$PHP_INI_RECOMMENDED" "$PHP_INI_DIR/php.ini"

	mkdir -p var/cache var/log
	setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var
	setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var

	# Lets setup an jwt certificate if needed
	# lets skipp build jwt tokens for now
	#if [ "$APP_ENV" != 'prod' ]; then
		#jwt_passphrase=$(grep '^JWT_PASSPHRASE=' .env | cut -f 2 -d '=')
		#if [ ! -f config/jwt/private.pem ] || ! echo "$jwt_passphrase" | openssl pkey -in config/jwt/private.pem -passin stdin -noout > /dev/null 2>&1; then
			#echo "Generating public / private keys for JWT"
			#mkdir -p config/jwt
			#echo "$jwt_passphrase" | openssl genpkey -out config/jwt/private.pem -pass stdin -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
			#echo "$jwt_passphrase" | openssl pkey -in config/jwt/private.pem -passin stdin -out config/jwt/public.pem -pubout
			#setfacl -R -m u:www-data:rX -m u:"$(whoami)":rwX config/jwt
            #setfacl -dR -m u:www-data:rX -m u:"$(whoami)":rwX config/jwt
		#fi
	#fi
	
	#wierd bug fix...
	#if [ "$APP_ENV" != 'prod' ]; then
		composer install --prefer-dist --no-progress --no-suggest --no-interaction
	#fi
	
	# Lets setup an nlx certificate if needed
	#if [ "$APP_ENV" != 'prod' ]; then
		mkdir -p /cert
		# Lets see if we already have cerificates and if we need to make any
		#if [ ! -f /cert/org.csr ] || [ ! -f /cert/org.key ] ; then
		  # openssl req -utf8 -nodes -sha256 -keyout org.key -out org.csr -subj "/C=$COUNTRY_NAME/ST=$STATE/L=$LOCALITY/O=$ORGANIZATION_NAME/OU=$ORGANIZATION_UNIT_NAME/CN=$COMMON_NAME"
		#fi
	#fi

	echo "Waiting for db to be ready..."
	until bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
		sleep 1
	done

	if [ "$APP_ENV" != 'prod' ]; then
	
		# If you want to retain data in your dev enviroment comment this command out
		echo "Clearing the database"
		bin/console doctrine:schema:drop --full-database --force --no-interaction	
		
		echo "Updating the database"
		bin/console doctrine:schema:update --force --no-interaction			
		
		# If you want to retain data in your dev enviroment comment this command out
		echo "Loading fixtures"
		bin/console doctrine:fixtures:load  --no-interaction
		
		# Lets update the docs to show the latest chages
		echo "Creating OAS documentation"
		bin/console api:openapi:export --output=/srv/api/public/schema/openapi.yaml --yaml --spec-version=3		
				
		# this should only be done in an build		
		echo "Updating Helm charts"
		bin/console app:helm:update --location=/srv/api/helm --spec-version=3		
		
		# this should only be done in an build		
		echo "Updating publiccode charts"
		bin/console app:publiccode:update --location=/srv/api/public/schema/ --spec-version=0.2		
	fi
fi

exec docker-php-entrypoint "$@"
