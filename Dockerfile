FROM richarvey/nginx-php-fpm:latest

# Copier les fichiers dans le conteneur
COPY . .

# Copier les scripts et le crontab
COPY docker/mon_script.sh /var/www/html/mon_script.sh
COPY docker/crontab /var/spool/cron/crontabs/root

# Démarrer le service cron
RUN echo "* * * * * /var/www/html/mon_script.sh" > /etc/crontabs/root
RUN crond -b

# Image config
ENV SKIP_COMPOSER 1
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr
ENV COMPOSER_ALLOW_SUPERUSER 1

CMD ["/start.sh"]