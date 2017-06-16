#!/bin/bash
mkdir /home/ubuntu/automated/
source /home/ubuntu/bearcatexchange.ini #Known issue: This does not retrieve passwords as intended
curl https://bootstrap.pypa.io/get-pip.py -o /home/ubuntu/automated/get-pip.py
python3pip /home/ubuntu/automated/get-pip.py
pip3 install awscli
mkdir /home/ubuntu/.aws
echo "[default]
aws_access_key_id = '$awsCliId'
aws_secret_access_key= '$awsCliSecret'
region=$awsRegion
output=json
" > /home/ubuntu/.aws/config
wget https://aws-codedeploy-us-east-1.s3.amazonaws.com/latest/install
apt-get install ruby #Needed to install CodeDeploy agent since this version does not fetch via s3
chmod +x ./install
./install auto
mv install automated/
service codedeploy-agent start
service codedeploy-agent status
