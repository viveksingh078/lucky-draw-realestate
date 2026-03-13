<?php
// Test form submission
require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use Botble\RealEstate\Models\LuckyDraw;

$draw = LuckyDraw::where('status', 'active')->first();

if (!$draw) {
    die('No active draw found. Please create an active draw first.');
}

$joinUrl = route('public.lucky-draws.join', $draw->id);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Form Submit</title>
    <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
    <style>
        body { font-family: Arial; padding: 20px; }
        .test-box { border: 1px solid #ccc; padding: 20px; margin: 10px 0; }
        button { padding: 10px 20px; font-size: 16px; cursor: pointer; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Form Submit Test</h1>
    
    <div class="test-box">
        <h3>Test Draw Info:</h3>
        <p><strong>Draw ID:</strong> <?php echo $draw->id; ?></p>
        <p><strong>Draw Name:</strong> <?php echo $draw->name; ?></p>
        <p><strong>Join URL:</strong> <?php echo $joinUrl; ?></p>
    </div>

    <div class="test-box">
        <h3>Test 1: Simple Form Submit</h3>
        <form action="<?php echo $joinUrl; ?>" method="POST" id="testForm1">
            <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
            <button type="submit" style="background: blue; color: white;">
                Click to Join Draw (Test 1)
            </button>
        </form>
    </div>

    <div class="test-box">
        <h3>Test 2: JavaScript Submit</h3>
        <button onclick="submitWithJS()" style="background: green; color: white;">
            Click to Join Draw (Test 2 - JS)
        </button>
    </div>

    <div class="test-box">
        <h3>Test 3: AJAX Submit</h3>
        <button onclick="submitWithAjax()" style="background: orange; color: white;">
            Click to Join Draw (Test 3 - AJAX)
        </button>
        <div id="ajaxResult"></div>
    </div>

    <div class="test-box">
        <h3>Debug Info:</h3>
        <p><strong>Logged In:</strong> <?php echo auth('account')->check() ? 'YES' : 'NO'; ?></p>
        <?php if (auth('account')->check()): ?>
            <?php $user = auth('account')->user(); ?>
            <p><strong>User:</strong> <?php echo $user->name; ?></p>
            <p><strong>Email:</strong> <?php echo $user->email; ?></p>
            <p><strong>Account Status:</strong> <?php echo $user->account_status; ?></p>
            <p><strong>Membership Status:</strong> <?php echo $user->membership_status ?? 'N/A'; ?></p>
            <p><strong>Draws Remaining:</strong> <?php echo $user->draws_remaining ?? 0; ?></p>
            <p><strong>Current Active Draw:</strong> <?php echo $user->current_active_draw_id ?? 'None'; ?></p>
        <?php else: ?>
            <p class="error">You are not logged in! <a href="<?php echo route('public.account.login'); ?>">Login here</a></p>
        <?php endif; ?>
    </div>

    <script>
        function submitWithJS() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo $joinUrl; ?>';
            
            const token = document.createElement('input');
            token.type = 'hidden';
            token.name = '_token';
            token.value = '<?php echo csrf_token(); ?>';
            
            form.appendChild(token);
            document.body.appendChild(form);
            
            console.log('Submitting form via JS...');
            form.submit();
        }

        function submitWithAjax() {
            const resultDiv = document.getElementById('ajaxResult');
            resultDiv.innerHTML = 'Submitting...';
            
            fetch('<?php echo $joinUrl; ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo csrf_token(); ?>',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                console.log('Response:', data);
                resultDiv.innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            })
            .catch(error => {
                console.error('Error:', error);
                resultDiv.innerHTML = '<p class="error">Error: ' + error.message + '</p>';
            });
        }

        // Log when page loads
        console.log('Test page loaded');
        console.log('Join URL:', '<?php echo $joinUrl; ?>');
        console.log('CSRF Token:', '<?php echo csrf_token(); ?>');
    </script>
</body>
</html>
