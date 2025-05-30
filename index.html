<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bulk MX Verifier Tool</title>
    <style>
        body {
            background-color: #111;
            color: white;
            font-family: Arial, sans-serif;
            text-align: center;
        }

        h1 {
            color: #e63946;
            margin-top: 40px;
        }

        form {
            margin-top: 40px;
            padding: 20px;
            background-color: #333;
            display: inline-block;
            border-radius: 8px;
        }

        input[type="file"], input[type="submit"] {
            margin: 10px;
            padding: 10px;
            border-radius: 5px;
            border: none;
        }

        input[type="submit"] {
            background-color: #e63946;
            color: white;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #d62828;
        }
    </style>
</head>
<body>
    <h1>Bulk MX Verifier Tool</h1>

    <form action="process_bulk_emails.php" method="post" enctype="multipart/form-data">
        <label for="fileUpload">Upload Bulk Email File (CSV/TXT):</label><br>
        <input type="file" name="fileUpload" id="fileUpload" required><br>
        <input type="submit" value="Upload and Verify">
    </form>

   <div id="progressBox" style="margin-top: 30px;">
    <p id="progressText">Preparing...</p>
</div>

<script>
function pollProgress() {
    fetch('mx_results/<?php echo session_id(); ?>/progress.log?' + new Date().getTime())
        .then(response => response.text())
        .then(text => {
            const last = text.trim().split('\n').pop();
            document.getElementById('progressText').innerText =
                last === 'done' ? '✅ Done!' : last;

            if (last !== 'done') {
                setTimeout(pollProgress, 1000);
            }
        })
        .catch(error => {
            console.error("Progress polling failed:", error);
        });
}
pollProgress();
</script>

 
