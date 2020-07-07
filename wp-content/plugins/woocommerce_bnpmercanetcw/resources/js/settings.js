
jQuery(document).ready(function() {
	
	jQuery('.bnpmercanetcw-transaction-table .bnpmercanetcw-more-details-button').each(function() {
		jQuery(this).click(function() {
			
			// hide all open 
			jQuery('.bnpmercanetcw-transaction-table').find('.active').removeClass('active');
			
			// Get transaction ID
			var mainRow = jQuery(this).parents('.bnpmercanetcw-main-row');
			var transactionId = mainRow.attr('id').replace('bnpmercanetcw-main_row_', '');
			
			var selector = '.bnpmercanetcw-transaction-table #bnpmercanetcw_details_row_' + transactionId;
			jQuery(selector).addClass('active');
			jQuery(mainRow).addClass('active');
		})
	});
	
	jQuery('.bnpmercanetcw-transaction-table .bnpmercanetcw-less-details-button').each(function() {
		jQuery(this).click(function() {
			// hide all open 
			jQuery('.bnpmercanetcw-transaction-table').find('.active').removeClass('active');
		})
	});
	
});

(function($) {
	
	var BNPMercanetCwLineItemGrid = {
		decimalPlaces: 2,
		currencyCode: 'EUR',
		
		init: function() {
			this.decimalPlaces = parseFloat($("#bnpmercanetcw-decimal-places").val());
			this.currencyCode = $("#bnpmercanetcw-currency-code").val();
			this.attachListeners();
		},
		
		attachListeners: function() {
			$(".bnpmercanetcw-line-item-grid input.line-item-quantity").each(function() {
				BNPMercanetCwLineItemGrid.attachListener(this);
			});
			$(".bnpmercanetcw-line-item-grid input.line-item-price-excluding").each(function() {
				BNPMercanetCwLineItemGrid.attachListener(this);
			});
			$(".bnpmercanetcw-line-item-grid input.line-item-price-including").each(function() {
				BNPMercanetCwLineItemGrid.attachListener(this);
			});
		},
		
		attachListener: function(element) {
			$(element).change(function() {
				BNPMercanetCwLineItemGrid.recalculate(this);
			});
			
			$(element).attr('data-before-change', $(element).val());
			$(element).attr('data-original', $(element).val());
		},
		
		recalculate: function(eventElement) {
			var lineItemIndex = $(eventElement).parents('tr').attr('data-line-item-index');
			var row = $('.bnpmercanetcw-line-item-grid tr[data-line-item-index="' + lineItemIndex + '"]');
			var taxRate = parseFloat(row.find('input.tax-rate').val());
				
			var quantity = parseFloat(row.find('input.line-item-quantity').val());
			var quantityBefore = parseFloat(row.find('input.line-item-quantity').attr('data-before-change'));
			if(isNaN(quantity)) {
				quantity = quantityBefore;
			}
			
			var priceExcluding = parseFloat(row.find('input.line-item-price-excluding').val());
			var priceExcludingBefore = parseFloat(row.find('input.line-item-price-excluding').attr('data-before-change'));
			if(isNaN(priceExcluding)) {
				priceExcluding = priceExcludingBefore;
			}
			
			var priceIncluding = parseFloat(row.find('input.line-item-price-including').val());
			var priceIncludingBefore = parseFloat(row.find('input.line-item-price-including').attr('data-before-change'));
			if(isNaN(priceIncluding)) {
				priceIncluding = priceIncludingBefore;
			}
			
			if ($(eventElement).hasClass('line-item-quantity')) {
				if (quantityBefore == 0) {
					quantityBefore = quantity;
					priceExcludingBefore = parseFloat(row.find('input.line-item-price-excluding').attr('data-original'));
				}
				var pricePerItemExcluding = parseFloat(priceExcludingBefore / quantityBefore);
				priceExcluding = quantity * pricePerItemExcluding;
				priceIncluding = (taxRate / 100 + 1) * priceExcluding;
			}
			else if ($(eventElement).hasClass('line-item-price-excluding')) {
				priceIncluding = (taxRate / 100 + 1) * priceExcluding;
			}
			else if ($(eventElement).hasClass('line-item-price-including')) {
				priceExcluding = priceIncluding / (taxRate / 100 + 1);
			}
			
			quantity = quantity.toFixed(2);
			priceExcluding = priceExcluding.toFixed(this.decimalPlaces);
			priceIncluding = priceIncluding.toFixed(this.decimalPlaces);
			
				
			row.find('input.line-item-quantity').val(quantity);
			row.find('input.line-item-price-excluding').val(priceExcluding);
			row.find('input.line-item-price-including').val(priceIncluding);
			
			row.find('input.line-item-quantity').attr('data-before-change', quantity);
			row.find('input.line-item-price-excluding').attr('data-before-change', priceExcluding);
			row.find('input.line-item-price-including').attr('data-before-change', priceIncluding);
			
			// Update total
			var totalAmount = 0;
			$(".bnpmercanetcw-line-item-grid input.line-item-price-including").each(function() {
				totalAmount += parseFloat($(this).val());
			});
			
			$('#line-item-total').html(totalAmount.toFixed(this.decimalPlaces));
			$('#line-item-total').append(" " + this.currencyCode)
		},
		
	};
	
	$(document).ready(function() {
		BNPMercanetCwLineItemGrid.init();
	});

})(jQuery);