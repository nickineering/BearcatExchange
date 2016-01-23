#!/bin/bash
if [ ! -f /home/ubuntu/custom-after-install.log ]
    then
        echo "Proccess began at " >> /home/ubuntu/custom-after-install.log
        date >> /home/ubuntu/custom-after-install.log
        exec >> /home/ubuntu/custom-after-install.log 2>&1
        a2ensite bearcatexchange.com.conf
        #Must manually change to "AllowOverride All" in <Directory /var/www/> in etc/apache2/apache2.conf
        #Without that change local .htaccess files will not work.
        a2enmod rewrite
        service apache2 restart
fi

