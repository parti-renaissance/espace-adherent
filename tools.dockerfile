FROM ubuntu:16.04

ENV LANG="en_US.UTF-8" \
    LC_ALL="en_US.UTF-8" \
    LANGUAGE="en_US.UTF-8" \
    TERM="xterm" \
    DEBIAN_FRONTEND="noninteractive" \
    NODE_VERSION=6.9.4

WORKDIR /app

RUN apt-get update -q && \
    apt-get install --no-install-recommends -qy \
        language-pack-en-base \
        ca-certificates \
        curl && \

    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* && \

    cp /usr/share/zoneinfo/Europe/Paris /etc/localtime && echo "Europe/Paris" > /etc/timezone && \

    curl -L -o /tmp/nodejs.tar.gz https://nodejs.org/dist/v${NODE_VERSION}/node-v${NODE_VERSION}-linux-x64.tar.gz && \
    tar xfvz /tmp/nodejs.tar.gz -C /usr/local --strip-components=1 && \
    rm -f /tmp/nodejs.tar.gz && \
    npm install yarn -g
