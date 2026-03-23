<?php
if (!isset($conn) || !($conn instanceof mysqli)) {
    require_once __DIR__ . '/../../../database/db_connect.php';
}

require_once __DIR__ . '/../../../database/modules/discount_rules.php';

$discountRules = discount_get_rules($conn, true);
$discountKeywords = [];
$discountIdTypeOptions = discount_get_id_type_options_from_rules($discountRules);

foreach ($discountRules as $rule) {
    $discountKeywords[$rule['code']] = $rule['keywords'];
}
?>

<div class="space-y-4">
    <h3 class="text-md font-semibold text-gray-900">Discount Application (Optional)</h3>
    <p class="text-sm text-gray-600">You may proceed with booking without submitting an ID. Upload details only if you
        want to request a discount.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Discount Type</label>
            <select id="discount_type" name="discount_type" class="w-full border rounded px-3 py-2">
                <option value="none">No discount</option>
                <?php foreach ($discountRules as $rule): ?>
                    <option value="<?php echo htmlspecialchars($rule['code']); ?>">
                        <?php echo htmlspecialchars($rule['label'] . ' (' . rtrim(rtrim(number_format((float) $rule['percentage'], 2, '.', ''), '0'), '.') . '%)'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Discount ID Type Reference (optional)</label>
            <select id="discount_id_type_reference" name="discount_id_type_reference"
                class="w-full border rounded px-3 py-2">
                <option value="">Select ID type (optional)</option>
                <?php foreach ($discountIdTypeOptions as $idTypeCode => $idTypeLabel): ?>
                    <option value="<?php echo htmlspecialchars($idTypeCode); ?>">
                        <?php echo htmlspecialchars($idTypeLabel); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div id="discount_details_wrapper" class="hidden">
        <label id="discount_details_label" for="discount_details"
            class="block text-sm font-medium text-gray-700 mb-1">Discount Details</label>
        <textarea id="discount_details" name="discount_details" rows="2" class="w-full border rounded px-3 py-2"
            placeholder="Provide your discount details"></textarea>
    </div>

    <div id="discount_id_upload_wrapper" class="hidden">
        <label for="discount_proof" class="block text-sm font-medium text-gray-700 mb-1">Upload Discount
            ID/Proof</label>
        <input type="file" id="discount_proof" name="discount_proof" accept=".jpg,.jpeg,.png,.pdf"
            class="w-full border rounded px-3 py-2">
        <input type="hidden" id="discount_proof_base64" name="discount_proof_base64" value="">
        <input type="hidden" id="discount_proof_type" name="discount_proof_type" value="">
    </div>
</div>

<?php include __DIR__ . '/../Booking/image_crop_modal.php'; ?>

<script>
    window.DISCOUNT_KEYWORDS = <?php echo json_encode($discountKeywords, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;

    (function () {
        const discountType = document.getElementById('discount_type');
        const detailsWrapper = document.getElementById('discount_details_wrapper');
        const uploadWrapper = document.getElementById('discount_id_upload_wrapper');

        function toggleDiscountFields() {
            const hasDiscount = discountType && discountType.value && discountType.value !== 'none';
            if (detailsWrapper) {
                detailsWrapper.classList.toggle('hidden', !hasDiscount);
            }
            if (uploadWrapper) {
                uploadWrapper.classList.toggle('hidden', !hasDiscount);
            }
        }

        if (discountType) {
            discountType.addEventListener('change', toggleDiscountFields);
            toggleDiscountFields();
        }
    })();
</script>