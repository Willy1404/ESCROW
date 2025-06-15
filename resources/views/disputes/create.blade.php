@extends('layouts.app')

@section('title', 'File Dispute - Azania Bank')

@section('header', 'File a Dispute')

@section('header_buttons')
    <a href="{{ route('escrow.show', $escrow->escrow_id) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Transaction
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Dispute Details for Escrow #{{ $escrow->escrow_id }}</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Important:</strong> Filing a dispute is a serious matter. Please provide detailed information and photographic evidence to support your claim.
                    </div>
                    
                    <form action="{{ route('disputes.store', $escrow->escrow_id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason for Dispute</label>
                            <textarea class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" rows="5" required placeholder="Provide specific details about why you're disputing this transaction. Be clear and factual."></textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="card mb-3 border-danger">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-danger">Dispute Evidence Photos (Required)</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="photo" class="form-label">Primary Photo Evidence</label>
                                    <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" accept="image/*" capture required>
                                    <div class="form-text">This photo is mandatory and should clearly show the issue you're disputing.</div>
                                    @error('photo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div id="additional-photos">
                                    <!-- Additional photo fields will be added here -->
                                </div>
                                
                                <button type="button" id="add-more-photos" class="btn btn-outline-secondary btn-sm mt-2">
                                    <i class="fas fa-plus"></i> Add More Photos
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms_agree" required>
                                <label class="form-check-label" for="terms_agree">
                                    I understand that filing a dispute will put the transaction on hold until resolved by bank staff.
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-secondary" onclick="history.back()">Cancel</button>
                            <button type="submit" class="btn btn-danger">File Dispute</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Transaction Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Amount:</strong>
                        <div>{{ $escrow->currency }} {{ number_format($escrow->amount, 2) }}</div>
                    </div>
                    <div class="mb-3">
                        <strong>Seller:</strong>
                        <div>{{ $escrow->seller->name }}</div>
                    </div>
                    <div class="mb-3">
                        <strong>Item:</strong>
                        <div>{{ json_decode($escrow->contract_terms)->item ?? 'N/A' }}</div>
                    </div>
                    <div class="mb-3">
                        <strong>Created On:</strong>
                        <div>{{ $escrow->created_at->format('M d, Y') }}</div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Tips for good evidence:</strong>
                        <ul class="mb-0 mt-2 small">
                            <li>Take clear, well-lit photos</li>
                            <li>Include multiple angles if relevant</li>
                            <li>Show any damage or defects clearly</li>
                            <li>Include reference objects for scale if needed</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let photoCount = 1; // We already have the primary photo
            
            document.getElementById('add-more-photos').addEventListener('click', function() {
                photoCount++;
                
                const photoDiv = document.createElement('div');
                photoDiv.className = 'mb-3';
                photoDiv.innerHTML = `
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <label for="additional_photo_${photoCount}" class="form-label">Additional Photo Evidence</label>
                            <input type="file" class="form-control" id="additional_photo_${photoCount}" name="additional_photos[]" accept="image/*" capture>
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