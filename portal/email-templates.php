<?php
$page_title = "Email Templates | ErrandsCall Portal";
include('config/database.php');
include('includes/auth-check.php');
include('includes/header.php');
include('includes/sidebar.php');

if (!hasAccess(['admin', 'manager'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Email Templates</h5>
                        <button class="btn btn-light btn-sm" onclick="showAddTemplateModal()">
                            <i class="fas fa-plus mr-1"></i> Add Template
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Template Name</th>
                                        <th>Subject</th>
                                        <th>Variables</th>
                                        <th>Last Updated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="templatesTableBody">
                                    <!-- Templates will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                        <nav aria-label="Email templates pagination">
                            <ul class="pagination justify-content-center mb-0 mt-3" id="templatesPagination"></ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Template Modal -->
<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient text-white">
                <h5 class="modal-title" id="templateModalTitle">Add Email Template</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="templateForm">
                    <input type="hidden" id="templateId">
                    <div class="form-group">
                        <label>Template Name</label>
                        <input type="text" class="form-control" id="templateName" required>
                    </div>
                    <div class="form-group">
                        <label>Email Subject</label>
                        <input type="text" class="form-control" id="templateSubject" required>
                    </div>
                    <div class="form-group">
                        <label>Email Body</label>
                        <textarea class="form-control" id="templateBody" rows="10" required></textarea>
                        <small class="form-text text-muted">
                            Use variables like {customer_name}, {service_type}, {worker_name} etc.
                        </small>
                    </div>
                    <div class="form-group">
                        <label>Available Variables</label>
                        <div class="variables-list">
                            <span class="badge badge-light mr-1" onclick="insertVariable('{customer_name}')">{customer_name}</span>
                            <span class="badge badge-light mr-1" onclick="insertVariable('{service_type}')">{service_type}</span>
                            <span class="badge badge-light mr-1" onclick="insertVariable('{worker_name}')">{worker_name}</span>
                            <span class="badge badge-light mr-1" onclick="insertVariable('{vehicle_info}')">{vehicle_info}</span>
                            <span class="badge badge-light mr-1" onclick="insertVariable('{service_status}')">{service_status}</span>
                            <span class="badge badge-light mr-1" onclick="insertVariable('{update_text}')">{update_text}</span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-gradient" onclick="saveTemplate()">Save Template</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentEditId = null;

document.addEventListener('DOMContentLoaded', function() {
    loadTemplates();
});

function loadTemplates() {
    fetch('php/email-management.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayTemplates(data.templates);
            }
        })
        .catch(error => console.error('Error:', error));
}

let templatesPager = null;

function displayTemplates(templates) {
    const tbody = document.getElementById('templatesTableBody');

    if (templates.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4">
                    <i class="fas fa-envelope fa-2x text-muted mb-3"></i>
                    <p class="text-muted">No email templates found</p>
                    <button class="btn btn-gradient btn-sm" onclick="showAddTemplateModal()">
                        Create Your First Template
                    </button>
                </td>
            </tr>
        `;
        $('#templatesPagination').empty();
        return;
    }

    let html = '';
    templates.forEach(template => {
        html += `
            <tr>
                <td>${template.name}</td>
                <td>${template.subject}</td>
                <td>
                    <small class="text-muted">${template.variables || 'No variables'}</small>
                </td>
                <td>${formatDate(template.updated_at)}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="editTemplate(${template.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-info" onclick="previewTemplate(${template.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteTemplate(${template.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;

    if (!templatesPager) {
        templatesPager = createPagination({
            getItems: () => $('#templatesTableBody tr'),
            paginationContainer: '#templatesPagination',
            rowsPerPage: 10
        });
    }
    templatesPager.refresh();
}

function showAddTemplateModal() {
    currentEditId = null;
    document.getElementById('templateModalTitle').textContent = 'Add Email Template';
    document.getElementById('templateForm').reset();
    $('#templateModal').modal('show');
}

function editTemplate(templateId) {
    currentEditId = templateId;
    
    fetch(`php/email-management.php?id=${templateId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('templateModalTitle').textContent = 'Edit Email Template';
                document.getElementById('templateId').value = data.template.id;
                document.getElementById('templateName').value = data.template.name;
                document.getElementById('templateSubject').value = data.template.subject;
                document.getElementById('templateBody').value = data.template.body;
                $('#templateModal').modal('show');
            }
        })
        .catch(error => console.error('Error:', error));
}

function saveTemplate() {
    const formData = new FormData();
    if (currentEditId) {
        formData.append('id', currentEditId);
    }
    formData.append('name', document.getElementById('templateName').value);
    formData.append('subject', document.getElementById('templateSubject').value);
    formData.append('body', document.getElementById('templateBody').value);
    
    fetch('php/email-management.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#templateModal').modal('hide');
            showAlert('Template saved successfully!', 'success');
            loadTemplates();
        } else {
            showAlert('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showAlert('Error saving template', 'error');
        console.error('Error:', error);
    });
}

function deleteTemplate(templateId) {
    if (!confirm('Are you sure you want to delete this template?')) return;
    
    fetch('php/email-management.php', {
        method: 'DELETE',
        body: JSON.stringify({ id: templateId }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Template deleted successfully!', 'success');
            loadTemplates();
        } else {
            showAlert('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showAlert('Error deleting template', 'error');
        console.error('Error:', error);
    });
}

function insertVariable(variable) {
    const textarea = document.getElementById('templateBody');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    const before = text.substring(0, start);
    const after = text.substring(end, text.length);
    
    textarea.value = before + variable + after;
    textarea.selectionStart = textarea.selectionEnd = start + variable.length;
    textarea.focus();
}

function previewTemplate(templateId) {
    // Open template preview in new window
    window.open(`preview-template.php?id=${templateId}`, '_blank');
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString();
}

function showAlert(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    `;
    
    document.querySelector('.main-content').insertBefore(alert, document.querySelector('.container-fluid'));
    
    setTimeout(() => {
        alert.remove();
    }, 5000);
}
</script>

<style>
.variables-list .badge {
    cursor: pointer;
    transition: all 0.3s ease;
}

.variables-list .badge:hover {
    background-color: var(--primary-color) !important;
    color: white;
}
</style>

<?php include('includes/footer.php'); ?>