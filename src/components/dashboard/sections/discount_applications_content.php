<?php
// Discount Applications Content
// Fetch discount applications (if you have this table)
$discount_query = "SELECT da.*, b.id as booking_id, b.details as booking_details 
                 FROM discount_applications da 
                 LEFT JOIN bookings b ON da.booking_id = b.id 
                 ORDER BY da.created_at DESC";
$discount_result = $conn->query($discount_query);

if ($discount_result && $discount_result->num_rows > 0):
  while ($discount = $discount_result->fetch_assoc()):
    $status_class = $discount['status'] === 'approved' ? 'success' : ($discount['status'] === 'rejected' ? 'danger' : 'warning');
    ?>
    <tr>
      <td>
        <div class="d-flex align-items-center">
          <div class="avatar-circle me-2 bg-secondary text-white">
            <i class="fas fa-user"></i>
          </div>
          <div>
            <div class="fw-bold"><?= htmlspecialchars($discount['guest_name'] ?? 'Guest') ?></div>
            <small class="text-muted">Booking #<?= $discount['booking_id'] ?></small>
          </div>
        </div>
      </td>
      <td>
        <span class="badge bg-info px-3 py-2">
          <?= htmlspecialchars($discount['discount_type']) ?>
        </span>
      </td>
      <td>
        <strong class="text-primary"><?= $discount['discount_percentage'] ?>%</strong>
      </td>
      <td>
        <?php if ($discount['supporting_document']): ?>
          <a href="<?= htmlspecialchars($discount['supporting_document']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-file-alt me-1"></i>View Document
          </a>
        <?php else: ?>
          <span class="text-muted">No document</span>
        <?php endif; ?>
      </td>
      <td>
        <div class="text-end">
          <strong>₱<?= number_format($discount['original_amount'], 2) ?></strong>
        </div>
      </td>
      <td>
        <div class="text-end">
          <strong class="text-success">₱<?= number_format($discount['discounted_amount'], 2) ?></strong>
          <br>
          <small class="text-muted">Saved: ₱<?= number_format($discount['original_amount'] - $discount['discounted_amount'], 2) ?></small>
        </div>
      </td>
      <td>
        <span class="badge bg-<?= $status_class ?> px-3 py-2">
          <?= ucfirst($discount['status']) ?>
        </span>
      </td>
      <td>
        <div class="text-nowrap">
          <?= date('M j, Y', strtotime($discount['created_at'])) ?>
          <br>
          <small class="text-muted"><?= date('g:i A', strtotime($discount['created_at'])) ?></small>
        </div>
      </td>
      <td>
        <?php if ($discount['status'] === 'pending'): ?>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-success" onclick="processDiscount(<?= $discount['id'] ?>, 'approved')" title="Approve">
              <i class="fas fa-check"></i>
            </button>
            <button class="btn btn-danger" onclick="processDiscount(<?= $discount['id'] ?>, 'rejected')" title="Reject">
              <i class="fas fa-times"></i>
            </button>
          </div>
        <?php else: ?>
          <span class="text-muted">Processed</span>
        <?php endif; ?>
      </td>
    </tr>
  <?php endwhile; ?>
<?php else: ?>
  <tr>
    <td colspan="9" class="text-center text-muted py-4">
      <i class="fas fa-percent fa-3x mb-3 opacity-25"></i>
      <h6>No Discount Applications</h6>
      <p class="mb-0">Discount requests from guests will appear here</p>
    </td>
  </tr>
<?php endif; ?>