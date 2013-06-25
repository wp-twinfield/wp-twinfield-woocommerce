

var Woocommerce_Twinfield_Sync = {
	config: {
		dom: {}
	}

	, ready: function() {
		Woocommerce_Twinfield_Sync.config.dom.syncButton = jQuery('.WoocommerceTwinfieldSyncButton');
		Woocommerce_Twinfield_Sync.config.dom.messageHolder = jQuery('.WoocommerceTwinfieldSyncMessageHolder');
		Woocommerce_Twinfield_Sync.config.dom.postID = jQuery('#post_ID');
		Woocommerce_Twinfield_Sync.config.dom.customerID = jQuery('.WoocommerceTwinfieldSyncCustomerIDInput');
		Woocommerce_Twinfield_Sync.config.dom.spinnerHolder = jQuery('.WoocommerceTwinfieldSyncSpinnerHolder');
		
		Woocommerce_Twinfield_Sync.binds();
	}

	, binds: function() {
		Woocommerce_Twinfield_Sync.config.dom.syncButton.click(Woocommerce_Twinfield_Sync.syncOrder);
	}
	
	, spinnerImage: function() {
		var img = new Image();
		img.src = Woocommerce_Twinfield_Vars.spinner;
		
		return img;
	}

	, syncOrder: function(e) {
		e.preventDefault();
		
		Woocommerce_Twinfield_Sync.clearMessagesHolder();
		Woocommerce_Twinfield_Sync.config.dom.spinnerHolder.html(Woocommerce_Twinfield_Sync.spinnerImage());
		
		jQuery.ajax({
			type: 'POST'
			, url: ajaxurl
			, dataType: 'json'
			, data: {
				action: 'woocommerce_twinfield_sync',
				post_id: Woocommerce_Twinfield_Sync.config.dom.postID.val(),
				customer_id: Woocommerce_Twinfield_Sync.config.dom.customerID.val()
			}
			, success: Woocommerce_Twinfield_Sync.syncOrderSuccess
			, error: Woocommerce_Twinfield_Sync.syncOrderFailed
		});
	}

	, syncOrderSuccess: function(data) {
		
		Woocommerce_Twinfield_Sync.config.dom.spinnerHolder.empty();
		
		if (true === data.ret) {
			Woocommerce_Twinfield_Sync.setSuccessMessage(data.msg);
		} else {
			Woocommerce_Twinfield_Sync.setErrorMessages(data.msgs);
		}
	}

	, syncOrderFailed: function(one, two, three) {
		console.log(one);
		console.log(two);
		console.log(three);
	}
	
	, clearMessagesHolder: function() {
		Woocommerce_Twinfield_Sync.config.dom.messageHolder.empty();
	}

	, setSuccessMessage: function(successMessage) {
		var successMessageDom = jQuery('<div class="updated"></div>');
		successMessageDom.html(jQuery('<p></p>').html(successMessage));
		
		Woocommerce_Twinfield_Sync.config.dom.messageHolder.append(successMessageDom);
	}

	, setErrorMessages: function(errorMessages) {
		var errorMessagesDom = jQuery('<div></div>');

		jQuery.each(errorMessages, function(i, data) {
			var errorMessageDom = jQuery('<div class="error"></div>');
			errorMessageDom.html(jQuery('<p></p>').html(data));

			errorMessagesDom.append(errorMessageDom);
		});
		
		Woocommerce_Twinfield_Sync.config.dom.messageHolder.append(errorMessagesDom);
	}
};

jQuery(Woocommerce_Twinfield_Sync.ready);