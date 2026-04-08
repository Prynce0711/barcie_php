<?php
// Feedback Section - Admin-only comprehensive view
?>

<?php
ob_start(); ?>
<button class="btn btn-sm btn-light" id="exportFeedbackBtn"><i class="fas fa-file-csv me-1"></i>Export CSV</button>
<button class="btn btn-sm btn-light" id="refreshFeedbackBtn"><i class="fas fa-sync-alt me-1"></i>Refresh</button>
<?php $sectionActions = ob_get_clean(); ?>
<?php
ob_start();
if (isset($_SESSION) && !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
<div class="d-flex align-items-center gap-2 flex-wrap py-1">
	<?php $searchScope = 'feedback'; $searchPlaceholder = 'Search messages, names, rooms...'; include __DIR__ . '/../../Filter/Searchbar.php'; ?>
	<div class="vr d-none d-md-block" style="height:28px;"></div>
	<select id="feedbackRatingFilter" class="form-select form-select-sm" style="width:auto; min-width:120px;">
		<option value="">All ratings</option>
		<option value="5">5 stars</option>
		<option value="4">4 stars</option>
		<option value="3">3 stars</option>
		<option value="2">2 stars</option>
		<option value="1">1 star</option>
	</select>
	<div class="vr d-none d-md-block" style="height:28px;"></div>
	<?php $dateScope = 'feedback'; $dateShowRange = true; include __DIR__ . '/../../Filter/DateFilter.php'; ?>
	<div class="ms-auto d-flex align-items-center gap-2">
		<small id="feedbackCount" class="text-muted">Loading...</small>
		<?php $resetScope = 'feedback'; include __DIR__ . '/../../Filter/ResetFilter.php'; ?>
	</div>
</div>
<?php endif; ?>
<?php $sectionFilters = ob_get_clean(); ?>
<?php
$sectionTitle    = 'Feedback';
$sectionIcon     = 'fa-comments';
$sectionSubtitle = 'Comprehensive admin feedback review';
include __DIR__ . '/../Shared/SectionHeader.php';
?>
<div class="row mb-4">
	<div class="col-12">
		<div class="card">
			<div class="card-body">
				<?php if (isset($_SESSION) && !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
					<!-- Bridge: sync reusable components → existing feedback filter logic -->
					<script>
					(function(){
						function sync(){ if(typeof window.applyFeedbackFilters==='function') window.applyFeedbackFilters(); }
						document.addEventListener('search-changed', function(e){
							if(e.detail.scope!=='feedback') return;
							var el=document.getElementById('feedbackSearch');
							if(!el){el=document.createElement('input');el.type='hidden';el.id='feedbackSearch';document.body.appendChild(el);}
							el.value=e.detail.value||'';
							sync();
						});
						document.addEventListener('date-filter-changed', function(e){
							if(e.detail.scope!=='feedback') return;
							var from=document.getElementById('feedbackDateFrom');
							if(!from){from=document.createElement('input');from.type='hidden';from.id='feedbackDateFrom';document.body.appendChild(from);}
							from.value=e.detail.from||'';
							var to=document.getElementById('feedbackDateTo');
							if(!to){to=document.createElement('input');to.type='hidden';to.id='feedbackDateTo';document.body.appendChild(to);}
							to.value=e.detail.to||'';
							sync();
						});
						var rat=document.getElementById('feedbackRatingFilter');
						if(rat) rat.addEventListener('change', sync);
						document.addEventListener('filters-reset', function(e){
							if(e.detail&&e.detail.scope&&e.detail.scope!=='feedback') return;
							var rat2=document.getElementById('feedbackRatingFilter');if(rat2) rat2.value='';
							sync();
						});
					})();
					</script>

					<?php
					$tableId = 'feedbackTable';
					$tableScope = 'feedback';
					$tablePageSize = 10;
					$tableClass = 'barcie-table-striped';
					$tableColumns = [
						['label' => '#',       'width' => '4%'],
						['label' => 'Guest',   'width' => '12%'],
						['label' => 'Room',    'width' => '15%'],
						['label' => 'Rating',  'width' => '8%'],
						['label' => 'Message'],
						['label' => 'Created', 'width' => '12%'],
					];
					include __DIR__ . '/../../Table/Table.php';
					?>
								<tr><td colspan="6" class="text-center">Loading...</td></tr>
					<?php $tableClose = true; include __DIR__ . '/../../Table/Table.php'; ?>

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
		const ratingFilter = document.getElementById('feedbackRatingFilter');
		const refreshBtn = document.getElementById('refreshFeedbackBtn');
		const exportBtn = document.getElementById('exportFeedbackBtn');
		const countEl = document.getElementById('feedbackCount');

		let allData = [];
		let filtered = [];

		async function fetchData(){
			setLoading();
			try {
				const res = await fetch(apiUrl, { cache: 'no-store' });
				const json = await res.json();
				if (!json.success) {
					console.error('API returned error:', json);
					tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Failed to load feedback: ' + (json.error || 'Unknown error') + '</td></tr>';
					if (countEl) countEl.innerText = '';
					return;
				}
				allData = json.data || json.feedback || [];
				// normalize created field
				allData = allData.map(r => Object.assign({ created_at: r.created_at || r.created || r.date || '' }, r));
				applyFilters();
			} catch (err) {
				console.error('Fetch error:', err);
				tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading feedback: ' + err.message + '</td></tr>';
				if (countEl) countEl.innerText = '';
			}
		}

		function setLoading(){
			tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Loading...</td></tr>';
			if (countEl) countEl.innerText = 'Loading...';
		}

		function applyFilters(){
			const searchInput = document.getElementById('feedbackSearch');
			const dateFrom = document.getElementById('feedbackDateFrom');
			const dateTo = document.getElementById('feedbackDateTo');
			const q = (searchInput?.value || '').toLowerCase().trim();
			const r = (ratingFilter?.value || '').trim();
			// status removed; all feedbacks are shown without approval filter
			let fromVal = dateFrom?.value || '';
			let toVal = dateTo?.value || '';
			if ((!fromVal && !toVal) && window.DateFilter && window.DateFilter['feedback']) {
				const vals = window.DateFilter['feedback'].getValues();
				fromVal = vals.from || '';
				toVal = vals.to || '';
			}
			const from = fromVal ? new Date(fromVal) : null;
			const to = toVal ? new Date(toVal) : null;

			filtered = allData.filter(item => {
				// search across message, name, room name
				const hay = ((item.message||'') + ' ' + (item.feedback_name||'') + ' ' + (item.room_name||'') + ' ' + (item.username||'')).toLowerCase();
				if (q && !hay.includes(q)) return false;
				if (r && String(item.rating) !== r) return false;
				// no approval_status filtering
				if (from || to) {
					const c = item.created_at ? new Date(item.created_at) : null;
					if (from && c && c < from) return false;
					if (to && c && c > (new Date(to).setHours(23,59,59,999))) return false;
				}
				return true;
			});

			renderAllRows();
		}

		// Expose for bridge events from reusable filter components.
		window.applyFeedbackFilters = applyFilters;

		function renderAllRows(){
			const total = filtered.length;
			if (total === 0) {
				tableBody.innerHTML = '<tr><td colspan="6" class="text-center">No feedback found</td></tr>';
			} else {
				tableBody.innerHTML = '';
				filtered.forEach((r, idx) => {
					const tr = document.createElement('tr');
					const stars = Number(r.rating) || 0;
					const starHtml = Array.from({length: stars}).map(()=>'<span class="feedback-star">★</span>').join('') + (stars===0?'<span class="text-muted">—</span>':'' );
					const msg = (r.message || '').replace(/</g,'&lt;').replace(/>/g,'&gt;');
					const guestName = r.username || 'Anonymous Guest';
					const roomInfo = r.room_name ? r.room_name + ' <small class="text-muted">(' + (r.room_type||'') + ')</small>' : '<span class="text-muted">—</span>';
					tr.innerHTML =
						'<td>' + (idx + 1) + '</td>' +
						'<td><small>' + guestName + '</small></td>' +
						'<td><small>' + roomInfo + '</small></td>' +
						'<td>' + starHtml + '</td>' +
						'<td class="feedback-message"><small>' + msg + '</small></td>' +
						'<td><small>' + (r.created_at || '') + '</small></td>';
					tableBody.appendChild(tr);
				});
			}

			if (countEl) countEl.innerText = total + ' feedback';
			// Let unified pagination handle paging
			if (window.BarcieTable && window.BarcieTable.feedback) {
				window.BarcieTable.feedback.refresh();
			}
		}

		function exportCSV(){
			const rows = [['id','guest','room','rating','message','created_at']];
			filtered.forEach(r => rows.push([
				r.id||'', 
				r.username||'', 
				r.room_name||'', 
				r.rating||'', 
				(r.message||'').replace(/\r?\n/g,' '), 
				r.created_at||''
			]));
			const csv = rows.map(r => r.map(c=> '"'+String(c).replace(/"/g,'""')+'"').join(',')).join('\n');
			const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
			const url = URL.createObjectURL(blob);
			const a = document.createElement('a'); a.href = url; a.download = 'feedback_export.csv'; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
		}

		// Event wiring
		if (ratingFilter) ratingFilter.addEventListener('change', ()=> applyFilters());
		// status filter removed
		if (refreshBtn) refreshBtn.addEventListener('click', ()=> fetchData());
		if (exportBtn) exportBtn.addEventListener('click', ()=> exportCSV());

		// initial load
		document.addEventListener('DOMContentLoaded', ()=> fetchData());

	})();
</script>

