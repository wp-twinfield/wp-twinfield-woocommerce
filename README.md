# Twinfield for WooCommerce

WordPress Twinfield plugin for WooCommerce.

## Requirements

*	[Twinfield](https://github.com/wp-twinfield/wp-twinfield) plugin.

## Installation

To create Twinfield invoices from WooCommerce orders the WooCommerce products must be linked
to Twinfield articles. To achieve this you can can create Twinfield articles for all your WooCommerce
products, but you can also create one Twinfield article which you use for all your WooCommerce products.

It is recommended that you enable the "Prices can be changed" feature for the Twinfield articles that
you link to WooCommerce products. This allows the Twinfield WooCommerce plugin to override the 
Twinfield article price with the WooCommerce product price. This way your Twinfield article price don't
have to correspond with your WooCommerce product price.

![Twinfield edit Item](http://pronamic.nl/wp-content/uploads/2014/11/twinfield-article-edit.png)

For each WooCommerce product you can specify an Twinfield article and subarticle code. If you enter
an Twinfield article code for an WooCommerce product this code will be used to create the Twinfield invoice.
If you leave the article and subarticle code fields empty then the article and subarticle code from
the Twinfield settings page will be used.
