# Replace the docker run to customise the rabbitmq container.
# For example: `docker run -d --hostname rabbitmq --name rabbitmq --network enmarche -p 8080:15672 rabbitmq:3-management`
# to expose rabbitmq to the port 8080.

rabbitmq-start:
	@{ \
	set -e ;\
	if [ "$$(docker ps | grep '\brabbitmq$$')" ]; then \
	    exit 0 ;\
	fi ;\
    docker run -d --hostname rabbitmq --name rabbitmq --network enmarche rabbitmq:3 ;\
	}

rabbitmq-stop:
	docker kill rabbitmq
	docker rm -v rabbitmq
