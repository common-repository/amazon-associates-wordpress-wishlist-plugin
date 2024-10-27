<?php
    if(!class_exists("wishlist_view"))
    {
    	class wishlist_view
    	{
    		function getWishlist($items)
    		{
    			$plugin_array = array_reverse(explode('/', dirname(__FILE__)));
			$plugin_name = $plugin_array[1];
			
			require_once(ABSPATH."wp-content/plugins/$plugin_name/models/wishlist_model.php");

    			$wishlist_model = new wishlist_model();

    			$str = "<table>";

    			foreach($items as $item)
    			{
    				$link = $wishlist_model->getItemLink($item);

    				$str.= "<tr><td><a href='".$link."'><img src='".$wishlist_model->getItemImage($item)."' /></a></td>";
    				$str.= "<td><span><a href='".$link."'>".$item->title." - ".$item->author."</a></span> ";
    				$str.= "<span>&pound;".($item->price/100)."</span></td></tr>";
    			}

    			$str .= "</table>";
    			return $str;
    		}

    		function printMessage($message)
    		{
    			?>
    			<div id="message" class="updated fade">
    				<p><?=$message;?></p>
    			</div>
    			<?
    		}

    		function printOptionPage($settings)
    		{
    			?>
    			<div class='wrap'>
				<h2>Amazon Wishlist Options</h2>
				<form method="post" action="<?php echo $_SERVER['REQUEST_URI'];?>">

					<table class="optiontable">
						<tbody>
							<tr valign="top">
							<th scope="row">
								<label for="amazon_locale">Locale</label>
							</th>
							<td>
								<input id="amazon_locale" name="amazon_locale" type="text" size="40"  value="<?=$settings['wishlist_locale'];?>"/>
							</td>
							</tr>
							<tr valign="top">
							<th scope="row">
								<label for="amazon_wishlist_id">Wishlist ID</label>
							</th>
							<td>
								<input id="amazon_wishlist_id" name="amazon_wishlist_id" type="text" size="40" value="<?=$settings['wishlist_id'];?>"/>
							</td>
							</tr>
							<tr valign="top">
							<th scope="row">
								<label for="amazon_ws_id">Amazon Web Services ID</label>
							</th>
							<td>
								<input id="amazon_ws_id" name="amazon_ws_id" type="text" size="40" value="<?=$settings['wishlist_services_id'];?>" />
							</td>
							</tr>
							<tr valign="top">
							<th scope="row">
								<label for="wishlist_ws_secret_key">Amazon Secret Access Key</label>
							</th>
							<td>
								<input id="wishlist_ws_secret_key" name="wishlist_ws_secret_key" type="text" size="40" value="<?=$settings['wishlist_secret_access_key'];?>" />
							</td>
							</tr>
							<tr valign="top">
							<th scope="row">
								<label for="amazon_associate_id">Amazon Associate ID</label>
							</th>
							<td>
								<input id="amazon_associate_id" name="amazon_associate_id" type="text" size="40" value="<?=$settings['wishlist_associate_id'];?>" />
							</td>
							</tr>
							<tr>
							<td colspan="2">
								<p class="submit">
									<input type="submit" name="update" value="<?php _e('Fetch Items') ?>" />
									<input type="submit" name="save" value="<?php _e('Save settings') ?>" />
								</p>
							</td>
							</tr>
						</tbody>
					</table>
					</form>
				</div>
    			<?
    		}
    	}
    }
?>
