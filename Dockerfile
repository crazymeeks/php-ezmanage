FROM ubuntu:22.04

RUN apt -y --fix-missing update && \
# Install apache, PHP, and supplimentary programs, openssh-server, curl, and lynx-cur
apt -y install software-properties-common && \
LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php && \
apt -y update && apt-cache pkgnames | grep php8.3 && apt -y update && apt -y install nginx php8.3 php8.3-fpm \ 
&& apt -y update && apt -y install php8.3-cli php8.3-gd php8.3-intl php8.3-common php8.3-mysql php8.3-curl curl \
php8.3-dom zip unzip php8.3-xml \
php8.3-zip php8.3-mbstring \
php8.3-opcache php8.3-dev php8.3-sqlite3 php8.3-xdebug git vim iputils-ping \
&& apt-cache search php8.3 && apt -y update && \
curl -sS https://getcomposer.org/installer -o composer-setup.php && \
curl -s https://getcomposer.org/installer | php && \
mv composer.phar /usr/local/bin/composer && \
apt install sqlite3 libsqlite3-dev redis-server -y

RUN rm /etc/nginx/sites-enabled/default
RUN rm /etc/nginx/sites-available/default
# RUN ln -s /etc/nginx/sites-available/vhost-nginx.conf /etc/nginx/sites-enabled/vhost
# Set php version to use
RUN update-alternatives --set php /usr/bin/php8.3


# Replace shell with bash so we can source files
RUN rm /bin/sh && ln -s /bin/bash /bin/sh
ENV NVM_DIR /usr/local/nvm
ENV NODE_VERSION 14.19.3


# Install nvm with node and npm
RUN curl https://raw.githubusercontent.com/creationix/nvm/v0.30.1/install.sh | bash \
    && source $NVM_DIR/nvm.sh \
    && nvm install $NODE_VERSION \
    && nvm alias default $NODE_VERSION \
    && nvm use default

WORKDIR /var/www/html


EXPOSE 80
EXPOSE 3000
EXPOSE 443

CMD service php8.3-fpm start && nginx -g "daemon off;"