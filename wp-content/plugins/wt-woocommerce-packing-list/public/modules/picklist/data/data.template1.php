<style type="text/css">
.clearfix::after {
    display: block;
    clear: both;
  content: "";}
.wfte_invoice-main{ color:#73879C; font-family:"Helvetica Neue", Roboto, Arial, "Droid Sans", sans-serif; font-size:12px; font-weight:400; line-height:18px; box-sizing:border-box; width:100%; margin:0px;}
.wfte_invoice-main *{ box-sizing:border-box;}
.wfte_invoice-header{ background:#fff; color:#000; padding:10px 20px; width:100%; }
.wfte_invoice-header_top{ padding:15px 0px; padding-bottom:10px; width:100%;}
.wfte_left_box{width:40%;}
.wfte_company_logo{ float:left; }
.wfte_company_logo_img{ width:150px; max-width:100%; }
.wfte_company_name{ font-size:18px; font-weight:bold; }
.wfte_company_logo_extra_details{ font-size:12px; }
.wfte_addrss_field_main{ width:100%; font-size:12px; padding-top:5px; }
.wfte_from_address{ width:30%; }
.wfte_addrss_fields{ width:50%; padding:0px .5%; line-height:16px;}
.wfte_address-field-header{ font-weight:bold; }
.wfte_invoice-body{background:#ffffff; color:#23272c; padding:10px 20px; width:100%;}
.wfte_product_table{ width:100%; border-collapse:collapse;}
.wfte_product_table_head_bg{background-color:#212529;}
.wfte_table_head_color{color:#ffffff;}
.wfte_product_table_head{}
.wfte_product_table_head th{ border:solid 1px #000; height:36px; padding:0px 5px; color:inherit; font-size:.75rem; text-align:center; line-height:10px;}
.wfte_product_table_body td{padding:8px 5px; text-align:center; font-size:12px; line-height:10px; border:solid 1px #dadada; }
.wfte_product_table_category_row{ background:#f6f6f6; color:#333; text-align:left; font-weight:bold; }
.wfte_product_table_category_row td{text-align:left;}
.wfte_product_table_order_row{ background:#efefef; color:#333; text-align:left; font-weight:bold; }
.wfte_product_table_order_row td{text-align:left; padding:13px 5px; font-size:14px;}
.wfte_footer{width:100%; height:auto; padding:5px 0px; margin-top:10px; font-size:10px;}
.float_left{ float:left; }
.float_right{ float:right; }
.wfte_picklist_data_main{ width:100%; font-size:12px; padding-top:5px; }
</style>
<div class="wfte_rtl_main wfte_invoice-main">
  <div class="wfte_invoice-header clearfix">
      <div class="wfte_invoice-header_top clearfix">
        <div class="float_left wfte_left_box">
          <div class="wfte_company_logo">
            <div class="wfte_company_logo_img_box">
              <img src="[wfte_company_logo_url]" class="wfte_company_logo_img">
            </div>
            <div class="wfte_company_name wfte_hidden">[wfte_company_name]</div>
            <div class="wfte_company_logo_extra_details">__[]__</div>
          </div>          
        </div>
        <div class="wfte_from_address float_right wfte_text_left">
          <div class="wfte_address-field-header wfte_from_address_label">__[From Address:]__</div>
          <div class="wfte_from_address_val">[wfte_from_address]</div>
        </div>       
      </div>
      <div class="wfte_data_main clearfix">
        <div class="wfte_order_count">[wfte_order_count] __[orders]__</div>
        <div class="wfte_order_number_list">__[Orders]__: [wfte_order_number_list]</div>
        <div class="wfte_printed_on" data-printed_on-format="Y/d/M h:i:s A">__[Printed on]__: [wfte_printed_on]</div>
      </div>
  </div>
  <div class="wfte_invoice-body clearfix">
    [wfte_product_table_start]   
    <table class="wfte_product_table">
      <thead class="wfte_product_table_head wfte_table_head_color wfte_product_table_head_bg">
        <tr>
          <th class="wfte_product_table_head_sku" col-type="sku">__[SKU]__</th>
          <th class="wfte_product_table_head_product" col-type="product">__[Product]__</th>
          <th class="wfte_product_table_head_quantity" col-type="quantity">__[Quantity]__</th>
          <th class="wfte_product_table_head_total_weight" col-type="total_weight">__[Total Weight]__</th>    
        </tr>
      </thead>
      <tbody class="wfte_product_table_body wfte_table_body_color">
      </tbody>
    </table>
    [wfte_product_table_end]
    <div class="wfte_footer clearfix wfte_text_left">
      [wfte_footer]
    </div>
  </div>
</div>