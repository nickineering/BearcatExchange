#!/bin/bash
if [ ! -f /home/ubuntu/custom.log ]
    then
        echo "Proccess began at " >> /home/ubuntu/custom.log
        date >> /home/ubuntu/custom.log
        exec >> /home/ubuntu/custom.log 2>&1
        add-apt-repository ppa:ondrej/php5-5.6
        apt-get update -y && apt-get dist-upgrade -y
        apt-get install python-software-properties
        curl https://bootstrap.pypa.io/get-pip.py -O /home/ubuntu/
        python3.4 get-pip.py
        apt-get install apache2 -y
        apt-get install php5 libapache2-mod-php5 php5-mcrypt -y
        apt-get install ruby2.0 -y
        pip3.4 install awscli
        cd /var/www/html/
        aws s3 cp s3://aws-codedeploy-us-east-1/latest/install . --region us-east-1
        chmod +x ./install
        ./install auto
        service codedeploy-agent start
        service codedeploy-agent status
        service apache2 restart
        mkdir /var/www/html/live
        rm /var/www/html/index.html
fi
