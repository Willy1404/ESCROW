@extends('layouts.app')

@section('title', 'Generate Control Number - Azania Bank')

@section('header', 'Generate New Control Number')

@section('header_buttons')
    <a href="{{ route('control-numbers.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Control Numbers
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Control Number Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('control-numbers.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="item_name" class="form-label">Item Name</label>
                                <input type="text" class="form-control @error('item_name') is-invalid @enderror" id="item_name" name="item_name" required>
                                @error('item_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="item_condition" class="form-label">Item Condition</label>
                                <select class="form-select @error('item_condition') is-invalid @enderror" id="item_condition" name="item_condition">
                                    <option value="">Select condition</option>
                                    <option value="New">New</option>
                                    <option value="Used - Like New">Used - Like New</option>
                                    <option value="Used - Good">Used - Good</option>
                                    <option value="Used - Fair">Used - Fair</option>
                                    <option value="Refurbished">Refurbished</option>
                                </select>
                                @error('item_condition')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="item_description" class="form-label">Item Description</label>
                            <textarea class="form-control @error('item_description') is-invalid @enderror" id="item_description" name="item_description" rows="3"></textarea>
                            @error('item_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="amount" class="form-label">Amount</label>
                                <div class="input-group">
                                    <select class="form-select @error('currency') is-invalid @enderror" style="max-width: 100px;" name="currency">
                                        <option value="TZS">TZS</option>
                                        <option value="USD">USD</option>
                                        <option value="EUR">EUR</option>
                                        <option value="GBP">GBP</option>
                                    </select>
                                    <input type="number" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" min="0.01" step="0.01" required>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="delivery_deadline" class="form-label">Delivery Deadline</label>
                                <input type="date" class="form-control @error('delivery_deadline') is-invalid @enderror" id="delivery_deadline" name="delivery_deadline" required>
                                @error('delivery_deadline')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="inspection_period" class="form-label">Inspection Period (days)</label>
                            <input type="number" class="form-control @error('inspection_period') is-invalid @enderror" id="inspection_period" name="inspection_period" min="1" max="30" value="7" required>
                            <div class="form-text">Number of days the buyer has to inspect the item after delivery.</div>
                            @error('inspection_period')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Photo Evidence Section -->
                        <div class="card mt-4 mb-4 border-primary">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Item Photo Evidence</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Upload clear photos of the item to document its current condition. This helps prevent disputes later.
                                </div>
                                
                                <div class="mb-3">
                                    <label for="photo1" class="form-label">Primary Photo (Required)</label>
                                    <input type="file" class="form-control @error('photos.0') is-invalid @enderror" id="photo1" name="photos[]" accept="image/*" capture required>
                                    @error('photos.0')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div id="additional-photos">
                                    <div class="mb-3">
                                        <label for="photo2" class="form-label">Additional Photo (Optional)</label>
                                        <input type="file" class="form-control @error('photos.1') is-invalid @enderror" id="photo2" name="photos[]" accept="image/*" capture>
                                        @error('photos.1')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <button type="button" id="add-more-photos" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-plus"></i> Add More Photos
                                </button>
                                
                                <div class="mt-3">
                                    <label for="photo_description" class="form-label">Photo Description</label>
                                    <textarea class="form-control @error('photo_description') is-invalid @enderror" id="photo_description" name="photo_description" rows="2" placeholder="Describe the condition of the item shown in the photos"></textarea>
                                    @error('photo_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-secondary" onclick="history.back()">Cancel</button>
                            <button type="submit" class="btn btn-primary">Generate Control Number</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">About Control Numbers</h5>
                </div>
                <div class="card-body">
                    <p>Control numbers are unique identifiers that allow buyers to create escrow transactions with pre-defined terms.</p>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>How it works:</strong>
                        <ol class="mb-0 mt-2">
                            <li>You generate a control number with transaction details</li>
                            <li>Upload photos documenting the item's condition</li>
                            <li>Share the control number with your buyer</li>
                            <li>The buyer uses this number to create an escrow transaction</li>
                            <li>All transaction details are pre-filled based on your input</li>
                        </ol>
                    </div>
                    
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Note:</strong> Control numbers are valid for 7 days from creation.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let photoCount = 2; // We already have photo1 and photo2
            
            document.getElementById('add-more-photos').addEventListener('click', function() {
                photoCount++;
                
                const photoDiv = document.createElement('div');
                photoDiv.className = 'mb-3';
                photoDiv.innerHTML = `
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <label for="photo${photoCount}" class="form-label">Additional Photo (Optional)</label>
                            <input type="file" class="form-control" id="photo${photoCount}" name="photos[]" accept="image/*" capture>
                        </div>
                        <div class="ms-2 pt-4">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-photo">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                `;
                
                document.getElementById('additional-photos').appendChild(photoDiv);
                
                // Add event listener to the new remove button
                photoDiv.querySelector('.remove-photo').addEventListener('click', function() {
                    photoDiv.remove();
                });
                
                // Limit to a reasonable number of photos (e.g., 5)
                if (photoCount >= 5) {
                    document.getElementById('add-more-photos').style.display = 'none';
                }
            });
        });
    </script>
@endsection