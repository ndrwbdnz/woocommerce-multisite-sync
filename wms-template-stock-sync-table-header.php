<script type="text/javascript">
    /* <![CDATA[ */
    // var wmsLogMetaData = <?php //echo json_encode( $meta_array ); ?>;
    // var ajax_url = <?php //echo json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
    var admin_url = <?php echo json_encode( admin_url() ); ?>;
    /* ]]> */
</script>

<div class="wrap woocommerce">
    <h1><?php _e('WMS Stock Log', 'wms-woocommerce-stock-log');?></h1>
    <br />
    <br class="clear" />
    <div class="wrap">
        <!-- <div id="wms-print-csv-div">
            Od: <input type="date" id="wms_input_csv_from_id"  name="wms_input_csv_from" /> 
            Do: <input type="date" id="wms_input_csv_to_id" name="wms_input_csv_to" /> 
            <input type="button" class="button button-primary" name="woonvnetory_button_csv_generate" value="Generuj Raport" />
        </div> -->
        <br />
        <table id="wms_log_table" class="wp-list-table widefat fixed " >
            <thead>
                <tr>
                    <th class="wms-checkbox-col" ><input type="checkbox" name="wms-check-all"></th>
                    <th class="wms_log_header" scope="col"><a href="<?php echo $sku_link; ?>"><?php _e('SKU', 'wms-woocommerce-stock-log');?></a></th>
                    <th class="wms_log_header" scope="col"><?php _e('Photo', 'wms-woocommerce-stock-log');?></th>
                    <th class="wms_log_header" scope="col"><?php _e('Name', 'wms-woocommerce-stock-log');?></th>
                    <th class="wms_log_header" scope="col"><?php _e('Variation', 'wms-woocommerce-stock-log');?></th>
                    <th class="wms_log_header" scope="col"><a href="<?php echo $stock_link; ?>"><?php _e('Stock', 'wms-woocommerce-stock-log');?></a></th>
                    <th class="wms_log_header" scope="col"> </th>
                </tr>
            </thead>