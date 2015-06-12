// Create and edit modal show/hide listeners as a global function
function attachModalListeners() {
	
	// Show listener for create and edit modals
	$('#cb-create-modal, #cb-modal-edit').on('shown.bs.modal', function(event) {
		var $this = $(this);
		
		// Apply input masks
		$this.find('input[name="phone"]').mask('(999) 999-9999? x99999');
		$this.find('input[name="zip"]').mask('99999?-999');
	});
	
	// Hide listener for create and edit modals
	$('#cb-create-modal, #cb-modal-edit').on('hide.bs.modal', function(event) {
		var $this = $(this);
		
		// Completely reset the form and remove errors
		$this.find('input:not(input[type="submit"])').val('');
		$this.find('.error').html('');
		$this.find('.form-group').removeClass('has-error');
	});
	
	// New contact button listener
	$('#cb-create-btn').on('click', function(event){
		event.preventDefault();
		$('#cb-create-modal').modal('show');
		return false;
	});
	
	// Import button listener
	$('#cb-import-btn').on('click', function(event){
		event.preventDefault();
		$('#cb-import-modal').modal('show');
	});
}

(function($){
	$(document).ready(function(){
		
		$(":file").filestyle();
		
		// Set up datatable
		$('#cb-table').DataTable({
			"order": [],
		    "columnDefs": [ {
		      "targets"  : 'no-sort',
		      "orderable": false,
		    }]
		});
		
		$('.cb-action-delete').on('click', function(event) {
			event.preventDefault();
			var $this = $(this);
			var data = {};
			data.ids = [];
			$('.cb-contact-row.selected-row').each(function(k, v){
				data.ids.push($(this).attr('data-id'));
			});
			if (data.ids.length === 0) {
				alert('No rows selected');
			} else {
				$this.request($this.attr('data-request'), {
					confirm: 'Are you sure?',
					data: data
				});	
			}
			return false;
		});
		
		// Attach listener for the export
		$('.cb-action-export').on('click', function(event){
			event.preventDefault();
			$('#cb-list-form').submit();
			return false;
			
		});
		
		// Select all listener
		/*
		$('#cb-select-all').on('change', function(){
			if (this.checked){
				$('.cb-selected').prop('checked', true).closest('tr').addClass('selected');
			} else {
				$('.cb-selected').prop('checked', false).closest('tr').removeClass('selected');
			}
		});
		$('.cb-selected').on('change', function(){
			if (this.checked) {
				$(this).closest('tr').addClass('selected');	
			} else {
				$(this).closest('tr').removeClass('selected');
			}	
		});*/

		
		attachModalListeners();
	});
})(jQuery);

