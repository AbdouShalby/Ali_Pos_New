<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Warehouse;
use App\Models\CashRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class POSController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        
        // التحقق من صلاحيات المستخدم للوصول إلى نقطة البيع
        $this->middleware('permission:manage sales', ['only' => ['checkout']]);
        
        // إضافة تعليمات CORS للسماح بالاتصال من الخادم المحلي
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token, Origin, Authorization');
    }

    /**
     * عرض واجهة نقطة البيع
     */
    public function index(Request $request)
    {
        // تعيين اللغة من الطلب إذا تم تمريرها
        if ($request->has('locale')) {
            $locale = $request->locale;
            if (in_array($locale, ['en', 'ar'])) {
                app()->setLocale($locale);
                session(['locale' => $locale]);
                session(['text_direction' => $locale == 'ar' ? 'rtl' : 'ltr']);
            }
        }
        
        $categories = Category::all();
        $brands = Brand::all();
        $customers = Customer::all();
        $warehouses = Warehouse::all();
        $defaultWarehouse = $warehouses->first();
        
        // جلب المنتجات المتاحة في المخزن الافتراضي
        $products = [];
        if ($defaultWarehouse) {
            $products = $defaultWarehouse->products()
                ->withPivot('stock')
                ->where('product_warehouse.stock', '>', 0)
                ->get();
        }

        return view('pos.index', compact(
            'categories',
            'brands',
            'customers',
            'warehouses',
            'defaultWarehouse',
            'products'
        ))->with('activePage', 'pos');
    }

    /**
     * جلب منتجات مخزن معين
     */
    public function getWarehouseProducts(Request $request)
    {
        try {
            $warehouseId = $request->warehouse_id;
            
            if (!$warehouseId) {
                return response()->json(['error' => 'يجب تحديد معرف المخزن'], 400);
            }
            
            $warehouse = Warehouse::find($warehouseId);
            
            if (!$warehouse) {
                return response()->json(['error' => 'المخزن غير موجود'], 404);
            }
            
            $products = $warehouse->products()
                ->withPivot('stock')
                ->where('product_warehouse.stock', '>', 0)
                ->get()
                ->map(function ($product) {
                    // التأكد من أن السعر رقم
                    $product->price = (float) $product->price;
                    return $product;
                });
            
            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json(['error' => 'حدث خطأ أثناء جلب المنتجات: ' . $e->getMessage()], 500);
        }
    }

    /**
     * البحث عن منتج بالباركود
     */
    public function searchByBarcode(Request $request)
    {
        try {
            $barcode = $request->barcode;
            $warehouseId = $request->warehouse_id;
            
            if (!$barcode) {
                return response()->json(['error' => 'يجب تحديد الباركود'], 400);
            }
            
            if (!$warehouseId) {
                return response()->json(['error' => 'يجب تحديد معرف المخزن'], 400);
            }
            
            $product = Product::where('barcode', $barcode)
                ->whereHas('warehouses', function($query) use ($warehouseId) {
                    $query->where('warehouse_id', $warehouseId)
                        ->where('stock', '>', 0);
                })
                ->with(['warehouses' => function($query) use ($warehouseId) {
                    $query->where('warehouse_id', $warehouseId);
                }])
                ->first();
                
            if (!$product) {
                return response()->json(['error' => 'المنتج غير موجود أو غير متوفر في المخزن'], 404);
            }
            
            // التأكد من أن السعر رقم
            $product->price = (float) $product->price;
            
            return response()->json($product);
        } catch (\Exception $e) {
            return response()->json(['error' => 'حدث خطأ أثناء البحث عن المنتج: ' . $e->getMessage()], 500);
        }
    }

    /**
     * البحث عن منتجات
     */
    public function searchProducts(Request $request)
    {
        try {
            // تسجيل البيانات المستلمة للتشخيص
            \Log::info('Search Products Request:', $request->all());
            
            $search = $request->search;
            $warehouseId = $request->warehouse_id;
            $categoryId = $request->category_id;
            $brandId = $request->brand_id;
            
            // تسجيل المتغيرات بعد المعالجة
            \Log::info('Processed Variables:', [
                'search' => $search,
                'warehouseId' => $warehouseId,
                'categoryId' => $categoryId,
                'brandId' => $brandId
            ]);
            
            if (!$warehouseId) {
                \Log::warning('Missing warehouse_id in search request');
                return response()->json(['error' => 'يجب تحديد معرف المخزن'], 400);
            }
            
            $warehouse = Warehouse::find($warehouseId);
            
            if (!$warehouse) {
                \Log::warning('Warehouse not found: ' . $warehouseId);
                return response()->json(['error' => 'المخزن غير موجود'], 404);
            }
            
            $query = Product::query();
            
            // البحث بالاسم
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('barcode', 'like', "%{$search}%");
                });
            }
            
            // تصفية حسب الفئة
            if ($categoryId && $categoryId !== '' && $categoryId !== 'null' && $categoryId !== 'undefined') {
                \Log::info('Filtering by category: ' . $categoryId);
                
                // التحقق من وجود الفئة
                $categoryExists = \App\Models\Category::where('id', $categoryId)->exists();
                if ($categoryExists) {
                    $query->where('category_id', $categoryId);
                } else {
                    \Log::warning('Category not found: ' . $categoryId);
                }
            }
            
            // تصفية حسب العلامة التجارية
            if ($brandId && $brandId !== '' && $brandId !== 'null' && $brandId !== 'undefined') {
                \Log::info('Filtering by brand: ' . $brandId);
                
                // التحقق من وجود العلامة التجارية
                $brandExists = \App\Models\Brand::where('id', $brandId)->exists();
                if ($brandExists) {
                    $query->where('brand_id', $brandId);
                } else {
                    \Log::warning('Brand not found: ' . $brandId);
                }
            }
            
            // التأكد من وجود جدول product_warehouse
            if (\Schema::hasTable('product_warehouse')) {
                // التأكد من توفر المنتج في المخزن المحدد
                $query->whereHas('warehouses', function($q) use ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId)
                      ->where('stock', '>', 0);
                });
            } else {
                \Log::error('Table product_warehouse does not exist');
                return response()->json(['error' => 'جدول المنتجات والمخازن غير موجود'], 500);
            }
            
            // تسجيل الاستعلام SQL للتشخيص
            \DB::enableQueryLog();
            $products = $query->with(['warehouses' => function($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            }])->get();
            \Log::info('SQL Query:', \DB::getQueryLog());
            
            // تحويل السعر إلى رقم عشري وإضافة معلومات المخزون
            $products = $products->map(function ($product) use ($warehouseId) {
                try {
                    // تحويل السعر إلى رقم عشري
                    $product->price = (float) $product->price;
                    
                    // التأكد من وجود معلومات المخزون
                    $stock = 0;
                    if ($product->warehouses && count($product->warehouses) > 0) {
                        foreach ($product->warehouses as $warehouse) {
                            if ($warehouse->id == $warehouseId && isset($warehouse->pivot) && isset($warehouse->pivot->stock)) {
                                $stock = $warehouse->pivot->stock;
                                break;
                            }
                        }
                    }
                    
                    // إضافة معلومات المخزون مباشرة إلى المنتج
                    $product->stock = $stock;
                    
                    return $product;
                } catch (\Exception $e) {
                    \Log::error('Error processing product ID ' . $product->id . ': ' . $e->getMessage());
                    $product->price = 0;
                    $product->stock = 0;
                    return $product;
                }
            });
            
            \Log::info('Found ' . count($products) . ' products');
            return response()->json($products);
        } catch (\Exception $e) {
            \Log::error('Error in searchProducts: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'حدث خطأ أثناء البحث عن المنتجات: ' . $e->getMessage()], 500);
        }
    }

    /**
     * إتمام عملية البيع
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function checkout(Request $request)
    {
        // تسجيل محاولة إتمام عملية البيع
        \Log::info('Checkout attempt by user: ' . Auth::id(), [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'data' => $request->except(['_token'])
        ]);
        
        // التحقق من البيانات المدخلة
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'payment_method' => 'required|in:cash,card,bank_transfer',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
        
        DB::beginTransaction();
        
        try {
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['quantity'] * $item['price'];
            }

            // حساب الخصم والضريبة
            $discount = $request->discount ?? 0;
            $afterDiscount = $subtotal - $discount;
            
            // حساب الضريبة كنسبة مئوية
            $taxPercent = $request->tax ?? 0;
            $taxAmount = ($afterDiscount * $taxPercent) / 100;
            
            // إجمالي الفاتورة
            $totalAmount = $afterDiscount + $taxAmount;
            
            // إنشاء فاتورة البيع
            $sale = Sale::create([
                'customer_id' => $request->customer_id,
                'sale_date' => now(),
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_percentage' => $taxPercent,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
                'user_id' => Auth::id(),
                'warehouse_id' => $request->warehouse_id,
            ]);
            
            $warehouseId = $request->warehouse_id;
            
            // إضافة عناصر الفاتورة
            foreach ($request->items as $item) {
                $productId = $item['product_id'];
                $quantity = $item['quantity'];
                $price = $item['price'];
                
                // التحقق من توفر المنتج في المخزن
                $warehouseProduct = DB::table('product_warehouse')
                    ->where('warehouse_id', $warehouseId)
                    ->where('product_id', $productId)
                    ->first();
                
                if (!$warehouseProduct || $warehouseProduct->stock < $quantity) {
                    DB::rollBack();
                    return response()->json([
                        'error' => 'الكمية المطلوبة من المنتج غير متوفرة في المخزن'
                    ], 422);
                }
                
                // تحديث مخزون المنتج
                DB::table('product_warehouse')
                    ->where('warehouse_id', $warehouseId)
                    ->where('product_id', $productId)
                    ->decrement('stock', $quantity);
                
                // إضافة عنصر الفاتورة
                $sale->saleDetails()->create([
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'unit_price' => $price,
                    'total_price' => $price * $quantity,
                ]);
            }
            
            // إضافة المبلغ للخزنة إذا كان الدفع نقدي
            if ($request->payment_method === 'cash') {
                // إنشاء سجل صندوق نقدي جديد إذا لم يكن موجوداً
                $cashRegister = CashRegister::firstOrCreate(
                    ['id' => 1],
                    ['transaction_type' => 'initial', 'amount' => 0, 'balance' => 0]
                );

                // تحديث رصيد الصندوق
                CashRegister::updateBalance(
                    'sale',
                    $totalAmount,
                    'Sale #' . $sale->id
                );

                // تحديث cash_register_id للمستخدم إذا لم يكن معيناً
                if (!Auth::user()->cash_register_id) {
                    Auth::user()->update(['cash_register_id' => $cashRegister->id]);
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'تمت عملية البيع بنجاح',
                'sale_id' => $sale->id
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in checkout: ' . $e->getMessage());
            return response()->json([
                'error' => 'حدث خطأ أثناء إتمام عملية البيع: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * طباعة فاتورة البيع
     */
    public function printReceipt($id)
    {
        $sale = Sale::with(['customer', 'saleItems.product', 'user'])
            ->findOrFail($id);
            
        return view('pos.receipt', compact('sale'));
    }

    public function fullscreen()
    {
        $products = Product::with(['category', 'brand', 'warehouses'])
            ->whereHas('warehouses', function($query) {
                $query->where('stock', '>', 0);
            })
            ->get();

        $categories = Category::all();
        $brands = Brand::all();
        $customers = Customer::all();
        $warehouses = Warehouse::all();
        $defaultWarehouse = Warehouse::first();

        return view('pos.fullscreen', compact(
            'products',
            'categories',
            'brands',
            'customers',
            'warehouses',
            'defaultWarehouse'
        ));
    }
}
