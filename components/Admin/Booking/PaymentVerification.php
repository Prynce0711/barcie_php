<?php
// Payment Verification Section for Dashboard
// This displays bookings where payment_status = 'pending' or proof_of_payment is present

// Set timezone to ensure consistent time display
date_default_timezone_set('Asia/Manila');
?>

<?php ob_start(); ?>
<div class="d-flex align-items-center gap-2 flex-wrap py-1">
	<?php $dateScope = 'payments'; include __DIR__ . '/../../Filter/DateFilter.php'; ?>
	<div class="vr d-none d-md-block" style="height:28px;"></div>
	<?php $searchScope = 'payments'; $searchPlaceholder = 'Search guest or receipt...'; include __DIR__ . '/../../Filter/Searchbar.php'; ?>
	<div class="ms-auto d-flex align-items-center gap-2">
		<?php $resetScope = 'payments'; include __DIR__ . '/../../Filter/ResetFilter.php'; ?>
		<button type="button" class="btn btn-sm btn-outline-primary" onclick="downloadPaymentsExcel()">
			<i class="fas fa-file-excel me-1"></i>Excel
		</button>
		<button type="button" class="btn btn-sm btn-primary text-white" onclick="downloadPaymentsPDF()">
			<i class="fas fa-file-alt me-1"></i>Text
		</button>
	</div>
</div>
<?php $sectionFilters = ob_get_clean(); ?>
<?php
$sectionTitle    = 'Payment Verifications (Pending)';
$sectionIcon     = 'fa-credit-card';
$sectionSubtitle = 'Review payment proofs and verify or reject payments.';
include __DIR__ . '/../Shared/SectionHeader.php';
?>
<div class="row mb-4">
	<div class="col-12">
		<div class="card">
			<div class="card-body">
				<!-- Bridge: sync reusable components → existing payment filter logic -->
				<script>
				(function(){
					function sync(){ if(typeof filterPayments==='function') filterPayments(); }
					document.addEventListener('date-filter-changed', function(e){
						if(e.detail.scope!=='payments') return;
						var el=document.getElementById('paymentDateFilter');
						if(!el){el=document.createElement('input');el.type='hidden';el.id='paymentDateFilter';document.body.appendChild(el);}
						el.value=e.detail.from||'';
						sync();
					});
					document.addEventListener('search-changed', function(e){
						if(e.detail.scope!=='payments') return;
						var el=document.getElementById('paymentSearchFilter');
						if(!el){el=document.createElement('input');el.type='hidden';el.id='paymentSearchFilter';document.body.appendChild(el);}
						el.value=e.detail.value||'';
						sync();
					});
					document.addEventListener('filters-reset', function(e){
						if(e.detail&&e.detail.scope&&e.detail.scope!=='payments') return;
						sync();
					});
				})();
				</script>

				<?php
				$tableId = 'paymentsTable';
				$tableScope = 'payments';
				$tablePageSize = 10;
				$tableColumns = [
					['label' => 'Receipt #'],
					['label' => 'Guest'],
					['label' => 'Amount / Details'],
					['label' => 'View Details'],
					['label' => 'Submitted'],
					['label' => 'Actions'],
				];
				include __DIR__ . '/../../Table/Table.php';
				?>
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
										if (preg_match('/Guest:\s*([^|]+)/', $details, $m))
											$guest = trim($m[1]);
										$amount = '';
										if (preg_match('/Amount:\s*([^|]+)/', $details, $m))
											$amount = trim($m[1]);

										// Extract date from created_at for filtering (format: YYYY-MM-DD)
										$booking_date = date('Y-m-d', strtotime($created));

										echo '<tr id="payment-row-' . $id . '" data-date="' . htmlspecialchars($booking_date) . '">';
										echo '<td><strong>' . htmlspecialchars($receipt) . '</strong></td>';
										echo '<td>' . htmlspecialchars($guest) . '</td>';
										echo '<td>' . htmlspecialchars($amount ?: $room) . '</td>';
										echo '<td>';
										echo '<button class="btn btn-info btn-sm" onclick="viewPaymentDetails(' . $id . ')" title="View Details"><i class="fas fa-eye me-1"></i>View Details</button>';
										echo '</td>';
										// Format date/time with proper timezone handling
										$display_date = $payment_date ? date('M j, Y H:i', strtotime($payment_date)) : 'N/A';
										echo '<td>' . htmlspecialchars($display_date) . '</td>';
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
				<?php $tableClose = true; include __DIR__ . '/../../Table/Table.php'; ?>
			</div>
		</div>
	</div>
</div>

<script>
	(function () {
		// Notification helper: prefer showToast if available, fallback to alert
		function notify(message, type = 'info') {
			try {
				if (typeof showToast === 'function') return showToast(message, type);
			} catch (e) { }
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
		document.addEventListener('click', async function (e) {
			const btn = e.target.closest('.payment-action');
			if (!btn) return;
			const bookingId = btn.dataset.bookingId;
			const action = btn.dataset.action; // verify|reject
			if (!bookingId || !action) return;

			const confirmMsg = action === 'verify' ? 'Verify this payment?' : 'Reject this payment?';
			if (typeof window.showConfirm !== 'function') {
				notify('Confirmation popup is not available right now.', 'error');
				return;
			}

			const confirmed = await window.showConfirm(confirmMsg, {
				title: 'Confirm Payment Action',
				confirmText: action === 'verify' ? 'Verify' : 'Reject',
				confirmClass: action === 'verify' ? 'btn-success' : 'btn-danger',
				cancelText: 'Cancel'
			});
			if (!confirmed) return;

			btn.disabled = true;

			// show spinner overlay while request runs
			let removeSpinner = function () { };
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
						removeSpinner = function () { try { if (overlay && overlay.parentNode) overlay.parentNode.removeChild(overlay); } catch (e) { }; try { if (computed === 'static') parent.style.position = prevPos || ''; } catch (e) { } };
					} catch (e) { /* ignore */ }
				}
			} catch (e) { /* ignore */ }

			const body = 'action=admin_update_payment&booking_id=' + encodeURIComponent(bookingId) + '&payment_action=' + encodeURIComponent(action);
			fetch('database/index.php?endpoint=user_auth', {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
					'X-Requested-With': 'XMLHttpRequest'
				},
				body: body
			}).then(r => r.json()).then(json => {
				try { removeSpinner(); } catch (e) { }
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
				try { removeSpinner(); } catch (e) { }
				console.error(err);
				notify('Request failed — check console', 'error');
				btn.disabled = false;
			});
		});	// reuse proof modal logic from bookings_section (basic)
		document.addEventListener('click', function (e) {
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
				modalEl.addEventListener('hidden.bs.modal', function () { modalEl.remove(); });
			} catch (err) {
				console.error('Failed to show proof modal', err);
				window.open(proof, '_blank');
			}
		});

		// Register filter function with unified BarcieTable pagination
		(function () {
			function doesPaymentMatch(row) {
				if (row.id === 'payments-no-results') return false;
				var dateFilter = document.getElementById('paymentDateFilter')?.value || '';
				var searchFilter = (document.getElementById('paymentSearchFilter')?.value || '').toLowerCase();
				// Fallback: read from component APIs
				if (!dateFilter && window.DateFilter && window.DateFilter['payments']) {
					var vals = window.DateFilter['payments'].getValues();
					dateFilter = vals.from || '';
				}
				if (!searchFilter && window.Searchbar && window.Searchbar['payments']) {
					searchFilter = (window.Searchbar['payments'].getValue() || '').toLowerCase();
				}
				var rdate = row.dataset.date || '';
				if (dateFilter && rdate !== dateFilter) return false;
				if (searchFilter && row.textContent.toLowerCase().indexOf(searchFilter) === -1) return false;
				return true;
			}

			function registerFilter() {
				if (window.BarcieTable && window.BarcieTable.payments) {
					window.BarcieTable.payments.setFilter(doesPaymentMatch);
				} else {
					setTimeout(registerFilter, 50);
				}
			}

			window.filterPayments = function () {
				if (window.BarcieTable && window.BarcieTable.payments) {
					window.BarcieTable.payments.refresh();
				}
			};

			// Register immediately if BarcieTable is ready, otherwise retry
			registerFilter();

			window.downloadPaymentsPDF = function () {
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

			window.downloadPaymentsExcel = function () {
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

	
		window.viewPaymentDetails = function (bookingId) {
			if (!bookingId) return;

			
			fetch('database/index.php?endpoint=user_auth', {
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

					
					const existingModal = document.getElementById(modalId);
					if (existingModal) existingModal.remove();

					
					document.body.insertAdjacentHTML('beforeend', modalHTML);
					const modalEl = document.getElementById(modalId);
					const bsModal = new bootstrap.Modal(modalEl);
					bsModal.show();

					modalEl.addEventListener('hidden.bs.modal', function () {
						modalEl.remove();
					});
				})
				.catch(err => {
					console.error('Error fetching booking details:', err);
					notify('Failed to load booking details', 'error');
				});
		};

		window.showImageModal = function (imageSrc, title) {
			const imageModalId = 'image-viewer-modal';

			const existingImageModal = document.getElementById(imageModalId);
			if (existingImageModal) existingImageModal.remove();

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

			document.body.insertAdjacentHTML('beforeend', imageModalHTML);
			const imageModalEl = document.getElementById(imageModalId);
			const bsImageModal = new bootstrap.Modal(imageModalEl);
			bsImageModal.show();

			imageModalEl.addEventListener('hidden.bs.modal', function () {
				imageModalEl.remove();
			});
		};
	})();

	(function () {
		function applyPaymentRoleRestrictions() {
			const role = (window.currentAdmin && window.currentAdmin.role) || 'staff';
			console.log('Applying payment verification restrictions for role:', role);

			if (role === 'staff') {
				document.querySelectorAll('.payment-action').forEach(btn => {
					btn.style.display = 'none';
				});

				const exportBtns = document.querySelectorAll('[onclick*="downloadPayments"]');
				exportBtns.forEach(btn => btn.style.display = 'none');

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
