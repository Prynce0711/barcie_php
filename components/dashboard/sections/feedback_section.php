<?php
// Feedback Section - Admin-only comprehensive view
?>

<div class="row mb-4" id="feedback-section">
	<div class="col-12">
		<div class="card">
			<div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
				<div>
					<h5 class="mb-0"><i class="fas fa-comments me-2"></i>Feedback</h5>
					<small class="opacity-75">Comprehensive admin feedback review</small>
				</div>
				<div>
					<button class="btn btn-sm btn-light" id="exportFeedbackBtn"><i class="fas fa-file-csv"></i> Export CSV</button>
					<button class="btn btn-sm btn-light" id="refreshFeedbackBtn"><i class="fas fa-sync-alt"></i> Refresh</button>
				</div>
			</div>
			<div class="card-body">
				<?php if (isset($_SESSION) && !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
					<div class="mb-3 row g-2 align-items-center">
						<div class="col-md-3">
							<input id="feedbackSearch" class="form-control" placeholder="Search messages, names, rooms..." />
						</div>
						<div class="col-md-2">
							<select id="feedbackRatingFilter" class="form-select">
								<option value="">All ratings</option>
								<option value="5">5 stars</option>
								<option value="4">4 stars</option>
								<option value="3">3 stars</option>
								<option value="2">2 stars</option>
								<option value="1">1 star</option>
							</select>
						</div>
						<div class="col-md-2">
							<select id="feedbackStatusFilter" class="form-select">
								<option value="">All Status</option>
								<option value="pending">Pending</option>
								<option value="approved">Approved</option>
								<option value="rejected">Rejected</option>
							</select>
						</div>
						<div class="col-md-2 d-flex">
							<input id="dateFrom" type="date" class="form-control me-1" />
							<input id="dateTo" type="date" class="form-control" />
						</div>
						<div class="col-md-3 text-end">
							<small id="feedbackCount" class="text-muted">Loading...</small>
						</div>
					</div>

					<div class="table-responsive" style="max-height:520px; overflow:auto;">
						<table class="table table-striped table-hover align-middle" id="feedbackTable">
							<thead class="table-light sticky-top">
								<tr>
									<th style="width:4%">#</th>
									<th style="width:12%">Guest</th>
									<th style="width:15%">Room</th>
									<th style="width:8%">Rating</th>
									<th>Message</th>
									<th style="width:10%">Status</th>
									<th style="width:12%">Created</th>
									<th style="width:12%">Actions</th>
								</tr>
							</thead>
							<tbody>
								<tr><td colspan="8" class="text-center">Loading...</td></tr>
							</tbody>
						</table>
					</div>

					<nav class="mt-2" aria-label="Feedback pagination">
						<ul class="pagination pagination-sm" id="feedbackPager"></ul>
					</nav>

				<?php else: ?>
					<div class="alert alert-warning">You must be an administrator to view feedback.</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<style>
	.feedback-star { color: #ffc107; font-weight:700; }
	.feedback-message { white-space: pre-wrap; }
	/* Make header controls compact on small screens */
	@media (max-width: 576px) {
		.card-header .btn { margin-top:6px; }
	}
</style>

<script>
	(function(){
		// Admin-only comprehensive feedback UI with approval system
		const apiUrl = 'database/user_auth.php?action=get_feedback_data';
		const tableBody = document.querySelector('#feedbackTable tbody');
		const searchInput = document.getElementById('feedbackSearch');
		const ratingFilter = document.getElementById('feedbackRatingFilter');
		const statusFilter = document.getElementById('feedbackStatusFilter');
		const dateFrom = document.getElementById('dateFrom');
		const dateTo = document.getElementById('dateTo');
		const refreshBtn = document.getElementById('refreshFeedbackBtn');
		const exportBtn = document.getElementById('exportFeedbackBtn');
		const countEl = document.getElementById('feedbackCount');
		const pager = document.getElementById('feedbackPager');

		let allData = [];
		let filtered = [];
		const pageSize = 10;

		async function fetchData(){
			setLoading();
			try {
				const res = await fetch(apiUrl, { cache: 'no-store' });
				const json = await res.json();
				if (!json.success) {
					tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Failed to load feedback</td></tr>';
					if (countEl) countEl.innerText = '';
					return;
				}
				allData = json.data || json.feedback || [];
				// normalize created field
				allData = allData.map(r => Object.assign({ created_at: r.created_at || r.created || r.date || '' }, r));
				applyFilters();
			} catch (err) {
				tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error loading feedback</td></tr>';
				if (countEl) countEl.innerText = '';
			}
		}

		function setLoading(){
			tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Loading...</td></tr>';
			if (countEl) countEl.innerText = 'Loading...';
		}

		function applyFilters(){
			const q = (searchInput?.value || '').toLowerCase().trim();
			const r = (ratingFilter?.value || '').trim();
			const s = (statusFilter?.value || '').trim();
			const from = dateFrom?.value ? new Date(dateFrom.value) : null;
			const to = dateTo?.value ? new Date(dateTo.value) : null;

			filtered = allData.filter(item => {
				// search across message, name, room name
				const hay = ((item.message||'') + ' ' + (item.feedback_name||'') + ' ' + (item.room_name||'') + ' ' + (item.username||'')).toLowerCase();
				if (q && !hay.includes(q)) return false;
				if (r && String(item.rating) !== r) return false;
				if (s && (item.approval_status||'') !== s) return false;
				if (from || to) {
					const c = item.created_at ? new Date(item.created_at) : null;
					if (from && c && c < from) return false;
					if (to && c && c > (new Date(to).setHours(23,59,59,999))) return false;
				}
				return true;
			});

			renderPage(1);
		}

		function renderPage(page){
			const total = filtered.length;
			const pages = Math.max(1, Math.ceil(total / pageSize));
			page = Math.min(Math.max(1, page), pages);
			const start = (page - 1) * pageSize;
			const slice = filtered.slice(start, start + pageSize);

			if (slice.length === 0) {
				tableBody.innerHTML = '<tr><td colspan="8" class="text-center">No feedback found</td></tr>';
			} else {
				tableBody.innerHTML = '';
				slice.forEach((r, idx) => {
					const tr = document.createElement('tr');
					const stars = Number(r.rating) || 0;
					const starHtml = Array.from({length: stars}).map(()=>'<span class="feedback-star">★</span>').join('') + (stars===0?'<span class="text-muted">—</span>':'' );
					const msg = (r.message || '').replace(/</g,'&lt;').replace(/>/g,'&gt;');
					const guestName = r.username || 'Anonymous Guest';
					const roomInfo = r.room_name ? `${r.room_name} <small class="text-muted">(${r.room_type||''})</small>` : '<span class="text-muted">—</span>';
					const status = r.approval_status || 'pending';
					const statusClass = status === 'approved' ? 'success' : (status === 'rejected' ? 'danger' : 'warning');
					const statusBadge = `<span class="badge bg-${statusClass}">${status}</span>`;
					
					// Action buttons based on status
					let actionBtns = '';
					if (status === 'pending') {
						actionBtns = `
							<button class="btn btn-sm btn-success me-1" onclick="approveFeedback(${r.id})" title="Approve">
								<i class="fas fa-check"></i>
							</button>
							<button class="btn btn-sm btn-danger" onclick="rejectFeedback(${r.id})" title="Reject">
								<i class="fas fa-times"></i>
							</button>
						`;
					} else if (status === 'approved') {
						actionBtns = `
							<button class="btn btn-sm btn-warning" onclick="rejectFeedback(${r.id})" title="Reject">
								<i class="fas fa-ban"></i>
							</button>
						`;
					} else if (status === 'rejected') {
						actionBtns = `
							<button class="btn btn-sm btn-success" onclick="approveFeedback(${r.id})" title="Approve">
								<i class="fas fa-check-circle"></i>
							</button>
						`;
					}
					
					tr.innerHTML = `
						<td>${start + idx + 1}</td>
						<td><small>${guestName}</small></td>
						<td><small>${roomInfo}</small></td>
						<td>${starHtml}</td>
						<td class="feedback-message"><small>${msg}</small></td>
						<td>${statusBadge}</td>
						<td><small>${r.created_at || ''}</small></td>
						<td>${actionBtns}</td>
					`;
					tableBody.appendChild(tr);
				});
			}

			renderPager(page, pages);
			if (countEl) countEl.innerText = total + ' feedback';
		}

		function renderPager(active, pages){
			pager.innerHTML = '';
			if (pages <= 1) return;
			const createLi = (p, label, cls='') => {
				const li = document.createElement('li'); li.className = 'page-item ' + (p===active? 'active':'');
				const a = document.createElement('a'); a.className = 'page-link'; a.href = '#'; a.dataset.page = p; a.innerText = label;
				a.addEventListener('click', (e)=>{ e.preventDefault(); renderPage(Number(e.target.dataset.page)); });
				li.appendChild(a); return li;
			};
			// prev
			pager.appendChild(createLi(Math.max(1, active-1), '‹'));
			// pages (show up to 7)
			const start = Math.max(1, active-3);
			const end = Math.min(pages, start + 6);
			for (let p = start; p <= end; p++) pager.appendChild(createLi(p, p));
			// next
			pager.appendChild(createLi(Math.min(pages, active+1), '›'));
		}

		function exportCSV(){
			const rows = [['id','guest','room','rating','message','status','created_at']];
			filtered.forEach(r => rows.push([
				r.id||'', 
				r.username||'', 
				r.room_name||'', 
				r.rating||'', 
				(r.message||'').replace(/\r?\n/g,' '), 
				r.approval_status||'',
				r.created_at||''
			]));
			const csv = rows.map(r => r.map(c=> '"'+String(c).replace(/"/g,'""')+'"').join(',')).join('\n');
			const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
			const url = URL.createObjectURL(blob);
			const a = document.createElement('a'); a.href = url; a.download = 'feedback_export.csv'; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
		}

		// Approve/Reject functions
		window.approveFeedback = async function(feedbackId) {
			if (!confirm('Approve this feedback? It will be visible to guests.')) return;
			
			const formData = new FormData();
			formData.append('action', 'approve_feedback');
			formData.append('feedback_id', feedbackId);
			
			try {
				const res = await fetch('database/user_auth.php', {
					method: 'POST',
					body: formData
				});
				const json = await res.json();
				if (json.success) {
					alert(json.message || 'Feedback approved!');
					fetchData(); // Reload data
				} else {
					alert('Error: ' + (json.error || 'Failed to approve feedback'));
				}
			} catch (err) {
				console.error(err);
				alert('Error approving feedback');
			}
		};

		window.rejectFeedback = async function(feedbackId) {
			if (!confirm('Reject this feedback? It will NOT be visible to guests.')) return;
			
			const formData = new FormData();
			formData.append('action', 'reject_feedback');
			formData.append('feedback_id', feedbackId);
			
			try {
				const res = await fetch('database/user_auth.php', {
					method: 'POST',
					body: formData
				});
				const json = await res.json();
				if (json.success) {
					alert(json.message || 'Feedback rejected');
					fetchData(); // Reload data
				} else {
					alert('Error: ' + (json.error || 'Failed to reject feedback'));
				}
			} catch (err) {
				console.error(err);
				alert('Error rejecting feedback');
			}
		};

		// Event wiring
		if (searchInput) searchInput.addEventListener('input', ()=> applyFilters());
		if (ratingFilter) ratingFilter.addEventListener('change', ()=> applyFilters());
		if (statusFilter) statusFilter.addEventListener('change', ()=> applyFilters());
		if (dateFrom) dateFrom.addEventListener('change', ()=> applyFilters());
		if (dateTo) dateTo.addEventListener('change', ()=> applyFilters());
		if (refreshBtn) refreshBtn.addEventListener('click', ()=> fetchData());
		if (exportBtn) exportBtn.addEventListener('click', ()=> exportCSV());

		// initial load
		document.addEventListener('DOMContentLoaded', ()=> fetchData());

	})();
</script>

