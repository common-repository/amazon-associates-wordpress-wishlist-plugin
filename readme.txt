=== Amazon Associates Wishlist  ===
Contributors: ironyboy
Donate link: http://www.ianwootten.co.uk/wishlist
Tags: amazon, associates, wishlist
Requires at least: 2.0
Tested up to: 2.8

A plugin to allow display of amazon wishlists upon your blog, whilst earning a associates referral fee for yourself or someone else.

== Installation ==

1. Upload the contents of /wishlist to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the Wishlist options under the plugin page that appears. Your wishlist id is the 13 letter/digit string that 
appears in your address bar when visiting your wishlist via amazon. Your locale is the final part of the amazon address you
usually visit (i.e. com for US or .co.uk for UK, etc). You'll find your web services id and secret access key under your 
amazon web services account. Add your own associate id (or leave it as the default :) )
4. Click the "Fetch Items" button to get details of all your wishlist items. Don't worry, you don't have to do this every 
time you add a item to your wishlist, it will be updated on an hourly basis. 
5. Place `<!--wishlist_content-->` on a post or page of your choosing, which will be replaced with all your wishlist contents
when displayed.

== Changelog ==

= 0.21 =
* Fixed hardcoded naming errors.

= 0.2 =
* Updated to make use of amazons new digital signitures
