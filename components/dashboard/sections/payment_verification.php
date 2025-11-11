<?php
// Payment Verification Section for Dashboard
// This displays bookings where payment_status = 'pending' or proof_of_payment is present
?>

<div class="row mb-4">
	<div class="col-12">
		<div class="card">
			<div class="card-header bg-info text-white">
				<h6 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Verifications (Pending)</h6>
				<small class="opacity-75">Review payment proofs and verify or reject payments.</small>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-hover align-middle" id="paymentsTable">
						<thead class="table-light">
							<tr>
								<th>Receipt #</th>
								<th>Guest</th>
								<th>Amount / Details</th>
								<th>Proof</th>
								<th>Submitted</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
<?php
$stmt = $conn->prepare("SELECT b.id, b.receipt_no, b.details, b.proof_of_payment, b.created_at, i.name as room_name FROM bookings b LEFT JOIN items i ON b.room_id = i.id WHERE (b.payment_status = 'pending' OR (b.proof_of_payment IS NOT NULL AND b.proof_of_payment <> '')) ORDER BY b.created_at DESC");
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
			$room = $row['room_name'] ?: 'Unassigned';

			// Try to extract guest and amount info from details
			$guest = 'Guest';
			if (preg_match('/Guest:\s*([^|]+)/', $details, $m)) $guest = trim($m[1]);
			$amount = '';
			if (preg_match('/Amount:\s*([^|]+)/', $details, $m)) $amount = trim($m[1]);

			echo '<tr id="payment-row-' . $id . '">';
			echo '<td><strong>' . htmlspecialchars($receipt) . '</strong></td>';
			echo '<td>' . htmlspecialchars($guest) . '</td>';
			echo '<td>' . htmlspecialchars($amount ?: $room) . '</td>';
			echo '<td>';
			if (!empty($proof) && file_exists(__DIR__ . '/../../' . $proof)) {
				$url = '../' . ltrim($proof, '/');
				echo '<a href="#" class="view-payment-proof" data-proof="' . htmlspecialchars($url) . '" data-booking-id="' . $id . '"><img src="' . htmlspecialchars($url) . '" alt="Payment Proof" style="max-width:120px; max-height:80px; object-fit:cover; border-radius:4px; border:1px solid #e9ecef;"></a>';
			} elseif (!empty($proof)) {
				echo '<a href="#" class="view-payment-proof" data-proof="' . htmlspecialchars($proof) . '" data-booking-id="' . $id . '">View Proof</a>';
			} else {
				echo 'No proof uploaded';
			}
			echo '</td>';
			echo '<td>' . htmlspecialchars(date('M j, Y H:i', strtotime($created))) . '</td>';
			echo '<td>';
			echo '<button class="btn btn-success btn-sm payment-action" data-booking-id="' . $id . '" data-action="verify"><i class="fas fa-check me-1"></i>Verify</button> ';
			echo '<button class="btn btn-danger btn-sm payment-action" data-booking-id="' . $id . '" data-action="reject"><i class="fas fa-times me-1"></i>Reject</button>';
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
		try { alert(message); } catch (e) { /* ignore */ }
	}
	// Delegated handler for verify/reject buttons
	document.addEventListener('click', function(e){
		const btn = e.target.closest('.payment-action');
		if (!btn) return;
		const bookingId = btn.dataset.bookingId;
		const action = btn.dataset.action; // verify|reject
		if (!bookingId || !action) return;

		const confirmMsg = action === 'verify' ? 'Verify this payment?' : 'Reject this payment?';
		if (!confirm(confirmMsg)) return;

		btn.disabled = true;
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
			if (json && json.success) {
				const row = document.getElementById('payment-row-' + bookingId);
				if (row) {
					const actionsCell = row.querySelector('td:last-child');
					if (actionsCell) {
						actionsCell.innerHTML = action === 'verify' ? '<span class="badge bg-success">Verified</span>' : '<span class="badge bg-danger">Rejected</span>';
						// append verifier info if available
						if (json.verifier_username || json.verified_at) {
							const info = document.createElement('div');
							info.className = 'mt-2 small text-muted';
							let txt = '';
							if (json.verifier_username) txt += 'By: ' + json.verifier_username;
							if (json.verified_at) txt += (txt ? ' • ' : '') + json.verified_at;
							info.textContent = txt;
							actionsCell.appendChild(info);
						}
					}
					row.classList.add('table-success');
				}
				notify(json.message || 'Payment updated', 'success');
			} else {
				notify((json && (json.error || json.message)) || 'Failed to update payment', 'error');
				btn.disabled = false;
			}
		}).catch(err => {
			console.error(err);
				notify('Request failed — check console', 'error');
			btn.disabled = false;
		});
	});

	// reuse proof modal logic from bookings_section (basic)
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
			return Array.from(document.querySelectorAll('#paymentsTable tbody tr')).filter(r => r.id !== 'payments-no-results');
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
			const total = rows.length;
			pstate.totalPages = Math.max(1, Math.ceil(total / pstate.perPage));
			if (pstate.currentPage > pstate.totalPages) pstate.currentPage = pstate.totalPages;

			const currentlyVisible = rows.filter(r => r.style.display !== 'none' && !r.hasAttribute('data-hidden-by-pagination'));
			const myToken = ++paymentsFadeToken;

			// show spinner while transitioning
			const removeSpinner = (function(){
				try {
					const tbl = document.querySelector('#paymentsTable');
					if (!tbl) return function(){};
					let parent = tbl.closest && tbl.closest('.table-responsive') ? tbl.closest('.table-responsive') : tbl;
					const prevPos = parent.style.position || '';
					const computed = window.getComputedStyle(parent).position;
					if (computed === 'static') parent.style.position = 'relative';
					const overlay = document.createElement('div'); overlay.className = 'table-spinner-overlay'; overlay.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
					parent.appendChild(overlay);
					return function(){ try { if (overlay && overlay.parentNode) overlay.parentNode.removeChild(overlay); } catch(e){}; try { if (computed === 'static') parent.style.position = prevPos || ''; } catch(e){} };
				} catch (err) { return function(){}; }
			})();

			// fade out current rows then show new slice
			fadeOutRows(currentlyVisible).then(()=>{
				if (myToken !== paymentsFadeToken) { removeSpinner(); return; }
				rows.forEach(r => { r.style.display = 'none'; r.setAttribute('data-hidden-by-pagination','true'); r.style.opacity = 0; });
				const start = (pstate.currentPage - 1) * pstate.perPage;
				const end = start + pstate.perPage;
				rows.slice(start, end).forEach(r => {
					r.removeAttribute('data-hidden-by-pagination'); r.style.display = ''; r.style.opacity = 0;
					requestAnimationFrame(()=>{ r.style.transition = r.style.transition || 'opacity 220ms ease-in-out'; r.style.opacity = 1; });
				});
				setTimeout(()=>{ try { removeSpinner(); } catch(e){} }, 220);
				pRender();
			});
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
	})();
})();
</script>