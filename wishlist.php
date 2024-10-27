<?php
    /*
      Plugin Name: Wishlist
      Plugin URI: http://www.ianwootten.co.uk/wishlist
      Description: A plugin to allow display of amazon wishlists upon your blog. Use the tag <!--wishlist_content--> in any post once items have been fetched.
      Version: v0.2
      Author: Ian Wootten
	  Author URI: http://www.ianwootten.co.uk
     */

	if(!class_exists("Wishlist"))
	{
		class Wishlist
		{
			function Wishlist()
			{
				add_action('hourly_hook', array(&$this, 'cronUpdateWishlist'));
			}

			function _install()
			{
				if (!wp_next_scheduled('hourly_hook')) {
					wp_schedule_event(0, 'hourly', 'hourly_hook' );
				}
			}

			function _uninstall()
			{
				remove_action('hourly_hook', 'hourly_hook');
				wp_clear_scheduled_hook('hourly_hook');
    			}

    			function cronUpdateWishlist()
			{
				require_once("models/wishlist_model.php");
				$wishlist_model = new wishlist_model();

				$noOfItems = count($wishlist_model->getItems());

				if($noOfItems>0)
				{
					$wishlist_model->emptyWishlist();
				}

				$wishlist_model->fetchWishlist();
			}

			function wishlistController()
			{
				require_once("views/wishlist_view.php");
				require_once("models/wishlist_model.php");

				$wishlist_view = new wishlist_view();
				$wishlist_model = new wishlist_model();

				if(isset($_POST['save']))
				{
					$wishlist_model->updateOptions();
					$wishlist_view->printMessage("Options successfully saved.");
				}

				if(isset($_POST['update']))
				{
					$noOfItems = count($wishlist_model->getItems());

					if($noOfItems>0)
					{
						$wishlist_model->emptyWishlist();

						$message = "Removed ".$noOfItems." items from database. ";
					}

					$count = $wishlist_model->fetchWishlist();

					if($count>0)
					{
						$message .= "Success! Found ".$count." items from amazon";

						$wishlist_view->printMessage($message);
					}
					else
					{
						$message .= "Wasn't able to find any items using those settings.";

						$wishlist_view->printMessage($message);
					}
				}

				$settings = $wishlist_model->getOptions();

				$wishlist_view->printOptionPage($settings);
			}
		}

		$wishlist = new Wishlist();
	}

	if(!function_exists("addWishlistPanel"))
	{
		function addWishlistPanel()
		{
			global $wishlist;

			if(!isset($wishlist))
			{
				return;
			}

			if(function_exists('add_options_page'))
			{
				add_submenu_page("plugins.php", "Wishlist", "Wishlist", 9, __FILE__,
				array(&$wishlist, 'wishlistController'));
			}
		}
	}

	if(!function_exists("wishlistContent"))
	{
		function wishlistContent($content)
		{
			$search_string = '<!--wishlist_content-->';

			if(preg_match('|'.$search_string.'|', $content))
			{
				require_once("models/wishlist_model.php");
				require_once("views/wishlist_view.php");

				$wishlist_model = new wishlist_model();
				$wishlist_view = new wishlist_view();

				$items = $wishlist_model->getItems();

				$replace_string = $wishlist_view->getWishList($items);
				$content = str_replace($search_string, $replace_string, $content);
			}
			return $content;
		}
	}

	if(isset($wishlist))
	{
		require_once("models/wishlist_model.php");
		$wishlist_model = new wishlist_model();

		add_action('admin_menu', 'addWishlistPanel');
		add_filter('the_content', 'wishlistContent');

		register_activation_hook(__FILE__, array(&$wishlist, '_install'));
		register_activation_hook(__FILE__, array(&$wishlist, '_uninstall'));

		register_activation_hook(__FILE__, array(&$wishlist_model, 'dbInstall'));
		register_activation_hook(__FILE__, array(&$wishlist_model, "setOptions"));
		register_deactivation_hook(__FILE__, array(&$wishlist_model, "unsetOptions"));
	}
?>
