This plugin will enable syncing woocommerce product stock and other product data across sites in a wordpress multisite install.

For now it is a work in progress. The plugin is not functional yet.

The plugin will take the approach of hooking up to wooocommerce functions rather than WP REST API calls, like e.g. Stock Sync for Woocommerce or (probably) Woo Multistore.
This apparoach has the following advantages (+) and drawbacks (-):
+ the sync process is faster, as it is not asynchronous API request, but a function triggered in the main program loop
+ this means that stock (and other product parameters) are always synced and there is smaller risk of selling a product that has just run out of stock
- this also means that the main program loop takes a bit longer - so the customer has to wait a tiny bit longer for the checkout process to complete, as the program will have to go to all connected products in a multisite install to update their stock

As a side note: the speed of checkout should not be affected significanlty (depending on the number of sites aftected and the number of products it should be in the range of miliseconds). On the other hand the benefit is that there is virtually no possibility of over-selling an item, like there theoretically is with asynchronos API calls.

The plugin is also going to have:
1. a dashboard for inspecting stock changes for each product
2. a dashboard for managing stock levels on all sites at the same time and for reconciling any differences between them
3. an option to use SKU to link products or to link products across sites manually (if SKU is not unique)
4. options to choose which product data should be synced. This will probably include an option to sync data as it is changed, to sync it in a cron process, or to sync it only manually when specifically triggered by the admin
5. an integration with polylang plugin (as I am using polylang for my website). When polylang is used SKU is no longer unique, but rather SKU+language is unique on a particular site.


