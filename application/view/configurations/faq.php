<!-- Styles -->
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
<link href="<?= URL; ?>css/tables.css" rel="stylesheet" />

<main>
<div class="container-fluid px-4">
    <h3 class="mt-4">FAQs</h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= URL; ?>home">Home</a></li>
        <li class="breadcrumb-item">Configurations</li>
        <li class="breadcrumb-item">FAQs</li>
    </ol>

    <!-- Search -->
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-search me-1"></i> Search FAQs</div>
        <div class="card-body">
            <form id="faqSearchForm">
                <div class="input-group">
                    <input type="text" id="searchQuestion" name="question" class="form-control" placeholder="Type your question..." required>
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </form>
            <div id="searchResults" class="mt-3"></div>
        </div>
    </div>

    <!-- FAQ Table -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-question-circle me-1"></i> FAQs</span>
            <div>
                <button class="btn btn-sm btn-primary" onclick="openAddModal()">Add FAQ</button>
                <a href="<?= URL ?>faq/analytics" class="btn btn-sm btn-info">View Analytics</a>
            </div>
        </div>
        <div class="card-body table-responsive">
            <table id="faqsTable" class="table table-bordered table-hover">
                <thead>
                    <tr><th>Question</th><th>Answer</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php if(!empty($faqs)): ?>
                        <?php foreach($faqs as $faq): ?>
                            <tr>
                                <td>
                                    <!-- CHANGED: Removed clickable classes and styles -->
                                    <?= htmlspecialchars($faq['question']); ?>
                                </td>
                                <td><?= htmlspecialchars($faq['answer']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning"
                                        onclick="openEditModal(<?= intval($faq['id']); ?>, '<?= htmlspecialchars(addslashes($faq['question'])); ?>', '<?= htmlspecialchars(addslashes($faq['answer'])); ?>')">Edit</button>
                                    <a href="<?= URL ?>faq/delete/<?= intval($faq['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
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

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // --- FAQ CLICK LOGGING ---
    async function logFaqClick(faqId, searchLogId = ''){
        console.log('🚀 logFaqClick function called with FAQ ID:', faqId, 'Search Log ID:', searchLogId);
        
        if(!faqId) {
            console.error('❌ No FAQ ID provided');
            return;
        }
        
        const fd = new FormData();
        fd.append('faq_id', faqId);
        
        // Add search_log_id if available
        if (searchLogId) {
            fd.append('search_log_id', searchLogId);
            console.log('📋 Added search_log_id to FormData:', searchLogId);
        }
        
        console.log('📋 FormData created with faq_id:', faqId);

        try {
            console.log('📤 Sending POST request to: <?= URL ?>faq/logClick');
            const res = await fetch('<?= URL ?>faq/logClick', { 
                method:'POST', 
                body:fd 
            });
            
            console.log('📥 Response status:', res.status);
            const data = await res.json();
            console.log('✅ FAQ click logged response:', data);
            
            if(data.status === 'error') {
                console.error('❌ Server returned error:', data.message);
                alert('Server Error: ' + data.message);
            } else if(data.status === 'logged') {
                console.log('✅ Successfully logged click for FAQ ID:', data.faq_id);
                // Show success notification
                showNotification('Click logged successfully!', 'success');
            }
        } catch(e) { 
            console.error('🌐 Fetch error:', e); 
            alert('Network Error: ' + e.message);
        }
    }

    // Notification function
    function showNotification(message, type = 'info') {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alert);
        
        setTimeout(() => {
            alert.remove();
        }, 3000);
    }

    // IMPROVED FAQ CLICK HANDLER - Now accepts search_log_id
    function handleFaqClick(e) {
        console.log('🖱️ === FAQ CLICK HANDLER TRIGGERED ===');
        
        // Check if we clicked on a FAQ question element OR inside it
        let faqElement = null;
        
        // First, check if the clicked element itself has the class
        if (e.target.classList.contains('faq-question')) {
            faqElement = e.target;
            console.log('✅ Direct click on faq-question element');
        } 
        // Check if any parent element has the class
        else {
            // Try to find parent with faq-question class
            let parent = e.target;
            while (parent && parent !== document) {
                if (parent.classList && parent.classList.contains('faq-question')) {
                    faqElement = parent;
                    console.log('✅ Found parent with faq-question class');
                    break;
                }
                parent = parent.parentElement;
            }
        }
        
        // If still not found, try closest() as fallback
        if (!faqElement) {
            faqElement = e.target.closest('.faq-question');
            if (faqElement) {
                console.log('✅ Found via closest()');
            }
        }
        
        if (!faqElement) {
            console.log('❌ Not a FAQ question click');
            return;
        }
        
        console.log('✅ FAQ element found:', faqElement);
        
        // Get FAQ ID
        let faqId = faqElement.getAttribute('data-faq-id') || faqElement.dataset.faqId;
        
        // Get search_log_id from the element
        let searchLogId = faqElement.getAttribute('data-search-log-id') || faqElement.dataset.searchLogId;
        
        console.log('FAQ ID found:', faqId);
        console.log('Search Log ID found:', searchLogId);
        
        if (!faqId) {
            console.error('❌ No FAQ ID found on element');
            return;
        }
        
        const faqIdNum = parseInt(faqId);
        console.log('Parsed FAQ ID:', faqIdNum);
        
        if (isNaN(faqIdNum)) {
            console.error('❌ FAQ ID is not a number:', faqId);
            return;
        }
        
        if (faqIdNum <= 0) {
            console.error('❌ FAQ ID must be > 0, got:', faqIdNum);
            return;
        }
        
        // Prevent default
        e.preventDefault();
        e.stopPropagation();
        
        console.log('📞 Calling logFaqClick with ID:', faqIdNum, 'and search_log_id:', searchLogId);
        logFaqClick(faqIdNum, searchLogId);
        
        // Also log to console which FAQ was clicked
        const questionText = faqElement.textContent.trim().substring(0, 50);
        console.log(`📝 Clicked FAQ: "${questionText}..."`);
        
        // Optional: Highlight the clicked FAQ briefly
        faqElement.style.backgroundColor = '#e8f4fd';
        setTimeout(() => {
            faqElement.style.backgroundColor = '';
        }, 500);
    }

    // Attach click handler
    document.addEventListener('click', handleFaqClick);

    // --- FAQ SEARCH ---
    document.getElementById('faqSearchForm').addEventListener('submit', async function(e){
        e.preventDefault();
        const question = document.getElementById('searchQuestion').value.trim();
        if(!question) {
            alert('Please type a question');
            return;
        }

        const formData = new FormData(this);
        console.log('🔍 Searching for question:', question);
        
        try {
            const res = await fetch('<?= URL ?>faq/search', { 
                method:'POST', 
                body:formData 
            });
            
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            
            const data = await res.json();
            console.log('📊 Search results:', data);

            const container = document.getElementById('searchResults');
            container.innerHTML = '';

            if(!data.answers || data.answers.length === 0){
                container.innerHTML = '<p class="text-muted">No match found.</p>';
                return;
            }

            // Get search_log_id from response
            const searchLogId = data.search_log_id || '';
            console.log('📝 Search Log ID from server:', searchLogId);

            data.answers.forEach(faq => {
                const div = document.createElement('div');
                div.classList.add('faq-item','mb-3','p-3','border','rounded','bg-light');
                
                // Make sure the FAQ ID is properly set as a data attribute
                const faqId = parseInt(faq.id);
                console.log('Adding FAQ with ID:', faqId, 'Question:', faq.question);
                
                // Create the FAQ question element with BOTH faq_id and search_log_id
                div.innerHTML = `
                    <div class="faq-question fw-bold text-primary" 
                         data-faq-id="${faqId}"
                         data-search-log-id="${searchLogId}"
                         style="cursor:pointer;text-decoration:underline;">
                        Q: ${faq.question}
                    </div>
                    <div class="faq-answer mt-2 text-secondary">
                        <strong>A:</strong> ${faq.answer}
                    </div>
                    <small class="text-muted d-block mt-1">ID: ${faqId} | Relevance: ${faq.relevance || 'N/A'}</small>
                `;
                container.appendChild(div);
            });
            
            console.log(`✅ Added ${data.answers.length} search results to DOM with search_log_id: ${searchLogId}`);
            
        } catch (error) {
            console.error('❌ Search error:', error);
            alert('Search failed: ' + error.message);
        }
    });

});
</script>