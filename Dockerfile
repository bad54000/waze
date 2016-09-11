FROM php:7.0.10-apache

RUN apt-get update && apt-get install -y \
        libcurl4-openssl-dev \
    && docker-php-ext-install -j$(nproc) curl

# yaml
RUN buildRequirements="libyaml-dev" \
    && apt-get update && apt-get install -y ${buildRequirements} \
    && pecl install yaml-beta \
    && echo "extension=yaml.so" > /usr/local/etc/php/conf.d/ext-yaml.ini \
    && apt-get purge -y ${buildRequirements} \
    && rm -rf /var/lib/apt/lists/*

#Install Cron
RUN apt-get update
RUN apt-get -y install supervisor cron

# cron
ADD crontab /etc/cron.d/waze-cron
RUN chmod 0644 /etc/cron.d/waze-cron
ADD supervisord.conf /etc/supervisor/conf.d/supervisord.conf

CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
