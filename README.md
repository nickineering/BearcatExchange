# Bearcat Exchange

**The master branch of this repository automatically deploys**

This is the source code repository for [BearcatExchange.com](https://bearcatexchange.com).

Local copies require a database to operate. That database can be created by running 'bearcatexchange.sql'. The website is hosted on and AWS EC2 instance running a LAMP stack with Ubuntu 16.04 LTS and PHP 7.0 and is connected to and RDS Database. Code is deployed with AWS CodeDeploy through GitHub. Regardless of the deployment environment the code will attempt to send all emails via Amazon SES servers. bearcatexchange-example.ini is replaced in the production environment by bearcatexchange.ini which has the live passwords. For local testing use a copy called bearcatexchange-local.ini.

If you have any questions about this repository please contact Nicholas Ferrara at support@bearcatexchange.com.
