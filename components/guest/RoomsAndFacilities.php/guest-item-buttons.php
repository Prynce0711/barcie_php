<?php  ?>
<script>
function setupItemButtons() {
  console.log("Setting up item buttons...");


  const viewDetailsBtns = document.querySelectorAll(".view-details-btn");
  console.log("Found view details buttons:", viewDetailsBtns.length);

  viewDetailsBtns.forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();
      const { itemId } = this.dataset;
      console.log("View details clicked for item:", itemId);
      showItemDetails(itemId);
    });
  });


  const bookNowBtns = document.querySelectorAll(".book-now-btn");
  console.log("Found book now buttons:", bookNowBtns.length);

  bookNowBtns.forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();
      const { itemId } = this.dataset;
      console.log("Book now clicked for item:", itemId);
      redirectToBooking(itemId);
    });
  });


  const cardImages = document.querySelectorAll(".card-image");
  console.log("Found card images:", cardImages.length);

  cardImages.forEach((cardImage) => {
    cardImage.addEventListener("click", function (e) {
      e.preventDefault();
      const { itemId } = this.dataset;
      const card = this.closest(".card");
      if (card) {
        const images = JSON.parse(card.dataset.images || "[]");
        const item = window.allItems.find((item) => item.id == itemId);
        if (item && images.length > 0) {
          openImageGallery(images, 0, item.name);
        }
      }
    });
  });

  console.log("Item buttons setup complete");
}

function showItemDetails(itemId) {
  const item = window.allItems.find((item) => item.id == itemId);

  if (!item) {
    showToast("Item details not found", "error");
    return;
  }


  let modal = document.getElementById("itemDetailsModal");
  if (!modal) {
    modal = createItemDetailsModal();
  }


  populateItemModal(modal, item);


  const bsModal = new bootstrap.Modal(modal);
  bsModal.show();
}


function createItemDetailsModal() {
  const modalHtml = `
    <div class="modal fade" id="itemDetailsModal" tabindex="-1" aria-labelledby="itemDetailsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="itemDetailsModalLabel">Room/Facility Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="itemDetailsBody">
            <!-- Content will be populated dynamically -->
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-success" id="modalBookNowBtn">Book Now</button>
          </div>
        </div>
      </div>
    </div>
  `;

  document.body.insertAdjacentHTML("beforeend", modalHtml);
  return document.getElementById("itemDetailsModal");
}



</script>
