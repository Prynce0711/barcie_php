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
						<div class="col-md-4">
							<input id="feedbackSearch" class="form-control" placeholder="Search messages, IP, or guest text..." />
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
						<div class="col-md-3 d-flex">
							<input id="dateFrom" type="date" class="form-control me-2" />
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
									<th style="width:8%">Rating</th>
									<th>Message</th>
									<th style="width:18%">Created</th>
									<th style="width:12%">Meta</th>
								</tr>
							</thead>
							<tbody>
								<tr><td colspan="5" class="text-center">Loading...</td></tr>
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
		// Admin-only comprehensive feedback UI
		const apiUrl = 'database/user_auth.php?action=get_feedback_data';
		const tableBody = document.querySelector('#feedbackTable tbody');
		const searchInput = document.getElementById('feedbackSearch');
		const ratingFilter = document.getElementById('feedbackRatingFilter');
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
			const from = dateFrom?.value ? new Date(dateFrom.value) : null;
			const to = dateTo?.value ? new Date(dateTo.value) : null;

			filtered = allData.filter(item => {
				// search across message and additional metadata
				const hay = ((item.message||'') + ' ' + (item.ip||'') + ' ' + (item.email||'') + ' ' + (item.details||'')).toLowerCase();
				if (q && !hay.includes(q)) return false;
				if (r && String(item.rating) !== r) return false;
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
				tableBody.innerHTML = '<tr><td colspan="5" class="text-center">No feedback found</td></tr>';
			} else {
				tableBody.innerHTML = '';
				slice.forEach((r, idx) => {
					const tr = document.createElement('tr');
					const stars = Number(r.rating) || Number(r.stars) || 0;
					const starHtml = Array.from({length: stars}).map(()=>'<span class="feedback-star">★</span>').join('') + (stars===0?'<span class="text-muted">—</span>':'' );
					const msg = (r.message || r.msg || '').replace(/</g,'&lt;').replace(/>/g,'&gt;');
					tr.innerHTML = `
						<td>${start + idx + 1}</td>
						<td>${starHtml}</td>
						<td class="feedback-message">${msg}</td>
						<td>${r.created_at || ''}</td>
						<td><small class="text-muted">IP: ${r.ip||''}<br/>ID: ${r.id||''}</small></td>
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
			const rows = [['id','rating','message','created_at','ip']];
			filtered.forEach(r => rows.push([r.id||'', r.rating||r.stars||'', (r.message||'').replace(/\r?\n/g,' '), r.created_at||'', r.ip||'']));
			const csv = rows.map(r => r.map(c=> '"'+String(c).replace(/"/g,'""')+'"').join(',')).join('\n');
			const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
			const url = URL.createObjectURL(blob);
			const a = document.createElement('a'); a.href = url; a.download = 'feedback_export.csv'; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
		}

		// Event wiring
		if (searchInput) searchInput.addEventListener('input', ()=> applyFilters());
		if (ratingFilter) ratingFilter.addEventListener('change', ()=> applyFilters());
		if (dateFrom) dateFrom.addEventListener('change', ()=> applyFilters());
		if (dateTo) dateTo.addEventListener('change', ()=> applyFilters());
		if (refreshBtn) refreshBtn.addEventListener('click', ()=> fetchData());
		if (exportBtn) exportBtn.addEventListener('click', ()=> exportCSV());

		// initial load
		document.addEventListener('DOMContentLoaded', ()=> fetchData());

	})();
</script>

