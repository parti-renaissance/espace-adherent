FROM debian:latest

ENV NODE_VERSION 6.9.4

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        ca-certificates \
    && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false -o APT::AutoRemove::SuggestsImportant=false $buildDeps \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# NodeJS
RUN curl -L -o /tmp/nodejs.tar.gz https://nodejs.org/dist/v${NODE_VERSION}/node-v${NODE_VERSION}-linux-x64.tar.gz \
    && tar xfvz /tmp/nodejs.tar.gz -C /usr/local --strip-components=1 \
    && rm -r /tmp/nodejs.tar.gz \
    && npm install yarn -g

# Config
RUN mkdir -p /var/www

WORKDIR /var/www
