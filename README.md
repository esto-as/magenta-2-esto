# Esto Payment integration

## Installation

* Put the module code to your Magento root directory so the full path is `app/code/Esto/HirePurchase`.
* From command line as a Magento root filesystem owner run the following commands:
  * `php bin/magento module:enable Esto_HirePurchase` (to enable the module).
  * `php bin/magento setup:upgrade` (to install the module in the system properly).
* If your Magento is in **production mode** then additionally run the following commands:
  * `php bin/magento setup:di:compile` (dependencies compilation).
  * `php bin/magento setup:static-content:deploy` for static content deployment. You might need to provide specific locales (en_US is used by default). In this case you must provide them in the command as arguments. For Estonian and English locales run `php bin/magento setup:static-content:deploy en_US et_EE`.
  
## Requirements

The module requires Magento 2.4+.