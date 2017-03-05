FROM enmarche-common

COPY docker/prod/crontab /etc/cron.d/enmarche
RUN chmod 0644 /etc/cron.d/enmarche && touch /var/log/cron.log

CMD cron && tail -f /var/log/cron.log
