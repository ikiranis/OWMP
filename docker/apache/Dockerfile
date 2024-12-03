FROM ubuntu:18.04
MAINTAINER rocean <rocean74@gmail.com>

# Install required dependencies
RUN apt-get update && apt-get -y upgrade && DEBIAN_FRONTEND=noninteractive apt-get -y install \
    software-properties-common build-essential zlib1g-dev libncurses5-dev libgdbm-dev libnss3-dev \
    libssl-dev libsqlite3-dev libreadline-dev libffi-dev curl wget apache2 php7.2 php7.2-mysql \
    libapache2-mod-php7.2 lynx-common lynx php-xml php7.2-gd php-curl php7.2-mbstring ffmpeg \
    aria2 lame nano git \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Download and build Python 3.9 from source
RUN wget https://www.python.org/ftp/python/3.9.18/Python-3.9.18.tgz \
    && tar -xf Python-3.9.18.tgz \
    && cd Python-3.9.18 \
    && ./configure --enable-optimizations \
    && make -j$(nproc) \
    && make altinstall \
    && cd .. \
    && rm -rf Python-3.9.18 Python-3.9.18.tgz

# Install yt-dlp
RUN wget https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -O /usr/local/bin/yt-dlp \
    && chmod a+rx /usr/local/bin/yt-dlp

# Enable apache mods
RUN a2enmod php7.2
RUN a2enmod rewrite

# Configure Python alternatives
RUN update-alternatives --install /usr/bin/python3 python3 /usr/local/bin/python3.9 1 \
    && update-alternatives --config python3

# Set PATH environment variable
RUN export PATH=${PATH}:/usr/bin/python

# Configure Apache environment variables
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid

# Expose apache
EXPOSE 80

# Update the default apache site with the config we created
ADD apache-config.conf /etc/apache2/sites-enabled/000-default.conf

ADD php.ini /etc/php/7.2/apache2/php.ini

# By default, start up apache in the foreground, override with /bin/bash for interactive
CMD /usr/sbin/apache2ctl -D FOREGROUND
