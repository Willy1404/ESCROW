@extends('layouts.app')

@section('title', 'Create Escrow - Azania Bank')

@section('header', 'Create New Escrow')

@section('header_buttons')
    <a href="{{ route('escrow.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Transactions
    </a>
@endsection

@section('styles')
<style>
    /* Lightbox CSS */
    .image-lightbox {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
    }

    .lightbox-overlay {
        position: absolute;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
    }

    .lightbox-content {
        position: relative;
        max-width: 90%;
        max-height: 90%;
        margin: 2% auto;
        text-align: center;
        z-index: 10000;
    }

    #lightbox-image {
        max-width: 100%;
        max-height: 90vh;
        margin: 0 auto;
        box-shadow: 0 0 20px rgba(0,0,0,0.5);
    }

    .lightbox-close {
        position: absolute;
        top: -20px;
        right: -20px;
        background: white;
        color: black;
        border: none;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        font-size: 20px;
        line-height: 1;
        cursor: pointer;
        box-shadow: 0 0 5px rgba(0,0,0,0.5);
    }

    .lightbox-close:hover {
        background-color: #f0f0f0;
    }
</style>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Escrow Transaction Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('escrow.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="control_number" class="form-label">Control Number</label>
                            <div class="input-group">
                                <input type="text" class="form-control @error('control_number') is-invalid @enderror" id="control_number" name="control_number" required>
                                <button type="button" class="btn btn-outline-primary" id="verifyControlNumber">Verify</button>
                            </div>
                            <div class="form-text">Enter the control number provided by the seller.</div>
                            @error('control_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div id="escrowDetails" class="border rounded p-3 mb-3" style="display: none;">
                            <h6 class="mb-3">Transaction Details</h6>
                            <div class="row mb-2">
                                <div class="col-md-4 fw-bold">Seller:</div>
                                <div class="col-md-8" id="sellerName"></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4 fw-bold">Item:</div>
                                <div class="col-md-8" id="itemName"></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4 fw-bold">Amount:</div>
                                <div class="col-md-8" id="amount"></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4 fw-bold">Delivery Deadline:</div>
                                <div class="col-md-8" id="deliveryDeadline"></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4 fw-bold">Inspection Period:</div>
                                <div class="col-md-8" id="inspectionPeriod"></div>
                            </div>
                            <!-- Add this inside the escrowDetails div, after the existing rows -->
                            <div id="itemDetailsContainer">
                                <!-- Dynamic content for item condition, description, and photos will be inserted here -->
                            </div>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="terms_agree" required>
                            <label class="form-check-label" for="terms_agree">
                                I agree to the terms and conditions of the escrow service.
                            </label>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-secondary" onclick="history.back()">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="createEscrowBtn" disabled>Create Escrow</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">How It Works</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex mb-2">
                            <div class="bg-primary rounded-circle text-white fs-5 d-flex justify-content-center align-items-center" style="width: 30px; height: 30px;">
                                <span>1</span>
                            </div>
                            <h6 class="ms-2 mt-1">Enter Control Number</h6>
                        </div>
                        <p class="text-muted small ms-4">Enter the control number provided by the seller.</p>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex mb-2">
                            <div class="bg-primary rounded-circle text-white fs-5 d-flex justify-content-center align-items-center" style="width: 30px; height: 30px;">
                                <span>2</span>
                            </div>
                            <h6 class="ms-2 mt-1">Verify Details</h6>
                        </div>
                        <p class="text-muted small ms-4">Verify that the transaction details are correct.</p>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex mb-2">
                            <div class="bg-primary rounded-circle text-white fs-5 d-flex justify-content-center align-items-center" style="width: 30px; height: 30px;">
                                <span>3</span>
                            </div>
                            <h6 class="ms-2 mt-1">Create Escrow</h6>
                        </div>
                        <p class="text-muted small ms-4">Create the escrow transaction and proceed to deposit funds.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const verifyButton = document.getElementById('verifyControlNumber');
    const controlNumberInput = document.getElementById('control_number');
    const escrowDetails = document.getElementById('escrowDetails');
    const createEscrowBtn = document.getElementById('createEscrowBtn');
    
    // Setup for image lightbox
    const setupImageLightbox = () => {
        const photoGallery = document.getElementById('itemPhotosGallery');
        if (photoGallery) {
            const images = photoGallery.querySelectorAll('img');
            
            images.forEach(img => {
                img.style.cursor = 'pointer';
                img.addEventListener('click', function() {
                    showLightbox(this.src);
                });
            });
        }
    };
    
    // Function to show lightbox
    const showLightbox = (imageSrc) => {
        // Create lightbox elements if they don't exist
        let lightbox = document.getElementById('image-lightbox');
        
        if (!lightbox) {
            lightbox = document.createElement('div');
            lightbox.id = 'image-lightbox';
            lightbox.className = 'image-lightbox';
            lightbox.innerHTML = `
                <div class="lightbox-overlay"></div>
                <div class="lightbox-content">
                    <img src="" alt="Enlarged view" id="lightbox-image">
                    <button class="lightbox-close">&times;</button>
                </div>
            `;
            document.body.appendChild(lightbox);
            
            // Add event listener to close button
            lightbox.querySelector('.lightbox-close').addEventListener('click', () => {
                lightbox.style.display = 'none';
            });
            
            // Add event listener to overlay for closing
            lightbox.querySelector('.lightbox-overlay').addEventListener('click', () => {
                lightbox.style.display = 'none';
            });
        }
        
        // Set the image source and display the lightbox
        document.getElementById('lightbox-image').src = imageSrc;
        lightbox.style.display = 'block';
    };
    
    verifyButton.addEventListener('click', function() {
        const controlNumber = controlNumberInput.value.trim();
        
        if (!controlNumber) {
            alert('Please enter a control number');
            return;
        }
        
        // Show loading state
        verifyButton.disabled = true;
        verifyButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verifying...';
        
        // Add debugging console log
        console.log('Verifying control number:', controlNumber);
        
        // AJAX request to verify control number
        fetch('/control-numbers/verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ control_number: controlNumber })
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            verifyButton.disabled = false;
            verifyButton.innerHTML = 'Verify';
            
            if (data.valid) {
                // Display transaction details
                document.getElementById('sellerName').textContent = data.data.seller;
                document.getElementById('itemName').textContent = data.data.item_name;
                document.getElementById('amount').textContent = `${data.data.currency} ${data.data.amount}`;
                document.getElementById('deliveryDeadline').textContent = data.data.delivery_deadline;
                document.getElementById('inspectionPeriod').textContent = `${data.data.inspection_period} days`;
                
                // Display item condition and description if available
                const itemDetailsContainer = document.getElementById('itemDetailsContainer');
                itemDetailsContainer.innerHTML = ''; // Clear previous content
                
                if (data.data.item_condition) {
                    let conditionRow = document.createElement('div');
                    conditionRow.className = 'row mb-2';
                    conditionRow.innerHTML = `
                        <div class="col-md-4 fw-bold">Condition:</div>
                        <div class="col-md-8">${data.data.item_condition}</div>
                    `;
                    itemDetailsContainer.appendChild(conditionRow);
                }
                
                if (data.data.item_description) {
                    let descriptionRow = document.createElement('div');
                    descriptionRow.className = 'row mb-2';
                    descriptionRow.innerHTML = `
                        <div class="col-md-4 fw-bold">Description:</div>
                        <div class="col-md-8">${data.data.item_description}</div>
                    `;
                    itemDetailsContainer.appendChild(descriptionRow);
                }
                
                // Display item photos if available
                if (data.data.photos && data.data.photos.length > 0) {
                    let photosContainer = document.createElement('div');
                    photosContainer.className = 'mt-3';
                    photosContainer.innerHTML = `
                        <h6 class="mb-2">Item Photos:</h6>
                        <div class="row" id="itemPhotosGallery"></div>
                    `;
                    
                    itemDetailsContainer.appendChild(photosContainer);
                    
                    // Add each photo to the gallery
                    data.data.photos.forEach(photo => {
                        let photoCol = document.createElement('div');
                        photoCol.className = 'col-md-4 col-6 mb-3';
                        photoCol.innerHTML = `
                            <div class="card h-100">
                                <img src="${photo.url}" class="card-img-top" alt="Item Photo" 
                                     style="height: 150px; object-fit: cover; cursor: pointer;">
                                <div class="card-body p-2">
                                    <p class="card-text small text-muted">${photo.description || 'No description provided'}</p>
                                </div>
                            </div>
                        `;
                        document.getElementById('itemPhotosGallery').appendChild(photoCol);
                    });
                    
                    // Set up lightbox for photos
                    setupImageLightbox();
                }
                
                // Show details and enable create button
                escrowDetails.style.display = 'block';
                createEscrowBtn.disabled = false;
            } else {
                alert(data.message);
                escrowDetails.style.display = 'none';
                createEscrowBtn.disabled = true;
            }
        })
        .catch(error => {
            console.error('Error during verification:', error);
            verifyButton.disabled = false;
            verifyButton.innerHTML = 'Verify';
            alert('Error verifying control number. Please try again.');
        });
    });
});
</script>
@endsection