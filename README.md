# Bearcat Exchange

**The master branch of this repository automatically deploys**

This is the source code repository for
[BearcatExchange.com](https://bearcatexchange.com).

Local copies require a database to operate. That database can be created by running
'bearcatexchange.sql'. The website is hosted on and AWS EC2 instance running a LAMP
stack with Ubuntu 16.04 LTS and PHP 7.0 and is connected to and RDS Database. Code is
deployed with AWS CodeDeploy through GitHub. Regardless of the deployment environment
the code will attempt to send all emails via Amazon SES servers.
bearcatexchange-example.ini is replaced in the production environment by
bearcatexchange.ini which has the live passwords. For local testing use a copy called
bearcatexchange-local.ini.

If you have any questions about this repository please contact Nicholas Ferrara at
support@bearcatexchange.com.

## Installation

While updates are fully automated the initial installation is not. In future, it should
be automated but until then you must follow the following steps:

1. Start a public facing ubuntu server
2. Connect it to some sort of MySQL database
3. Create an AWS Account so that emails can be sent with SES. This is much faster than
   using your webserver. You do not need to use other AWS products if you don't want to,
   although they are used in the current implementation.
4. `git clone` the repo onto the server.
5. Run `cd BearcatExchange` to move into the folder your just cloned.
6. Place your real passwords into a file called `bearcatexchange.ini`. This has the same
   fields as `bearcatexchange-example.ini`.
7. Run `sudo chmod 777 codeDeploySetup.sh` followed by `./codeDeploySetup.sh` to install
   the code deploy agent if you are using AWS, or only run the parts of that script that
   are relevant to your setup.
8. Copy the files and run the scripts that would be installed by the code deploy agent
   in `appspec.yml` to complete the setup. these are not perfect and will require some
   manual intervention.
9. Enjoy your new install!
