#!/bin/sh

cd $HOME/coupcritique-back

if [ ! -f config/jwt/private.pem ]
then
	cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1 > ../passphrase.txt
	openssl genrsa -passout file:../passphrase.txt -out config/jwt/private.pem -aes256 4096
	openssl rsa -passin file:../passphrase.txt -pubout -in config/jwt/private.pem -out config/jwt/public.pem

	if [ ! -f coupcritique/.env ]
	then
		cp .env.sample .env
	fi;

	if [ ! -f coupcritique/.env.local ]
	then
		echo ".env.local is missing : you should create your own"
		cp .env .env.local
		chown $(id -u):$(id -g) .env.local
	fi

	sed -i 's/JWT_PASSPHRASE=.*/JWT_PASSPHRASE='$(cat ../passphrase.txt)'/g' .env.local

fi

php-fpm

