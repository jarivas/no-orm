FROM mariadb:10.3

# Install extra system packages
RUN apt-get update && apt-get install -y \
        curl \

RUN curl -sL https://deb.nodesource.com/setup | bash - && \
    apt-get update && \
    apt-get install --no-install-recommends -y \
        unzip && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

ADD sql/ /sql/

WORKDIR /sql

ADD installation-scripts/ /
RUN chmod 700 /db.sh