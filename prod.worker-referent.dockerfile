FROM enmarche-common

CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord-referent.conf"]
