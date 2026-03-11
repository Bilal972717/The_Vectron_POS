@extends('backend.master')

@section('title', 'Create Product')

@section('content')
<div class="card">
  <div class="card-body">
    <form action="{{ route('backend.admin.products.store') }}" method="post" class="accountForm"
      enctype="multipart/form-data">
      @csrf
      <div class="card-body">
        <div class="row">
          <div class="mb-3 col-md-6">
            <label for="title" class="form-label">
              Name
              <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control" placeholder="Enter title" name="name"
              value="{{ old('name') }}" required>
          </div>
          <div class="mb-3 col-md-6">
            <label for="sku" class="form-label">
              Barcode (EAN-13)
              <span class="text-danger">*</span>
            </label>
            <div class="input-group">
              <input type="text" class="form-control" name="sku" id="sku_field"
                value="{{ old('sku', $autoSku ?? '') }}" required maxlength="13" pattern="\d{13}" title="Must be 13 digits">
              <div class="input-group-append">
                <button type="button" class="btn btn-outline-secondary" id="regenerate_sku" title="Generate new EAN-13">
                  <i class="fas fa-sync-alt"></i>
                </button>
              </div>
            </div>
            <small class="text-muted">Auto-generated EAN-13. You can change it or regenerate.</small>
            <div id="barcode_preview" class="mt-2"></div>
          </div>
          <div class="mb-3 col-md-6">
            <label for="brand_id" class="form-label">
              Brand
              <span class="text-danger">*</span>
            </label>
            <select class="form-control select2" style="width: 100%;" name="brand_id" required>
              <option value="">Select Brand</option>
              @foreach ($brands as $item)
              <option value={{ $item->id }}
                {{ old('brand_id') == $item->id ? 'selected' : '' }}>
                {{ $item->name }}
              </option>
              @endforeach
            </select>
          </div>
          <div class="mb-3 col-md-6">
            <label for="category_id" class="form-label">
              Category
              <span class="text-danger">*</span>
            </label>
            <select class="form-control select2" style="width: 100%;" name="category_id" required>
              <option value="">Select Category</option>
              @foreach ($categories as $item)
              <option value={{ $item->id }}
                {{ old('category_id') == $item->id ? 'selected' : '' }}>
                {{ $item->name }}
              </option>
              @endforeach
            </select>
          </div>
          {{-- Feature 5: Sub-category --}}
          <div class="mb-3 col-md-6">
            <label class="form-label">Sub-Category <small class="text-muted">(optional)</small></label>
            <select class="form-control select2" style="width: 100%;" name="sub_category_id">
              <option value="">Select Sub-Category</option>
              @foreach ($categories as $item)
              <option value="{{ $item->id }}" {{ old('sub_category_id') == $item->id ? 'selected' : '' }}>
                {{ $item->name }}
              </option>
              @endforeach
            </select>
          </div>
          <div class="mb-3 col-md-6">
            <label for="price" class="form-label">
              Price
              <span class="text-danger">*</span>
            </label>
            <input type="number" step="0.01" min="0" class="form-control"
              placeholder="Enter price" name="price" value="{{ old('price') }}" required>
          </div>
          <!-- <div class="mb-3 col-md-6">
          <label for="quantity" class="form-label">
            Initial Stock
            <span class="text-danger">*</span>
          </label>
          <input type="number" class="form-control" placeholder="Enter quantity" name="quantity"
            value="{{ old('quantity') }}" required>
        </div> -->
          <div class="mb-3 col-md-6">
            <label for="unit_id" class="form-label">
              Unit
              <span class="text-danger">*</span>
            </label>
            <select class="form-control" style="width: 100%;" name="unit_id" required>
              <option value="">Select Unit</option>
              @foreach ($units as $item)
              <option value={{ $item->id }}
                {{ old(key: 'unit_id') == $item->id ? 'selected' : '' }}>
                {{ $item->title . ' (' . $item->short_name . ')' }}
              </option>
              @endforeach
            </select>
          </div>
          <div class="mb-3 col-md-6">
            <label for="discount_type" class="form-label">
              Discount Type
            </label>
            <select class="form-control form-select" name="discount_type">
              <option value="">Select Discount Type</option>
              <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>
                Fixed
              </option>
              <option value="percentage"
                {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>
                Percentage
              </option>
            </select>
          </div>
          <div class="mb-3 col-md-6">
            <label for="purchase_price" class="form-label">
              Purchase Price
              <span class="text-danger">*</span>
            </label>
            <input type="number" step="0.01" min="0" class="form-control"
              placeholder="Enter purchase Price" name="purchase_price" value="{{ old('purchase_price') }}" required>
          </div>
          <div class="mb-3 col-md-6">
            <label for="discount_value" class="form-label">
              Discount Amount
            </label>
            <input type="number" step="0.01" min="0" class="form-control"
              placeholder="Enter discount" name="discount" value="{{ old('discount') }}">
          </div>
          <div class="mb-3 col-md-6">
            <label for="thumbnailInput" class="form-label">
              Image
            </label>
            <div class="image-upload-container" id="imageUploadContainer">
              <input type="file" class="form-control" name="product_image" id="thumbnailInput" accept="image/*" style="display: none;">
              <div class="thumb-preview" id="thumbPreviewContainer">
                <img src="{{ asset('backend/assets/images/blank.png') }}" alt="Thumbnail Preview"
                  class="img-thumbnail d-none" id="thumbnailPreview">
                <div class="upload-text">
                  <i class="fas fa-plus-circle"></i>
                  <span>Upload Image</span>
                </div>
              </div>
            </div>
          </div>

          <div class="mb-3 col-md-12">
            <label for="description" class="form-label">
              Description
            </label>
            <textarea class="form-control" placeholder="Enter description" name="description">{{ old('description') }}</textarea>
          </div>

          <div class="mb-3 col-md-6">
            <label for="expire_date" class="form-label">
              Expire date
            </label>
            <div class="input-group date" id="reservationdate" data-target-input="nearest">
              <input type="text" placeholder="Enter product expire date" class="form-control datetimepicker-input" data-target="#reservationdate" name="expire_date" value="{{ old('expire_date') }}" />
              <div class="input-group-append" data-target="#reservationdate" data-toggle="datetimepicker">
                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
              </div>
            </div>
          </div>
          <div class="mb-3 col-md-12">
            <div class="form-switch px-4">
              <input type="hidden" name="status" value="0">
              <input class="form-check-input" type="checkbox" name="status" id="active"
                value="1" checked>
              <label class="form-check-label" for="active">
                Active
              </label>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <button type="submit" class="btn bg-gradient-primary">Create</button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('style')
<style>
  .select2-container--default .select2-selection--single {
    height: calc(1.5em + 0.75rem + 2px) !important;
  }
</style>

@endpush
@push('script')
<script src="{{ asset('js/image-field.js') }}"></script>
<script>
  $(function() {
    $('#reservationdate').datetimepicker({ format: 'YYYY-MM-DD' });

    // Barcode live preview
    function updateBarcodePreview(sku) {
      if (sku.length === 13 && /^\d{13}$/.test(sku)) {
        $('#barcode_preview').html('<img src="/admin/products/barcode-svg?sku=' + encodeURIComponent(sku) + '" style="max-height:60px;" alt="barcode">');
      } else {
        $('#barcode_preview').html('');
      }
    }
    updateBarcodePreview($('#sku_field').val());
    $('#sku_field').on('input', function() { updateBarcodePreview($(this).val()); });

    // Regenerate EAN-13
    $('#regenerate_sku').on('click', function() {
      $.get('/admin/products/generate-sku', function(data) {
        $('#sku_field').val(data.sku);
        updateBarcodePreview(data.sku);
      });
    });
  });
</script>
@endpush