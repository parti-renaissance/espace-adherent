FROM enmarche-common

COPY docker/prod/supervisord_cron.conf /etc/supervisor/conf.d/supervisord_cron.conf

RUN echo "0 2 * * * root . /root/project_env.sh; php /app/bin/console app:produce:referent-managed-users-dump -e prod >> /var/log/cron.log 2>&1" >> enmarcheCron && \
    crontab enmarcheCron && \
    rm enmarcheCron

CMD printenv | sed 's/^\(.*\)$/export \1/g' > /root/project_env.sh && supervisord -c /etc/supervisor/conf.d/supervisord_cron.conf
