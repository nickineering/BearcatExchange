# Bearcat Exchange
**The master branch of this repository automatically deploys**

This is the source code repository for [BearcatExchange.com](https://bearcatexchange.com).

Local copies require a database to operate. That database can be created by running 'bearcatexchange.sql'. The website is hosted on and AWS EC2 instance running a LAMP stack with Ubuntu 16.04 LTS and PHP 7.0. Code is deployed with AWS CodeDeploy through GitHub. Regardless of the deployment enviorment the code will attempt to send all emails via Amazon SES servers. Passwords appearing in this document are placeholders only and are replaced on production servers.

If you have any questions about this repository please contact Nicholas Ferrara at support@bearcatexchange.com.
