<?php
require_once __DIR__ . '/../includes/auth_functions.php';
require_once __DIR__ . '/../includes/db_connection.php';

if (!isset($_SESSION['pending_student_registration'])) {
    header("Location: signup.php");
    exit();
}

// Fetch unique programs, years, and sections
$programs = [];
$years = [];
$sections = [];

// Fetch unique address components
$provinces = [];
$municipalities = [];
$barangays = [];

try {
    // Get unique programs
    $stmt = $pdo->query("SELECT DISTINCT program_name FROM program_tbl ORDER BY program_name");
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get unique provinces
    $stmt = $pdo->query("SELECT DISTINCT province FROM address_tbl ORDER BY province");
    $provinces = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
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
            <div class="alert alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <form action="process_student_info.php" method="post">
            <input type="hidden" name="std_id" value="<?= $_SESSION['pending_student_registration']['std_id'] ?>">
            
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
                        <option value="<?= htmlspecialchars($program['program_name']) ?>"><?= htmlspecialchars($program['program_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="year">Year Level</label>
                <select id="year" name="year" required class="form-control" onchange="fetchSections(this.value, document.getElementById('program').value)" disabled>
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
                        <option value="<?= htmlspecialchars($province['province']) ?>"><?= htmlspecialchars($province['province']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="municipality">Municipality/City</label>
                <select id="municipality" name="municipality" required class="form-control" onchange="fetchBarangays(this.value, document.getElementById('province').value)" disabled>
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
// Function to fetch years based on selected program
function fetchYears(programName) {
    if (!programName) {
        document.getElementById('year').innerHTML = '<option value="">Select Year Level</option>';
        document.getElementById('year').disabled = true;
        document.getElementById('section').innerHTML = '<option value="">Select Section</option>';
        document.getElementById('section').disabled = true;
        return;
    }

    fetch('fetch_years.php?program_name=' + encodeURIComponent(programName))
        .then(response => response.json())
        .then(data => {
            const yearSelect = document.getElementById('year');
            yearSelect.innerHTML = '<option value="">Select Year Level</option>';
            
            data.forEach(year => {
                yearSelect.innerHTML += `<option value="${year.year_level}">${year.year_level}</option>`;
            });
            
            yearSelect.disabled = false;
            document.getElementById('section').innerHTML = '<option value="">Select Section</option>';
            document.getElementById('section').disabled = true;
        });
}

// Function to fetch sections based on program and year
function fetchSections(yearLevel, programName) {
    if (!yearLevel || !programName) {
        document.getElementById('section').innerHTML = '<option value="">Select Section</option>';
        document.getElementById('section').disabled = true;
        return;
    }

    fetch(`fetch_sections.php?program_name=${encodeURIComponent(programName)}&year_level=${encodeURIComponent(yearLevel)}`)
        .then(response => response.json())
        .then(data => {
            const sectionSelect = document.getElementById('section');
            sectionSelect.innerHTML = '<option value="">Select Section</option>';
            
            data.forEach(section => {
                sectionSelect.innerHTML += `<option value="${section.section}">${section.section}</option>`;
            });
            
            sectionSelect.disabled = false;
        });
}

// Function to fetch municipalities based on selected province
function fetchMunicipalities(province) {
    if (!province) {
        document.getElementById('municipality').innerHTML = '<option value="">Select Municipality/City</option>';
        document.getElementById('municipality').disabled = true;
        document.getElementById('barangay').innerHTML = '<option value="">Select Barangay</option>';
        document.getElementById('barangay').disabled = true;
        return;
    }

    fetch('fetch_municipalities.php?province=' + encodeURIComponent(province))
        .then(response => response.json())
        .then(data => {
            const municipalitySelect = document.getElementById('municipality');
            municipalitySelect.innerHTML = '<option value="">Select Municipality/City</option>';
            
            data.forEach(municipality => {
                municipalitySelect.innerHTML += `<option value="${municipality.municipality}">${municipality.municipality}</option>`;
            });
            
            municipalitySelect.disabled = false;
            document.getElementById('barangay').innerHTML = '<option value="">Select Barangay</option>';
            document.getElementById('barangay').disabled = true;
        });
}

// Function to fetch barangays based on selected municipality and province
function fetchBarangays(municipality, province) {
    if (!municipality || !province) {
        document.getElementById('barangay').innerHTML = '<option value="">Select Barangay</option>';
        document.getElementById('barangay').disabled = true;
        return;
    }

    fetch(`fetch_barangays.php?municipality=${encodeURIComponent(municipality)}&province=${encodeURIComponent(province)}`)
        .then(response => response.json())
        .then(data => {
            const barangaySelect = document.getElementById('barangay');
            barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
            
            data.forEach(barangay => {
                barangaySelect.innerHTML += `<option value="${barangay.brgy}">${barangay.brgy}</option>`;
            });
            
            barangaySelect.disabled = false;
        });
}
</script>

<?php output_footer(); ?>