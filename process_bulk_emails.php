<?php
session_start();
$userFolder = 'mx_results/' . session_id();
if (!is_dir($userFolder)) {
    mkdir($userFolder, 0777, true);
}

// File upload check
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fileUpload'])) {
    $file = $_FILES['fileUpload']['tmp_name'];
    if (!file_exists($file) || filesize($file) === 0) {
        die("<p>No valid file uploaded or file is empty.</p>");
    }

    // Read & filter emails
    $emails = array_filter(array_map('trim', file($file)), function ($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    });

    // MX Categories
    $categories = ['Gmail', 'G Suite', 'Office365', 'Yahoo', 'AOL', 'Hotmail/MSN', 'Others'];
    $categoryFiles = [];
    foreach ($categories as $cat) {
        $filename = strtolower(str_replace(['/', ' '], ['_', '_'], $cat)) . '.txt';
        $categoryFiles[$cat] = "$userFolder/$filename";
    }

    // Progress setup
    $progressFile = "$userFolder/progress.log";
    file_put_contents($progressFile, "Starting...\n");

    $total = count($emails);
    foreach ($emails as $i => $email) {
        $category = getEmailCategory($email);
        file_put_contents($categoryFiles[$category], $email . PHP_EOL, FILE_APPEND);

        // Update progress
        $percent = round((($i + 1) / $total) * 100);
        file_put_contents($progressFile, "Processing: $percent%\n");
    }

    // Create ZIP archive
    $zipFile = "$userFolder/mx_results.zip";
    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
        foreach ($categoryFiles as $file) {
            if (file_exists($file)) {
                $zip->addFile($file, basename($file));
            }
        }
        $zip->close();
    }

    // Mark completion
    file_put_contents($progressFile, "done\n");

    echo "<h2 style='color:white;'>✅ Processing Complete!</h2>";
    echo "<a href='$zipFile' download>Download ZIP of Categorized Emails</a><br>";
    echo "<a href='delete_folder.php?folder=" . urlencode($userFolder) . "'>Delete ZIP Folder to Save Space</a>";
} else {
    echo "No file uploaded.";
}

// Email categorization logic
function getEmailCategory($email) {
    $domain = substr(strrchr($email, "@"), 1);
    $mxRecords = [];
    if (getmxrr($domain, $mxRecords)) {
        foreach ($mxRecords as $mx) {
            $mx = strtolower($mx);
            if (strpos($mx, 'google.com') !== false) {
                return $domain === 'gmail.com' ? 'Gmail' : 'G Suite';
            }
            if (strpos($mx, 'outlook.com') !== false || strpos($mx, 'protection.outlook.com') !== false) {
                return in_array($domain, ['hotmail.com', 'msn.com']) ? 'Hotmail/MSN' : 'Office365';
            }
            $yahooMXs = ['mx-vip2.mail.gq1.yahoo.com', 'mta7.am0.yahoodns.net', 'mta5.am0.yahoodns.net'];
            foreach ($yahooMXs as $yahooMX) {
                if (strpos($mx, $yahooMX) !== false) return 'Yahoo';
            }
            $aolMXs = ['aolsmtp-in.odin.com', 'mailin-02.mx.aol.com', 'mailin-03.mx.aol.com', 'mx-aol.mail.gm0.yahoodns.net'];
            foreach ($aolMXs as $aolMX) {
                if (strpos($mx, $aolMX) !== false) return 'AOL';
            }
        }
    }
    return 'Others';
}
?>
