##Maintenance Mode Extension

This extension is perfect if you want to put your Magento storefront into maintenance mode for a few minutes/hours/days while you update your site. Showing a blank or low-quality maintenance page leaves a negative impression on your customers's mind. With this extension, you can customize pages as per your requirements and show newsletter, countdown time, social links, contact us, etc... to keep your customers engaged and updated.

##Support: 
version - 2.3.x, 2.4.x

##How to install Extension

1. Download the archive file.
2. Unzip the file
3. Create a folder [Magento_Root]/app/code/Sparsh/MaintenanceMode
4. Drop/move the unzipped files to directory '[Magento_Root]/app/code/Sparsh/MaintenanceMode'

#Enable Extension:
- php bin/magento module:enable Sparsh_MaintenanceMode
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile
- php bin/magento setup:static-content:deploy
- php bin/magento cache:flush

#Disable Extension:
- php bin/magento module:disable Sparsh_MaintenanceMode
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile
- php bin/magento setup:static-content:deploy
- php bin/magento cache:flush
