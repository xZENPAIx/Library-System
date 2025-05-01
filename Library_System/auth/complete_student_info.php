<?php
require_once __DIR__ . '/../includes/auth_functions.php';
require_once __DIR__ . '/../config/db_config.php';

if (!isset($_SESSION['pending_student_registration'])) {
    header("Location: signup.php");
    exit();
}

// Fetch unique programs and provinces
$programs = [];
$provinces = [];

try {
    // Get unique programs
    $stmt = $conn->query("SELECT DISTINCT program_name FROM program_tbl ORDER BY program_name");
    $programs = $stmt->fetch_all(MYSQLI_ASSOC);

    // Get unique provinces
    $stmt = $conn->query("SELECT DISTINCT province FROM address_tbl ORDER BY province");
    $provinces = $stmt->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}

$page_title = "Complete Student Information";
include __DIR__ . '/../includes/template_functions.php';
output_header();
?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Complete Your Student Information</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <form action="process_student_info.php" method="post">
            <input type="hidden" name="std_id" value="<?= htmlspecialchars($_SESSION['pending_student_registration']['std_id'] ?? '') ?>">
            
            <!-- Personal Information -->
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required class="form-control">
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required class="form-control">
            </div>
            
            <div class="form-group">
                <label for="contact">Contact Number</label>
                <input type="tel" id="contact" name="contact" required class="form-control">
            </div>
            
            <!-- Program Information -->
            <div class="form-group">
                <label for="program">Program</label>
                <select id="program" name="program" required class="form-control" onchange="fetchYears(this.value)">
                    <option value="">Select Program</option>
                    <?php foreach ($programs as $program): ?>
                        <option value="<?= htmlspecialchars($program['program_name']) ?>">
                            <?= htmlspecialchars($program['program_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="year">Year Level</label>
                <select id="year" name="year" required class="form-control" disabled>
                    <option value="">Select Year Level</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="section">Section</label>
                <select id="section" name="section" required class="form-control" disabled>
                    <option value="">Select Section</option>
                </select>
            </div>
            
            <!-- Address Information -->
            <div class="form-group">
                <label for="province">Province</label>
                <select id="province" name="province" required class="form-control" onchange="fetchMunicipalities(this.value)">
                    <option value="">Select Province</option>
                    <?php foreach ($provinces as $province): ?>
                        <option value="<?= htmlspecialchars($province['province']) ?>">
                            <?= htmlspecialchars($province['province']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="municipality">Municipality/City</label>
                <select id="municipality" name="municipality" required class="form-control" disabled>
                    <option value="">Select Municipality/City</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="barangay">Barangay</label>
                <select id="barangay" name="barangay" required class="form-control" disabled>
                    <option value="">Select Barangay</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Submit Information</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize event listeners
    document.getElementById('program').addEventListener('change', function() {
        fetchYears(this.value);
    });
    
    document.getElementById('year').addEventListener('change', function() {
        const program = document.getElementById('program').value;
        fetchSections(this.value, program);
    });
    
    document.getElementById('province').addEventListener('change', function() {
        fetchMunicipalities(this.value);
    });
    
    document.getElementById('municipality').addEventListener('change', function() {
        const province = document.getElementById('province').value;
        fetchBarangays(this.value, province);
    });
});

async function fetchYears(programName) {
    if (!programName) {
        resetDropdown('year');
        resetDropdown('section');
        return;
    }

    try {
        const response = await fetch(`/Library_System/ajax/fetch_years.php?program_name=${encodeURIComponent(programName)}`);
        if (!response.ok) throw new Error('Network response was not ok');
        const data = await response.json();
        
        const yearSelect = document.getElementById('year');
        updateDropdown(yearSelect, data, 'year_level');
        
        // Reset section dropdown
        resetDropdown('section');
    } catch (error) {
        console.error('Error fetching years:', error);
    }
}

async function fetchSections(yearLevel, programName) {
    if (!yearLevel || !programName) {
        resetDropdown('section');
        return;
    }

    try {
        const response = await fetch(`/Library_System/ajax/fetch_sections.php?program_name=${encodeURIComponent(programName)}&year_level=${encodeURIComponent(yearLevel)}`);
        if (!response.ok) throw new Error('Network response was not ok');
        const data = await response.json();
        
        const sectionSelect = document.getElementById('section');
        updateDropdown(sectionSelect, data, 'section');
    } catch (error) {
        console.error('Error fetching sections:', error);
    }
}

async function fetchMunicipalities(province) {
    if (!province) {
        resetDropdown('municipality');
        resetDropdown('barangay');
        return;
    }

    try {
        const response = await fetch(`/Library_System/ajax/fetch_municipalities.php?province=${encodeURIComponent(province)}`);
        if (!response.ok) throw new Error('Network response was not ok');
        const data = await response.json();
        
        const municipalitySelect = document.getElementById('municipality');
        updateDropdown(municipalitySelect, data, 'municipality');
        
        // Reset barangay dropdown
        resetDropdown('barangay');
    } catch (error) {
        console.error('Error fetching municipalities:', error);
    }
}

async function fetchBarangays(municipality, province) {
    if (!municipality || !province) {
        resetDropdown('barangay');
        return;
    }

    try {
        const response = await fetch(`/Library_System/ajax/fetch_barangays.php?municipality=${encodeURIComponent(municipality)}&province=${encodeURIComponent(province)}`);
        if (!response.ok) throw new Error('Network response was not ok');
        const data = await response.json();
        
        const barangaySelect = document.getElementById('barangay');
        updateDropdown(barangaySelect, data, 'brgy');
    } catch (error) {
        console.error('Error fetching barangays:', error);
    }
}

// Helper functions
function updateDropdown(selectElement, data, valueField) {
    selectElement.innerHTML = '<option value="">Select ' + selectElement.id.charAt(0).toUpperCase() + selectElement.id.slice(1) + '</option>';
    
    if (data && data.length > 0) {
        data.forEach(item => {
            const option = document.createElement('option');
            option.value = item[valueField];
            option.textContent = item[valueField];
            selectElement.appendChild(option);
        });
        selectElement.disabled = false;
    } else {
        selectElement.disabled = true;
    }
}

function resetDropdown(id) {
    const select = document.getElementById(id);
    select.innerHTML = '<option value="">Select ' + id.charAt(0).toUpperCase() + id.slice(1) + '</option>';
    select.disabled = true;
    select.selectedIndex = 0;
}
</script>

<?php output_footer(); ?>