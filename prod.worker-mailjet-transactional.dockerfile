FROM enmarche-common

CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord-mailjet-transactional.conf"]
