<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Azania Bank Escrow</title>
    <!-- Bootstrap CSS direct inclusion -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }
        
        .register-wrapper {
            width: 100%;
            max-width: 600px;
            padding: 15px;
            margin: 20px auto;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin: 0 auto;
        }
        
        .card-header {
            background-color: #3399BB;
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
            font-weight: bold;
            text-align: center;
        }
        
        .btn-primary {
            background-color: #3399BB;
            border-color: #3399BB;
        }
        
        .btn-primary:hover {
            background-color: #2980a5;
            border-color: #2980a5;
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo {
            max-width: 280px;
            height: auto;
        }
        
        a {
            color: #3399BB;
            text-decoration: none;
        }
        
        a:hover {
            text-decoration: underline;
        }
        
        .kyc-section {
            display: none;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }
        
        .kyc-section h4 {
            color: #3399BB;
            margin-bottom: 20px;
        }
        
        .form-section {
            margin-bottom: 15px;
        }
        
        .step-indicator {
            display: flex;
            margin-bottom: 20px;
            justify-content: space-between;
        }
        
        .step {
            flex: 1;
            text-align: center;
            padding: 8px;
            background-color: #e9ecef;
            border-radius: 5px;
            margin: 0 5px;
            font-size: 14px;
        }
        
        .step.active {
            background-color: #3399BB;
            color: white;
        }
        
        .required-field::after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>
    <div class="register-wrapper">
        <div class="logo-container">
            <img src="{{ asset('images/azania-logo.png') }}" alt="Azania Bank" class="logo img-fluid">
        </div>
        
        <div class="card">
            <div class="card-header">Register for Azania Bank Escrow</div>
            <div class="alert alert-info m-3 mb-0" id="sellerKycNotice" style="display: none;">
                <strong>Notice:</strong> Sellers are required to complete KYC verification for security and compliance.
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" id="registrationForm">
                    @csrf
                    
                    <!-- Step Indicator -->
                    <div class="step-indicator" id="stepIndicator" style="display: none;">
                        <div class="step active">Account</div>
                        <div class="step" id="kycStep">Seller Verification</div>
                        <div class="step" id="uploadStep">Document Upload</div>
                    </div>

                    <!-- Basic Account Info Section -->
                    <div id="basicInfoSection">
                        <!-- Role Selection -->
                        <div class="mb-3">
                            <label for="role" class="form-label required-field">Role</label>
                            <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                <option value="">Select your role</option>
                                <option value="buyer" {{ old('role') == 'buyer' ? 'selected' : '' }}>Buyer</option>
                                <option value="seller" {{ old('role') == 'seller' ? 'selected' : '' }}>Seller</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label required-field">Full Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label required-field">Email Address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label required-field">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                            <div class="form-text">Password must be at least 8 characters long.</div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Confirm Password -->
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label required-field">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="button" id="nextToKYC" class="btn btn-primary d-none">Next: Seller Verification</button>
                            <button type="submit" id="regularSubmit" class="btn btn-primary">Register</button>
                        </div>
                    </div>
                    
                    <!-- KYC Information Section for Sellers -->
                    <div id="kycSection" class="kyc-section">
                        <h4>Seller Verification</h4>
                        
                        <!-- Date of Birth -->
                        <div class="mb-3">
                            <label for="dob" class="form-label required-field">Date of Birth</label>
                            <input type="date" class="form-control" id="dob" name="dob" value="{{ old('dob') }}" required>
                        </div>
                        
                        <!-- Address -->
                        <div class="mb-3">
                            <label for="address" class="form-label required-field">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2" required>{{ old('address') }}</textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label required-field">City</label>
                                <input type="text" class="form-control" id="city" name="city" value="{{ old('city') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code" value="{{ old('postal_code') }}">
                            </div>
                        </div>
                        
                        <!-- Phone Number -->
                        <div class="mb-3">
                            <label for="phone" class="form-label required-field">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="{{ old('phone') }}" required>
                        </div>
                        
                        <!-- ID Type and Number -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="id_type" class="form-label required-field">ID Type</label>
                                <select class="form-select" id="id_type" name="id_type" required>
                                    <option value="">Select ID Type</option>
                                    <option value="national_id" {{ old('id_type') == 'national_id' ? 'selected' : '' }}>National ID</option>
                                    <option value="passport" {{ old('id_type') == 'passport' ? 'selected' : '' }}>Passport</option>
                                    <option value="drivers_license" {{ old('id_type') == 'drivers_license' ? 'selected' : '' }}>Driver's License</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="id_number" class="form-label required-field">ID Number</label>
                                <input type="text" class="form-control" id="id_number" name="id_number" value="{{ old('id_number') }}" required>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="button" id="backToBasic" class="btn btn-outline-secondary mb-2">Back</button>
                            <button type="button" id="nextToUpload" class="btn btn-primary">Next: Document Upload</button>
                        </div>
                    </div>
                    
                    <!-- Document Upload Section -->
                    <div id="uploadSection" class="kyc-section">
                        <h4>Document Upload</h4>
                        <p class="text-muted mb-4">Please upload clear photos or scans of the following documents:</p>
                        
                        <!-- ID Proof Upload -->
                        <div class="mb-3">
                            <label for="id_proof" class="form-label required-field">ID Proof (National ID, Passport, or Driver's License)</label>
                            <input type="file" class="form-control" id="id_proof" name="id_proof" required>
                            <div class="form-text">Supported formats: JPG, PNG, PDF. Max size: 5MB</div>
                        </div>
                        
                        <!-- Address Proof Upload -->
                        <div class="mb-3">
                            <label for="address_proof" class="form-label required-field">Address Proof (Utility Bill, Bank Statement)</label>
                            <input type="file" class="form-control" id="address_proof" name="address_proof" required>
                            <div class="form-text">Document should be less than 3 months old. Supported formats: JPG, PNG, PDF. Max size: 5MB</div>
                        </div>
                        
                        <!-- Selfie Upload -->
                        <div class="mb-3">
                            <label for="selfie" class="form-label required-field">Selfie with ID Document</label>
                            <input type="file" class="form-control" id="selfie" name="selfie" required>
                            <div class="form-text">Please hold your ID next to your face. Supported formats: JPG, PNG. Max size: 5MB</div>
                        </div>
                        
                        <!-- Consent Checkbox -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="consent" name="consent" required>
                                <label class="form-check-label required-field" for="consent">
                                    I consent to Azania Bank collecting, processing, and storing my personal information for KYC verification purposes in accordance with applicable regulations.
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="button" id="backToKYC" class="btn btn-outline-secondary mb-2">Back</button>
                            <button type="submit" class="btn btn-primary">Complete Registration</button>
                        </div>
                    </div>
                    
                    <div class="text-center mt-3">
                        <p class="mb-0">Already have an account? <a href="{{ route('login') }}">Login</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            const basicInfoSection = document.getElementById('basicInfoSection');
            const kycSection = document.getElementById('kycSection');
            const uploadSection = document.getElementById('uploadSection');
            const nextToKYCBtn = document.getElementById('nextToKYC');
            const nextToUploadBtn = document.getElementById('nextToUpload');
            const backToBasicBtn = document.getElementById('backToBasic');
            const backToKYCBtn = document.getElementById('backToKYC');
            const regularSubmitBtn = document.getElementById('regularSubmit');
            const kycStep = document.getElementById('kycStep');
            const uploadStep = document.getElementById('uploadStep');
            const registrationForm = document.getElementById('registrationForm');
            
            // Function to toggle KYC sections based on role
            function toggleKYCSection() {
                const sellerKycNotice = document.getElementById('sellerKycNotice');
                const stepIndicator = document.getElementById('stepIndicator');
                
                if (roleSelect.value === 'seller') {
                    nextToKYCBtn.classList.remove('d-none');
                    regularSubmitBtn.classList.add('d-none');
                    sellerKycNotice.style.display = 'block';
                    stepIndicator.style.display = 'flex';
                } else {
                    nextToKYCBtn.classList.add('d-none');
                    regularSubmitBtn.classList.remove('d-none');
                    sellerKycNotice.style.display = 'none';
                    stepIndicator.style.display = 'none';
                }
            }
            
            // Initial check
            toggleKYCSection();
            
            // Listen for role changes
            roleSelect.addEventListener('change', toggleKYCSection);
            
            // Next to KYC button with validation
            nextToKYCBtn.addEventListener('click', function() {
                // Validate required fields in the basic info section
                const name = document.getElementById('name');
                const email = document.getElementById('email');
                const password = document.getElementById('password');
                const passwordConfirmation = document.getElementById('password_confirmation');
                
                // Check if fields are filled out
                if (!name.value.trim()) {
                    alert('Please enter your full name.');
                    name.focus();
                    return;
                }
                
                if (!email.value.trim()) {
                    alert('Please enter your email address.');
                    email.focus();
                    return;
                }
                
                // Simple email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email.value.trim())) {
                    alert('Please enter a valid email address.');
                    email.focus();
                    return;
                }
                
                if (!password.value) {
                    alert('Please enter a password.');
                    password.focus();
                    return;
                }
                
                if (password.value.length < 8) {
                    alert('Password must be at least 8 characters long.');
                    password.focus();
                    return;
                }
                
                if (!passwordConfirmation.value) {
                    alert('Please confirm your password.');
                    passwordConfirmation.focus();
                    return;
                }
                
                if (password.value !== passwordConfirmation.value) {
                    alert('Passwords do not match.');
                    passwordConfirmation.focus();
                    return;
                }
                
                // If all validations pass, proceed to the next step
                basicInfoSection.style.display = 'none';
                kycSection.style.display = 'block';
                uploadSection.style.display = 'none';
                kycStep.classList.add('active');
            });
            
            // Next to Upload button with validation
            nextToUploadBtn.addEventListener('click', function() {
                // Validate required fields in the KYC section
                const dob = document.getElementById('dob');
                const address = document.getElementById('address');
                const city = document.getElementById('city');
                const phone = document.getElementById('phone');
                const idType = document.getElementById('id_type');
                const idNumber = document.getElementById('id_number');
                
                // Check if fields are filled out
                if (!dob.value) {
                    alert('Please enter your date of birth.');
                    dob.focus();
                    return;
                }
                
                if (!address.value.trim()) {
                    alert('Please enter your address.');
                    address.focus();
                    return;
                }
                
                if (!city.value.trim()) {
                    alert('Please enter your city.');
                    city.focus();
                    return;
                }
                
                if (!phone.value.trim()) {
                    alert('Please enter your phone number.');
                    phone.focus();
                    return;
                }
                
                if (!idType.value) {
                    alert('Please select an ID type.');
                    idType.focus();
                    return;
                }
                
                if (!idNumber.value.trim()) {
                    alert('Please enter your ID number.');
                    idNumber.focus();
                    return;
                }
                
                // If all validations pass, proceed to the next step
                basicInfoSection.style.display = 'none';
                kycSection.style.display = 'none';
                uploadSection.style.display = 'block';
                uploadStep.classList.add('active');
            });
            
            // Form submission validation
            registrationForm.addEventListener('submit', function(event) {
                // Check if user is a seller and on the final step (upload section is visible)
                if (roleSelect.value === 'seller' && uploadSection.style.display === 'block') {
                    // Validate required uploads
                    const idProof = document.getElementById('id_proof');
                    const addressProof = document.getElementById('address_proof');
                    const selfie = document.getElementById('selfie');
                    const consent = document.getElementById('consent');
                    
                    if (!idProof.files || idProof.files.length === 0) {
                        event.preventDefault();
                        alert('Please upload your ID proof document.');
                        return;
                    }
                    
                    if (!addressProof.files || addressProof.files.length === 0) {
                        event.preventDefault();
                        alert('Please upload your address proof document.');
                        return;
                    }
                    
                    if (!selfie.files || selfie.files.length === 0) {
                        event.preventDefault();
                        alert('Please upload a selfie with your ID document.');
                        return;
                    }
                    
                    if (!consent.checked) {
                        event.preventDefault();
                        alert('You must consent to the KYC verification process to continue.');
                        return;
                    }
                }
                
                // If we're a buyer or all seller validations passed, allow the form to submit
            });
            
            // Back to Basic Info button
            backToBasicBtn.addEventListener('click', function() {
                basicInfoSection.style.display = 'block';
                kycSection.style.display = 'none';
                uploadSection.style.display = 'none';
                kycStep.classList.remove('active');
            });
            
            // Back to KYC button
            backToKYCBtn.addEventListener('click', function() {
                basicInfoSection.style.display = 'none';
                kycSection.style.display = 'block';
                uploadSection.style.display = 'none';
                uploadStep.classList.remove('active');
            });
        });
    </script>
</body>
</html>