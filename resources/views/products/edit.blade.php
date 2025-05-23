@extends('layouts.app')

@section('title', '- ' . __('products.edit_product'))

@section('content')
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                    {{ __('products.edit_product') }}
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('home') }}" class="text-muted text-hover-primary">{{ __('products.dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('products.index') }}" class="text-muted text-hover-primary">{{ __('products.all_products') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">{{ __('products.edit') }}</li>
                </ul>
            </div>
        </div>
    </div>

    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl pb-0">
            <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="form d-flex flex-column flex-lg-row mb-0 pb-0">
                @csrf
                @method('PUT')

                <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-12">
                    <div class="position-fixed top-0 end-0 p-3" style="z-index: 11">
                        <div id="formErrorToast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                            <div class="d-flex">
                                <div class="toast-body">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <span id="formErrorMessage">{{ __('products.please_correct_errors') }}</span>
                                </div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- بداية القسم العام -->
                                <div class="card card-flush py-4">
                                    <div class="card-header mb-5">
                                        <div class="card-title">
                                            <h2>{{ __('products.general') }}</h2>
                                        </div>
                                        <div class="card-title">
                                            <h2 class="me-5">{{ __('products.is_device') }}</h2>
                                            <input type="checkbox" class="form-check-input me-2" id="is_mobile" name="is_mobile" {{ $product->mobileDetail ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_mobile">{{ __('products.yes') }}</label>
                                        </div>
                                    </div>
                                    <div class="card-body row pt-0">
                                        <!-- Name -->
                                        <div class="mb-10 col-md-5">
                                            <label class="form-label">{{ __('products.name') }}</label>
                                            <input type="text" class="form-control mb-2" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                                        </div>
                                        <!-- Barcode -->
                                        <div class="mb-10 col-md-7">
                                            <label class="form-label">{{ __('products.barcode') }}</label>
                                            <div class="input-group d-flex align-items-center">
                                                <input type="text" class="form-control mb-2"
                                                       style="border-top-right-radius: 0; border-bottom-right-radius: 0;"
                                                       id="barcode" name="barcode"
                                                       value="{{ old('barcode', $product->barcode) }}"
                                                       onblur="checkBarcode()" required>

                                                <button type="button" class="btn btn-primary"
                                                        style="border-top-left-radius: 0; border-bottom-left-radius: 0; margin-left: -1px; margin-top: -7px;"
                                                        id="generateBarcode">
                                                    {{ __('products.generate') }}
                                                </button>

                                                <button type="button" class="btn btn-primary"
                                                        style="border-top-left-radius: 0; border-bottom-left-radius: 0; margin-left: -1px; margin-top: -7px;"
                                                        id="printBarcode">
                                                    {{ __('products.print') }}
                                                </button>
                                            </div>
                                <div class="d-flex align-items-center mt-2">
                                            <small id="barcode-feedback" class="text-danger d-none">{{ __('products.This barcode already exists!') }}</small>
                                    <small id="barcode-format-feedback" class="text-danger d-none ms-2">{{ __('products.invalid_barcode_format') }}</small>
                                    <small id="barcode-valid-feedback" class="text-success d-none">{{ __('products.valid_barcode') }}</small>
                                </div>
                                        </div>
                                        <!-- Warehouses -->
                                        <div class="card py-10 mb-10">
                                            <div class="card-header">
                                                <h4>{{ __('products.assign_stock_to_warehouses') }}</h4>
                                            </div>
                                            <div class="card-body" id="warehouse-container">
                                                @foreach($product->warehouses as $index => $warehouse)
                                                    <div class="input-group mb-2 warehouse-entry">
                                                        <select class="form-select" name="warehouses[{{ $index }}][id]" required>
                                                            <option value="">{{ __('products.select_warehouse') }}</option>
                                                            @foreach($warehouses as $availableWarehouse)
                                                                <option value="{{ $availableWarehouse->id }}" {{ $warehouse->id == $availableWarehouse->id ? 'selected' : '' }}>
                                                                    {{ $availableWarehouse->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <input type="number" class="form-control" name="warehouses[{{ $index }}][stock]" placeholder="{{ __('products.stock') }}" value="{{ $warehouse->pivot->stock }}" required>
                                                        <input type="number" class="form-control" name="warehouses[{{ $index }}][stock_alert]" placeholder="{{ __('products.stock_alert') }}" value="{{ $warehouse->pivot->stock_alert }}" required>
                                                        <button type="button" class="btn btn-danger remove-warehouse" data-warehouse-id="{{ $warehouse->id }}" data-product-id="{{ $product->id }}">{{ __('products.remove') }}</button>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <button type="button" id="add-warehouse" class="btn btn-primary">{{ __('products.add_warehouse') }}</button>
                                        </div>
                                        <!-- Cost -->
                                        <div class="mb-10 col-md-3">
                                            <label class="form-label">{{ __('products.cost') }}</label>
                                            <input type="number" class="form-control mb-2" id="cost" name="cost" value="{{ old('cost', $product->cost) }}" step="0.01" required>
                                        </div>
                                        <!-- Price -->
                                        <div class="mb-10 col-md-3">
                                            <label class="form-label">{{ __('products.price') }}</label>
                                            <input type="number" class="form-control mb-2" id="price" name="price" value="{{ old('price', $product->price) }}" step="0.01" required>
                                <div id="price-feedback" class="form-text text-danger d-none">{{ __('products.price_cost_warning') }}</div>
                                        </div>
                                        <!-- Wholesale Price -->
                            <div class="mb-10 col-md-3">
                                            <label class="form-label">{{ __('products.wholesale_price') }}</label>
                                <input type="number" class="form-control mb-2" id="wholesale_price" name="wholesale_price" value="{{ old('wholesale_price', $product->wholesale_price) }}" required>
                                        </div>
                                        <!-- Minimum Sale Price -->
                            <div class="mb-10 col-md-3">
                                            <label class="form-label">{{ __('products.lowest_price_for_sale') }}</label>
                                <input type="number" class="form-control mb-2" id="min_sale_price" name="min_sale_price" value="{{ old('min_sale_price', $product->min_sale_price) }}" required>
                                        </div>
                                        <!-- Description -->
                            <div class="mb-10 col-md-12">
                                            <label class="form-label">{{ __('products.description') }}</label>
                                <textarea class="form-control mb-2 min-h-100px" id="description" name="description">{{ old('description', $product->description) }}</textarea>
                                        </div>
                                        <!-- Image -->
                            <div class="mb-10 col-md-4">
                                            <label class="form-label">{{ __('products.image') }}</label>
                                            <div class="image-upload-container position-relative">
                                    <input type="file" class="form-control mb-2" id="image" name="image">
                                                @if($product->image)
                                                    <div class="image-preview mt-3">
                                                        <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}"
                                                             class="img-thumbnail"
                                                             style="max-width: 100%; height: auto; border-radius: 10px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
                                                        <div class="image-actions text-center mt-2">
                                                            <button type="button" class="btn btn-danger btn-sm delete-image" data-image-type="image" data-product-id="{{ $product->id }}">{{ __('products.delete') }}</button>
                                                        </div>
                                                    </div>
                                                @else
                                                    <p class="text-muted mt-2">{{ __('products.no_image_uploaded') }}</p>
                                                @endif
                                            </div>
                                        </div>
                            <div class="mb-10 col-md-4">
                                            <label class="form-label">{{ __('products.category') }}</label>
                                            <div class="d-flex align-items-center">
                                                <select class="form-select me-2" id="category_id" name="category_id" required>
                                                    <option value="">{{ __('products.choose_category') }}</option>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                                            {{ $category->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                                    {{ __('products.add') }}
                                                </button>
                                            </div>
                                        </div>
                            <div class="mb-10 col-md-4">
                                            <label class="form-label">{{ __('products.brand') }}</label>
                                            <div class="d-flex align-items-center">
                                                <select class="form-select me-2" id="brand_id" name="brand_id" required>
                                                    <option value="">{{ __('products.choose_brand') }}</option>
                                                    @foreach($brands as $brand)
                                                        <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                                            {{ $brand->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBrandModal">
                                                    {{ __('products.add') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                    <!-- نهاية القسم العام -->

                    <!-- بداية قسم تفاصيل الجهاز -->
                    <div id="device_details_section" style="display: {{ $product->mobileDetail ? 'block' : 'none' }};">
                                <div class="card card-flush py-4">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>{{ __('products.device_details') }}</h2>
                                        </div>
                                    </div>
                                    <div class="card-body row pt-0">
                                <div class="mb-10 col-md-2">
                                            <label class="form-label">{{ __('products.color') }}</label>
                                    <input type="text" class="form-control mb-2" id="color" name="color" value="{{ old('color', $product->mobileDetail->color ?? '') }}">
                                        </div>
                                <div class="mb-10 col-md-2">
                                            <label class="form-label">{{ __('products.storage') }}</label>
                                    <input type="text" class="form-control mb-2" id="storage" name="storage" value="{{ old('storage', $product->mobileDetail->storage ?? '') }}">
                                        </div>
                                <div class="mb-10 col-md-2">
                                            <label class="form-label">{{ __('products.battery_health') }}</label>
                                    <input type="number" class="form-control mb-2" id="battery_health" name="battery_health" min="0" max="100" value="{{ old('battery_health', $product->mobileDetail->battery_health ?? 0) }}">
                                        </div>
                                <div class="mb-10 col-md-2">
                                            <label class="form-label">{{ __('products.ram') }}</label>
                                    <input type="text" class="form-control mb-2" id="ram" name="ram" value="{{ old('ram', $product->mobileDetail->ram ?? '') }}">
                                        </div>
                                <div class="mb-10 col-md-2">
                                            <label class="form-label">{{ __('products.condition') }}</label>
                                    <input type="text" class="form-control mb-2" id="condition" name="condition" value="{{ old('condition', $product->mobileDetail->condition ?? '') }}">
                                        </div>
                                <div class="mb-10 col-md-2">
                                            <label class="form-label">{{ __('products.with_box') }}</label>
                                            <select class="form-select mb-2" id="has_box" name="has_box">
                                        <option value="">{{ __('products.choose') }}</option>
                                        <option value="1" {{ old('has_box', $product->mobileDetail->has_box ?? '') == '1' ? 'selected' : '' }}>{{ __('products.yes') }}</option>
                                        <option value="0" {{ old('has_box', $product->mobileDetail->has_box ?? '') == '0' || (old('has_box') === null && $product->mobileDetail && $product->mobileDetail->has_box === 0) ? 'selected' : '' }}>{{ __('products.no') }}</option>
                                            </select>
                                        </div>
                                <div class="mb-10 col-md-2">
                                    <label class="form-label">{{ __('products.cpu') }}</label>
                                    <input type="text" class="form-control mb-2" id="cpu" name="cpu" value="{{ old('cpu', $product->mobileDetail->cpu ?? '') }}">
                                </div>
                                <div class="mb-10 col-md-2">
                                    <label class="form-label">{{ __('products.gpu') }}</label>
                                    <input type="text" class="form-control mb-2" id="gpu" name="gpu" value="{{ old('gpu', $product->mobileDetail->gpu ?? '') }}">
                                        </div>
                                <div class="mb-10 col-md-2">
                                    <label class="form-label">{{ __('products.imei') }}</label>
                                    <input type="text" class="form-control mb-2" id="imei" name="imei" value="{{ old('imei', $product->mobileDetail->imei ?? '') }}">
                                        </div>
                                <div class="mb-10 col-md-2">
                                            <label class="form-label">{{ __('products.scan_id') }}</label>
                                            <div class="image-upload-container position-relative">
                                        <input type="file" class="form-control" id="scan_id" name="scan_id" accept="image/*">
                                                @if($product->scan_id)
                                                    <div class="image-preview mt-3">
                                                <img src="{{ Storage::url($product->scan_id) }}" alt="ID Scan"
                                                             class="img-thumbnail"
                                                             style="max-width: 100%; height: auto; border-radius: 10px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
                                                        <div class="image-actions text-center mt-2">
                                                    <button type="button" class="btn btn-danger btn-sm delete-image" data-image-type="scan_id" data-product-id="{{ $product->id }}">{{ __('products.delete') }}</button>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                <div class="mb-10 col-md-2">
                                            <label class="form-label">{{ __('products.scan_documents') }}</label>
                                            <div class="image-upload-container position-relative">
                                        <input type="file" class="form-control" id="scan_documents" name="scan_documents" accept="image/*">
                                                @if($product->scan_documents)
                                                    <div class="image-preview mt-3">
                                                <img src="{{ Storage::url($product->scan_documents) }}" alt="Documents Scan"
                                                             class="img-thumbnail"
                                                             style="max-width: 100%; height: auto; border-radius: 10px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
                                                        <div class="image-actions text-center mt-2">
                                                    <button type="button" class="btn btn-danger btn-sm delete-image" data-image-type="scan_documents" data-product-id="{{ $product->id }}">{{ __('products.delete') }}</button>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                <div class="mb-10 col-md-2">
                                    <label class="form-label">{{ __('products.payment_method') }}</label>
                                    <select class="form-select" id="payment_method" name="payment_method">
                                        <option value="">{{ __('products.choose_payment_method') }}</option>
                                        <option value="cash" {{ old('payment_method', $product->payment_method) == 'cash' ? 'selected' : '' }}>{{ __('products.cash') }}</option>
                                        <option value="credit" {{ old('payment_method', $product->payment_method) == 'credit' ? 'selected' : '' }}>{{ __('products.credit') }}</option>
                                    </select>
                                </div>
                                <div class="mb-10 col-md-12">
                                    <label class="form-label">{{ __('products.device_description') }}</label>
                                    <textarea class="form-control mb-2 min-h-100px" id="device_description" name="device_description">{{ old('device_description', $product->mobileDetail->device_description ?? '') }}</textarea>
                                </div>
                                <div class="mb-10 col-md-4">
                                    <label class="form-label">{{ __('products.seller_name') }}</label>
                                    <input type="text" class="form-control" id="seller_name" name="seller_name" value="{{ old('seller_name', $product->seller_name) }}">
                                </div>
                                        <div class="mb-10 col-md-4">
                                            <label class="form-label">{{ __('products.client_type') }}</label>
                                    <select class="form-select" id="client_type" name="client_type" onchange="toggleClientSections(this.value)">
                                                <option value="">{{ __('products.choose_client_type') }}</option>
                                        <option value="customer" {{ old('client_type', $product->client_type) == 'customer' ? 'selected' : '' }}>{{ __('products.customer') }}</option>
                                        <option value="supplier" {{ old('client_type', $product->client_type) == 'supplier' ? 'selected' : '' }}>{{ __('products.supplier') }}</option>
                                            </select>
                                        </div>
                                <div class="mb-10 col-md-4" id="customer_section" style="display: {{ old('client_type', $product->client_type) == 'customer' ? 'block' : 'none' }};">
                                            <label class="form-label">{{ __('products.select_customer') }}</label>
                                    <div class="d-flex align-items-center">
                                        <select class="form-select me-2" id="customer_id" name="customer_id">
                                                <option value="">{{ __('products.choose_customer') }}</option>
                                                @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}" {{ old('customer_id', $product->customer_id) == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                                @endforeach
                                            </select>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                                            {{ __('products.add') }}
                                        </button>
                                    </div>
                                        </div>
                                <div class="mb-10 col-md-4" id="supplier_section" style="display: {{ old('client_type', $product->client_type) == 'supplier' ? 'block' : 'none' }};">
                                            <label class="form-label">{{ __('products.select_supplier') }}</label>
                                    <div class="d-flex align-items-center">
                                        <select class="form-select me-2" id="supplier_id" name="supplier_id">
                                                <option value="">{{ __('products.choose_supplier') }}</option>
                                                @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}" {{ old('supplier_id', $product->supplier_id) == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                                @endforeach
                                            </select>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                                            {{ __('products.add') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- نهاية قسم تفاصيل الجهاز -->

                    <!-- زر الحفظ -->
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" id="submit-form" class="btn btn-primary">
                            <span class="indicator-label">{{ __('products.save') }}</span>
                            <span class="indicator-progress">{{ __('products.please_wait') }}
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">{{ __('products.add_new_category') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addCategoryForm">
                        @csrf
                        <div class="mb-3">
                            <label for="category_name" class="form-label">{{ __('products.category_name') }}</label>
                            <input type="text" class="form-control" id="category_name" name="name" required>
                        </div>
                        <button type="submit" class="btn btn-primary">{{ __('products.add') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addBrandModal" tabindex="-1" aria-labelledby="addBrandModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBrandModalLabel">{{ __('products.add_new_brand') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addBrandForm">
                        @csrf
                        <div class="mb-3">
                            <label for="brand_name" class="form-label">{{ __('products.brand_name') }}</label>
                            <input type="text" class="form-control" id="brand_name" name="name" required>
                            <small id="brand-error" class="text-danger d-none">{{ __('products.brand_already_exists') }}</small>
                        </div>
                        <button type="submit" class="btn btn-primary">{{ __('products.add') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @section('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Image preview functionality
                function initImagePreviews() {
                    const imageInputs = document.querySelectorAll('input[type="file"]');
                    
                    imageInputs.forEach(input => {
                        input.addEventListener('change', function() {
                            const fileId = this.id;
                            const previewContainer = this.closest('.image-upload-container');
                            
                            if (this.files && this.files[0]) {
                                // Remove existing preview if present
                                const existingPreview = previewContainer.querySelector('.image-preview');
                                if (existingPreview) {
                                    existingPreview.remove();
                                }
                                
                                // Create new preview
                                const preview = document.createElement('div');
                                preview.classList.add('image-preview', 'mt-3');
                                
                                const img = document.createElement('img');
                                img.src = URL.createObjectURL(this.files[0]);
                                img.alt = "Preview";
                                img.classList.add('img-thumbnail');
                                img.style.cssText = "max-width: 100%; height: auto; border-radius: 10px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);";
                                
                                const removeBtn = document.createElement('button');
                                removeBtn.type = 'button';
                                removeBtn.classList.add('btn', 'btn-danger', 'btn-sm', 'mt-2');
                                removeBtn.innerHTML = '<i class="fas fa-times"></i> {{ __("products.remove") }}';
                                removeBtn.addEventListener('click', function() {
                                    input.value = '';
                                    preview.remove();
                                });
                                
                                preview.appendChild(img);
                                preview.appendChild(removeBtn);
                                previewContainer.appendChild(preview);
                                
                                // Revoke object URL when done to free memory
                                img.onload = function() {
                                    URL.revokeObjectURL(this.src);
                                }
                            }
                        });
                    });
                }
                
                // Initialize image previews
                initImagePreviews();

                const isMobileCheckbox = document.getElementById('is_mobile');
                const deviceDetailsSection = document.getElementById('device_details_section');

                function toggleDeviceDetailsSection() {
                    deviceDetailsSection.style.display = isMobileCheckbox.checked ? 'block' : 'none';
                }

                toggleDeviceDetailsSection();

                isMobileCheckbox.addEventListener('change', toggleDeviceDetailsSection);
            });

            document.addEventListener('DOMContentLoaded', function () {
                const addWarehouseButton = document.getElementById('add-warehouse');
                const warehouseContainer = document.getElementById('warehouse-container');
                let warehouseIndex = {{ count($product->warehouses) }};

                addWarehouseButton.addEventListener('click', function () {
                    const newEntry = document.createElement('div');
                    newEntry.classList.add('input-group', 'mb-2', 'warehouse-entry');

                    newEntry.innerHTML = `
                        <select class="form-select" name="warehouses[${warehouseIndex}][id]" required>
                            <option value="">{{ __('products.select_warehouse') }}</option>
                            @foreach($warehouses as $availableWarehouse)
                                <option value="{{ $availableWarehouse->id }}">{{ $availableWarehouse->name }}</option>
                            @endforeach
                                </select>
                                <input type="number" class="form-control" name="warehouses[${warehouseIndex}][stock]" placeholder="{{ __('products.stock') }}" required>
                        <input type="number" class="form-control" name="warehouses[${warehouseIndex}][stock_alert]" placeholder="{{ __('products.stock_alert') }}" required>
                        <button type="button" class="btn btn-danger remove-warehouse">{{ __('products.remove') }}</button>
                    `;

                    warehouseContainer.appendChild(newEntry);

                    warehouseIndex++;

                    newEntry.querySelector('.remove-warehouse').addEventListener('click', function () {
                        newEntry.remove();
                    });
                });
            });

            document.addEventListener('DOMContentLoaded', function () {
                const removeButtons = document.querySelectorAll('.remove-warehouse');

                removeButtons.forEach((button) => {
                    button.addEventListener('click', function () {
                        const warehouseId = this.getAttribute('data-warehouse-id');
                        const productId = this.getAttribute('data-product-id');
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                        if (confirm('Are you sure you want to remove this warehouse?')) {
                            fetch(`/products/${productId}/remove-warehouse/${warehouseId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Content-Type': 'application/json',
                                },
                            })
                                .then((response) => response.json())
                                .then((data) => {
                                    if (data.success) {
                                        alert(data.message);
                                        location.reload();
                                    } else {
                                        alert(data.message || 'Error removing warehouse.');
                                    }
                                })
                                .catch((error) => console.error('Error:', error));
                        }
                    });
                });
            });

            document.addEventListener('DOMContentLoaded', function () {
                const paymentMethodSelect = document.getElementById('payment_method');
                const clientTypeSelect = document.getElementById('client_type');
                const customerSection = document.getElementById('customer_section');
                const supplierSection = document.getElementById('supplier_section');

                function toggleClientType() {
                    if (clientTypeSelect.value === 'customer') {
                        customerSection.style.display = 'block';
                        supplierSection.style.display = 'none';
                    } else if (clientTypeSelect.value === 'supplier') {
                        customerSection.style.display = 'none';
                        supplierSection.style.display = 'block';
                    } else {
                        customerSection.style.display = 'none';
                        supplierSection.style.display = 'none';
                    }
                }

                function handlePaymentMethodChange() {
                    if (paymentMethodSelect.value === 'credit') {
                        clientTypeSelect.value = 'supplier';
                        clientTypeSelect.setAttribute('disabled', true);
                        customerSection.style.display = 'none';
                        supplierSection.style.display = 'block';
                    } else {
                        clientTypeSelect.removeAttribute('disabled');
                        toggleClientType();
                    }
                }

                toggleClientType();
                handlePaymentMethodChange();

                clientTypeSelect.addEventListener('change', toggleClientType);

                paymentMethodSelect.addEventListener('change', handlePaymentMethodChange);
            });

            document.addEventListener('DOMContentLoaded', function () {
                const deleteButtons = document.querySelectorAll('.delete-image');

                deleteButtons.forEach((button) => {
                    button.addEventListener('click', function () {
                        const imageType = this.getAttribute('data-image-type');
                        const productId = this.getAttribute('data-product-id');
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                        if (confirm('Are you sure you want to delete this image?')) {
                            fetch(`/products/${productId}/delete-image`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    image_type: imageType,
                                }),
                            })
                                .then((response) => response.json())
                                .then((data) => {
                                    if (data.success) {
                                        alert(data.message);
                                        location.reload();
                                    } else {
                                        alert(data.message || 'Error deleting image.');
                                    }
                                })
                                .catch((error) => console.error('Error:', error));
                        }
                    });
                });
            });

            function checkBarcode() {
                let barcodeInput = document.getElementById("barcode");
                let feedback = document.getElementById("barcode-feedback");
                let barcode = barcodeInput.value.trim();
                let productId = "{{ $product->id ?? '' }}";

                if (barcode === "") return;

                fetch(`{{ route('products.checkBarcode', '') }}/${barcode}?product_id=${productId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            feedback.classList.remove("d-none");
                            barcodeInput.classList.add("is-invalid");
                        } else {
                            feedback.classList.add("d-none");
                            barcodeInput.classList.remove("is-invalid");
                        }
                    })
                    .catch(error => console.error("Error checking barcode:", error));
            }

            document.addEventListener("DOMContentLoaded", function () {
                document.getElementById("barcode").addEventListener("input", checkBarcode);
            });

            document.getElementById('addCategoryForm').addEventListener('submit', function (event) {
                event.preventDefault();

                const form = document.getElementById('addCategoryForm');
                const formData = new FormData(form);

                fetch('{{ route('categories.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: formData,
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const categorySelect = document.getElementById('category_id');
                            const newOption = document.createElement('option');
                            newOption.value = data.category.id;
                            newOption.textContent = data.category.name;
                            newOption.selected = true;
                            categorySelect.appendChild(newOption);

                            const modal = bootstrap.Modal.getInstance(document.getElementById('addCategoryModal'));
                            modal.hide();

                            form.reset();
                        } else {
                            alert(data.message || 'Error saving category.');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });

            document.getElementById('addBrandForm').addEventListener('submit', function (event) {
                event.preventDefault();

                const form = document.getElementById('addBrandForm');
                const formData = new FormData(form);

                fetch('{{ route('brands.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: formData,
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const brandSelect = document.getElementById('brand_id');
                            const newOption = document.createElement('option');
                            newOption.value = data.brand.id;
                            newOption.textContent = data.brand.name;
                            newOption.selected = true;
                            brandSelect.appendChild(newOption);

                            const modal = bootstrap.Modal.getInstance(document.getElementById('addBrandModal'));
                            modal.hide();

                            form.reset();
                        } else {
                            alert(data.message || 'Error saving brand.');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });

            document.addEventListener('DOMContentLoaded', function () {
                const price = document.getElementById('price');
                const cost = document.getElementById('cost');
                const priceFeedback = document.getElementById('price-feedback');

                function validatePrice() {
                    if (parseFloat(price.value) < parseFloat(cost.value)) {
                        priceFeedback.classList.remove('d-none');
                        price.classList.add('is-invalid');
                    } else {
                        priceFeedback.classList.add('d-none');
                        price.classList.remove('is-invalid');
                    }
                }

                if (price && cost) {
                    price.addEventListener('input', validatePrice);
                    cost.addEventListener('input', validatePrice);
                    // Initial validation
                    validatePrice();
                }
            });

            document.addEventListener('DOMContentLoaded', function() {
                // Generate barcode functionality
                const generateBarcodeBtn = document.getElementById('generateBarcode');
                const barcodeInput = document.getElementById('barcode');
                const barcodeFeedback = document.getElementById('barcode-feedback');
                const barcodeFormatFeedback = document.getElementById('barcode-format-feedback');
                const barcodeValidFeedback = document.getElementById('barcode-valid-feedback');
                
                if (generateBarcodeBtn) {
                    generateBarcodeBtn.addEventListener('click', function() {
                        // Clear all feedback messages
                        barcodeFeedback.classList.add('d-none');
                        barcodeFormatFeedback.classList.add('d-none');
                        barcodeValidFeedback.classList.add('d-none');
                        
                        // Generate random 13-digit barcode (EAN-13 format)
                        const randomBarcode = Math.floor(Math.random() * 10000000000000).toString().padStart(13, '0');
                        barcodeInput.value = randomBarcode;
                        
                        // Check if barcode already exists
                        checkBarcode();
                    });
                }
                
                function toggleClientSections(clientType) {
                    const customerSection = document.getElementById('customer_section');
                    const supplierSection = document.getElementById('supplier_section');
                    
                    if (clientType === 'customer') {
                        customerSection.style.display = 'block';
                        supplierSection.style.display = 'none';
                    } else if (clientType === 'supplier') {
                        customerSection.style.display = 'none';
                        supplierSection.style.display = 'block';
                    } else {
                        customerSection.style.display = 'none';
                        supplierSection.style.display = 'none';
                    }
                }
                
                // Make toggleClientSections available globally
                window.toggleClientSections = toggleClientSections;
            });
        </script>
    @endsection

    <style>
        #kt_ecommerce_add_product_details:not(.show) {
            display: none !important;
            height: 0 !important;
            overflow: hidden !important;
        }
    </style>

@endsection
