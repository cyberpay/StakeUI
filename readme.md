[![Stories in Ready](https://badge.waffle.io/Reddhead-devs/StakeUI.png?label=ready&title=Ready)](https://waffle.io/Reddhead-devs/StakeUI)
StakeUI
=============

###Credits
This script was originally created by [@Convertor](https://www.reddcointalk.org/user/convertor)

###Purpose
StakeUI is a web interface for your staking, showing your addresses, accounts, transactions, staking and muuch more!
You can even see a nice graph over total weight, average weight, network weight, interest, balance & connections.
You'll also be able to see your current balances BTC value, pulled from [Reddex](https://reddex.tk)

###Install
* mkdir app/db
* mkdir app/backups
* chmod 777 app/db
* chmod 777 app/backups
* Add cronjob (*/15 * * * * php /var/www/cron/index.php) That will make the cron rune very 15 minutes, which I've found enough, feel free to change it.
* Make sure the path in cron/index.php @ top is correct for your setup.
Open app/config.php and change the settings to fit your needs.

###List of benefits
* Backup interface
* Easy user interface
* Nice graphs
* Take control over your staking
* Fast, Quick, Efficient
* Does not rely on a database
* And much more!

###Requirements
* PHP5
* Apache with mod_rewrite (You can use ngnix too, but you'll need to rewrite the .htaccess yourself then)
* Reddcoin Wallet with RPC access
* SSL support (if you're using HTTPS to communicate with your wallet)

###Reddcoin Donation Address
    ReFefyxdeFjbYp12JTT1BiAMCdquba9HTm

###Bitcoin Donation Address
    13A22BubLzAEZH1GGEAfc4g99VuJnEgp2f

###License | AGPL
    You should have received a copy of the Affero General Public License.  
	If not, see http://www.gnu.org/licenses/agpl-3.0.html
