<style>
    /* Modern UI Styles for Grading System */
    .grading-wrapper {
        background: #f8f9fc;
        border-radius: 12px;
        padding: 1.5rem;
        margin-top: 2rem;
        border: 1px solid #e3e6f0;
    }

    .grading-section-title {
        color: #4e73df;
        font-weight: 700;
        font-size: 1.25rem;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .grading-section-subtitle {
        color: #858796;
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
    }

    .gs-card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        border: none;
        height: 100%;
        overflow: hidden;
        transition: transform 0.2s;
    }
    
    .gs-card:hover {
        /* transform: translateY(-2px); */
    }

    .gs-card-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e3e6f0;
        font-weight: 700;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .gs-header-default {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        color: white;
    }

    .gs-header-edit {
        background: #fff;
        color: #2c3e50;
    }
    
    .gs-card-body {
        padding: 0;
    }

    /* Table Styles */
    .gs-table {
        margin-bottom: 0;
        width: 100%;
    }
    
    .gs-table th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.75rem 1rem;
        background: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
        color: #858796;
    }
    
    .gs-table td {
        padding: 0.75rem 1rem;
        vertical-align: middle;
        font-size: 0.9rem;
        border-bottom: 1px solid #e3e6f0;
    }

    .gs-table tr:last-child td {
        border-bottom: none;
    }

    /* Badges */
    .grade-badge {
        padding: 0.35em 0.8em;
        border-radius: 50rem;
        font-weight: 700;
        font-size: 0.85em;
        min-width: 40px;
        display: inline-block;
        text-align: center;
    }
    
    .badge-A-plus, .badge-A { background: #d1fae5; color: #065f46; }
    .badge-A-minus, .badge-B { background: #dbeafe; color: #1e40af; }
    .badge-C { background: #fef3c7; color: #92400e; }
    .badge-D { background: #fee2e2; color: #b91c1c; }
    .badge-F { background: #fef2f2; color: #991b1b; border: 1px solid #fee2e2; }

    /* Inputs */
    .gs-input {
        border: 1px solid #e3e6f0;
        border-radius: 6px;
        padding: 0.4rem 0.6rem;
        font-size: 0.9rem;
        width: 100%;
        transition: all 0.2s;
        color: #495057 !important; /* Force dark text */
        background-color: #fff !important; /* Force white background */
    }
    
    .gs-input:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        outline: none;
    }

    .gs-btn-add {
        background: #4e73df;
        color: white;
        border: none;
        padding: 0.4rem 1rem;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .gs-btn-add:hover {
        background: #2e59d9;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    .gs-btn-delete {
        color: #e74a3b;
        background: rgba(231, 74, 59, 0.1);
        border: none;
        width: 28px;
        height: 28px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .gs-btn-delete:hover {
        background: #e74a3b;
        color: white;
    }
    
    .calc-range {
        font-family: 'Courier New', monospace;
        background: #f1f5f9;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        color: #475569;
        font-weight: 600;
        font-size: 0.85rem;
    }
</style>

<div class="grading-wrapper">
    <div class="row">
        <div class="col-12">
            <h3 class="grading-section-title">
                <i class="fas fa-graduation-cap"></i> Grading Configuration
            </h3>
            <p class="grading-section-subtitle">
                Configure the grading logic for this exam. The system will auto-calculate mark ranges based on the Total Marks.
            </p>
        </div>
    </div>

    <div class="row">
        <!-- LEFT: Static Default Display -->
        <div class="col-lg-4 mb-4">
            <div class="gs-card">
                <div class="gs-card-header gs-header-default">
                    <span><i class="fas fa-bookmark mr-2"></i> Current System Default</span>
                </div>
                <div class="gs-card-body">
                    <div style="padding: 1rem; font-size: 0.85rem; background: #eef2ff; color: #4338ca; border-bottom: 1px solid #e0e7ff;">
                        <i class="fas fa-info-circle mr-1"></i> These rules are used by default.
                    </div>
                    <div class="table-responsive">
                        <table class="table gs-table">
                            <thead>
                                <tr>
                                    <th>Grade</th>
                                    <th>GPA</th>
                                    <th>Range</th>
                                </tr>
                            </thead>
                            <tbody id="staticDefaultBody">
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        <i class="fas fa-spinner fa-spin"></i> Loading default configuration...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT: Interactive Editor -->
        <div class="col-lg-8 mb-4">
            <div class="gs-card">
                <div class="gs-card-header gs-header-edit">
                    <span style="font-size: 1.1rem; color: #2c3e50;"><i class="fas fa-sliders-h mr-2" style="color: #4e73df;"></i> Exam Grading Rules</span>
                    <button type="button" class="gs-btn-add" onclick="addGradeRow()">
                        <i class="fas fa-plus mr-1"></i> Add Grade
                    </button>
                </div>
                <div class="gs-card-body">
                    <div class="table-responsive">
                        <table class="table gs-table">
                            <thead>
                                <tr>
                                    <th width="15%">Grade</th>
                                    <th width="15%">GPA</th>
                                    <th width="22%">Range (%)</th>
                                    <th width="25%">Marks (Calc)</th>
                                    <th width="8%"></th>
                                </tr>
                            </thead>
                            <tbody id="gradingTableBody">
                                <!-- Editable Rows -->
                            </tbody>
                        </table>
                    </div>
                    <div style="padding: 1rem; background: #f8f9fc; border-top: 1px solid #e3e6f0;">
                         <small class="text-muted d-block text-right">
                            <i class="fas fa-calculator mr-1"></i> Marks calculated based on Total: <strong><span id="displayTotalMarks">100</span></strong>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <input type="hidden" name="grading_system" id="grading_system_input">
</div>

<script>
let gradingRules = [];
let defaultRules = []; // Store defaults separately for the split view
const totalMarksInput = document.getElementById('total_marks');
const displayTotalMarks = document.getElementById('displayTotalMarks');
const gradingInput = document.getElementById('grading_system_input');
const tbody = document.getElementById('gradingTableBody');
const staticBody = document.getElementById('staticDefaultBody');

// Initial Data passed from PHP (for Edit page)
const serverGradingSystem = <?php echo isset($currentGradingSystem) && $currentGradingSystem ? json_encode($currentGradingSystem) : 'null'; ?>;

document.addEventListener('DOMContentLoaded', function() {
    // 1. Fetch Defaults (Always needed for the Left Box)
    fetchDefaults().then(defaults => {
        defaultRules = defaults; // Keep a copy of pure defaults
        renderStaticTable(defaultRules);
        
        // 2. Initialize Editable Area
        if (serverGradingSystem) {
            // Edit Mode: Use server data
            gradingRules = serverGradingSystem;
        } else {
            // Create Mode: Use defaults
            // important: Clone deep to avoid reference issues
            gradingRules = JSON.parse(JSON.stringify(defaultRules)); 
        }
        renderEditableRows();
    });

    // Listener for Total Marks change
    if (totalMarksInput) {
        totalMarksInput.addEventListener('input', () => {
             updateDisplayTotal();
             renderEditableRows(); // Re-calc marks
        });
        updateDisplayTotal();
    }
});

function updateDisplayTotal() {
    if(displayTotalMarks && totalMarksInput) {
        displayTotalMarks.textContent = totalMarksInput.value || 0;
    }
}

function fetchDefaults() {
    return fetch('<?php echo BASE_URL; ?>/admin/exams/get_last_grading_system.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                return data.data;
            }
            return [];
        })
        .catch(err => {
            console.error(err);
            return [];
        });
}

function renderStaticTable(rules) {
    if(!staticBody) return;
    staticBody.innerHTML = '';
    
    rules.forEach(rule => {
        const badgeClass = getBadgeClass(rule.grade);
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><span class="grade-badge ${badgeClass}">${rule.grade}</span></td>
            <td style="font-weight: 600; color: #555;">${parseFloat(rule.gpa).toFixed(2)}</td>
            <td>${rule.min_percent}% - ${rule.max_percent}%</td>
        `;
        staticBody.appendChild(tr);
    });
}

function getBadgeClass(grade) {
    grade = grade.toUpperCase();
    if(grade.includes('A+')) return 'badge-A-plus';
    if(grade === 'A') return 'badge-A';
    if(grade.includes('A-') || grade.includes('B')) return 'badge-B';
    if(grade.includes('C')) return 'badge-C';
    if(grade.includes('D')) return 'badge-D';
    if(grade.includes('F')) return 'badge-F';
    return 'badge-B'; // fallback
}

/* -------------------------------------------------------------------------- */
/*                          Editable Logic (Same as before)                   */
/* -------------------------------------------------------------------------- */

function addGradeRow() {
    gradingRules.push({
        grade: '',
        gpa: 0,
        min_percent: 0,
        max_percent: 0
    });
    renderEditableRows();
    setTimeout(() => {
        const lastRow = tbody.lastElementChild;
        if (lastRow) {
            const gradeInput = lastRow.querySelector('input[type="text"]');
            if (gradeInput) gradeInput.focus();
        }
    }, 100);
}

function removeGradeRow(index) {
    const grade = gradingRules[index].grade || 'this grade';
    if (!confirm(`Are you sure you want to delete "${grade}"? Adjacent ranges will be auto-joined.`)) {
        return;
    }
    
    // Auto-adjust logic
    const deletedMin = parseFloat(gradingRules[index].min_percent);
    const deletedMax = parseFloat(gradingRules[index].max_percent);
    
    gradingRules.splice(index, 1);
    
    if (gradingRules.length > 0) {
        // Try filling gap from above first
        let upperIndex = gradingRules.findIndex(r => parseFloat(r.min_percent) > deletedMax);
        // Find closest upper
        let closestUpperDiff = Infinity;
        let targetUpper = -1;
        
        gradingRules.forEach((r, i) => {
             const diff = parseFloat(r.min_percent) - deletedMax;
             if(diff > 0 && diff < closestUpperDiff) {
                 closestUpperDiff = diff;
                 targetUpper = i;
             }
        });

        if (targetUpper !== -1) {
             gradingRules[targetUpper].min_percent = deletedMin;
        } else {
             // Try filling from below
            let targetLower = -1;
            let closestLowerDiff = Infinity;
            
            gradingRules.forEach((r, i) => {
                 const diff = deletedMin - parseFloat(r.max_percent);
                 if(diff > 0 && diff < closestLowerDiff) {
                     closestLowerDiff = diff;
                     targetLower = i;
                 }
            });
            
            if(targetLower !== -1) {
                gradingRules[targetLower].max_percent = deletedMax;
            }
        }
    }
    renderEditableRows();
}

function updateRule(index, field, value) {
    gradingRules[index][field] = value;
    updateHiddenInput();
    
    // Smart update for calc display
    if (field === 'min_percent' || field === 'max_percent') {
        const row = tbody.children[index];
        if (row) {
            const total = parseFloat(totalMarksInput.value) || 100;
            const minMarks = Math.round((parseFloat(gradingRules[index].min_percent) / 100) * total);
            const maxMarks = Math.round((parseFloat(gradingRules[index].max_percent) / 100) * total);
            const calcSpan = row.querySelector('.calc-range');
            if(calcSpan) calcSpan.textContent = `${minMarks} - ${maxMarks}`;
        }
    }
}

function updateHiddenInput() {
    gradingInput.value = JSON.stringify(gradingRules);
}

function renderEditableRows() {
    tbody.innerHTML = '';
    const total = parseFloat(totalMarksInput.value) || 100;

    gradingRules.forEach((rule, index) => {
        const tr = document.createElement('tr');
        
        const minMarks = Math.round((rule.min_percent / 100) * total);
        const maxMarks = Math.round((rule.max_percent / 100) * total);
        
        tr.innerHTML = `
            <td>
                <input type="text" class="gs-input" value="${rule.grade || ''}" 
                       oninput="updateRule(${index}, 'grade', this.value)" placeholder="Label">
            </td>
            <td>
                <input type="number" class="gs-input" value="${rule.gpa || 0}" 
                       oninput="updateRule(${index}, 'gpa', this.value)" step="0.01" min="0" max="5">
            </td>
            <td>
                <div style="display: flex; align-items: center; gap: 5px;">
                    <input type="number" class="gs-input" value="${rule.min_percent || 0}" 
                           oninput="updateRule(${index}, 'min_percent', this.value)" step="0.1">
                    <span class="text-muted">-</span>
                    <input type="number" class="gs-input" value="${rule.max_percent || 0}" 
                           oninput="updateRule(${index}, 'max_percent', this.value)" step="0.1">
                </div>
            </td>
            <td class="align-middle">
                <div class="calc-range">${minMarks} - ${maxMarks}</div>
            </td>
            <td class="text-center align-middle">
                <button type="button" class="gs-btn-delete" onclick="removeGradeRow(${index})" title="Delete">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
    updateHiddenInput();
}
</script>
