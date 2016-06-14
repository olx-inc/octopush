FROM debian:wheezy
MAINTAINER Jason Wilder jwilder@litl.com

# Install wget and install/updates certificates
RUN apt-get update \
 && apt-get install -y -q --no-install-recommends \
    ca-certificates \
    wget \
    nginx \
    runit \
    socklog \
 && apt-get clean \
 && rm -r /var/lib/apt/lists/*

# Configure Nginx and apply fix for very long server names
RUN echo "daemon off;" >> /etc/nginx/nginx.conf \
 && sed -i 's/^http {/&\n    server_names_hash_bucket_size 128;/g' /etc/nginx/nginx.conf \
 && cat /etc/nginx/nginx.conf | egrep  -v '(access_log|error_log)' > out && mv out /etc/nginx/nginx.conf \
 && rm /etc/nginx/sites-enabled/default

ENV DOCKER_GEN_VERSION 0.3.9

RUN wget https://github.com/jwilder/docker-gen/releases/download/$DOCKER_GEN_VERSION/docker-gen-linux-amd64-$DOCKER_GEN_VERSION.tar.gz \
 && tar -C /usr/local/bin -xvzf docker-gen-linux-amd64-$DOCKER_GEN_VERSION.tar.gz \
 && rm /docker-gen-linux-amd64-$DOCKER_GEN_VERSION.tar.gz

COPY . /

WORKDIR /app/

ENV DOCKER_HOST unix:///tmp/docker.sock

VOLUME ["/etc/nginx/certs"]

EXPOSE 80

CMD ["/usr/bin/runsvdir", "/etc/service"]
