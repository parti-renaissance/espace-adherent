#!/usr/bin/env bash

if [[ "$OSTYPE" != "darwin"* ]]
then
    echo "Your system isn't a Mac"
    exit 1
fi

if ! type "docker-machine" > /dev/null
then
    echo "docker-machine command not found"
    echo "try to download and install Docker4Mac"
    echo "from: https://download.docker.com/mac/stable/Docker.dmg"
    exit 1
fi

if type "dinghy" > /dev/null
then
    echo -e "Dinghy lib detected!\n"

    read -p "Remove all trace of Dinghy Virtual machine? (y/n): " -n 1 -r
    echo ""

    if [[ $REPLY =~ ^[Yy]$ ]]
    then
        dinghy halt 2> /dev/null || true
        dinghy destroy --force 2> /dev/null || true
        echo -e " └─ Done\n"
    fi

    read -p "Remove Dinghy lib? (y/n): " -n 1 -r
    echo ""

    if [[ $REPLY =~ ^[Yy]$ ]]
    then
        brew uninstall --force dinghy || true
        echo -e " └─ Done\n"
    fi
fi

# Remove virtual machine with `default` name
VIRTUAL_MACHINES=`docker-machine ls --quiet`

if [[ ${VIRTUAL_MACHINES} =~ "default" ]]
then
    echo -e "Default virtual matched was found\n"

    read -p "Do you want delete it? (y/n): " -n 1 -r
    echo ""

    if [[ $REPLY =~ ^[Yy]$ ]]
    then
        docker-machine kill default 2> /dev/null || true
        docker-machine rm -y default 2> /dev/null || true
        echo -e " └─ Done\n"
    fi
fi

VIRTUAL_MACHINES=`docker-machine ls --quiet`

if [[ ! ${VIRTUAL_MACHINES} =~ "default" ]]
then
    echo -e "Create default VM with 3G RAM and 2CPUs\n"
    docker-machine create default --driver virtualbox --virtualbox-memory "3000" --virtualbox-cpu-count "2"
    echo " └─ Done\n"
fi

echo -e "Start default virtual machine\n"
docker-machine restart default
eval $(docker-machine env default)
echo -e " └─ Done\n"

if ! type "docker-machine-nfs" > /dev/null
then
    echo -e "Install docker-machine-nfs command\n"
    brew install docker-machine-nfs
else
    echo -e "Update docker-machine-nfs command\n"
    brew upgrade docker-machine-nfs || true
fi
echo -e " └─ Done\n"

docker network create proxy || true
docker-machine-nfs default --shared-folder=$HOME --nfs-config="-alldirs -maproot=0" --mount-opts="async,noatime,actimeo=1,nolock,vers=3,udp" -f
docker run -d -p 80:80 -v /var/run/docker.sock:/tmp/docker.sock:ro --net=default --net=proxy --name=proxy --restart=always jwilder/nginx-proxy || true
