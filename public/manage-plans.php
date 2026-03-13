<?php
/**
 * Membership Plans Management
 * Temporary solution until autoload is refreshed
 */

// Bootstrap Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle($request = Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

// Check if admin is logged in
if (!auth()->guard('web')->check()) {
    die('Please login to admin panel first: <a href="/admin">Login</a>');
}

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

?>
<!DOCTYPE html>
<html>
<head>
    <title>Membership Plans Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Membership Plans Management</h2>
    <hr>

    <?php if ($action == 'list'): ?>
        <a href="?action=create" class="btn btn-primary mb-3">Add New Plan</a>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $plans = DB::table('membership_plans')->orderBy('sort_order')->get();
                foreach ($plans as $plan):
                ?>
                <tr>
                    <td><?= $plan->id ?></td>
                    <td><?= $plan->name ?></td>
                    <td>₹<?= number_format($plan->price, 2) ?></td>
                    <td><?= round($plan->duration_days / 30) ?> Months</td>
                    <td><?= $plan->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                    <td>
                        <a href="?action=edit&id=<?= $plan->id ?>" class="btn btn-sm btn-info">Edit</a>
                        <a href="?action=delete&id=<?= $plan->id ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this plan?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php elseif ($action == 'create'): ?>
        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $features = array_filter($_POST['features'] ?? []);
            DB::table('membership_plans')->insert([
                'name' => $_POST['name'],
                'slug' => \Illuminate\Support\Str::slug($_POST['name']),
                'description' => $_POST['description'],
                'price' => $_POST['price'],
                'duration_days' => $_POST['duration_days'],
                'features' => json_encode($features),
                'is_active' => $_POST['is_active'] ?? 1,
                'sort_order' => $_POST['sort_order'] ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            echo '<div class="alert alert-success">Plan created successfully!</div>';
            echo '<script>setTimeout(function(){ window.location="?action=list"; }, 1000);</script>';
        }
        ?>
        <form method="POST">
            <div class="mb-3">
                <label>Plan Name *</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Price (₹) *</label>
                    <input type="number" name="price" class="form-control" step="0.01" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Duration (Days) *</label>
                    <input type="number" name="duration_days" class="form-control" value="365" required>
                </div>
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label>Features</label>
                <div id="features">
                    <input type="text" name="features[]" class="form-control mb-2" placeholder="Feature 1">
                </div>
                <button type="button" class="btn btn-sm btn-secondary" onclick="addFeature()">Add Feature</button>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="0">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Status</label>
                    <select name="is_active" class="form-control">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Create Plan</button>
            <a href="?action=list" class="btn btn-secondary">Cancel</a>
        </form>

    <?php elseif ($action == 'edit' && $id): ?>
        <?php
        $plan = DB::table('membership_plans')->where('id', $id)->first();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $features = array_filter($_POST['features'] ?? []);
            DB::table('membership_plans')->where('id', $id)->update([
                'name' => $_POST['name'],
                'slug' => \Illuminate\Support\Str::slug($_POST['name']),
                'description' => $_POST['description'],
                'price' => $_POST['price'],
                'duration_days' => $_POST['duration_days'],
                'features' => json_encode($features),
                'is_active' => $_POST['is_active'] ?? 1,
                'sort_order' => $_POST['sort_order'] ?? 0,
                'updated_at' => now(),
            ]);
            echo '<div class="alert alert-success">Plan updated successfully!</div>';
            echo '<script>setTimeout(function(){ window.location="?action=list"; }, 1000);</script>';
        }
        $features = json_decode($plan->features, true) ?: [''];
        ?>
        <form method="POST">
            <div class="mb-3">
                <label>Plan Name *</label>
                <input type="text" name="name" class="form-control" value="<?= $plan->name ?>" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Price (₹) *</label>
                    <input type="number" name="price" class="form-control" step="0.01" value="<?= $plan->price ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Duration (Days) *</label>
                    <input type="number" name="duration_days" class="form-control" value="<?= $plan->duration_days ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3"><?= $plan->description ?></textarea>
            </div>
            <div class="mb-3">
                <label>Features</label>
                <div id="features">
                    <?php foreach ($features as $feature): ?>
                    <input type="text" name="features[]" class="form-control mb-2" value="<?= $feature ?>">
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn btn-sm btn-secondary" onclick="addFeature()">Add Feature</button>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= $plan->sort_order ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Status</label>
                    <select name="is_active" class="form-control">
                        <option value="1" <?= $plan->is_active ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= !$plan->is_active ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Update Plan</button>
            <a href="?action=list" class="btn btn-secondary">Cancel</a>
        </form>

    <?php elseif ($action == 'delete' && $id): ?>
        <?php
        $count = DB::table('re_accounts')->where('membership_plan_id', $id)->count();
        if ($count > 0) {
            echo '<div class="alert alert-danger">Cannot delete! ' . $count . ' accounts are using this plan.</div>';
        } else {
            DB::table('membership_plans')->where('id', $id)->delete();
            echo '<div class="alert alert-success">Plan deleted successfully!</div>';
        }
        echo '<script>setTimeout(function(){ window.location="?action=list"; }, 1500);</script>';
        ?>
    <?php endif; ?>

</div>

<script>
function addFeature() {
    var input = document.createElement('input');
    input.type = 'text';
    input.name = 'features[]';
    input.className = 'form-control mb-2';
    input.placeholder = 'Enter feature';
    document.getElementById('features').appendChild(input);
}
</script>
</body>
</html>
