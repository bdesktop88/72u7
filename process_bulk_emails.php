<?php
// Generate a unique folder name using session or random string
session_start();  // Start a session for each user
$userFolder = 'mx_results/' . session_id();  // Unique folder based on session ID
if (!is_dir($userFolder)) {
    mkdir($userFolder, 0777, true);  // Create the folder if it doesn't exist
}

// Get uploaded file
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fileUpload'])) {
    // Read uploaded file
    $file = $_FILES['fileUpload']['tmp_name'];
    $emails = file($file, FILE_IGNORE_NEW_LINES);

    // MX categories
    $categories = ['Gmail', 'G Suite', 'Office365', 'Yahoo', 'AOL', 'Hotmail/MSN', 'Others'];
    $categoryFiles = [
        'Gmail' => $userFolder . '/gmail.txt',
        'G Suite' => $userFolder . '/gsuite.txt',
        'Office365' => $userFolder . '/office365.txt',
        'Yahoo' => $userFolder . '/yahoo.txt',
        'AOL' => $userFolder . '/aol.txt',
        'Hotmail/MSN' => $userFolder . '/hotmail_msn.txt',
        'Others' => $userFolder . '/others.txt'
    ];

    // Process emails
    foreach ($emails as $email) {
        $category = getEmailCategory(trim($email));  // Check email category (use existing function for MX check)

        // Append email to the corresponding category file
        file_put_contents($categoryFiles[$category], $email . PHP_EOL, FILE_APPEND);
    }

    // Create ZIP file for download
    $zipFile = $userFolder . '/mx_results.zip';
    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
        // Add each category file to the ZIP
        foreach ($categoryFiles as $file) {
            if (file_exists($file)) {
                $zip->addFile($file, basename($file));
            }
        }
        $zip->close();
    }

    // Provide download link
    echo "<h2>Processing Complete. Download the ZIP file:</h2>";
    echo "<a href='$zipFile' download>Download ZIP of Categorized Emails</a><br>";

    // Provide option to delete the folder after download
    echo "<a href='delete_folder.php?folder=" . urlencode($userFolder) . "'>Delete ZIP Folder to Save Space</a>";
}

// Function to categorize email
function getEmailCategory($email) {
    // MX records logic (reuse your existing categorization logic here)
    $domain = substr(strrchr($email, "@"), 1);
    $mxRecords = [];
    if (getmxrr($domain, $mxRecords)) {
        foreach ($mxRecords as $mx) {
            // Match for Gmail, G Suite, Office365, etc.
            if (strpos(strtolower($mx), 'google.com') !== false && $domain === 'gmail.com') {
                return 'Gmail';
            }
            if (strpos(strtolower($mx), 'google.com') !== false && $domain !== 'gmail.com') {
                return 'G Suite';
            }
            if (strpos(strtolower($mx), 'outlook.com') !== false || strpos(strtolower($mx), 'protection.outlook.com') !== false) {
                // Ensure Hotmail/MSN are excluded from Office365 category
                if ($domain !== 'hotmail.com' && $domain !== 'msn.com') {
                    return 'Office365';
                } else {
                    return 'Hotmail/MSN';  // Explicitly categorize Hotmail/MSN
                }
            }

            // Updated: Yahoo MX records check
            $yahooMXs = [
                'mx-vip2.mail.gq1.yahoo.com',  // Yahoo MX record
                'mta7.am0.yahoodns.net',      // Another Yahoo MX record
                'mta5.am0.yahoodns.net',      // Another Yahoo MX record
            ];

            foreach ($yahooMXs as $yahooMX) {
                if (strpos(strtolower($mx), $yahooMX) !== false) {
                    return 'Yahoo';
                }
            }

            // AOL MX records check
            $aolMXs = [
                'aolsmtp-in.odin.com',       // AOL MX record
                'mailin-02.mx.aol.com',      // AOL MX record
                'mailin-03.mx.aol.com',      // AOL MX record
                'mx-aol.mail.gm0.yahoodns.net'  // Additional AOL MX record
            ];

            foreach ($aolMXs as $aolMX) {
                if (strpos(strtolower($mx), $aolMX) !== false) {
                    return 'AOL';
                }
            }
        }
    }
    return 'Others';  // Default category
}
?>
