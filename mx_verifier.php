<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the email from the POST request
    $email = isset($_POST['email']) ? $_POST['email'] : '';

    // Check if the email is valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['error' => 'Invalid email address']);
        exit;
    }

    // Extract domain from the email
    $domain = substr(strrchr($email, "@"), 1);

    // Perform MX record lookup for the domain
    $mxRecords = [];
    if (getmxrr($domain, $mxRecords)) {
        // Log the MX records to check what's returned
        error_log("MX Records for $domain: " . implode(", ", $mxRecords));

        // Debugging: Return MX records in the response for inspection
        $debugOutput = "MX Records for $domain: " . implode(", ", $mxRecords);

        // Known MX records for specific services
        $gsuiteMX = 'aspmx.l.google.com';  // G Suite MX record
        $gmailMX = 'gmail-smtp-in.l.google.com';  // Gmail MX record
        $yahooMXs = [
            'mx-vip2.mail.gq1.yahoo.com', // Yahoo MX record
            'mta7.am0.yahoodns.net',     // Another Yahoo MX record
            'mta5.am0.yahoodns.net',     // Another Yahoo MX record
        ]; 
        $aolMXs = [
            'aolsmtp-in.odin.com',       // AOL MX record
            'mailin-02.mx.aol.com',      // AOL MX record
            'mailin-03.mx.aol.com',      // AOL MX record
            'mx-aol.mail.gm0.yahoodns.net',      // Added another possible AOL MX record
        ]; 
        $office365MXs = [
            'mail.protection.outlook.com',  // Office 365 common MX record
            'outlook.office365.com',        // Another common Office 365 MX record
            'smtp.office365.com'            // Another possible MX for Office365
        ];
        $hotmailMSNMXs = [
            'hotmail.com',                  // Hotmail
            'msn.com',                      // MSN
            'smtp.live.com',                // SMTP server for Hotmail/MSN
            'outlook.live.com',             // Outlook Live MX for Hotmail/MSN
        ];

        // Default category is 'Others'
        $category = 'Others';

        // Check if the email is from Hotmail/MSN directly by domain
        if ($domain === 'hotmail.com' || $domain === 'msn.com') {
            $category = 'Hotmail/MSN';
        }

        // Check if the MX records match known services
        foreach ($mxRecords as $mx) {
            // Check for Gmail (not G Suite)
            if (strpos(strtolower($mx), 'google.com') !== false && $domain === 'gmail.com') {
                $category = 'Gmail';
                break;
            }
            // Check for G Suite (Google Apps) with a custom domain
            elseif (strpos(strtolower($mx), 'google.com') !== false && $domain !== 'gmail.com') {
                $category = 'G Suite';
                break;
            }
            // Check for Office 365 (make sure Hotmail/MSN are excluded)
            elseif (strpos(strtolower($mx), 'outlook.com') !== false || strpos(strtolower($mx), 'protection.outlook.com') !== false) {
                // Make sure we don't categorize Hotmail/MSN as Office365
                if ($category !== 'Hotmail/MSN') {
                    $category = 'Office365';
                }
                break;
            }
            // Check for Yahoo
            elseif (in_array(strtolower($mx), $yahooMXs)) {
                $category = 'Yahoo';
                break;
            }
            // Check for AOL
            elseif (in_array(strtolower($mx), $aolMXs)) {
                $category = 'AOL';
                break;
            }
        }

        // Return debug information with the category
        echo json_encode([
            'email' => $email,
            'category' => $category,
            'mxRecords' => $debugOutput
        ]);
    } else {
        echo json_encode(['error' => 'MX records not found for this domain']);
    }
    exit;
}
?>
