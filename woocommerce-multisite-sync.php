<?php
/**
 * Plugin Name: Woocommerce Multisite Product Sync and Stock Management
 * Plugin URI: https://github.com/ndrwbdnz/woocommerce-multisite-sync
 * Description: Sync woocommerce products and other content in wordpress multisite. Manage woocommerce products stock.
 * Version: 0.1
 * Author: Andrzej Bednorz
 * Author URI: https://github.com/ndrwbdnz/
 * License: GPL2
 */
 
/*  Copyright 2021 Andrzej Bednorz  (email : abednorz@gmail.com)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WMS_Woocommerce_Multisite_Sync_Class' ) ) :

class WMS_Woocommerce_Multisite_Sync_Class{

    protected static $_instance;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function __construct(){
        //admin views
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );

        //scripts & styles
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_print_styles', array( $this, 'enqueue_styles') );

        //logging stock changes in product meta data
        add_action( 'woocommerce_product_set_stock', array( $this, 'wms_stock_logging' ) );
        add_action( 'woocommerce_variation_set_stock', array( $this, 'wms_stock_logging' ) );

        //synchronize stock changes across blogs
        add_filter( 'woocommerce_update_product_stock_query', array( $this, 'wms_sync_stock' ), 99, 4 );
    }

	public function enqueue_scripts() {
        if (isset($_GET['page']) && $_GET['page'] == 'wms-stock-log'){
            wp_enqueue_script( 'jquery-ui-datepicker' );
            wp_enqueue_script( 'wms-jquery-qtip', plugin_dir_url( __FILE__ ) . '/assets/jquery.qtip.min.js' );
            wp_enqueue_script( 'wms-js', plugin_dir_url( __FILE__ ) . 'assets/wms.js', array('jquery', 'jquery-ui-datepicker'), null, true );
        }
	}

    public function enqueue_styles(){
        if (isset($_GET['page']) && $_GET['page'] == 'wms-stock-log'){
            wp_enqueue_style( 'wms-css',  plugin_dir_url( __FILE__ ) . 'assets/wms.css');
            wp_enqueue_style( 'wms-jquery-datepicker-style', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css' );
            wp_enqueue_style( 'wms-qtip-style', plugin_dir_url( __FILE__ ) . '/assets/jquery.qtip.min.css' );
        }
    }

    public function admin_menu() {
        add_submenu_page( 'edit.php?post_type=product',             //parent
                            'WMS Stock Log',                        //page title
                            'WMS Stock Log',                        //menu title
                            'manage_options',                       //capability
                            'wms-stock-log',                        //menu slug
                            array( $this, 'wms_stock_log_dashboard' ) ); //function to generate contents of the page
        add_submenu_page( 'edit.php?post_type=product',             //parent
                            'WMS Sync',                             //page title
                            'WMS Sync',                             //menu title
                            'manage_options',                       //capability
                            'wms-sync',                             //menu slug
                            array( $this, 'wms_sync' ) ); //function to generate contents of the page
    }

    public function wms_stock_log_dashboard() {

        $sku_link = admin_url() . 'admin.php?page=wms-stock-log&order_by=sku&direct=DESC';
        if ( !empty( $_GET['order_by'] ) && $_GET['order_by'] == 'sku' && !empty( $_GET['direct'] ) && $_GET['direct'] == 'DESC' ) {
            $sku_link = admin_url() . 'admin.php?page=wms-stock-log&order_by=sku&direct=ASC';
            usort( $result_products, array( $this, 'sortBySkuDESC' ) );
        }
        if ( !empty( $_GET['order_by'] ) && $_GET['order_by'] == 'sku' && !empty( $_GET['direct'] ) && $_GET['direct'] == 'ASC' ) {
            $sku_link = admin_url() . 'admin.php?page=wms-stock-log&order_by=sku&direct=DESC';
            usort( $result_products, array( $this, 'sortBySkuASC' ) );
        }
        $stock_link = admin_url() . 'admin.php?page=wms-stock-log&order_by=stock&direct=DESC';
        if ( !empty( $_GET['order_by'] ) && $_GET['order_by'] == 'stock' && !empty( $_GET['direct'] ) && $_GET['direct'] == 'DESC' ) {
            $stock_link = admin_url() . 'admin.php?page=wms-stock-log&order_by=stock&direct=ASC';
            usort( $result_products, array( $this, 'sortByStockDESC' ) );
        }
        if ( !empty( $_GET['order_by'] ) && $_GET['order_by'] == 'stock' && !empty( $_GET['direct'] ) && $_GET['direct'] == 'ASC' ) {
            $stock_link = admin_url() . 'admin.php?page=wms-stock-log&order_by=stock&direct=DESC';
            usort( $result_products, array( $this, 'sortByStockASC' ) );
        }

        //main page template header
        include( WP_PLUGIN_DIR . '/woocommerce-multisite-sync/wms-template-stock-sync-table-header.php');

        if (is_multisite()){

        }

        //product loop
        $loop = new \WP_Query( array( 'post_type' => 'product', 'nopaging' => true, 'post_status' => 'publish' ) );
        $products = $loop->posts;

        // $not_completed_orders = get_posts( array(
        //     'numberposts' => -1,
        //     'post_type' => wc_get_order_types(),
        //     'post_status' => array( 'wc-pending', 'wc-processing', 'wc-on-hold' ),
        //         ) );
        //$booked_products = array();
        // foreach ( $not_completed_orders as $key => $value ) {
            //     $post_id = $value->ID;
            //     $tmp_order = wc_get_order( $post_id );
            //     $tmp_products = $tmp_order->get_items();
            //     $dla = '';
            //     $dla .= $tmp_order->get_billing_first_name() . ' ' . $tmp_order->get_billing_last_name() . ' ' . $tmp_order->get_billing_company();
            //     foreach ( $tmp_products as $key1 => $value1 ) {
            //         if ( $value1['variation_id'] != '0' && !empty( $value1['variation_id'] ) ) {
            //             if ( !empty( $booked_products[$value1['variation_id']] ) ) {
            //                 $booked_products[$value1['variation_id']]['qty'] += $value1['qty'];
            //                 $booked_products[$value1['variation_id']]['orders'][] = array( 'id' => $post_id, 'dla' => $dla, 'szt' => $value1['qty'] );
            //             } else {
            //                 $booked_products[$value1['variation_id']]['qty'] = $value1['qty'];
            //                 $booked_products[$value1['variation_id']]['orders'][] = array( 'id' => $post_id, 'dla' => $dla, 'szt' => $value1['qty'] );
            //             }
            //         } else {
            //             if ( !empty( $booked_products[$value1['product_id']] ) ) {
            //                 $booked_products[$value1['product_id']]['qty'] += $value1['qty'];
            //                 $booked_products[$value1['product_id']]['orders'][] = array( 'id' => $post_id, 'dla' => $dla, 'szt' => $value1['qty'] );
            //             } else {
            //                 $booked_products[$value1['product_id']]['qty'] = $value1['qty'];
            //                 $booked_products[$value1['product_id']]['orders'][] = array( 'id' => $post_id, 'dla' => $dla, 'szt' => $value1['qty'] );
            //             }
            //         }
            //     }
            // }



        
        if ( !empty( $products ) ) {
            foreach ( $products as $product ) {
                $product = wc_get_product( $product->ID );

                //check if product has variations and if the stock is managed on the variation level
                $variations = $product->get_children();
                foreach ($variations as $variation_id){
                    
                    $variation = wc_get_product($variation_id);

                    if ($variation->get_manage_stock()){
    
                        $title = $product->get_title();
                        $va1 = $variation->get_variation_attributes();
                        $var_attributes = '';
                        foreach ( $va1 as $attr_name => $attr_value ) {
                            $var_attributes .= str_ireplace( 'attribute_pa_', '', $attr_name ) . ': ' . $attr_value . '; ';
                        }
                        $this->wms_stock_sync_table_item_details($variation, $title, $var_attributes);
                    }
                }

                //see if stock is managed for the product
                if ($product->get_manage_stock()){
                    
                    $title = $product->get_title();
                    $this->wms_stock_sync_table_item_details($product, $title, "");
                }

            }
        }

        include( WP_PLUGIN_DIR . '/woocommerce-multisite-sync/wms-template-stock-sync-table-footer.php');
          
    }

    public function wms_stock_sync_table_item_details($product, $title, $var_attributes){
        $sku = $product->get_sku();
        $id = $product->get_id();
        $image_id = $product->get_image_id();
        $image_src = wp_get_attachment_image_src( $image_id );
        $image_link = esc_html( isset( $image_src['0'] ) ? $image_src['0'] : ''  );
        $stock = (int) $product->get_stock_quantity();
        $link = admin_url() . "post.php?post=" . $id . "&action=edit";
        $stock_log = get_post_meta( $id, 'wms_stock_log', true );
        include( WP_PLUGIN_DIR . '/woocommerce-multisite-sync/wms-template-stock-sync-table-item.php');
    }

    public function wms_stock_logging( $that ) {
        $meta = get_post_meta( $that->get_id(), 'wms_stock_log', true );
        if ( empty( $meta ) ) {
            $meta = array();
        }

        $debug_backtrace = '';

        $data = strftime( "%Y%m%d %H:%M:%S", time() );
        $id = (!empty( $that ) && $that->get_id() > 0) ? (int) $that->get_id() : 0;
        $product = wc_get_product( $id );
        $sku = $product->get_sku();
       
        //user
        $user_id = get_current_user_id();
        if ( $user_id !== 0 ) {
            $user_name = get_userdata( $user_id );
            $user_login = (!is_null( $user_name->user_login )) ? $user_name->user_login : 'gosc';
        } else {
            $user_login = 'gosc';
        }

        //change function            
        if ( !empty( $_POST['action'] ) &&  in_array($_POST['action'], array('woocommerce_reduce_order_item_stock', 'woocommerce_increase_order_item_stock', 'wms_save' ) ) ) {
            $change_function = $_POST['action'];
        } elseif ( !empty( $_REQUEST['action'] ) && in_array($_POST['action'], array('editpost', 'edit', 'inline-save' ) ) ) {
            $change_function = $_POST['action'];
            if($_REQUEST['woocommerce_bulk_edit']) {$change_function .= ' (bulk edit)';}
            if($_REQUEST['woocommerce_quick_edit']) {$change_function .= ' (quick edit)';}
        } elseif ( !empty( $_REQUEST['payment_method'] ) && $_REQUEST['payment_method'] != '' ) {
            $change_function = 'Woocommerce - Nowe Zamowienie (' . $_REQUEST['payment_method'] . ')';
        } else {
            $debug_backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
            $plugin_name = '';
            foreach ( $debug_backtrace as $key => $value ) {
                if ( isset( $value['file'] ) ) {
                    $path = $value['file'];
                    $matches = array();
                    preg_match( '/plugins\/([^\/]+?)\/(?:[^\/]+\/)?(.+)/', $path, $matches );
                    if ( !empty( $matches[1] ) ) {
                        $dir = $matches[1];
                        if ( $dir !== '' && $dir !== 'woocommerce' ) {
                            $plugin_name = $dir;
                            break;
                        }
                    }
                }
            }
            $change_function = 'Other change - ' . $plugin_name;
        }

        $order_id = '';
        if (!empty( $_REQUEST['order_id'] ) && $_REQUEST['order_id']){
            $order_id = $_REQUEST['order_id'];
        } else {
            $debug_backtrace = ($debug_backtrace == '') ? debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ) : $debug_backtrace;
            foreach ( $debug_backtrace as $key => $value ) {
                if ( !empty( $value['function'] ) && $value['function'] == 'wc_reduce_stock_levels' ) {
                    $object = $value['args'];
                    $order_id = (!empty( $object['0'] )) ? $object['0'] : '';
                }
            }
        }

        $stock = $product->get_stock_quantity();

        //stock diff
        $tmp_meta = array_reverse( $meta );
        $tmp_stock = (!empty( $tmp_meta[0]['stock'] )) ? $tmp_meta[0]['stock'] : 0;
        $difff = $stock - $tmp_stock;

        //output
        $value = array(
            'data' => $data,
            'id' => $id,
            'sku' => $sku,
            'change_function' => $change_function,
            'order_id' => $order_id,
            'user' => $user_login,
            'stock' => (int) $stock,
            'diff' => $difff,
        );
        array_push( $meta, $value );
        update_post_meta( $that->get_id(), 'wms_stock_log', $meta );
    }

    public function sortBySkuASC( $a, $b ) {
		return strnatcmp( $a['sku'], $b['sku'] );
	}

	public function sortBySkuDESC( $a, $b ) {
		return strnatcmp( $b['sku'], $a['sku'] );
	}

	public function sortByStockASC( $a, $b ) {
		return $a['stock'] - $b['stock'];
	}

	public function sortByStockDESC( $a, $b ) {
		return $b['stock'] - $a['stock'];
	}

    public function wms_sync_stock($sql, $product_id_with_stock, $new_stock, $operation ){
        if (is_multisite()){
            
            $blogs = get_sites();
            $prod = wc_get_product($product_id_with_stock);
            $prod_sku = $prod->get_sku();
            $current_blog_id = get_current_blog_id();

            //to prevent infinite loop keep track of what is being done
            if (!array_key_exists($current_blog_id, $GLOBALS['_wms_processing_sync'][$prod_sku])){
                $GLOBALS['_wms_processing_sync'][$prod_sku][$current_blog_id] = 'processing';
                $GLOBALS['_wms_processing_sync'][$prod_sku]['done'] = 1;
            }

            foreach( $blogs as $b ){
                if (!array_key_exists($b->blog_id, $GLOBALS['_wms_processing_sync'][$prod_sku])){

                    $GLOBALS['_wms_processing_sync'][$prod_sku][$b->blog_id] = 'processing';
                    switch_to_blog($b->blog_id);
                    $product_id_in_blog = wc_get_product_id_by_sku($prod_sku);
                    wc_update_product_stock($product_id_in_blog, $new_stock, $operation);

                    $GLOBALS['_wms_processing_sync'][$prod_sku]['done'] += 1;
                    //restore_current_blog();
                }
            }

            if ($GLOBALS['_wms_processing_sync'][$prod_sku]['done'] == count($blogs)){
                $GLOBALS['_wms_processing_sync'][$prod_sku] = '';
            }

        }
    
        return $sql;

    }

}
endif;

function WMS_Woocommerce_Multisite_Sync_Function() {
	return WMS_Woocommerce_Multisite_Sync_Class::instance();
}

$wms_woocommerce_multisite_sync_instance = WMS_Woocommerce_Multisite_Sync_Function();