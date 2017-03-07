FROM enmarche-common

COPY docker/prod/supervisord_cron.conf /etc/supervisor/conf.d/supervisord_cron.conf

RUN echo "0 2 * * * sh /root/cron.sh >> /var/log/cron.log 2>&1" >> enmarcheCron && \
    crontab enmarcheCron && \
    rm enmarcheCron && \
    touch /var/log/cron.log && \
    chmod 777 /var/log/cron.log

CMD printenv | sed 's/^\(.*\)$/export \1/g' > /root/cron.sh && \
    echo "php /app/bin/console app:produce:referent-managed-users-dump -e prod" >> /root/cron.sh && \
    chmod a+x /root/cron.sh && \
    supervisord -c /etc/supervisor/conf.d/supervisord_cron.conf && \
    echo "Container started, watching logs..." && \
    tail -f /var/log/cron.log
