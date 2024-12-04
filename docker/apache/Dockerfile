FROM ubuntu:24.04
MAINTAINER rocean <rocean74@gmail.com>

# Install dependencies for adding external repositories and building PHP
RUN apt-get update && apt-get -y install \
    software-properties-common wget curl git nano apt-transport-https lsb-release \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Add Ondrej Sury's PPA for PHP 7.2
RUN add-apt-repository ppa:ondrej/php && apt-get update

# Install Apache, PHP 7.2, and required modules
RUN DEBIAN_FRONTEND=noninteractive apt-get -y install \
    apache2 php7.2 php7.2-mysql libapache2-mod-php7.2 \
    php7.2-xml php7.2-gd php7.2-curl php7.2-mbstring \
    ffmpeg aria2 lame wget python3 nano \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install yt-dlp
RUN wget https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -O /usr/local/bin/yt-dlp \
    && chmod a+rx /usr/local/bin/yt-dlp

# Enable apache mods
RUN a2enmod php7.2
RUN a2enmod rewrite

# Update the PHP.ini file
ADD php.ini /etc/php/7.2/apache2/php.ini

# Set up Apache environment variables
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid

# Expose Apache
EXPOSE 80

# Add Apache configuration
ADD apache-config.conf /etc/apache2/sites-enabled/000-default.conf

# Start Apache in the foreground
CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
