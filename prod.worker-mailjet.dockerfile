FROM enmarche-common

CMD ["php", "/app/bin/console", "rabbitmq:consumer", "-e", "prod", "-w", "mailjet_referent"]
