<!-- Styles -->
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
<link href="<?php echo URL; ?>css/tables.css" rel="stylesheet" />

<main>
<div class="container-fluid px-4">
    <h3 class="mt-4">FAQs</h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
        <li class="breadcrumb-item">Configurations</li>
        <li class="breadcrumb-item">FAQs</li>
    </ol>

    <div class="card mb-4">
        <div class="card-body">Here is the list of frequently asked questions.</div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-question-circle me-1"></i> FAQs</span>
            <button class="btn btn-sm btn-primary" onclick="openAddModal()">Add FAQ</button>
        </div>

        <div class="card-body table-responsive">
            <table id="faqsTable">
                <thead>
                    <tr>
                        <th>Question</th>
                        <th>Answer</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($faqs)): ?>
                        <?php foreach ($faqs as $faq): ?>
                            <tr>
                                <td><?= htmlspecialchars($faq['question']); ?></td>
                                <td><?= htmlspecialchars($faq['answer']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning"
                                            onclick="openEditModal(<?= $faq['id']; ?>, '<?= htmlspecialchars(addslashes($faq['question'])); ?>', '<?= htmlspecialchars(addslashes($faq['answer'])); ?>')">
                                            Edit
                                    </button>
                                    <a href="<?= URL ?>faq/delete/<?= $faq['id']; ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Are you sure you want to delete this FAQ?')">
                                       Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center text-muted">No FAQs found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</main>

<!-- ADD FAQ MODAL -->
<div class="modal fade" id="addFaqModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="addFaqForm">
        <div class="modal-header">
          <h5 class="modal-title">Add FAQ</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Question</label>
            <input type="text" name="question" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Answer</label>
            <textarea name="answer" class="form-control" rows="3" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Save</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- EDIT FAQ MODAL -->
<div class="modal fade" id="editFaqModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editFaqForm">
        <div class="modal-header">
          <h5 class="modal-title">Edit FAQ</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="edit_id">
          <div class="mb-3">
            <label>Question</label>
            <input type="text" name="question" id="edit_question" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Answer</label>
            <textarea name="answer" id="edit_answer" class="form-control" rows="3" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-warning">Update</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

<script>
const addModal = new bootstrap.Modal(document.getElementById('addFaqModal'));
const editModal = new bootstrap.Modal(document.getElementById('editFaqModal'));

function openAddModal() {
    document.getElementById('addFaqForm').reset();
    addModal.show();
}

function openEditModal(id, question, answer) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_question').value = question;
    document.getElementById('edit_answer').value = answer;
    editModal.show();
}

// === ADD FAQ ===
document.getElementById('addFaqForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const response = await fetch('<?= URL ?>faq/add', {
        method: 'POST',
        body: formData
    });
    const result = await response.json();
    alert(result.message);
    if (result.status === 'success') location.reload();
});

// === EDIT FAQ ===
document.getElementById('editFaqForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const response = await fetch('<?= URL ?>faq/update', {
        method: 'POST',
        body: formData
    });
    const result = await response.json();
    alert(result.message);
    if (result.status === 'success') location.reload();
});
</script>
