<tr id='<?php echo $blog_id . $id;?>'>    
    <th class='wms-checkbox-col'><input type='checkbox' name='wms_log_checkbox' id='<?php echo $id;?>-wms_log_checkox' value='<?php echo $id;?>' /></th>
        <td><?php echo $sku;?> </td>
        <td id='<?php echo $id;?>'>
            <a href='<?php echo $link;?> ' target='_blank' >
                <img class='attachment-shop_thumbnail size-shop_thumbnail wp-post-image' src='<?php echo $image_link;?>' alt='<?php echo $title;?>' sizes='(max-width: 180px) 85vw, 180px' height='180' width='180' ></img>
            </a>
        </td>
        <td>
            <a href='<?php echo $link;?> ' target='_blank'" ><?php echo $title;?></a><br />
        </td>
        <td>
            <?php echo $var_attributes;?><br />
        </td>
        <td>
            <?php echo $stock;?>
            <input id="" class="wms_edit_stock" type='text' style='display:none; width: 3em; margin: 0; padding: 0; text-align: center;' value="<?php echo $stock;?>" >
            <a class="" href="#"></a>
        </td>
        <td>
            <?php if(is_array($stock_log) ) { ?>
            <input type='button' class='button button-large button-secondary wms-expand-button' id='expand-<?php echo $id;?>' name='wms-expand-bttn' value='<?php _e('expand', 'wms-woocommerce-stock-log');?>'/>
            <?php } ?>
        </td>
</tr>

<?php if(is_array($stock_log) ) { ?>
    <tr id='<?php echo $id;?>-wms-stock-log' style='display:none;'>
        <td colspan='7' >
            <div class='wms_product_log_div_class' id='<?php echo $id;?>-wms_product_log_div'>
                <table class='wms_table_with_logs'>
                <thead><tr>
                <th><?php _e('Date', 'wms-woocommerce-stock-log');?></th>
                <th><?php _e('ID', 'wms-woocommerce-stock-log');?></th>
                <th><?php _e('SKU', 'wms-woocommerce-stock-log');?></th>
                <th><?php _e('Change type', 'wms-woocommerce-stock-log');?></th>
                <th><?php _e('Order no.', 'wms-woocommerce-stock-log');?></th>
                <th><?php _e('User', 'wms-woocommerce-stock-log');?></th>
                <th><?php _e('Stock', 'wms-woocommerce-stock-log');?></th>
                <th><?php _e('Change', 'wms-woocommerce-stock-log');?></th>
                </tr></thead>
                </tbody>
                <?php foreach ( $stock_log as $log_entry ) { ?>
                    <tr>
                        <td><?php echo $log_entry['data'];?> </td>
                        <td><?php echo $log_entry['id'];?> </td>
                        <td><?php echo $log_entry['sku'];?> </td>
                        <td><?php echo $log_entry['change_function'];?> </td>
                        <td><a href='<?php echo admin_url();?>post.php?post=<?php echo $log_entry['order_id'];?>&action=edit' target='_blank' ><?php echo $log_entry['order_id'];?> </a></td>
                        <td><?php echo $log_entry['user'];?> </td>
                        <td><?php echo $log_entry['stock'];?> </td>
                        <td><?php echo $log_entry['diff'];?> </td>
                        </tr>
                <?php } ?>
                </tbody>
                </table>
                <br />
            </div>
        </td>
    </tr>

<?php } ?>