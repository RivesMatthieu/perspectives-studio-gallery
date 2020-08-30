"use strict";
jQuery(document).ready(function(){
	// Fallback for case if library was not loaded
	if(!jQuery.fn.jqGrid) {
		return;
	}
	var tblId = 'wpfTableTbl',
		tableObj = jQuery('#'+ tblId);
	tableObj.jqGrid({
		url: wpfTblDataUrl,
		datatype: 'json',
		autowidth: true,
		shrinkToFit: true,
		colNames:[toeLangWpf('ID'), toeLangWpf('Title'), toeLangWpf('Shortcode')],
		colModel:[
			{name: 'id', index: 'id', searchoptions: {sopt: ['eq']}, width: '50', align: 'center'},
			{name: 'title', index: 'title', searchoptions: {sopt: ['eq']}, align: 'center'},
			{name: 'shortcode', index: 'shortcode', searchoptions: {sopt: ['eq']}, align: 'center'}
		],
		postData: {
			search: {
				text_like: jQuery('#'+ tblId+ 'SearchTxt').val()
			}
		},
		rowNum: 10,
		rowList: [10, 20, 30, 1000],
		pager: '#'+ tblId+ 'Nav',
		sortname: 'id',
		viewrecords: true,
		sortorder: 'desc',
		jsonReader: { repeatitems : false, id: '0' },
		caption: toeLangWpf('Current PopUp'),
		height: '100%',
		emptyrecords: toeLangWpf('You have no Filters for now.'),
		multiselect: true,
		onSelectRow: function(rowid, e) {
			var tblId = jQuery(this).attr('id'),
				selectedRowIds = jQuery('#'+ tblId).jqGrid ('getGridParam', 'selarrrow'),
				totalRows = jQuery('#'+ tblId).getGridParam('reccount'),
				totalRowsSelected = selectedRowIds.length;
			if(totalRowsSelected) {
				jQuery('#wpfTableRemoveGroupBtn').removeAttr('disabled');
				if(totalRowsSelected == totalRows) {
					jQuery('#cb_'+ tblId).prop('indeterminate', false);
					jQuery('#cb_'+ tblId).attr('checked', 'checked');
				} else {
					jQuery('#cb_'+ tblId).prop('indeterminate', true);
				}
			} else {
				jQuery('#wpfTableRemoveGroupBtn').attr('disabled', 'disabled');
				jQuery('#cb_'+ tblId).prop('indeterminate', false);
				jQuery('#cb_'+ tblId).removeAttr('checked');
			}
			wpfCheckUpdate(jQuery(this).find('tr:eq('+rowid+')').find('input[type=checkbox].cbox'));
			wpfCheckUpdate('#cb_'+ tblId);
		}
	,	beforeRequest: function() {
			jQuery('#wpfTableTblNav_center .ui-pg-table').addClass('woobewoo-hidden');
		}
	,	gridComplete: function(a, b, c) {
			var tblId = jQuery(this).attr('id');
			jQuery('#wpfTableRemoveGroupBtn').attr('disabled', 'disabled');
			jQuery('#cb_'+ tblId).prop('indeterminate', false);
			jQuery('#cb_'+ tblId).removeAttr('checked');
			// Custom checkbox manipulation
			wpfInitCustomCheckRadio('#'+ jQuery(this).attr('id') );
			wpfCheckUpdate('#cb_'+ jQuery(this).attr('id'));
			jQuery('#wpfTableTblNav_center .ui-pg-table').removeClass('woobewoo-hidden');
		}
	,	loadComplete: function() {
			var tblId = jQuery(this).attr('id');
			if (this.p.reccount === 0) {
				jQuery(this).hide();
				jQuery('#'+ tblId+ 'EmptyMsg').removeClass('woobewoo-hidden');
			} else {
				jQuery(this).show();
				jQuery('#'+ tblId+ 'EmptyMsg').addClass('woobewoo-hidden');
			}
		}
	});
	jQuery(window).on('load resize', tableObj, function(event) {
		tableObj.jqGrid('setGridWidth', jQuery('#containerWrapper').width());
	});
	jQuery('#'+ tblId+ 'NavShell').append( jQuery('#'+ tblId+ 'Nav') );
	jQuery('#'+ tblId+ 'Nav').find('.ui-pg-selbox').insertAfter( jQuery('#'+ tblId+ 'Nav').find('.ui-paging-info') );
	jQuery('#'+ tblId+ 'Nav').find('.ui-pg-table td:first').remove();
	// Make navigation tabs to be with our additional buttons - in one row
	jQuery('#'+ tblId+ 'Nav_center').prepend( jQuery('#'+ tblId+ 'NavBtnsShell') ).css({
		'width': '80%'
	,	'white-space': 'normal'
	,	'padding-top': '8px'
	});
	jQuery('#'+ tblId+ 'SearchTxt').keyup(function(){
		var searchVal = jQuery.trim( jQuery(this).val() );
		if( true ) {
			wpfGridDoListSearch({
				text_like: searchVal
			}, tblId);
		}
	});

	jQuery('#'+ tblId+ 'EmptyMsg').insertAfter(jQuery('#'+ tblId+ '').parent());
	jQuery('#'+ tblId+ '').jqGrid('navGrid', '#'+ tblId+ 'Nav', {edit: false, add: false, del: false});
	jQuery('#cb_'+ tblId+ '').change(function(){
		jQuery(this).attr('checked')
			? jQuery('#wpfTableRemoveGroupBtn').removeAttr('disabled')
			: jQuery('#wpfTableRemoveGroupBtn').attr('disabled', 'disabled');
	});

	jQuery('#wpfTableRemoveGroupBtn').click(function(){
		var selectedRowIds = jQuery('#wpfTableTbl').jqGrid ('getGridParam', 'selarrrow'),
			listIds = [];
		for(var i in selectedRowIds) {
			var rowData = jQuery('#wpfTableTbl').jqGrid('getRowData', selectedRowIds[ i ]);
			listIds.push( rowData.id );
		}
		var popupLabel = '';
		if(listIds.length == 1) {	// In table label cell there can be some additional links
			var labelCellData = wpfGetGridColDataById(listIds[0], 'title', 'wpfTableTbl');
			popupLabel = jQuery(labelCellData).text();
		}
		var confirmMsg = listIds.length > 1
			? toeLangWpf('Are you sur want to remove '+ listIds.length+ ' Filters?')
			: toeLangWpf('Are you sure want to remove "'+ popupLabel+ '" Filter?');
		if(confirm(confirmMsg)) {
			jQuery.sendFormWpf({
				btn: this,
				data: {mod: 'woofilters', action: 'removeGroup', listIds: listIds},
				onSuccess: function(res) {
					if(!res.error) {
						jQuery('#wpfTableTbl').trigger( 'reloadGrid' );
					}
				}
			});
		}
		return false;
	});
	wpfInitCustomCheckRadio('#'+ tblId+ '_cb');
});
