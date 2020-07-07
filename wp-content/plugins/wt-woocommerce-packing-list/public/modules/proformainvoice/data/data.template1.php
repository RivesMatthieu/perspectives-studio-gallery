<div class="wfte_rtl_main wfte_invoice-main">
  <div class="wfte_invoice-header clearfix">
      <div class="wfte_doc_title wfte_text_right clearfix">__[Proforma Invoice]__</div>
      <div class="wfte_invoice-header_top clearfix">
        <div class="wfte_company_logo float_left">
          <div class="wfte_company_logo_img_box">
            <img src="[wfte_company_logo_url]" class="wfte_company_logo_img">
          </div>
          <div class="wfte_company_name wfte_hidden">[wfte_company_name]</div>
          <div class="wfte_company_logo_extra_details">__[]__</div>
        </div>
        <div class="wfte_invoice_data float_right">
          <div class="wfte_proforma_invoice_number">
            <span class="wfte_proforma_invoice_number_label">__[Proforma Invoice no:]__</span> [wfte_proforma_invoice_number]
          </div>
          <div class="wfte_proforma_invoice_date" data-proforma_invoice_date-format="m/d/Y">
            <span class="wfte_proforma_invoice_date_label">__[Proforma Invoice date:]__</span> [wfte_proforma_invoice_date]
          </div>
          <div class="wfte_order_number">
            <span class="wfte_order_number_label">__[Order No:]__</span> [wfte_order_number]
          </div>
          <div class="wfte_order_date" data-order_date-format="m/d/Y">
            <span class="wfte_order_date_label">__[Date:]__</span> [wfte_order_date]
          </div>
        </div>
      </div>
      <div class="wfte_addrss_field_main clearfix">
         <div class="wfte_addrss_fields wfte_from_address float_left wfte_text_left">
           <div class="wfte_address-field-header wfte_from_address_label">__[From Address:]__</div>
           <div class="wfte_from_address_val">[wfte_from_address]</div>
         </div>
         <div class="wfte_addrss_fields wfte_billing_address float_left wfte_text_left">
           <div class="wfte_address-field-header wfte_billing_address_label">__[Billing Address:]__</div>
           [wfte_billing_address]
         </div>
         <div class="wfte_addrss_fields wfte_shipping_address float_left wfte_text_left">
           <div class="wfte_address-field-header wfte_shipping_address_label">__[Shipping Address:]__</div>
           [wfte_shipping_address]
         </div>
         <div class="wfte_invoice_data wfte_addrss_fields wfte_text_left float_left">
          <div class="wfte_vat_number">
            <span class="wfte_vat_number_label">__[VAT:]__</span>
            <span>[wfte_vat_number]</span>
          </div>
          <div class="wfte_ssn_number">
            <span class="wfte_ssn_number_label">__[SSN:]__</span>
            <span>[wfte_ssn_number]</span>
          </div>
          <div class="wfte_email">
            <span class="wfte_email_label">__[Email:]__</span>
            <span>[wfte_email]</span>
          </div>
          <div class="wfte_tel">
            <span class="wfte_tel_label">__[Tel:]__</span>
            <span>[wfte_tel]</span>
          </div>
          <div class="wfte_shipping_method">
            <span class="wfte_shipping_method_label">__[Shipping Method:]__</span>
            <span>[wfte_shipping_method]</span>
          </div>
          <div class="wfte_tracking_number">
            <span class="wfte_tracking_number_label">__[Tracking number:]__</span>
            <span>[wfte_tracking_number]</span>
          </div>
          <div class="wfte_order_item_meta">[wfte_order_item_meta]</div>
          [wfte_extra_fields]
         </div>
      </div>
  </div>
  <div class="wfte_invoice-body clearfix">
  <div class="wfte_received_seal wfte_hidden"><span class="wfte_received_seal_text">__[RECEIVED]__</span>[wfte_received_seal_extra_text]</div>
  [wfte_product_table_start]
    <table class="wfte_product_table">
        <thead class="wfte_product_table_head wfte_table_head_color wfte_product_table_head_bg">
          <tr>
            <th class="wfte_product_table_head_image" col-type="image">__[Image]__</th>
            <th class="wfte_product_table_head_sku" col-type="sku">__[SKU]__</th>
            <th class="wfte_product_table_head_product" col-type="product">__[Product]__</th>
            <th class="wfte_product_table_head_quantity" col-type="quantity">__[Quantity]__</th>
            <th class="wfte_product_table_head_price" col-type="price">__[Price]__</th>
            <th class="wfte_product_table_head_total_price" col-type="total_price">__[Total Price]__</th>
            <th class="wfte_product_table_head_tax_items" col-type="tax_items">[wfte_product_table_tax_item_column_label]</th>
            <th class="wfte_product_table_head_tax" col-type="-tax">__[Total Tax]__</th>     
          </tr>
        </thead>
        <tbody class="wfte_product_table_body wfte_table_body_color">
        </tbody>
    </table>   
  [wfte_product_table_end]   
  <table class="wfte_payment_summary_table wfte_product_table">
    <tbody class="wfte_payment_summary_table_body wfte_table_body_color">
      <tr class="wfte_payment_summary_table_row wfte_product_table_subtotal">
        <td class="wfte_left_column"></td>
        <td class="wfte_product_table_subtotal_label wfte_text_right">__[Subtotal]__</td>
        <td class="wfte_right_column">[wfte_product_table_subtotal]</td>
      </tr>
      <tr class="wfte_payment_summary_table_row wfte_product_table_shipping">
        <td class="wfte_left_column"></td>
        <td class="wfte_product_table_shipping_label wfte_text_right">__[Shipping]__</td>
        <td class="wfte_right_column">[wfte_product_table_shipping]</td>
      </tr>
      <tr class="wfte_payment_summary_table_row wfte_product_table_cart_discount">
        <td class="wfte_left_column"></td>
        <td class="wfte_product_table_cart_discount_label wfte_text_right">__[Cart Discount]__</td>
        <td class="wfte_right_column">[wfte_product_table_cart_discount]</td>
      </tr>
      <tr class="wfte_payment_summary_table_row wfte_product_table_order_discount">
        <td class="wfte_left_column"></td>
        <td class="wfte_product_table_order_discount_label wfte_text_right">__[Order Discount]__</td>
        <td class="wfte_right_column">[wfte_product_table_order_discount]</td>
      </tr>
      <tr data-row-type="wfte_tax_items" class="wfte_payment_summary_table_row wfte_product_table_tax_item">
        <td class="wfte_left_column"></td>
        <td class="wfte_product_table_tax_item_label wfte_text_right">[wfte_product_table_tax_item_label]</td>
        <td class="wfte_right_column">[wfte_product_table_tax_item]</td>
      </tr>
      <tr class="wfte_payment_summary_table_row wfte_product_table_total_tax">
        <td class="wfte_left_column"></td>
        <td class="wfte_product_table_total_tax_label wfte_text_right">__[Total Tax]__</td>
        <td class="wfte_right_column">[wfte_product_table_total_tax]</td>
      </tr>
      <tr class="wfte_payment_summary_table_row wfte_product_table_fee">
        <td class="wfte_left_column"></td>
        <td class="wfte_product_table_fee_label wfte_text_right">__[Fee]__</td>
        <td class="wfte_right_column">[wfte_product_table_fee]</td>
      </tr>
      <tr class="wfte_payment_summary_table_row wfte_product_table_coupon">
        <td class="wfte_left_column"></td>
        <td class="wfte_product_table_coupon_label wfte_text_right">__[Coupon Used]__</td>
        <td class="wfte_right_column">[wfte_product_table_coupon]</td>
      </tr>
      <tr class="wfte_payment_summary_table_row wfte_product_table_payment_method">
        <td class="wfte_left_column"></td>
        <td class="wfte_product_table_payment_method_label wfte_text_right">__[Payment Method]__</td>
        <td class="wfte_right_column">[wfte_product_table_payment_method]</td>
      </tr>
      <tr class="wfte_payment_summary_table_row wfte_product_table_payment_total wfte_table_head_color">
        <td class="wfte_left_column"></td>
        <td class="wfte_product_table_payment_total_label wfte_text_right wfte_product_table_head_bg">__[Total]__</td>
        <td class="wfte_product_table_payment_total_val wfte_right_column wfte_product_table_head_bg">[wfte_product_table_payment_total]</td>
      </tr>
    </tbody>
  </table>
    <div class="clearfix"></div>
    <div class="wfte_special_notes wfte_text_left clearfix">
      [wfte_special_notes]
    </div>
    <div class="wfte_transport_terms wfte_text_left clearfix">
      [wfte_transport_terms]
    </div>
    <div class="wfte_sale_terms wfte_text_left clearfix">
      [wfte_sale_terms]
    </div>
    <div class="wfte_footer wfte_text_left clearfix">
      [wfte_footer]
    </div>
  </div>
</div>
<style type="text/css">
.clearfix::after {
    display: block;
    clear: both;
  content: "";}
.wfte_invoice-main{ color:#73879C; font-family:"Helvetica Neue", Roboto, Arial, "Droid Sans", sans-serif; font-size:12px; font-weight:400; line-height:18px; box-sizing:border-box; width:100%; margin:0px; padding:0px;}
.wfte_invoice-main *{ box-sizing:border-box;}
.wfte_invoice-header{ background:#445aa8; color:#ffffff; padding:15px 20px; width:100%; }
.wfte_doc_title{ padding:15px 0px; padding-top:5px; width:100%; font-size:24px; font-weight:bold; color:#efefef;}
.wfte_invoice-header_top{ padding:15px 0px; padding-bottom:25px; border-bottom:solid 1px rgba(255, 255, 255, 0.2); width:100%; height:auto;}
.wfte_company_logo{ max-width:40%;  }
.wfte_company_logo_img{ width:150px; max-width:100%; }
.wfte_company_name{ font-size:18px; font-weight:bold; }
.wfte_company_logo_extra_details{ font-size:12px; }
.wfte_order_number, .wfte_order_date, .wfte_proforma_invoice_number, .wfte_proforma_invoice_date{ font-size:12px; }
.wfte_proforma_invoice_number{ font-weight:bold; }
.wfte_invoice_data{ padding:0px 15px; max-width:30%;}
.wfte_addrss_field_main{width:100%; font-size:12px; padding-top:25px; }
.wfte_addrss_fields{ width:24%; padding:0px .5%;}
.wfte_address-field-header{ font-weight:bold; }
.wfte_invoice-body{background:#ffffff; color:#23272c; padding:15px 20px; width:100%;}
.wfte_product_table{ width:100%; border-collapse:collapse; margin:0px; }
.wfte_product_table .wfte_right_column{ width:15%; }
.wfte_payment_summary_table{ margin-bottom:15px; }
.wfte_payment_summary_table .wfte_left_column{ width:60%; }
.wfte_product_table_head_bg{background-color:#212529;}
.wfte_product_table_head{}
.wfte_product_table_head th{ height:36px; padding:0px 5px; font-size:.75rem; text-align:center; line-height:10px;}
.wfte_product_table_body td, .wfte_payment_summary_table_body td{ text-align:center; font-size:12px; line-height:10px;}
.wfte_product_table_body td{padding:2px 5px;}
.wfte_payment_summary_table_body td{padding:8px 5px;}
.wfte_table_head_color{ color:#ffffff; }
.wfte_product_table_payment_total{ font-size:14px; font-weight:bold; height:36px; padding:0px;line-height:10px;}
td.wfte_product_table_payment_total_label{ text-align:right; height:36px; padding:0px 5px; line-height:10px;}
.wfte_product_table_payment_total_val{}
.wfte_footer{width:100%; height:auto; padding:15px 0px; margin-top:15px;}
.wfte_special_notes{width:100%; height:auto; padding:5px 0px; margin-top:5px;}
.wfte_sale_terms{width:100%; height:auto; padding:5px 0px; margin-top:5px;}
.wfte_transport_terms{width:100%; height:auto; padding:5px 0px; margin-top:5px;}

.float_left{ float:left; }
.float_right{ float:right; }
</style>