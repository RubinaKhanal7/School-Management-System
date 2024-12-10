@extends('backend.layouts.master')

@section('content')
<div class="container">
    <h2>Inventory Details</h2>
    
    <div class="card">
        <div class="card-body">
            <p><strong>School:</strong> {{ $inventory->school ? $inventory->school->name : 'N/A' }}</p>
            <p><strong>Inventory Head:</strong> {{ $inventory->inventoryHead ? $inventory->inventoryHead->name : 'N/A' }}</p>
            <p><strong>Source:</strong> {{ $inventory->source ? $inventory->source->source_title : 'N/A' }}</p>
            <p><strong>Item Name:</strong> {{ $inventory->name }}</p>
            <p><strong>Condition:</strong> {{ $inventory->condition }}</p>
            <p><strong>Cost Price:</strong> {{ $inventory->costprice }}</p>
            <p><strong>Tax:</strong> {{ $inventory->tax ?? 'N/A' }}</p>
            <p><strong>Specifications/Details:</strong> {{ $inventory->specs_details ?? 'N/A' }}</p>
            <p><strong>Estimated Life:</strong> {{ $inventory->guess_life ?? 'N/A' }}</p>
            <p><strong>Tax-Free Amount:</strong> {{ $inventory->tax_free_amount ?? 'N/A' }}</p>
            <p><strong>Tax-Free Details:</strong> {{ $inventory->tax_free_details ?? 'N/A' }}</p>
            <p><strong>Depreciation Percentage:</strong> {{ $inventory->depreciation_percentage ?? 'N/A' }}</p>
            <p><strong>Other Details:</strong> {{ $inventory->other_details ?? 'N/A' }}</p>

            <!-- Land Details -->
            @if($inventory->land_area || $inventory->land_type || $inventory->land_costprice || 
                 $inventory->land_owner_certificate_no || $inventory->land_location || 
                 $inventory->land_kitta_no || $inventory->land_market_value || 
                 $inventory->if_donation)
            <h4>Land Details</h4>
                <p><strong>Land Area:</strong> {{ $inventory->land_area ?? 'N/A' }}</p>
                <p><strong>Land Type:</strong> {{ $inventory->land_type ?? 'N/A' }}</p>
                <p><strong>Land Cost Price:</strong> {{ $inventory->land_costprice ?? 'N/A' }}</p>
                <p><strong>Land Owner Certificate No:</strong> {{ $inventory->land_owner_certificate_no ?? 'N/A' }}</p>
                <p><strong>Land Location:</strong> {{ $inventory->land_location ?? 'N/A' }}</p>
                <p><strong>Kitta No:</strong> {{ $inventory->land_kitta_no ?? 'N/A' }}</p>
                <p><strong>Donation:</strong> {{ $inventory->if_donation ? 'Yes' : 'No' }}</p>
                <p><strong>Market Value:</strong> {{ $inventory->land_market_value ?? 'N/A' }}</p>
            @endif
            
            <!-- Physical Structure Details -->
            @if($inventory->if_physical_structure_there || $inventory->physical_structure_detail)
            <h4>Physical Structure</h4>
                <p><strong>Structure Exists:</strong> {{ $inventory->if_physical_structure_there ? 'Yes' : 'No' }}</p>
                <p><strong>Structure Details:</strong> {{ $inventory->physical_structure_detail ?? 'N/A' }}</p>
            @endif

            <p><strong>Date Added:</strong> {{ $inventory->created_at }}</p>
        </div>
    </div>
    
    <a href="{{ route('admin.municipalityAdmin.inventoryReport.report') }}" class="btn btn-primary mt-3">Back to Report</a>
</div>
@endsection
