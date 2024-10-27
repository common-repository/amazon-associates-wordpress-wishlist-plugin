<?php
    if(!class_exists("wishlist_model"))
    {
    	class wishlist_model
    	{
			var $img_prefix = 'http://images-eu.amazon.com/images/P/';
			var $img_postfix = '.02.TXXXXXXX.jpg';

    		function dbInstall()
			{
			   global $wpdb;

			   $table_name = $wpdb->prefix . "wishlist";

			   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

			   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
				$sql = "CREATE TABLE " . $table_name . " (
					  id mediumint(9) NOT NULL AUTO_INCREMENT,
					  asin text NOT NULL,
					  price int(6) DEFAULT '0' NOT NULL,
					  author text NOT NULL,
					  title text NOT NULL,
					  UNIQUE KEY id (id)
					);";

				dbDelta($sql);
			   }
			}

    		function getOptions()
    		{
				$settings = Array();

				$settings['wishlist_locale'] = get_option("wishlist_locale");
				$settings['wishlist_id'] = get_option("wishlist_id");
				$settings['wishlist_secret_access_key'] = get_option("wishlist_secret_access_key");
				$settings['wishlist_services_id'] = get_option("wishlist_services_id");
				$settings['wishlist_associate_id'] = get_option("wishlist_associate_id");

				return $settings;
    		}

    		function updateOptions()
    		{
    			update_option("wishlist_locale", $_POST['amazon_locale']);
				update_option("wishlist_id", $_POST['amazon_wishlist_id']);
				update_option("wishlist_secret_access_key", $_POST['wishlist_ws_secret_key']);
				update_option("wishlist_services_id", $_POST['amazon_ws_id']);
				update_option("wishlist_associate_id",$_POST['amazon_associate_id']);
    		}

	    	function setOptions()
		{
			add_option("wishlist_locale", "co.uk");
			add_option("wishlist_id", "2ZGQU6TDBXOYL");
			add_option("wishlist_services_id", "");
			add_option("wishlist_secret_access_key", "");
			add_option("wishlist_associate_id", "staplediet-21");
		}

	    	function unsetOptions()
			{
				delete_option("wishlist_locale");
				delete_option("wishlist_id");
				delete_option("wishlist_services_id");
				delete_option("wishlist_secret_access_key");
				delete_option("wishlist_associate_id");
			}

			function getItemLink($item)
			{
				$settings = $this->getOptions();

				$link = "http://www.amazon.co.uk/gp/redirect.html%3F";
				$link.= "ASIN=".$item->asin."%26tag=".$settings['wishlist_associate_id'];
				$link.= "%26lcode=xm2%26cID=2025%26ccmID=165953%26location=/o/ASIN/";
				$link.= $item->asin."%253F%2526colid=".$settings['wishlist_id'];

				return $link;
			}

			function getItemImage($item)
			{
				return $this->img_prefix.$item->asin.$this->img_postfix;
			}

			function insertItem($item)
			{
				global $wpdb;

				$table_name = $wpdb->prefix . "wishlist";

				if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {

					$insert = "INSERT INTO " . $table_name .
			            " (asin, title, author, price) " .
			            "VALUES ('" . $item['asin'] . "','" . $wpdb->escape($item['title']) . "','" . $wpdb->escape($item['author']) ."','" . $wpdb->escape($item['price']) . "')";

			      		$results = $wpdb->query( $insert );

					return $results;
				}
			}

    			function getItems()
			{
			   global $wpdb;

				$table_name = $wpdb->prefix . "wishlist";

				if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
					$sql = "SELECT * from ".$table_name." ORDER BY id";

					$results = $wpdb->get_results($sql);

					return $results;
				}
			}

			function emptyWishlist()
			{
			   	global $wpdb;

				$table_name = $wpdb->prefix . "wishlist";

				if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
					$sql = "TRUNCATE TABLE ".$table_name;

					$results = $wpdb->query($sql);

					return $results;
				}
			}

			function fetchWishlist()
			{
				$settings = $this->getOptions();

				$Timestamp = gmdate("Y-m-d\TH:i:s\Z");
				$Timestamp = str_replace(":", "%3A", $Timestamp);
				$ResponseGroup = "ListItems,Large";
				$ResponseGroup = str_replace(",", "%2C", $ResponseGroup);

				$String = "AWSAccessKeyId=".$settings['wishlist_services_id'].
							"&ListId=".$settings['wishlist_id'].
							"&ListType=WishList".
							"&Operation=ListLookup&ResponseGroup=$ResponseGroup&Service=AWSECommerceService&Timestamp=$Timestamp&Version=2009-01-06";

				$String = str_replace("\n", "", $String);
				$Prepend = "GET\necs.amazonaws.".$settings['wishlist_locale']."\n/onca/xml\n";
				$PrependString = $Prepend.$String;

				$Signature = base64_encode(hash_hmac("sha256", $PrependString, $settings['wishlist_secret_access_key'], True));
				$Signature = str_replace("+", "%2B", $Signature);
				$Signature = str_replace("=", "%3D", $Signature);

				$BaseUrl = "http://ecs.amazonaws.".$settings["wishlist_locale"]."/onca/xml?";
				$SignedRequest = $BaseUrl . $String . "&Signature=" . $Signature;

				$ch = curl_init();
				$timeout = 15; // set to zero for no timeout
				curl_setopt ($ch, CURLOPT_URL, $SignedRequest);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
				$xml = curl_exec($ch);

				curl_close($ch);

				$simple_xml = simplexml_load_string($xml);

				$page_count = $simple_xml->Lists->List->TotalPages;
				$item_count = $simple_xml->Lists->List->TotalItems;

				for($i=1; $i <= $page_count; $i++)
				{

					$Timestamp = gmdate("Y-m-d\TH:i:s\Z");
					$Timestamp = str_replace(":", "%3A", $Timestamp);
					$ResponseGroup = "ListItems,Large";
					$ResponseGroup = str_replace(",", "%2C", $ResponseGroup);

					$String = 	"AWSAccessKeyId=".$settings['wishlist_services_id'].
								"&ListId=".$settings['wishlist_id'].
								"&ListType=WishList".
								"&Operation=ListLookup&ProductPage=$i&ResponseGroup=$ResponseGroup&Service=AWSECommerceService&Timestamp=$Timestamp&Version=2009-01-06";

					$String = str_replace("\n", "", $String);
					$Prepend = "GET\necs.amazonaws.".$settings['wishlist_locale']."\n/onca/xml\n";
					$PrependString = $Prepend.$String;

					$Signature = base64_encode(hash_hmac("sha256", $PrependString, $settings['wishlist_secret_access_key'], True));
					$Signature = str_replace("+", "%2B", $Signature);
					$Signature = str_replace("=", "%3D", $Signature);

					$SignedRequest = $BaseUrl . $String . "&Signature=" . $Signature;

					$ch = curl_init();
					$timeout = 15; // set to zero for no timeout
					curl_setopt ($ch, CURLOPT_URL, $SignedRequest);
					curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
					$xml = curl_exec($ch);

					curl_close($ch);

					$simple_xml = simplexml_load_string($xml);

					foreach($simple_xml->Lists->List->ListItem as $item)
					{
						$qd = (int) $item->QuantityDesired;
						$qr = (int) $item->QuantityReceived;

						$recieved = (($qd-$qr)==0);

						if(!$recieved)
						{
							$item = $item->Item;

							$item_to_store = Array();

							$product_group = (string) $item->ItemAttributes->ProductGroup;

							$item_to_store['author'] = '';

							if($product_group=='Music')
							{
								$item_to_store['author'] = (string) $item->ItemAttributes->Artist;
							}

							if($product_group=='Book')
							{
								$item_to_store['author'] = (string) $item->ItemAttributes->Author;
							}

							$item_to_store['title'] = (string) $item->ItemAttributes->Title;
							$item_to_store['asin'] = (string) $item->ASIN;
							$item_to_store['price'] = (int) $item->Offers->Offer->OfferListing->Price->Amount;

							$this->insertItem($item_to_store);

							$product_count++;
						}
					}
					sleep(1);
				}
				return $product_count;
			}
    	}
    }
?>
