# 🏠 Property Purchase System - Files Checklist

## ✅ FILES TO CHECK ON LIVE SERVER

### 1. **Controller File** (MOST IMPORTANT)
**Path:** `platform/plugins/real-estate/src/Http/Controllers/PropertyPurchaseController.php`

**Check these lines:**
- Line 5: `use Botble\Base\Http\Controllers\BaseController;`
- Line 13: `class PropertyPurchaseController extends BaseController`
- Line 145: `page_title()->setTitle('Property Purchases');`
- Line 151: `return view('plugins/real-estate::property-purchases.index', compact('purchases'));`

**If these are NOT correct, replace the entire file with the correct version!**

---

### 2. **Admin View Files**
**Path:** `platform/plugins/real-estate/resources/views/property-purchases/`

**Required Files:**
- ✅ `index.blade.php` - Admin list page
- ✅ `show.blade.php` - Admin detail page

**Check:** Both files should exist in this directory

---

### 3. **Model File**
**Path:** `platform/plugins/real-estate/src/Models/PropertyPurchase.php`

**Check:**
- Line 8: `class PropertyPurchase extends BaseModel`
- Line 10: `protected $table = 're_property_purchases';`

---

### 4. **Routes File**
**Path:** `platform/plugins/real-estate/routes/web.php`

**Check around line 264-288:**
```php
// Property Purchase Admin Routes
Route::group(['prefix' => 'property-purchases', 'as' => 'property-purchases.'], function () {
    Route::get('/', [
        'as' => 'index',
        'uses' => 'PropertyPurchaseController@adminIndex',
        'permission' => 'account.index',
    ]);
    
    Route::get('{id}', [
        'as' => 'show',
        'uses' => 'PropertyPurchaseController@adminShow',
        'permission' => 'account.index',
    ]);
    
    Route::post('{id}/approve', [
        'as' => 'approve',
        'uses' => 'PropertyPurchaseController@approve',
        'permission' => 'account.edit',
    ]);
    
    Route::post('{id}/reject', [
        'as' => 'reject',
        'uses' => 'PropertyPurchaseController@reject',
        'permission' => 'account.edit',
    ]);
});
```

---

### 5. **Service Provider**
**Path:** `platform/plugins/real-estate/src/Providers/RealEstateServiceProvider.php`

**Check around line 350-360:**
```php
// Property Purchases
dashboard_menu()->registerItem([
        'id'          => 'cms-plugins-property-purchases',
        'priority'    => 26,
        'parent_id'   => null,
        'name'        => 'Property Purchases',
        'icon'        => 'fa fa-home',
        'url'         => route('property-purchases.index'),
        'permissions' => ['account.index'],
    ]);
```

---

### 6. **Database Table**
**Table Name:** `re_property_purchases`

**Check in phpMyAdmin:**
- Table should exist
- Should have 1 record (from your test)

---

## 🔧 STEPS TO FIX ON LIVE SERVER:

### Step 1: Check Controller File
```bash
# Open this file on live server:
platform/plugins/real-estate/src/Http/Controllers/PropertyPurchaseController.php

# Make sure line 5 has:
use Botble\Base\Http\Controllers\BaseController;

# Make sure line 13 has:
class PropertyPurchaseController extends BaseController

# Make sure line 145 has:
page_title()->setTitle('Property Purchases');
```

### Step 2: Clear Cache
Visit: `https://sspl20.com/clear-all-cache.php`

### Step 3: Test Admin Panel
Visit: `https://sspl20.com/realestate/public/admin/real-estate/property-purchases`

---

## 🚨 IF STILL NOT WORKING:

The issue is likely that the **Controller file on live server is different** from what we have here.

**Solution:**
1. Download the PropertyPurchaseController.php from this project
2. Upload it to live server (replace existing file)
3. Clear cache
4. Test again

---

## 📋 QUICK TEST COMMANDS:

### Test if table exists:
```sql
SELECT * FROM re_property_purchases;
```

### Test if route exists:
Visit: `https://sspl20.com/test-property-purchases.php`

---

## ✅ EXPECTED RESULT:

When you visit admin Property Purchases page, you should see:
- Page title: "Property Purchases"
- Table with columns: ID, User, Property, Price, Final Amount, Discount, Status, Date, Actions
- 1 record showing your test purchase (Vivek Avenue property)

---

## 🔍 DEBUGGING:

If error persists, check Laravel logs:
`storage/logs/laravel-2026-02-02.log`

Look for the exact error message about view not found.
