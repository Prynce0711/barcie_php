<?php
// Payment Verification Section for Dashboard
// This displays bookings where payment_status = 'pending' or proof_of_payment is present
?>

<div class="row mb-4">
	<div class="col-12">
		<div class="card">
			<div class="card-header text-white" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
				<h6 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Verifications (Pending)</h6>
				<small class="opacity-75">Review payment proofs and verify or reject payments.</small>
			</div>
			<div class="card-body">
				<!-- Action Buttons -->
				<div class="d-flex justify-content-end mb-2 gap-2">
					<button type="button" class="btn btn-sm btn-outline-primary" onclick="downloadPaymentsExcel()">
						<i class="fas fa-file-excel me-1"></i>Export to Excel
					</button>
					<button type="button" class="btn btn-sm btn-primary text-white" onclick="downloadPaymentsPDF()">
						<i class="fas fa-file-alt me-1"></i>Export to Text
					</button>
				</div>
				
				<!-- Filters Section -->
				<div class="card mb-3 border-0 bg-light">
					<div class="card-body py-3">
						<div class="row g-3 align-items-end">
							<!-- Date Filter -->
							<div class="col-md-4">
								<label for="paymentDateFilter" class="form-label fw-semibold text-muted small mb-2">
									<i class="fas fa-calendar-alt me-1"></i>Date
								</label>
								<input type="date" id="paymentDateFilter" class="form-control" onchange="filterPayments()">
							</div>
							
							<!-- Quick Date Actions -->
							<div class="col-md-4">
								<label class="form-label fw-semibold text-muted small mb-2">Quick Filter</label>
								<div class="d-flex gap-2">
								<button type="button" class="btn btn-sm btn-primary text-white" onclick="setPaymentDateToday()">
										<i class="fas fa-calendar-day me-1"></i>Today
									</button>
									<button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearPaymentDate()">
										<i class="fas fa-calendar me-1"></i>All
									</button>
								</div>
							</div>
							
							<!-- Reset Button -->
							<div class="col-md-4 text-end">
								<button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('paymentDateFilter').value='';filterPayments();">
									<i class="fas fa-redo me-1"></i>Reset Filters
								</button>
							</div>
						</div>
					</div>
				</div>
				
				<div class="table-responsive">
					<table class="table table-hover align-middle" id="paymentsTable">
						<thead class="table-light">
							<tr>
								<th>Receipt #</th>
								<th>Guest</th>
								<th>Amount / Details</th>
								<th>View Details</th>
								<th>Submitted</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
<?php
$stmt = $conn->prepare("SELECT b.id, b.receipt_no, b.details, b.proof_of_payment, b.proof_of_id, b.guest_age, b.amount, b.created_at, b.payment_date, b.checkin, i.name as room_name FROM bookings b LEFT JOIN items i ON b.room_id = i.id WHERE b.payment_status = 'pending' ORDER BY COALESCE(b.payment_date, b.created_at) DESC");
if ($stmt) {
	$stmt->execute();
	$res = $stmt->get_result();
	if ($res && $res->num_rows > 0) {
		while ($row = $res->fetch_assoc()) {
			$id = $row['id'];
			$receipt = $row['receipt_no'] ?: '—';
			$details = $row['details'] ?: '';
			$proof = $row['proof_of_payment'] ?: '';
			$created = $row['created_at'];
			$payment_date = $row['payment_date'] ?: $created;
			$checkin = $row['checkin'] ?: '';
			$room = $row['room_name'] ?: 'Unassigned';

			// Try to extract guest and amount info from details
			$guest = 'Guest';
			if (preg_match('/Guest:\s*([^|]+)/', $details, $m)) $guest = trim($m[1]);
			$amount = '';
			if (preg_match('/Amount:\s*([^|]+)/', $details, $m)) $amount = trim($m[1]);
			
			// Extract date from created_at for filtering (format: YYYY-MM-DD)
			$booking_date = date('Y-m-d', strtotime($created));

			echo '<tr id="payment-row-' . $id . '" data-date="' . htmlspecialchars($booking_date) . '">';
			echo '<td><strong>' . htmlspecialchars($receipt) . '</strong></td>';
			echo '<td>' . htmlspecialchars($guest) . '</td>';
			echo '<td>' . htmlspecialchars($amount ?: $room) . '</td>';
			echo '<td>';
			echo '<button class="btn btn-info btn-sm" onclick="viewPaymentDetails(' . $id . ')" title="View Details"><i class="fas fa-eye me-1"></i>View Details</button>';
			echo '</td>';
			echo '<td>' . htmlspecialchars(date('M j, Y H:i', strtotime($payment_date))) . '</td>';
			echo '<td>';
			echo '<td class="payment-action-buttons">';
			echo '<button class="btn btn-success btn-sm payment-action me-1 mb-1" data-booking-id="' . $id . '" data-action="verify"><i class="fas fa-check me-1"></i>Verify</button> ';
			echo '<button class="btn btn-danger btn-sm payment-action me-1 mb-1" data-booking-id="' . $id . '" data-action="reject"><i class="fas fa-times me-1"></i>Reject</button>';
			echo '</td>';
			echo '</tr>';
		}
	} else {
		echo '<tr><td colspan="6">No pending payment verifications.</td></tr>';
	}
	$stmt->close();
} else {
	echo '<tr><td colspan="6">Failed to load payment verifications.</td></tr>';
}
?>
						</tbody>
					</table>
				</div>
					<!-- Pagination for Payments -->
					<div id="paymentsPagination" class="mt-2"></div>
			</div>
		</div>
	</div>
</div>

<script>
(function(){
	// Notification helper: prefer showToast if available, fallback to alert
	function notify(message, type = 'info') {
		try {
			if (typeof showToast === 'function') return showToast(message, type);
		} catch (e) {}
		try { showToast(message, 'info'); } catch (e) { /* ignore */ }
	}
	
	// Hide payment action buttons for staff
	function hideStaffPaymentActions() {
		const role = (window.currentAdmin && window.currentAdmin.role) || 'staff';
		if (role === 'staff') {
			document.querySelectorAll('.payment-action-buttons').forEach(td => {
				td.innerHTML = '<span class="badge bg-secondary">View Only</span>';
			});
		}
	}
	
	// Run on load
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', hideStaffPaymentActions);
	} else {
		hideStaffPaymentActions();
	}
	
	// Delegated handler for verify/reject buttons
	document.addEventListener('click', function(e) {
		const btn = e.target.closest('.payment-action');
		if (!btn) return;
		const bookingId = btn.dataset.bookingId;
		const action = btn.dataset.action; // verify|reject
		if (!bookingId || !action) return;

		const confirmMsg = action === 'verify' ? 'Verify this payment?' : 'Reject this payment?';
		const confirmed = confirm(confirmMsg);
		if (!confirmed) return;

		btn.disabled = true;

		// show spinner overlay while request runs
		let removeSpinner = function(){};
		try {
			const parentEl = btn.closest('.table-responsive') || document.querySelector('#paymentsTable');
			if (typeof showTableSpinner === 'function') {
				removeSpinner = showTableSpinner(parentEl);
			} else {
				// fallback overlay
				try {
					const parent = parentEl && parentEl.closest && parentEl.closest('.table-responsive') ? parentEl.closest('.table-responsive') : (parentEl || document.body);
					const prevPos = parent.style.position || '';
					const computed = window.getComputedStyle(parent).position;
					if (computed === 'static') parent.style.position = 'relative';
					const overlay = document.createElement('div'); overlay.className = 'table-spinner-overlay'; overlay.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
					parent.appendChild(overlay);
					removeSpinner = function(){ try { if (overlay && overlay.parentNode) overlay.parentNode.removeChild(overlay); } catch(e){}; try { if (computed === 'static') parent.style.position = prevPos || ''; } catch(e){} };
				} catch (e) { /* ignore */ }
			}
		} catch(e) { /* ignore */ }
		
		const body = 'action=admin_update_payment&booking_id=' + encodeURIComponent(bookingId) + '&payment_action=' + encodeURIComponent(action);
		fetch('database/user_auth.php', {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
				'X-Requested-With': 'XMLHttpRequest'
			},
			body: body
		}).then(r => r.json()).then(json => {
			try { removeSpinner(); } catch(e){}
			if (json && json.success) {
				const row = document.getElementById('payment-row-' + bookingId);
				if (row) {
					// Show success message with fade out effect
					const actionsCell = row.querySelector('td:last-child');
					if (actionsCell) {
						actionsCell.innerHTML = action === 'verify' ? '<span class="badge bg-success">Verified ✓</span>' : '<span class="badge bg-danger">Rejected ✗</span>';
					}
					row.classList.add('table-success');
					
					// Fade out and remove row after 2 seconds
					setTimeout(() => {
						row.style.transition = 'opacity 0.5s ease-out';
						row.style.opacity = '0';
						setTimeout(() => {
							row.remove();
							// Check if table is empty
							const tbody = document.querySelector('#paymentsTable tbody');
							if (tbody && tbody.querySelectorAll('tr').length === 0) {
								tbody.innerHTML = '<tr><td colspan="6" class="text-center">No pending payment verifications.</td></tr>';
							}
						}, 500);
					}, 2000);
				}
					notify(json.message || 'Payment updated', 'success');
			} else {
				notify((json && (json.error || json.message)) || 'Failed to update payment', 'error');
				btn.disabled = false;
			}
		}).catch(err => {
			try { removeSpinner(); } catch(e){}
			console.error(err);
			notify('Request failed — check console', 'error');
			btn.disabled = false;
		});
	});	// reuse proof modal logic from bookings_section (basic)
	document.addEventListener('click', function(e){
		const el = e.target.closest('.view-payment-proof');
		if (!el) return;
		e.preventDefault();
		const proof = el.getAttribute('data-proof');
		if (!proof) return;

		const modalId = 'payment-proof-modal-' + Date.now();
		const modalHTML = `
			<div class="modal fade" id="${modalId}" tabindex="-1">
				<div class="modal-dialog modal-dialog-centered modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">Payment Proof</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body text-center p-3">
							<img src="" alt="Proof" style="max-width:100%; height:auto; border-radius:6px; box-shadow:0 6px 20px rgba(0,0,0,0.12);" class="proof-image" />
						</div>
						<div class="modal-footer">
							<a href="#" target="_blank" class="btn btn-link proof-download">Open in new tab</a>
							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
		`;

		document.body.insertAdjacentHTML('beforeend', modalHTML);
		const modalEl = document.getElementById(modalId);
		const img = modalEl.querySelector('.proof-image');
		modalEl.querySelector('.proof-download').href = proof;
		try {
			const bs = new bootstrap.Modal(modalEl);
			bs.show();
			img.src = proof;
			modalEl.addEventListener('hidden.bs.modal', function(){ modalEl.remove(); });
		} catch (err) {
			console.error('Failed to show proof modal', err);
			window.open(proof, '_blank');
		}
	});

	// Client-side pagination for #paymentsTable
	(function(){
		const PER_PAGE_P = 8;
		let pstate = { perPage: PER_PAGE_P, currentPage: 1, totalPages: 1 };

		function pGetAllRows(){
			const dateFilter = document.getElementById('paymentDateFilter')?.value || '';
			return Array.from(document.querySelectorAll('#paymentsTable tbody tr')).filter(r => {
				if (r.id === 'payments-no-results') return false;
				const rdate = r.dataset.date || '';
				if (dateFilter && rdate !== dateFilter) return false;
				return true;
			});
		}

		let paymentsFadeToken = 0;

		// helper: fade out rows (local copy) to keep this module self-contained
		function fadeOutRows(rows, timeout = 300){
			return new Promise(resolve => {
				if (!rows || rows.length === 0) return resolve();
				let remaining = rows.length;
				const finishOne = (r) => {
					try { r.style.display = 'none'; r.setAttribute('data-hidden-by-pagination','true'); } catch(e){}
					if (--remaining <= 0) resolve();
				};

				const onEnd = (e) => {
					const r = e.currentTarget;
					r.removeEventListener('transitionend', onEnd);
					finishOne(r);
				};

				rows.forEach(r => {
					// ensure transition is set
					r.style.transition = r.style.transition || 'opacity 220ms ease-in-out';
					// listen for transition end
					r.addEventListener('transitionend', onEnd);
					// start fade
					requestAnimationFrame(() => { r.style.opacity = 0; });
					// safety timeout in case transitionend doesn't fire
					setTimeout(() => { try { r.removeEventListener('transitionend', onEnd); } catch(e){}; finishOne(r); }, timeout);
				});
			});
		}

		function pRecalc(){
			const rows = pGetAllRows();
			// Apply filter first to get matching rows
			const dateFilter = document.getElementById('paymentDateFilter')?.value || '';
			const matchingRows = rows.filter(row => {
				const rdate = row.dataset.date || '';
				return !dateFilter || rdate === dateFilter;
			});
			
			const total = matchingRows.length;
			pstate.totalPages = Math.max(1, Math.ceil(total / pstate.perPage));
			if (pstate.currentPage > pstate.totalPages) pstate.currentPage = pstate.totalPages;

			// Hide all rows first
			rows.forEach(r => { 
				r.style.display = 'none'; 
				r.setAttribute('data-hidden-by-pagination','true'); 
			});
			
			// Show only matching rows for current page
			const start = (pstate.currentPage - 1) * pstate.perPage;
			const end = start + pstate.perPage;
			matchingRows.slice(start, end).forEach(r => {
				r.removeAttribute('data-hidden-by-pagination');
				r.style.display = '';
			});
			
			pRender();
		}

		function pRender(){
			const container = document.getElementById('paymentsPagination'); if (!container) return; container.innerHTML = '';
			if (pstate.totalPages <= 1) return;
			const nav = document.createElement('nav'); const ul = document.createElement('ul'); ul.className = 'pagination justify-content-center mb-0';
			const make = (label,page,disabled,active)=>{ const li=document.createElement('li'); li.className='page-item'+(disabled?' disabled':'')+(active?' active':''); const btn=document.createElement('button'); btn.className='page-link'; btn.type='button'; btn.textContent=label; btn.addEventListener('click', e=>{ e.preventDefault(); if (disabled) return; pstate.currentPage = page; pRecalc(); }); li.appendChild(btn); return li; };
			ul.appendChild(make('«', Math.max(1,pstate.currentPage-1), pstate.currentPage===1, false));
			const maxButtons=7; let s=Math.max(1,pstate.currentPage-3); let e=Math.min(pstate.totalPages, s+maxButtons-1); if (e-s < maxButtons-1) s = Math.max(1, e-maxButtons+1);
			if (s>1){ ul.appendChild(make('1',1,false,pstate.currentPage===1)); if (s>2){ const gap=document.createElement('li'); gap.className='page-item disabled'; gap.innerHTML='<span class="page-link">…</span>'; ul.appendChild(gap); } }
			for (let p=s;p<=e;p++){ ul.appendChild(make(String(p), p, false, p===pstate.currentPage)); }
			if (e < pstate.totalPages){ if (e < pstate.totalPages-1){ const gap=document.createElement('li'); gap.className='page-item disabled'; gap.innerHTML='<span class="page-link">…</span>'; ul.appendChild(gap); } ul.appendChild(make(String(pstate.totalPages), pstate.totalPages, false, pstate.currentPage===pstate.totalPages)); }
			ul.appendChild(make('»', Math.min(pstate.totalPages, pstate.currentPage+1), pstate.currentPage===pstate.totalPages, false));
			nav.appendChild(ul); container.appendChild(nav); requestAnimationFrame(()=>{ const p = container.querySelector('.pagination'); if (p) p.classList.add('show'); });
		}

		document.addEventListener('DOMContentLoaded', function(){ pRecalc(); });

		window._paymentsPagination = { setPerPage: function(n){ pstate.perPage = Math.max(1, Number(n)||PER_PAGE_P); pstate.currentPage = 1; pRecalc(); }, goToPage: function(p){ pstate.currentPage = Math.min(Math.max(1, Number(p)||1), pstate.totalPages); pRecalc(); } };
		
		// Helper functions for date filter
		window.filterPayments = function() {
			try {
				pstate.currentPage = 1;
				pRecalc();
			} catch (err) { console.error('filterPayments error', err); }
		};
		
		window.setPaymentDateToday = function() {
			const today = new Date().toISOString().split('T')[0];
			const dateInput = document.getElementById('paymentDateFilter');
			if (dateInput) {
				dateInput.value = today;
				filterPayments();
			}
		};
		
		window.clearPaymentDate = function() {
			const dateInput = document.getElementById('paymentDateFilter');
			if (dateInput) {
				dateInput.value = '';
				filterPayments();
			}
		};
		
		// Set default filter to today on page load
		document.addEventListener('DOMContentLoaded', function() {
			const today = new Date().toISOString().split('T')[0];
			const dateInput = document.getElementById('paymentDateFilter');
			if (dateInput && !dateInput.value) {
				dateInput.value = today;
				filterPayments();
			}
		});
		
		// Download payment verifications as text backup
		window.downloadPaymentsPDF = function() {
			const rows = Array.from(document.querySelectorAll('#paymentsTable tbody tr')).filter(row => {
				return row.style.display !== 'none' && !row.id;
			});
			
			if (rows.length === 0) {
				showToast('No payment records to export with current filters', 'warning');
				return;
			}
			
			const dateFilter = document.getElementById('paymentDateFilter')?.value || 'All Dates';
			
			let content = `BARCIE INTERNATIONAL CENTER - PAYMENT VERIFICATIONS BACKUP
Generated: ${new Date().toLocaleString()}
Total Records: ${rows.length}

FILTERS APPLIED:
- Date: ${dateFilter}

${'='.repeat(80)}

`;

			rows.forEach((row, index) => {
				const cells = row.querySelectorAll('td');
				if (cells.length >= 6) {
					const receipt = cells[0].textContent.trim();
					const guest = cells[1].textContent.trim().replace(/\n/g, ' ');
					const amount = cells[2].textContent.trim();
					const method = cells[3].textContent.trim();
					const status = cells[4].textContent.trim();
					const date = cells[5].textContent.trim().replace(/\n/g, ' ');
					
					content += `${index + 1}. ${receipt}
   Guest: ${guest}
   Amount: ${amount}
   Payment Method: ${method}
   Status: ${status}
   Date: ${date}
${'-'.repeat(80)}

`;
				}
			});
			
			const blob = new Blob([content], { type: 'text/plain' });
			const url = URL.createObjectURL(blob);
			const a = document.createElement('a');
			a.href = url;
			a.download = `payment_verifications_backup_${new Date().toISOString().split('T')[0]}.txt`;
			document.body.appendChild(a);
			a.click();
			document.body.removeChild(a);
			URL.revokeObjectURL(url);
			
			showToast('Payment verifications backup downloaded successfully', 'success');
		};
		
		// Download payment verifications as Excel
		window.downloadPaymentsExcel = function() {
			const rows = Array.from(document.querySelectorAll('#paymentsTable tbody tr')).filter(row => {
				return row.style.display !== 'none' && !row.id;
			});
			
			if (rows.length === 0) {
				showToast('No payment records to export with current filters', 'warning');
				return;
			}
			
			const dateFilter = document.getElementById('paymentDateFilter')?.value || 'All Dates';
			
			let csv = 'Receipt #,Guest Name,Guest Contact,Amount,Payment Method,Status,Date\n';
			
			rows.forEach(row => {
				const cells = row.querySelectorAll('td');
				if (cells.length >= 6) {
					const receipt = cells[0].textContent.trim().replace(/,/g, ';');
					const guestText = cells[1].textContent.trim().replace(/\n/g, ' ').replace(/,/g, ';');
					const guestParts = guestText.split(/[📞✉]/);
					const guestName = guestParts[0].trim();
					const guestContact = guestParts.slice(1).join(' | ').trim();
					const amount = cells[2].textContent.trim().replace(/,/g, '');
					const method = cells[3].textContent.trim().replace(/,/g, ';');
					const status = cells[4].textContent.trim().replace(/,/g, ';');
					const date = cells[5].textContent.trim().replace(/\n/g, ' ').replace(/,/g, ';');
					
					csv += `"${receipt}","${guestName}","${guestContact}","${amount}","${method}","${status}","${date}"\n`;
				}
			});
			
			const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
			const url = URL.createObjectURL(blob);
			const a = document.createElement('a');
			a.href = url;
			a.download = `payment_verifications_${dateFilter.replace(/[^0-9-]/g, '') || 'all'}_${new Date().toISOString().split('T')[0]}.csv`;
			document.body.appendChild(a);
			a.click();
			document.body.removeChild(a);
			URL.revokeObjectURL(url);
			
			showToast(`Exported ${rows.length} payment records to Excel (Filter: Date=${dateFilter})`, 'success');
		};
	})();
	
	// View Payment Details Modal Function
	window.viewPaymentDetails = function(bookingId) {
		if (!bookingId) return;
		
		// Fetch booking details via AJAX
		fetch('database/user_auth.php', {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
				'X-Requested-With': 'XMLHttpRequest'
			},
			body: 'action=get_booking_details&booking_id=' + encodeURIComponent(bookingId)
		})
		.then(r => r.json())
		.then(data => {
			if (!data || !data.success) {
				notify('Failed to load booking details', 'error');
				return;
			}
			
			const booking = data.booking;
			const modalId = 'payment-details-modal-' + bookingId;
			
			// Build modal HTML
			let modalHTML = `
				<div class="modal fade" id="${modalId}" tabindex="-1">
					<div class="modal-dialog modal-dialog-centered modal-lg">
						<div class="modal-content">
							<div class="modal-header bg-primary text-white">
								<h5 class="modal-title"><i class="fas fa-file-invoice me-2"></i>Booking Details - ${booking.receipt_no || 'N/A'}</h5>
								<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
							</div>
							<div class="modal-body">
								<div class="row g-3">
									<div class="col-md-6">
										<h6 class="text-muted mb-2"><i class="fas fa-user me-2"></i>Guest Information</h6>
										<table class="table table-sm table-borderless">
											<tr><td class="fw-bold">Name:</td><td>${booking.guest_name || 'N/A'}</td></tr>
											<tr><td class="fw-bold">Email:</td><td>${booking.guest_email || 'N/A'}</td></tr>
											<tr><td class="fw-bold">Contact:</td><td>${booking.guest_phone || 'N/A'}</td></tr>
											<tr><td class="fw-bold">Age:</td><td>${booking.guest_age || 'N/A'}</td></tr>
										</table>
									</div>
									<div class="col-md-6">
										<h6 class="text-muted mb-2"><i class="fas fa-calendar me-2"></i>Booking Information</h6>
										<table class="table table-sm table-borderless">
											<tr><td class="fw-bold">Room:</td><td>${booking.room_name || 'N/A'}</td></tr>
											<tr><td class="fw-bold">Check-in:</td><td>${booking.checkin || 'N/A'}</td></tr>
											<tr><td class="fw-bold">Check-out:</td><td>${booking.checkout || 'N/A'}</td></tr>
											<tr><td class="fw-bold">Room Price:</td><td>₱${booking.room_price ? parseFloat(booking.room_price).toFixed(2) : '0.00'}</td></tr>
											<tr><td class="fw-bold">Total Amount:</td><td class="text-success fw-bold">₱${booking.amount ? parseFloat(booking.amount).toFixed(2) : '0.00'}</td></tr>
										</table>
									</div>
								</div>`;
			
			// Add-ons Section
			if (booking.add_ons) {
				try {
					const addOns = JSON.parse(booking.add_ons);
					if (addOns && addOns.length > 0) {
						modalHTML += `
								<hr class="my-3">
								<div class="row">
									<div class="col-12">
										<h6 class="text-muted mb-2"><i class="fas fa-plus-circle me-2"></i>Selected Add-ons</h6>
										<ul class="list-group list-group-flush">`;
						addOns.forEach(addon => {
							modalHTML += `<li class="list-group-item px-0"><i class="fas fa-check text-success me-2"></i>${addon.name} - ₱${parseFloat(addon.price).toFixed(2)}</li>`;
						});
						modalHTML += `
										</ul>
									</div>
								</div>`;
					}
				} catch (e) {
					console.error('Error parsing add-ons:', e);
				}
			}
			
			modalHTML += `
								<hr class="my-3">
								
								<div class="row g-3">`;
			
			// ID Proof Section
			if (booking.proof_of_id) {
				modalHTML += `
									<div class="col-md-6">
										<h6 class="text-muted mb-2"><i class="fas fa-id-card me-2"></i>Uploaded ID</h6>
										<div class="text-center p-2 border rounded">
											<img src="${booking.proof_of_id}" alt="ID Proof" class="img-thumbnail" style="max-width:100%; max-height:300px; cursor:pointer;" onclick="showImageModal('${booking.proof_of_id}', 'Uploaded ID')">
											<div class="mt-2">
												<button type="button" class="btn btn-sm btn-primary" onclick="showImageModal('${booking.proof_of_id}', 'Uploaded ID')"><i class="fas fa-search-plus me-1"></i>View Full Size</button>
											</div>
										</div>
									</div>`;
			}
			
			// Payment Proof Section
			if (booking.proof_of_payment) {
				modalHTML += `
									<div class="col-md-6">
										<h6 class="text-muted mb-2"><i class="fas fa-receipt me-2"></i>Payment Proof</h6>
										<div class="text-center p-2 border rounded">
											<img src="${booking.proof_of_payment}" alt="Payment Proof" class="img-thumbnail" style="max-width:100%; max-height:300px; cursor:pointer;" onclick="showImageModal('${booking.proof_of_payment}', 'Payment Proof')">
											<div class="mt-2">
												<button type="button" class="btn btn-sm btn-success" onclick="showImageModal('${booking.proof_of_payment}', 'Payment Proof')"><i class="fas fa-search-plus me-1"></i>View Full Size</button>
											</div>
										</div>
									</div>`;
			}
			
			modalHTML += `
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
							</div>
						</div>
					</div>
				</div>
			`;
			
			// Remove any existing modal with same ID
			const existingModal = document.getElementById(modalId);
			if (existingModal) existingModal.remove();
			
			// Add modal to body and show
			document.body.insertAdjacentHTML('beforeend', modalHTML);
			const modalEl = document.getElementById(modalId);
			const bsModal = new bootstrap.Modal(modalEl);
			bsModal.show();
			
			// Clean up after modal is hidden
			modalEl.addEventListener('hidden.bs.modal', function() {
				modalEl.remove();
			});
		})
		.catch(err => {
			console.error('Error fetching booking details:', err);
			notify('Failed to load booking details', 'error');
		});
	};
	
	// Function to show image in full-size modal
	window.showImageModal = function(imageSrc, title) {
		const imageModalId = 'image-viewer-modal';
		
		// Remove existing image modal if any
		const existingImageModal = document.getElementById(imageModalId);
		if (existingImageModal) existingImageModal.remove();
		
		// Create full-size image modal
		const imageModalHTML = `
			<div class="modal fade" id="${imageModalId}" tabindex="-1">
				<div class="modal-dialog modal-dialog-centered modal-xl">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title"><i class="fas fa-image me-2"></i>${title}</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
						</div>
						<div class="modal-body text-center p-2" style="background-color: #f8f9fa;">
							<img src="${imageSrc}" alt="${title}" style="max-width:100%; max-height:80vh; width:auto; height:auto; object-fit:contain;">
						</div>
						<div class="modal-footer">
							<a href="${imageSrc}" download class="btn btn-primary"><i class="fas fa-download me-1"></i>Download</a>
							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
		`;
		
		// Add modal to body and show
		document.body.insertAdjacentHTML('beforeend', imageModalHTML);
		const imageModalEl = document.getElementById(imageModalId);
		const bsImageModal = new bootstrap.Modal(imageModalEl);
		bsImageModal.show();
		
		// Clean up after modal is hidden
		imageModalEl.addEventListener('hidden.bs.modal', function() {
			imageModalEl.remove();
		});
	};
})();

// Role-based access control for Payment Verification
// Staff: CANNOT approve/verify payments (❌)
// Admin/Manager/Super Admin: Full access (✓)
(function() {
  function applyPaymentRoleRestrictions() {
    const role = (window.currentAdmin && window.currentAdmin.role) || 'staff';
    console.log('Applying payment verification restrictions for role:', role);
    
    if (role === 'staff') {
      // Hide verify/reject action buttons
      document.querySelectorAll('.payment-action').forEach(btn => {
        btn.style.display = 'none';
      });
      
      // Disable export buttons
      const exportBtns = document.querySelectorAll('[onclick*="downloadPayments"]');
      exportBtns.forEach(btn => btn.style.display = 'none');
      
      // Add read-only notice
      const cardBody = document.querySelector('.card-header.bg-info')?.parentElement;
      if (cardBody && !document.getElementById('payment-readonly-notice')) {
        const notice = document.createElement('div');
        notice.id = 'payment-readonly-notice';
        notice.className = 'alert alert-warning mx-3 mt-3 mb-0';
        notice.innerHTML = '<i class="fas fa-info-circle me-2"></i>You have view-only access to payment verifications.';
        cardBody.querySelector('.card-header').insertAdjacentElement('afterend', notice);
      }
      
      console.log('Payment Verification: Staff restricted to view-only');
    }
  }
  
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', applyPaymentRoleRestrictions);
  } else {
    applyPaymentRoleRestrictions();
  }
  
  const observer = new MutationObserver(applyPaymentRoleRestrictions);
  const paymentsTable = document.querySelector('#paymentsTable tbody');
  if (paymentsTable) {
    observer.observe(paymentsTable, { childList: true, subtree: true });
  }
  
  setTimeout(applyPaymentRoleRestrictions, 200);
})();
</script>