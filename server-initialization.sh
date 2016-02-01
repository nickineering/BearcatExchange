#!/bin/bash
if [ ! -f /home/ubuntu/automated/custom.log ]
    then
        mkdir /home/ubuntu/automated/
        cd /home/ubuntu/automated/
        echo "Proccess began at " >> /home/ubuntu/automated/custom.log
        date >> /home/ubuntu/automated/custom.log
        exec >> /home/ubuntu/automated/custom.log 2>&1
        add-apt-repository ppa:ondrej/php5-5.6
        apt-get update -y && apt-get dist-upgrade -y
        apt-get autoremove -y && apt-get autoclean -y
        apt-get install python-software-properties
        curl https://bootstrap.pypa.io/get-pip.py -O /home/ubuntu/automated/
        python3.4 /home/ubuntu/automated/get-pip.py
        apt-get install ruby2.0 apache2 php5 libapache2-mod-php5 php5-mcrypt php5-cli php5-fpm php5-gd libssh2-php mysql-server php5-mysqlnd git unzip zip postfix php5-curl mailutils php5-json phpmyadmin php-pear -y
        php5enmod mcrypt
        pear install mail
        curl -sS https://getcomposer.org/installer /var/www/be/live/ | php
        pip3.4 install awscli django
        cd /var/www/
        aws s3 cp s3://aws-codedeploy-us-east-1/latest/install . --region us-east-1
        chmod +x ./install
        ./install auto
        service codedeploy-agent start
        service codedeploy-agent status
        service apache2 restart
        mkdir /var/www/be/live/ -p
        chown -R $USER:$USER /var/www/be
        chmod -R 755 /var/www
        rm /var/www/html/index.html

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
