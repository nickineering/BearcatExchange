#!/bin/bash
if [ ! -f /home/ubuntu/automated/custom-after-install.log ]
    then
        echo "Proccess began at " >> /home/ubuntu/automated/custom-after-install.log
        date >> /home/ubuntu/automated/custom-after-install.log
        exec >> /home/ubuntu/automated/custom-after-install.log 2>&1
        a2ensite bearcatexchange.com.conf
        #Must manually change to "AllowOverride All" in <Directory /var/www/> in etc/apache2/apache2.conf
        #Without that change local .htaccess files will not work.
        a2enmod rewrite
        service apache2 restart
        #crontab -l | { cat; echo "0 0 */3 * * /home/ubuntu/automated/backup_database.sh"; } | crontab -
        php /var/www/be/live/composer.phar install
fi
