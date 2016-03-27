#!/bin/bash
echo "Proccess began: " >> /home/ubuntu/automated/database.log
date >> /home/ubuntu/automated/database.log
exec >> /home/ubuntu/automated/database.log 2>&1
DATE=`date +%Y%m%d`
aws rds create-db-snapshot --db-instance-identifier bearcat --db-snapshot-identifier 'bearcat-'$DATE
