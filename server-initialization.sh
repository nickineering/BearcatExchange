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
        apt-get install python-software-properties
        curl https://bootstrap.pypa.io/get-pip.py -o /home/ubuntu/automated/get-pip.py
        python3.4 /home/ubuntu/automated/get-pip.py
        apt-get install ruby2.0 apache2 php7.0 libapache2-mod-php7.0 php7.0-mcrypt php7.0-cli php7.0-fpm php7.0-gd libssh2-php php7.0-mysqlnd git unzip zip php7.0-curl mailutils php7.0-json -y4
        debconf-set-selections <<< 'mysql-server mysql-server/root_password password codebook'
        debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password codebook'
        debconf-set-selections <<< "postfix postfix/mailname string 'bearcatexchange.com'"
        debconf-set-selections <<< "postfix postfix/main_mailer_type string 'Internet Site'"
        echo "phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2" | debconf-set-selections
        echo "phpmyadmin phpmyadmin/dbconfig-install boolean true" | debconf-set-selections
        echo "phpmyadmin phpmyadmin/mysql/admin-user string root" | debconf-set-selections
        echo "phpmyadmin phpmyadmin/mysql/admin-pass password codebook" | debconf-set-selections
        echo "phpmyadmin phpmyadmin/mysql/app-pass password codebook" |debconf-set-selections
        echo "phpmyadmin phpmyadmin/app-password-confirm password codebook" | debconf-set-selections
        apt-get install postfix phpmyadmin mysql-server python-letsencrypt-apache -y
        php7.0enmod mcrypt
        pear install mail
        curl https://getcomposer.org/installer -o /var/www/be/live/composer.phar | php
        pip3.4 install awscli django
        aws s3 cp s3://aws-codedeploy-us-east-1/latest/install . --region us-east-1
        chmod +x ./install
        ./install auto
        mv install automated/
        service codedeploy-agent start
        service codedeploy-agent status
        mkdir /home/ubuntu/.aws
        echo -ne '\n nferrara100@gmail.com \n \n ^[[1;5B \n \n' | letsencrypt --apache
        touch /etc/cron.d/twicedaily
        chmod 777 /etc/cron.d/twicedaily
        echo "56 06,16 * * * letsencrypt renew --agree-tos -m nferrara100@gmail.com" >> /etc/cron.d/twicedaily
        rm mycron
        echo "set number" > ~/.vimrc
        cd /var/www/
        service apache2 restart
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
#$cfg['Servers'][$i]['password']      = 'codebook';
fi
