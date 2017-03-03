FROM enmarche-common

CMD ["php", "/app/bin/console", "rabbitmq:consumer", "-e", "prod", "-w", "referent_managed_users_dumper"]
