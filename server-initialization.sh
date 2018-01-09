#!/bin/bash
if [ ! -f /home/ubuntu/automated/custom.log ]
    then
        mkdir /home/ubuntu/automated/
        cd /home/ubuntu/automated/
        echo "Proccess began: " >> /home/ubuntu/automated/custom.log
        date >> /home/ubuntu/automated/custom.log
        exec >> /home/ubuntu/automated/custom.log 2>&1
        apt-get update -y && apt-get dist-upgrade -y
        apt-get autoremove -y && apt-get autoclean -y
        add-apt-repository ppa:certbot/certbot -y
        apt-get update
        apt-get install apache2 php7.0 php7.0-fpm libapache2-mod-php7.0 php7.0-json php7.0-mcrypt php7.0-mysqlnd mysql-server phpmyadmin software-properties-common python-certbot-apache -y
        debconf-set-selections <<< 'mysql-server mysql-server/root_password password myTempPass'
        debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password myTempPass'
        debconf-set-selections <<< "postfix postfix/mailname string 'bearcatexchange.com'"
        debconf-set-selections <<< "postfix postfix/main_mailer_type string 'Internet Site'"
        echo "phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2" | debconf-set-selections
        echo "phpmyadmin phpmyadmin/dbconfig-install boolean true" | debconf-set-selections
        echo "phpmyadmin phpmyadmin/mysql/admin-user string root" | debconf-set-selections
        echo "phpmyadmin phpmyadmin/mysql/admin-pass password myTempPass" | debconf-set-selections
        echo "phpmyadmin phpmyadmin/mysql/app-pass password myTempPass" |debconf-set-selections
        echo "phpmyadmin phpmyadmin/app-password-confirm password myTempPass" | debconf-set-selections
        php7.0enmod mcrypt
        curl https://getcomposer.org/installer -o /var/www/be/live/composer.phar | php
        certbot --apache
        service apache2 restart
        touch /etc/cron.d/server-initialization
        chmod 777 /etc/cron.d/server-initialization
        echo "56 06 16 * * * root /bin/sh certbot renew" >> /etc/cron.d/server-initialization
        echo "set number" > ~/.vimrc
        mkdir /var/www/be/live/ -p
        chown -R $USER:$USER /var/www/be
        chmod -R 755 /var/www
        rm /var/www/html/index.html
        apt-get autoremove -y
        apt-get autoclean -y

#        This must be done manually:
#        nano /etc/phpmyadmin/config.inc.php
#        --ADD LINES BELOW THE PMA CONFIG AREA AND FILL IN DETAILS--
#$i++;
#$cfg['Servers'][$i]['host']          = 'bearcat.cqfnkzrzji1p.us-east-1.rds.amazonaws.com';
#$cfg['Servers'][$i]['port']          = '3306';
#$cfg['Servers'][$i]['socket']        = '';
#$cfg['Servers'][$i]['connect_type']  = 'tcp';
#$cfg['Servers'][$i]['extension']     = 'mysql';
#$cfg['Servers'][$i]['compress']      = FALSE;
#$cfg['Servers'][$i]['auth_type']     = 'config';
#$cfg['Servers'][$i]['user']          = 'root';
#$cfg['Servers'][$i]['password']      = '$localDBPassword'; #Needs to be the remote password when setting up the remote server
fi
