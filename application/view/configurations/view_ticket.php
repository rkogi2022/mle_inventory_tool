<!-- Styles -->
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
<link href="<?php echo URL; ?>css/tables.css" rel="stylesheet" />

<main>
<div class="container-fluid px-4">
    <h3 class="mt-4">
        <?php echo $is_support ? 'All Support Tickets' : 'My Tickets'; ?>
    </h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
        <li class="breadcrumb-item">Configurations</li>
        <li class="breadcrumb-item">Tickets</li>
    </ol>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-ticket-alt me-1"></i> Tickets</span>
            <a href="<?= URL; ?>ticket" class="btn add-btn btn-sm">
                <i class="fas fa-plus-circle"></i> New Ticket
            </a>
        </div>

        <div class="card-body table-responsive">
            <table id="ticketsTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>Ticket #</th>
                        <th>Subject</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Requester</th>
                        <?php if ($is_support): ?>
                            <th>Assigned To</th>
                        <?php endif; ?>
                        <th>Created</th>
                        <?php if ($is_support): ?>
                            <th>Feedback</th>
                        <?php endif; ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($tickets)): ?>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td>
                                <strong>#<?= htmlspecialchars($ticket['ticket_number']) ?></strong>
                                <br><small class="text-muted">ID: <?= $ticket['id'] ?></small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($ticket['subject']) ?></strong>
                                <?php if (!empty($ticket['subcategory'])): ?>
                                    <br><small class="text-muted">
                                        <i class="fas fa-tag"></i> <?= htmlspecialchars($ticket['subcategory']) ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= ucfirst($ticket['category']) ?></span>
                            </td>
                            <td>
                                <span class="badge priority-<?= strtolower($ticket['priority']) ?>">
                                    <?= strtoupper($ticket['priority']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($is_support): ?>
                                    <select class="form-select form-select-sm status-select status-<?= strtolower($ticket['status']) ?>" 
                                            onchange="updateStatus(<?= $ticket['id'] ?>, this.value, this)">
                                        <option value="open" <?= $ticket['status'] == 'open' ? 'selected' : '' ?>>Open</option>
                                        <option value="ongoing" <?= $ticket['status'] == 'ongoing' ? 'selected' : '' ?>>Ongoing</option>
                                        <option value="resolved" <?= $ticket['status'] == 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                        <option value="closed" <?= $ticket['status'] == 'closed' ? 'selected' : '' ?>>Closed</option>
                                    </select>
                                <?php else: ?>
                                    <span class="badge status-<?= strtolower($ticket['status']) ?>">
                                        <?= ucfirst($ticket['status']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <i class="fas fa-user-circle" style="color: #e600a0;"></i>
                                <?= htmlspecialchars($ticket['requester_name']) ?>
                                <br><small class="text-muted"><?= htmlspecialchars($ticket['requester_email']) ?></small>
                                <?php if (!empty($ticket['department_name'])): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($ticket['department_name']) ?></small>
                                <?php endif; ?>
                            </td>
                            
                            <?php if ($is_support): ?>
                            <td>
                                <select class="form-select form-select-sm" style="width:130px;" 
                                        onchange="assignTicket(<?= $ticket['id'] ?>, this.value)">
                                    <option value="">Unassigned</option>
                                    <?php foreach ($staff_list as $staff): ?>
                                        <option value="<?= $staff['id'] ?>" <?= $ticket['assigned_to'] == $staff['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($staff['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <?php endif; ?>
                            
                            <td>
                                <?= $ticket['formatted_date'] ?>
                                <br><small class="text-muted"><?= $ticket['formatted_time'] ?></small>
                            </td>
                            
                            <?php if ($is_support): ?>
                            <td>
                                <?php if ($ticket['feedback']): ?>
                                    <span class="feedback-indicator feedback-yes"></span>
                                    <span class="rating-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fa<?= $i <= $ticket['feedback']['rating'] ? 's' : 'r' ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </span>
                                    <br><small><?= ucwords(str_replace('_', ' ', $ticket['feedback']['satisfaction'] ?? '')) ?></small>
                                    <?php if (!empty($ticket['feedback']['comments'])): ?>
                                        <i class="fas fa-comment ms-1" title="<?= htmlspecialchars($ticket['feedback']['comments']) ?>"></i>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="feedback-indicator feedback-no"></span>
                                    <span class="text-muted">None</span>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                            
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" 
                                            onclick="viewTicketDetails(<?= $ticket['id'] ?>)"
                                            title="View & Manage Ticket">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button type="button" class="btn btn-outline-info" 
                                            onclick="viewHistory(<?= $ticket['id'] ?>)"
                                            title="Status History">
                                        <i class="fas fa-history"></i> History
                                    </button>
                                </div>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?= $is_support ? 10 : 7 ?>" class="text-center py-4">
                            <i class="fas fa-ticket-alt fa-2x text-muted mb-3"></i>
                            <p class="text-muted">No tickets found.</p>
                            <a href="<?= URL; ?>ticket" class="btn btn-primary btn-sm">
                                Create Your First Ticket
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</main>

<!-- Status History Modal (unchanged) -->
<div id="historyModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-history"></i> Status History - Ticket #<span id="historyTicketNumber"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="historyContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- NEW: Ticket Management Modal - This is where support staff view & manage tickets -->
<div id="ticketManageModal" class="modal fade" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-ticket-alt"></i> Manage Ticket #<span id="manageTicketNumber"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="manageTicketContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                    <p>Loading ticket details...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Initialize DataTable
    const table = document.getElementById('ticketsTable');
    if (table) {
        new simpleDatatables.DataTable(table, {
            perPage: 25,
            perPageSelect: [10, 25, 50, 100],
            labels: {
                placeholder: "Search tickets...",
                perPage: "Show {select} entries",
                noRows: "No tickets found"
            }
        });
    }
});

// UPDATED: View ticket details - NOW OPENS MANAGEMENT MODAL for support staff
// function viewTicketDetails(id) {
//     <?php if ($is_support): ?>
//         // Support staff: Open management modal
//         loadTicketForManagement(id);
//     <?php else: ?>
//         // Regular users: Redirect to read-only view
//         window.location.href = '<?= URL ?>ticket/view/' + id;
//     <?php endif; ?>
// }

// View ticket details - ALWAYS OPEN MODAL for anyone on this page
function viewTicketDetails(id) {
    loadTicketForManagement(id);
}

// Render the ticket management interface inside modal
function renderTicketManagementModal(data) {
    const ticket = data.ticket;
    const staff_list = data.staff_list || [];
    const current_user_id = data.current_user_id;
    
    // Format date
    let createdDate = new Date(ticket.created_at);
    let formattedDate = createdDate.toLocaleDateString('en-US', { 
        month: 'short', day: 'numeric', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
    
    // Build assignment dropdown options
    let assignmentOptions = '<option value="">-- Unassigned --</option>';
    staff_list.forEach(staff => {
        assignmentOptions += `<option value="${staff.id}" ${ticket.assigned_to == staff.id ? 'selected' : ''}>
            ${escapeHtml(staff.name)}
        </option>`;
    });
    
    let html = `
        <div class="container-fluid">
            <!-- Status Banner -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                        <div>
                            <h4 class="mb-0">${escapeHtml(ticket.subject)}</h4>
                            <small class="text-muted">Created ${formattedDate} by ${escapeHtml(ticket.requester_name)}</small>
                        </div>
                        <span class="badge status-${ticket.status.toLowerCase()} p-3" style="font-size: 1rem;">
                            ${ticket.status.toUpperCase()}
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Main Content - 8 columns -->
                <div class="col-lg-8">
                    <!-- Requester Info -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <i class="fas fa-user"></i> Requester Information
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-circle fa-3x" style="color: #e600a0;"></i>
                                <div class="ms-3">
                                    <strong>${escapeHtml(ticket.requester_name)}</strong><br>
                                    <small>${escapeHtml(ticket.requester_email)}</small>
                                    ${ticket.department_name ? `<br><small class="text-muted">${escapeHtml(ticket.department_name)}</small>` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <i class="fas fa-file-alt"></i> Issue Description
                        </div>
                        <div class="card-body">
                            <div style="white-space: pre-wrap; background: #f8f9fa; padding: 15px; border-radius: 5px;">
                                ${escapeHtml(ticket.description).replace(/\n/g, '<br>')}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Resolution Form (if not resolved/closed) -->
                    ${ticket.status !== 'resolved' && ticket.status !== 'closed' ? `
                    <div class="card mb-3 border-success">
                        <div class="card-header bg-success text-white">
                            <i class="fas fa-check-circle"></i> Mark as Resolved
                        </div>
                        <div class="card-body">
                            <form id="resolutionForm_${ticket.id}">
                                <input type="hidden" name="ticket_id" value="${ticket.id}">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Resolution Notes</label>
                                    <textarea class="form-control" name="notes" rows="4" 
                                              placeholder="Describe how you resolved this issue..."></textarea>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="assignToMe_${ticket.id}" checked>
                                    <label class="form-check-label" for="assignToMe_${ticket.id}">
                                        Assign to me before resolving
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check-circle"></i> Resolve Ticket
                                </button>
                            </form>
                        </div>
                    </div>
                    ` : ticket.resolved_by ? `
                    <div class="card mb-3 border-info">
                        <div class="card-header bg-info text-white">
                            <i class="fas fa-info-circle"></i> Resolution Details
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Resolved By:</strong> ${escapeHtml(ticket.resolver_name || 'Unknown')}
                                </div>
                                <div class="col-md-6">
                                    <strong>Resolved At:</strong> ${new Date(ticket.resolved_at).toLocaleString()}
                                </div>
                            </div>
                        </div>
                    </div>
                    ` : ''}
                    
                    <!-- Attachment -->
                    ${ticket.attachment_name ? `
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <i class="fas fa-paperclip"></i> Attachment
                        </div>
                        <div class="card-body">
                            <a href="<?= URL ?>ticket/download/${ticket.id}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-download"></i> ${escapeHtml(ticket.attachment_name)}
                            </a>
                        </div>
                    </div>
                    ` : ''}
                </div>
                
                <!-- Sidebar - 4 columns -->
                <div class="col-lg-4">
                    <!-- Quick Stats -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <i class="fas fa-info-circle"></i> Ticket Info
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <strong>Priority:</strong><br>
                                    <span class="badge priority-${ticket.priority.toLowerCase()} p-2">
                                        ${ticket.priority.toUpperCase()}
                                    </span>
                                </li>
                                <li class="mb-2">
                                    <strong>Category:</strong><br>
                                    ${ticket.category.charAt(0).toUpperCase() + ticket.category.slice(1)}
                                    ${ticket.subcategory ? `<br><small class="text-muted">${escapeHtml(ticket.subcategory)}</small>` : ''}
                                </li>
                                <li class="mb-2">
                                    <strong>Assigned To:</strong><br>
                                    <span id="assignedDisplay_${ticket.id}">
                                        ${ticket.assigned_name ? 
                                            `<i class="fas fa-user-check text-success"></i> ${escapeHtml(ticket.assigned_name)}` : 
                                            '<span class="text-muted"><i class="fas fa-user-slash"></i> Unassigned</span>'}
                                    </span>
                                </li>
                                <li>
                                    <strong>Last Updated:</strong><br>
                                    ${new Date(ticket.updated_at).toLocaleString()}
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Status Update -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <i class="fas fa-exchange-alt"></i> Update Status
                        </div>
                        <div class="card-body">
                            <form id="statusForm_${ticket.id}">
                                <input type="hidden" name="ticket_id" value="${ticket.id}">
                                <div class="mb-2">
                                    <select class="form-select" name="status">
                                        <option value="open" ${ticket.status === 'open' ? 'selected' : ''}>Open</option>
                                        <option value="ongoing" ${ticket.status === 'ongoing' ? 'selected' : ''}>Ongoing</option>
                                        <option value="resolved" ${ticket.status === 'resolved' ? 'selected' : ''}>Resolved</option>
                                        <option value="closed" ${ticket.status === 'closed' ? 'selected' : ''}>Closed</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <textarea class="form-control" name="notes" rows="2" 
                                              placeholder="Add notes (optional)"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-sync-alt"></i> Update Status
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Assignment -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <i class="fas fa-user-plus"></i> Assignment
                        </div>
                        <div class="card-body">
                            <form id="assignForm_${ticket.id}">
                                <input type="hidden" name="ticket_id" value="${ticket.id}">
                                <div class="mb-2">
                                    <select class="form-select" name="assigned_to">
                                        ${assignmentOptions}
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-info w-100 text-white">
                                    <i class="fas fa-user-check"></i> Update Assignment
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('manageTicketContent').innerHTML = html;
    
    // Attach event handlers
    attachTicketEventHandlers(ticket.id);
}

// Attach event handlers to forms in the modal
function attachTicketEventHandlers(ticketId) {
    // Status update form
    let statusForm = document.getElementById(`statusForm_${ticketId}`);
    if (statusForm) {
        statusForm.addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            
            fetch('<?= URL ?>ticket/updateStatusAjax', {
                method: 'POST',
                body: new URLSearchParams(formData)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success!', 'Status updated', 'success');
                    // Reload ticket details
                    loadTicketForManagement(ticketId);
                    // Reload page after 1.5 seconds to update table
                    setTimeout(() => location.reload(), 1500);
                } else {
                    Swal.fire('Error!', data.message || 'Update failed', 'error');
                }
            })
            .catch(() => Swal.fire('Error!', 'Network error', 'error'));
        });
    }
    
    // Assignment form
    let assignForm = document.getElementById(`assignForm_${ticketId}`);
    if (assignForm) {
        assignForm.addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            
            fetch('<?= URL ?>ticket/assignTicketAjax', {
                method: 'POST',
                body: new URLSearchParams(formData)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success!', 'Assignment updated', 'success');
                    // Reload ticket details
                    loadTicketForManagement(ticketId);
                    // Reload page after 1.5 seconds to update table
                    setTimeout(() => location.reload(), 1500);
                } else {
                    Swal.fire('Error!', data.message || 'Assignment failed', 'error');
                }
            })
            .catch(() => Swal.fire('Error!', 'Network error', 'error'));
        });
    }
    
    // Resolution form
    let resolutionForm = document.getElementById(`resolutionForm_${ticketId}`);
    if (resolutionForm) {
        resolutionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            let assignToMe = document.getElementById(`assignToMe_${ticketId}`).checked;
            
            // Show loading
            Swal.fire({
                title: 'Resolving Ticket...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            
            // First assign to self if checked
            let assignPromise = Promise.resolve();
            if (assignToMe) {
                assignPromise = fetch('<?= URL ?>ticket/assignTicketAjax', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `ticket_id=${ticketId}&assigned_to=<?= $_SESSION['user_id'] ?? 0 ?>`
                });
            }
            
            // Then update status to resolved
            assignPromise.then(() => {
                return fetch('<?= URL ?>ticket/updateStatusAjax', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `ticket_id=${ticketId}&status=resolved&notes=${encodeURIComponent(formData.get('notes') || 'Issue has been resolved.')}`
                });
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Ticket Resolved!',
                        text: 'The ticket has been marked as resolved.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    // Reload ticket details and page
                    loadTicketForManagement(ticketId);
                    setTimeout(() => location.reload(), 2000);
                } else {
                    Swal.fire('Error!', data.message || 'Failed to resolve ticket', 'error');
                }
            })
            .catch(() => Swal.fire('Error!', 'Network error', 'error'));
        });
    }
}

// Update loadTicketForManagement to handle the response properly
function loadTicketForManagement(ticketId) {
    // Show modal with loading spinner
    let modal = new bootstrap.Modal(document.getElementById('ticketManageModal'));
    document.getElementById('manageTicketNumber').textContent = ticketId;
    modal.show();
    
    // Fetch ticket details
    fetch('<?= URL ?>ticket/getTicketDetailsAjax?ticket_id=' + ticketId)
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            renderTicketManagementModal(data);
        } else {
            document.getElementById('manageTicketContent').innerHTML = 
                '<div class="alert alert-danger m-3">' + (data.message || 'Failed to load ticket details.') + '</div>';
        }
    })
    .catch(error => {
        document.getElementById('manageTicketContent').innerHTML = 
            '<div class="alert alert-danger m-3">Network error. Please try again.</div>';
    });
}

// Helper function to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    let div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Update ticket status (from dropdown in table)
function updateStatus(ticketId, status, selectEl) {
    Swal.fire({
        title: 'Update Status',
        html: '<textarea id="notes" class="form-control" rows="3" placeholder="Add notes (optional)"></textarea>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Update',
        cancelButtonText: 'Cancel',
        preConfirm: () => document.getElementById('notes').value
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('<?= URL ?>ticket/updateStatusAjax', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'ticket_id=' + ticketId + '&status=' + status + '&notes=' + encodeURIComponent(result.value || '')
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success!', 'Status updated', 'success');
                    selectEl.className = 'form-select form-select-sm status-select status-' + status;
                } else {
                    Swal.fire('Error!', data.message || 'Update failed', 'error');
                }
            })
            .catch(() => Swal.fire('Error!', 'Network error', 'error'));
        }
    });
}

// Assign ticket (from dropdown in table)
function assignTicket(ticketId, assignedTo) {
    fetch('<?= URL ?>ticket/assignTicketAjax', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'ticket_id=' + ticketId + '&assigned_to=' + assignedTo
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Success!', 'Ticket assigned', 'success');
        } else {
            Swal.fire('Error!', data.message || 'Assignment failed', 'error');
        }
    })
    .catch(() => Swal.fire('Error!', 'Network error', 'error'));
}

// View status history (unchanged)
function viewHistory(ticketId) {
    let modal = new bootstrap.Modal(document.getElementById('historyModal'));
    modal.show();
    
    fetch('<?= URL ?>ticket/getStatusHistoryAjax?ticket_id=' + ticketId)
    .then(res => res.json())
    .then(data => {
        let html = '';
        if (data.success && data.history.length > 0) {
            data.history.forEach(item => {
                html += `<div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>${item.changed_by_name}</strong>
                                <span class="text-muted mx-2">changed status from</span>
                                <span class="badge bg-secondary">${item.old_status || 'N/A'}</span>
                                <span class="text-muted mx-2">to</span>
                                <span class="badge status-${item.new_status}">${item.new_status.toUpperCase()}</span>
                            </div>
                            <small class="text-muted">${item.formatted_date}</small>
                        </div>
                        ${item.notes ? `<p class="mb-0 mt-2"><small><strong>Notes:</strong> ${item.notes}</small></p>` : ''}
                    </div>
                </div>`;
            });
        } else {
            html = '<div class="alert alert-info mb-0">No status history found.</div>';
        }
        document.getElementById('historyContent').innerHTML = html;
    })
    .catch(() => {
        document.getElementById('historyContent').innerHTML = '<div class="alert alert-danger">Failed to load history.</div>';
    });
}
</script>

<style>
/* Priority badges */
.priority-low { background-color: #d4edda; color: #155724; }
.priority-medium { background-color: #fff3cd; color: #856404; }
.priority-high { background-color: #f8d7da; color: #721c24; }
.priority-critical { background-color: #dc3545; color: white; }

/* Status badges */
.status-open { background-color: #d4edda; color: #155724; }
.status-ongoing { background-color: #cce5ff; color: #004085; }
.status-resolved { background-color: #d1ecf1; color: #0c5460; }
.status-closed { background-color: #e2e3e5; color: #383d41; }

/* Status select */
.status-select {
    width: 120px;
    padding: 4px 8px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    border: 1px solid #ddd;
}
.status-select.open { background-color: #d4edda; color: #155724; }
.status-select.ongoing { background-color: #cce5ff; color: #004085; }
.status-select.resolved { background-color: #d1ecf1; color: #0c5460; }
.status-select.closed { background-color: #e2e3e5; color: #383d41; }

/* Feedback indicator */
.feedback-indicator {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 5px;
}
.feedback-yes { background-color: #28a745; }
.feedback-no { background-color: #dc3545; }

/* Rating stars */
.rating-stars { color: #ffc107; font-size: 11px; }

/* DataTable customization */
.dataTable-wrapper.no-header .dataTable-container {
    border-top: 1px solid #d9d9d9;
}
.dataTable-selector {
    padding: 0.375rem 1.75rem 0.375rem 0.75rem;
}
.dataTable-input {
    padding: 0.375rem 0.75rem;
}

/* Modal customization */
.modal-xl {
    max-width: 95%;
}
@media (min-width: 1200px) {
    .modal-xl {
        max-width: 1140px;
    }
}
</style>