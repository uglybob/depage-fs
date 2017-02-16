FROM debian:jessie

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update
RUN apt-get install -y php5 php-pear php5-dev libssh2-1-dev php5-curl
RUN printf "\n" | pecl install channel://pecl.php.net/ssh2-0.13

RUN echo "extension=ssh2.so" >> /etc/php5/cli/php.ini

ENTRYPOINT ["/depage-fs/vendor/bin/phpunit"]
