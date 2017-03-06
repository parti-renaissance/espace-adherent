FROM enmarche-common

COPY docker/prod/supervisord_cron.conf /etc/supervisor/conf.d/supervisord_cron.conf

RUN echo "* * * * * php /app/bin/console app:produce:referent-managed-users-dump -e prod >> /var/log/cron.log 2>&1" >> enmarcheCron && \
    crontab enmarcheCron && \
    rm enmarcheCron

CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord_cron.conf"]
